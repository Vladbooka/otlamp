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
 * Хелпер
*
 * @package    mod_otcourselogic
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace mod_otcourselogic\apanel\actions\enrol_to_course\helpers;

require_once($CFG->dirroot . '/mod/quiz/locallib.php');
require_once($CFG->dirroot . '/lib/authlib.php');

use core_privacy\local\request\approved_contextlist;
use context_module;
use core_user;
use dml_exception;
use stdClass;
use context_course;

/**
 * Контроллер записи на курс
 */
class enrol
{
    /**
     * Список пользователей, были записаны курс.
     * Хранится для избежания циклических записей на курс
     * 
     * @var array
     */
    protected static $enroled_users = [];
    
    /**
     * Запись пользователя на курс
     * 
     * @param stdClass $instance
     * @param int $userid
     * @param stdClass $course
     * @param stdClass $action_instance
     * 
     * @return bool
     */
    public static function enrol_to_course($instance, $userid, $course, $action_instance, &$pool)
    {
        global $DB;
        
        // Получение данных из инстанса
        $data = unserialize(base64_decode($action_instance->options));
        try
        {
            $course_to_enrol = get_course($data->course);
        } catch ( dml_exception $e )
        {
            // Курс не существует
            return false;
        }
        
        // Получение контекста курса, где сработала логика курса
        $context = context_course::instance($course->id);
        
        if ( ! has_capability('mod/otcourselogic:is_student', $context, $userid) )
        {// Пользователь не является студентом
            return true;
        }
        
        // контекст курса, куда записываем пользователя
        $contextto = context_course::instance($course_to_enrol->id);
        
        if ( array_key_exists("{$userid}_{$course_to_enrol->id}", static::$enroled_users) )
        {
            // Формирование события о цикличности записи на курс
            $context = context_module::instance($instance->id);
            $eventdata = [
                'courseid' => $course->id,
                'context' => $context,
                'relateduserid' => $userid,
                'objectid' => $action_instance->id,
                'other' => [
                    'cyclecourseid' => $course_to_enrol->id
                ]
            ];
            $event = \mod_otcourselogic\event\enrol_to_course_cycle::create($eventdata);
            $event->trigger();
            return false;
        } else 
        {
            static::$enroled_users["{$userid}_{$course_to_enrol->id}"] = 1;
        }
        
        // Поиск MANUAL плагинов записи на курс
        if ( ! $enrol_instances = $DB->get_records('enrol', ['courseid' => $course_to_enrol->id, 'enrol' => 'manual', 'status' => 0]) )
        {
            return false;
        }
        
        // Способ записи на курс
        $enrol_instance = array_shift($enrol_instances);
        
        // У пользователя уже есть активная подписка в курсе
        $is_enrolled = is_enrolled($contextto, $userid);
        
        if ( $is_enrolled && empty($data->reenrol) )
        {
            // Пользователь уже подписан, а галка перезаписи не стоит
            return false;
        }
        
        // Восстановление оценок
        $recover_grades = (bool)$data->recover;
        
        if ( $is_enrolled && ! empty($data->reenrol) )
        {// Пользователь записан на курс, но надо перезаписать
            
            // Параметры для селекта
            $params = [
                'userid' => $userid,
                'courseid' => $course_to_enrol->id
            ];
            
            // Формирование запроса
            $sql = "SELECT e.*
              FROM {enrol} e
              JOIN {user_enrolments} ue ON (e.id = ue.enrolid)
              WHERE e.courseid = :courseid AND ue.userid = :userid;";
            
            // Получение активных подписок студента
            $user_enrolments = $DB->get_records_sql($sql, $params);
            if ( ! empty($user_enrolments) )
            {
                $plugins = [];
                foreach ( $user_enrolments as $user_enrolment )
                {
                    if ( ! empty($plugins[$user_enrolment->enrol]) )
                    {
                        $pl = $plugins[$user_enrolment->enrol];
                    } else
                    {
                        $pl = $plugins[$user_enrolment->enrol] = enrol_get_plugin($user_enrolment->enrol);
                    }
                    $pl->unenrol_user($user_enrolment, $userid);
                }
            }
        }
        
        if ( empty($recover_grades) && ! empty($data->clear) )
        {
            // Восстановление оценок не включено, необходимо очистить все задания и тесты для пользователя
            self::delete_course_modules_info($userid, $course_to_enrol);
        }
        
        
        $startdate = time();
        if ( ! empty($enrol_instance->enrolstartdate) )
        {
            $startdate = $enrol_instance->enrolstartdate;
        }
        $roleid = $enrol_instance->roleid;
        if ( ! empty($data->role) )
        {
            $roleid = $data->role;
        }
        
        // Получение плагина способа записи на курс
        $plugin = enrol_get_plugin('manual');
        
        // Создание подписки пользвоателя
        $plugin->enrol_user(
                $enrol_instance,
                $userid,
                $roleid,
                $startdate,
                $enrol_instance->enrolenddate,
                0,
                $recover_grades
                );
        
        return true;
    }
    
    /**
     * Удаление всех попыток пользователя в курсе
     * 
     * @param string $userid
     * @param stdClass $course_to_enrol
     * 
     * @return void
     */
    public static function delete_course_modules_info($userid, stdClass $course_to_enrol)
    {
        global $DB;
        
        if ( ! $user = $DB->get_record('user', ['id' => $userid]) )
        {
            // Пользователь не существует
            return false;
        }
        try 
        {
            $course = get_course($course_to_enrol->id);
        } catch ( dml_exception $e )
        {
            // Курс не существует
            return false;
        }
        
        
        // Очистка тестов
        $quiz_all = $DB->get_records('quiz', ['course' => $course_to_enrol->id]);
        if ( ! empty($quiz_all) )
        {
            foreach ( $quiz_all as $quiz )
            {
                // Получение попыток пользователя в тесте
                $attempts = $DB->get_records('quiz_attempts', ['quiz' => $quiz->id, 'userid' => $user->id], 'attempt DESC');
                if ( ! empty($attempts) )
                {
                    foreach ( $attempts as $attempt )
                    {
                        quiz_delete_attempt($attempt, $quiz);
                    }
                }
            }
        }
        
        
        
        // Очистка заданий
        $assign_all = $DB->get_records('assign', ['course' => $course_to_enrol->id]);
        $contextids = [];
        if ( ! empty($assign_all) )
        {
            foreach ( $assign_all as $assign )
            {
                $cm = get_coursemodule_from_instance('assign', $assign->id);
                if ( empty($cm) )
                {
                    continue;
                }
                $context = context_module::instance($cm->id);
                $contextids[] = $context->id;
            }
        }
        
        if ( ! empty($contextids) )
        {
            // очистка заданий с помощью нового API privacy
            $contextsassigntodel = new approved_contextlist(core_user::get_user($userid), 'mod_assign', $contextids);
            \mod_assign\privacy\provider::delete_data_for_user($contextsassigntodel);
        }
        
    }
}