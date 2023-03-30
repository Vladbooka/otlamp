<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Хелпер для отправки уведомлений
*
 * @package    mod_otcourselogic
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_otcourselogic\apanel\actions\send_message\helpers;

require_once($CFG->dirroot . '/lib/authlib.php');

use auth_plugin_base;
use context_course;
use context_user;
use core_course_list_element;
use html_writer;
use moodle_exception;
use moodle_url;
use stdClass;
use mod_otcourselogic\state_checker;
use mod_otcourselogic\apanel\helper;
use core\message\message;

/**
 * Контроллер отправки уведомлений
 */
class message_sender
{
    /**
     * Получение массива ролей для рассылок
     * 
     * @return string[]
     */
    public static function get_roles()
    {
        return ['teacher', 'student', 'curator'];
    }
    
    /**
     * Получение массива типов сообщений
     * 
     * @return string[]
     */
    public static function get_message_types()
    {
        return ['activate', 'deactivate', 'periodic'];
    }
    
    /**
     * Инициализация рассылки сообщений при смене состояния элемента курса
     *
     * @param int $instanceid - ID экземпляра модуля курса
     * @param int $userid - ID пользователя
     * @param int $courseid - ID курса
     *
     * @return void
     */
    public static function sending_messages($instance, $userid, $course, $action_instance, &$pool)
    {
        global $DB;

        // Фильтрация пользователя
        if ( ! $user = $DB->get_record('user', ['id' => $userid, 'deleted' => 0]) )
        {// Указанный пользователь не найден
            print_error('nousersfound', 'moodle');
        }

        $otcourselogic_state = $DB->get_record('otcourselogic_state', ['instanceid' => $instance->id, 'userid' => $userid]);

        // Процесс отправки сообщения
        self::sending_messages_process($userid, $otcourselogic_state, $instance, $course, $action_instance, $pool);
    }
     
    /**
     * Получение типа уведомления, высылаемого пользователю
     *
     * Тип уведомления определяется исходя из текущего состояния элемента курса
     * для указанного пользователя
     *
     * @param int $instanceid - ID элемента курса
     * @param int $userid - ID пользователя
     *
     * @return string - Тип уведомления
     *
     * @throws moodle_exception - При ошибке получения состояния
     */
    private static function get_messagetype($instanceid, $userid)
    {
        // Получение состояния элемента курса для целевого пользователя
        $userstate = state_checker::get_state_nocache($instanceid, $userid);
         
        switch( $userstate )
        {
            case 0:
                return 'deactivate';
                break;
            case 1:
                return 'activate';
                break;
            default:
                throw new moodle_exception('error_wrong_status', 'mod_otcourselogic');
                break;
        }
    }

    /**
     * Подготовка сообщения
     *
     * Заменяет макроподстановки в тексте сообщения
     *
     * @param string $message - Текст сообщения
     * @param \stdClass $data - Данные макроподстановок
     *
     * @return \stdClass - Текст с подстановками
     */
    private static function prepeare_message($message, $data)
    {
        // Получение локализованного сообщения
        $message = format_text($message, FORMAT_MOODLE);
         
        // Обработка макроподстановок
        if ( ! empty($data) )
        {// Макроподстановки определены
            foreach ( $data as $placeholder => $value )
            {
                // Нормализация макроподстановки
                $placeholder = '{'.strtoupper($placeholder).'}';
                // Подстановка сообщения
                $message = str_replace($placeholder, $value, $message);
            }
        }
        return $message;
    }

    /**
     * Получить пользователей, которые определены как преподаватели
     * Пользователи проверяются правом 'mod/otcourselogic:is_teacher'
     *
     * @param context $context - Контекст курса
     *
     * @return array - Массив пользователей
     */
    private static function get_teachers($context)
    {
        return (array)get_enrolled_users(
            $context, 'mod/otcourselogic:is_teacher', 0, 'u.*', null, 0, 0, true);
    }

    /**
     * Получить идентификаторы пользователей - кураторов
     *
     * Кураторы определяются соответствующим правом доступа в контексте пользователя,
     * инициализировавшего рассылку
     *
     * @param int $userid - ID пользователя, для которого требуется получить кураторов
     *
     * @return array
     */
    private static function get_curators($userid)
    {
        global $DB;
        $curators = [];

        // Получение пользователей, имеющих роль в контексте пользователя
        $contexts = (array)$DB->get_records_sql(
            "SELECT DISTINCT(ra.userid)
                FROM {role_assignments} ra, {context} c, {user} u
                WHERE u.id = ?
                    AND ra.contextid = c.id
                    AND c.instanceid = u.id
                    AND c.contextlevel = ".CONTEXT_USER,
            [$userid]
            );
        $usercontext = context_user::instance($userid);
        foreach ( $contexts as $context )
        {
            // Проверка доступа
            if ( has_capability('mod/otcourselogic:is_curator', $usercontext, $context->userid) )
            {
                $curators[$context->userid] = $context->userid;
            }
        }

        return $curators;
    }

    /**
     * Процесс отправки сообщений по целевой персоне
     *
     * @param string $type - Тип рассылки сообщения:
     *      activate - Рассылка при активации элемента для пользователя
     *      deactivate - Рассылка при деактивации элемента для пользователя
     *      periodic - Рассылка периодических уведомлений
     * @param int $instanceid - ID элемента курса
     * @param int $userid - ID пользователя
     * @param int $userid - ID курса
     * @param \stdClass $otcourselogic - Конфигурация элемента курса
     * @param \stdClass $otcourselogic_state - Состояние пользователя
     *
     * @return boolean - Результат рассылки
     */
    private static function sending_messages_process($userid, $otcourselogic_state, $instance, $course, $action_instance, $pool)
    {
        global $DB;

        // Пользователь
        $user = $DB->get_record('user', ['id' => $userid]);
        
        // Элемент курса
        $cm = get_coursemodule_from_instance('otcourselogic', $instance->id, $course->id);
        
        // Контекст курса
        $context = context_course::instance($course->id);

        // Опции экшна
        $options = unserialize(base64_decode($action_instance->options));
        
        if ( is_siteadmin($userid) )
        {
            // глобальных админов пропускаем, так как у них есть все права и невозможно точно определить его роль в курсе
            return;
        }
        
        // Инициализация рассылки
        if ( has_capability('mod/otcourselogic:is_student', $context, $userid) )
        {// Пользователь является студентом
            // Генерация сообщений
            $eventdata = self::create_message($user, $course, $instance, $cm, $action_instance, $pool);
            if ( $eventdata )
            {// Отправка сообщения
                self::send_msg($options->recipient, $eventdata, $userid, $context);
            }
        }
    }

    /**
     * Формирование сообщения для отправки
     *
     * @param unknown $user - Пользователь для макроподстановок
     * @param unknown $course - Объект курса для макроподстановок
     * @param unknown $moduledata - Данные конфигурации элемента курса
     * @param unknown $cm - Объект модуля курса для макроподстановок
     *
     * @return NULL|stdClass
     */
    private static function create_message($user, $course, $instance, $cm, $action_instance, $pool)
    {
        global $CFG, $DB;

        // Подготовка сообщения
        $message = null;

        // Базовые параметры для генерации сообщения
        $deliveryoptions = unserialize(base64_decode($action_instance->options));
        
        $full = 'fullmessage';
        $short = 'shortmessage';
        
        $msg = $deliveryoptions->fullmessage;
        $strip_msg_text = strip_tags($msg['text']);

        if( ! empty($strip_msg_text) || ! empty($deliveryoptions->$short) )
        {// Текст писем указан

            $sender = 'sender';
            if ( empty($deliveryoptions->sender) )
            {
                $sender = 'admin';
            }
            $userfrom = get_admin();

            switch ( $deliveryoptions->$sender )
            {
                case 'sender' :
                    $userfrom = $user;
                    break;
                case 'teacher' :
                    $teacherfield = 'sender_user';
                    if ( ! empty($deliveryoptions->$teacherfield) )
                    {
                        $course = new core_course_list_element($course);
                        $coursecontacts = $course->get_course_contacts();
                        if ( isset($coursecontacts[$deliveryoptions->$teacherfield]) )
                        {
                            $teacher = $DB->get_record('user', ['id' => $deliveryoptions->$teacherfield]);
                            if ( $teacher )
                            {
                                $userfrom = $teacher;
                                break;
                            }
                        }
                    }
            }

            // формирование сообщения
            $message = new message();
            $message->component = 'mod_otcourselogic';
            $message->name = 'otcourselogic_reminders';
            $message->userfrom = $userfrom;
            $message->smallmessage = $deliveryoptions->$short;
            $message->fullmessage = format_text_email($msg['text'], FORMAT_MOODLE);
            $message->fullmessagehtml = $msg['text'];
            $message->subject = get_string(
                'otcourselogic_email_subject',
                'mod_otcourselogic'
                ).' '.format_string($course->fullname);
            $message->fullmessageformat = FORMAT_HTML;

            // Формирование сообщений с подстановками
            $message->fullmessage = helper::replace_macrosubstitutions($message->fullmessage, $instance, $course, $user, $pool);
            $message->fullmessagehtml = helper::replace_macrosubstitutions($message->fullmessagehtml, $instance, $course, $user, $pool);
            $message->smallmessage = helper::replace_macrosubstitutions($message->smallmessage, $instance, $course, $user, $pool);
        }
        return $message;
    }
     
    /**
     * Рассылка сообщения роли
     *
     * @param string $role - Роль для рассылки
     * @param stdClass $eventdata - Объект сообщения
     * @param int $userid - Пользователь, инициализировавший рассылку
     * @param context $context - Контекст курса
     *
     * @return boolean возвращает false, если курс не был найден
     *
     * @throws moodle_exception
     */
    private static function send_msg($role, $eventdata, $userid, $context)
    {
        global $DB;

        // Результат проведения рассылки
        $messagesend = true;

        switch( $role )
        {
            // Рассылка студенту
            case 'student':
                $eventdata->userto = $userid;
                $messagesend = ( message_send($eventdata) & $messagesend );
                break;
                // Рассылка преподавателям курса
            case 'teacher':
                // Получение преподавателей
                $teachers = self::get_teachers($context);

                // Отправка сообщения каждому преподавателю
                foreach ( $teachers as $teacherid => $teacher )
                {
                    // Отправка сообщения
                    $eventdata->userto = $teacherid;
                    $messagesend = ( message_send($eventdata) & $messagesend );
                }
                break;
                // Рассылка кураторам
            case 'curator':
                // Получение кураторов
                $curators = self::get_curators($userid);

                // Отправка сообщения каждому куратору
                foreach( $curators as $curatorid )
                {
                    // Отправка сообщения
                    $eventdata->userto = $curatorid;
                    $messagesend = ( message_send($eventdata) & $messagesend );
                }
                break;
            default:
                throw new moodle_exception('error_wrong_person', 'mod_otcourselogic');
                break;
        }
    }
}