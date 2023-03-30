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
 * Модуль Логика курса. Дополнительная библиотека функций плагина.
*
 * @package    mod_otcourselogic
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_otcourselogic;

use context_course;
use html_writer;
use moodle_url;
use stdClass;

defined('MOODLE_INTERNAL') || die;

class info
{
    /**
     * Роли, для которых проходят рассылки
     *
     * @var array
     */
    private static $roles = ['teacher', 'student', 'curator'];

    /**
     * Типы сообщений
     *
     * @var array
     */
    private static $messagetypes = ['activate', 'deactivate', 'periodic'];

    /**
     * Получение массива ролей для рассылок
     */
    public static function get_roles()
    {
        return self::$roles;
    }

    /**
     * Получение массива типов сообщений
     */
    public static function get_message_types()
    {
        return self::$messagetypes;
    }
    
    /**
     * Получение ролей пользователя
     * 
     * @param stdClass $user
     * @param stdClass $course
     *
     * @return string $administrators
     */
    public static function get_user_roles($user = null, $course = null)
    {
        // Валидация
        if ( empty($user) || empty($course) )
        {
            return '';
        }
        
        return get_user_roles_in_course($user->id, $course->id);}
    
    /**
     * Получение групп пользователя
     *
     * @param stdClass $user
     * @param stdClass $course
     *
     * @return string $administrators
     */
    public static function get_user_groups($user = null, $course = null)
    {
        
        // Валидация
        if ( empty($user) || empty($course) )
        {
            return '';
        }
        
        $html = '';
        $groups = groups_get_user_groups($course->id, $user->id);
        $last_elem = end($groups[0]);
        $all_groups = array_shift($groups);
        if ( empty($all_groups) )
        {
            // Групп нет
            return $html;
        }
        
        static $groups_name = [];
        foreach ( $all_groups as $groupid )
        {
            if ( ! array_key_exists($groupid, $groups_name) )
            {
                $groups_name[$groupid] = groups_get_group_name($groupid);
            }
            
            $add = ',';
            if ( $groupid == $last_elem )
            {
                $add = '';
            }
            
            $link = new moodle_url('/group/members.php', ['group' => $groupid]);
            $html .= html_writer::link($link, $groups_name[$groupid] . $add);
        }
        
        return $html;
    }
}