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
 * Модуль Логика курса. Перехватчик событий системы.
*
 * @package    mod_otcourselogic
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_otcourselogic;

use completion_info;
use stdClass;
use mod_otcourselogic\apanel\helper;

require_once($CFG->dirroot. '/mod/otcourselogic/lib.php');

defined('MOODLE_INTERNAL') || die();

/**
 * Обработчик событий для plagiarism_apru
 */
class observer
{
    /**
     * Обработка события просмотра курса пользователем
     *
     * @param \core\event\course_viewed - Событие просмотра курса
     *
     * @return void
     */
    public static function course_viewed(\core\event\course_viewed $event)
    {
        global $DB;

        $data = $event->get_data();

        // Определение базовых данных
        $userid = (int)$data['userid'];
        $courseid = (int)$data['courseid'];

        // Получение всех элементов, для которых включен перерасчет состояния
        $instances = (array)$DB->get_records('otcourselogic', ['course' => $courseid, 'catchcourseviewed' => 1]);

        // Определение состояния элементов для целевого пользователя
        foreach ( $instances as $instance )
        {
            if ( ! empty($instance->protect) )
            {
                // Защита от случайных срабатываний
                $cm = get_coursemodule_from_instance('otcourselogic', $instance->id, $courseid);
                if ( empty($cm->availability) )
                {
                    // Нет ограничений доступа
                    continue;
                }
            }
            
            // Проверяем статус элемента для пользователя
            otcourselogic_check_user_state((int)$instance->id, $courseid, $userid);
        }
    }

    /**
     * Обработка события изменения состояния элемента курса
     *
     * @param \mod_otcourselogic\event\state_switched - Событие изменения состояния
     *
     * @return void
     */
    public static function state_switched(\mod_otcourselogic\event\state_switched $event)
    {
        global $DB;

        // Получение данных события
        $data = $event->get_data();

        // Определение базовых данных
        $courseid = (int)$data['courseid'];
        $current_instance = (object)$data['other']['instance'];
        $instanceid = (int)$data['other']['instance']['id'];
        $new_status = (bool)$data['other']['state'];
        $userstateobj = (object)$data['other']['userstateobj'];
        $userid = (int)$data['relateduserid'];
        $cm = get_coursemodule_from_instance('otcourselogic', $instanceid, $courseid);
        $currentinstance = $DB->get_record(
            'otcourselogic',
            [
                'id' => $instanceid,
            ]
        );
        
        // Обновление статуса выполнения модуля пользователем
        $completion = new completion_info(get_course($courseid));
        
        // Включено выполнение по оценке и оценивание модуля разрешено
        $completiongradingchecked = ( ! is_null($cm->completiongradeitemnumber) && $currentinstance->grading == 1 );
        // Выключено выполнение по оценке
        $completionnotgrading = is_null($cm->completiongradeitemnumber);
        
        if ( $cm->completion == COMPLETION_TRACKING_AUTOMATIC )
        {
            $completion->update_state($cm, COMPLETION_UNKNOWN, $userid);
        }
        get_fast_modinfo($courseid, $userid, true);
        
        // Проверяем, скрыт ли модуль
        if ( ! empty($cm->visible) && ! empty($currentinstance) )
        {
            // Получение всех элементов курса, для которых требуется обработка по событию
            $instances = (array)$DB->get_records(
                'otcourselogic',
                [
                    'course' => $courseid,
                    'catchstatechange' => 1
                ]
                );
            // Фильтрация текущего элемента курса
            unset($instances[$instanceid]);
    
            // Определение состояния элементов для целевого пользователя
            foreach ( $instances as $instance )
            {
                // Получение контроллера состояний
                $statechecker = otcourselogic_get_state_checker();
                // Определение состояния элемента для целевого пользователя
                $statechecker->check_cm_user($instance->id, $userid);
    
            }
            
            // Текущее время для проверки отсрочки, если она есть
            $currentime = time();
            
            // Получение обработчиков инстанса
            $processors = helper::get_processors($current_instance->id, true);
            foreach ( $processors as $processor )
            {
                // Обновление состояния процессора для пользователя
                helper::refresh_processor_user_state($processor->id, $userstateobj);
                
                // Обработка не периодичных обработчиков
                if ( empty($processor->periodic) && empty($processor->delay) )
                {
                    $options = unserialize(base64_decode($processor->options));
                    if ( $options['on'] == 'otcourselogic_activate' )
                    {
                        $trigger_status = 1;
                    } else 
                    {
                        $trigger_status = 0;
                    }
                    
                    // Проверка статуса срабатывания обработчика и отсрочки выполнения обработчика
                    if ( (intval($new_status) == $trigger_status) )
                    {
                        // Обработчик срабатывает на тот статус, на который был переведен пользователь
                        $actions = helper::get_actions($processor->id, true);
                        $actions_pool = [];
                        $status = true;
                        foreach ( $actions as $action )
                        {
                            $action_object = helper::get_action_object($action->type, true);
                            $action_object->set_record($action);
                            $status = (bool)$action_object->execute($userid, $current_instance, get_course($courseid), $actions_pool, $action) && $status;
                        }
                        
                        // Сохранение записи в лог
                        helper::save_processor_log($processor, $userid, $status);
                    }
                }
            }
        }
    }

    /**
     * Обработка события подписания пользователя на курс
     *
     * @param \core\event\user_enrolment_created - Событие подписания пользователя
     *
     * @return void
     */
    public static function user_enrolment_created(\core\event\user_enrolment_created $event)
    {
        global $DB;

        $data = $event->get_data();

        // Определение базовых данных
        $userid = (int)$data['relateduserid'];
        $courseid = (int)$data['courseid'];

        // Получение всех элементов логики курса для целевого курса
        $instances = (array)$DB->get_records('otcourselogic', ['course' => $courseid]);

        // Определение состояния элементов для целевого пользователя
        foreach ( $instances as $instance )
        {
            if ( ! empty($instance->protect) )
            {
                // Защита от случайных срабатываний
                $cm = get_coursemodule_from_instance('otcourselogic', $instance->id, $courseid);
                if ( empty($cm->availability) )
                {
                    // Нет ограничений доступа
                    continue;
                }
            }
            
            otcourselogic_check_user_state((int)$instance->id, $courseid, $userid);
        }
    }

    
    /**
     * Обработка события присвоения роли пользователю
     *
     * @param \core\event\role_assigned - Событие назначения роли
     *
     * @return void
     */
    public static function role_assigned(\core\event\role_assigned $event)
    {
        global $DB, $CFG;
         
        $data = $event->get_data();
         
        // Определение базовых данных
        $userid = (int)$data['relateduserid'];
        $courseid = (int)$data['courseid'];
        
        /**
         * Если в курсе есть AUTOENROL способ записи на курс в активном режиме, то сообщения не отправляются
         * 1) Вызывается require_login
         * 2) Срабатывает autoenrol
         * 3) ЛК ловит событие назначение роли
         * 4) вызывается метод email_to_user, который вызывает метод get_renderer и устанавливает текущую тему
         * 5) затем в require_login еще раз вызывается метод get_renderer и запрос падает с эксепшном, тк тему менять нельзя
         */
        $enrol_instances = $DB->get_records('enrol', ['courseid' => $courseid, 'enrol' => 'autoenrol', 'status' => 0]);
        if ( ! empty($enrol_instances) )
        {
            return true;
        }
        
        // Получение всех элементов логики курса для целевого курса
        $instances = (array)$DB->get_records('otcourselogic', ['course' => $courseid]);
        
        // Определение состояния элементов для целевого пользователя
        foreach ( $instances as $instance )
        {
            if ( ! empty($instance->protect) )
            {
                // Защита от случайных срабатываний
                $cm = get_coursemodule_from_instance('otcourselogic', $instance->id, $courseid);
                if ( empty($cm->availability) )
                {
                    // Нет ограничений доступа
                    continue;
                }
            }
            
            otcourselogic_check_user_state((int)$instance->id, $courseid, $userid);
        }
    }

    /**
     * Обработка события отписания пользователя с курса
     *
     * @param \core\event\user_enrolment_deleted - Событие отписания пользователя
     *
     * @return void
     */
    public static function user_enrolment_deleted(\core\event\user_enrolment_deleted $event)
    {
        global $DB;

        // Определение базовых данных
        $data = $event->get_data();
        $userid = (int)$data['relateduserid'];
        $courseid = (int)$data['courseid'];

        // Получение всех элементов логики курса для целевого курса
        $instances = (array)$DB->get_records('otcourselogic', ['course' => $courseid]);

        // Удаление состояния элементов для целевого пользователя
        foreach ( $instances as $instance )
        {
            // Получение контроллера состояний
            $statechecker = otcourselogic_get_state_checker();
            
            // Очистка состояния элемента для целевого пользователя
            $statechecker->remove_cm_user($instance->id, $userid);
        }
    }

    /**
     * Обработка события удаления элемента модуля курса
     *
     * @param \core\event\course_module_deleted - Событие удаления элемента курса
     *
     * @return void
     */
    public static function course_module_deleted(\core\event\course_module_deleted $event)
    {
        global $DB;
        $data = $event->get_data();

        if ( isset($data['other']['modulename']) && $data['other']['modulename'] == 'otcourselogic' )
        {// Событие удаления элемента "Логика курса"

            // Получение идентификатора экземпляра
            $instanceid = (int)$data['other']['instanceid'];
            $courseid = (int)$data['courseid'];
            
            $cm = get_coursemodule_from_instance('otcourselogic', $instanceid, $courseid);
            if ( ! empty($instanceid) )
            {// Идентификатор указан
                
                $instance = $DB->get_record('otcourselogic', ['id' => $instanceid]);
                if ( ! empty($instance->protect) && empty($cm->availability) )
                {
                    // Защита от случайных срабатываний
                    return true;
                }
                // Получение контроллера состояний
                $statechecker = otcourselogic_get_state_checker();
                
                // Очистка состояний элемента
                $statechecker->remove_cm($instanceid);
            }
        }
    }
}
