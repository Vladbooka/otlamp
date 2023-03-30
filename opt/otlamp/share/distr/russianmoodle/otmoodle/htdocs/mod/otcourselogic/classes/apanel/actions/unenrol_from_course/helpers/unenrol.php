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

namespace mod_otcourselogic\apanel\actions\unenrol_from_course\helpers;

require_once($CFG->dirroot . '/lib/authlib.php');

use stdClass;
use context_course;

/**
 * Контроллер записи на курс
 */
class unenrol
{
    /**
     * Отписывание пользователя от текущего курса
     * 
     * @param stdClass $instance
     * @param int $userid
     * @param stdClass $course
     * @param stdClass $action_instance
     * 
     * @return bool
     */
    public static function unenrol_to_course($instance, $userid, $course, $action_instance, &$pool)
    {
        global $DB;
        
        // Получение контекста курса, на который подписываем пользователя
        $context = context_course::instance($course->id);
        
        if ( ! has_capability('mod/otcourselogic:is_student', $context, $userid) )
        {// Пользователь является преподавателем
            return true;
        }
        
        if ( is_enrolled($context, $userid) )
        {// Пользователь записан на курс, но надо перезаписать
            
            // Параметры для селекта
            $params = [
                'userid' => $userid,
                'courseid' => $course->id
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
        
        return true;
    }
}