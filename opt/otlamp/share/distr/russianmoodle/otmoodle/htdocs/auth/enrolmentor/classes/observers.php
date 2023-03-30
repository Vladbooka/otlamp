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
 * Обозреватель событий для плагина auth_enrolmentor
 * 
 * @package    auth_enrolmentor
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace auth_enrolmentor;

require_once($CFG->dirroot.'/auth/enrolmentor/classes/helper.php');

defined('MOODLE_INTERNAL') || die();

/**
 * Обработчик событий для auth_enrolmentor
 */
class observers 
{
    /**
     * Обработчик события \core\event\user_created (создание пользователя)
     * @param \core\event\user_created $event
     */
    public static function user_created(\core\event\user_created $event)
    {
        if (!is_enabled_auth('enrolmentor')) {
            return;
        }
        
        $data = $event->get_data();
        $userchildid = $data['objectid'];
        $flag = $data['crud'];
        helper::role_assignment_process($userchildid, $flag);
    }
    
    /**
     * Обработчик события \core\event\user_updated (обновление пользователя)
     * @param \core\event\user_updated $event
     */
    public static function user_updated(\core\event\user_updated $event)
    {
        if (!is_enabled_auth('enrolmentor')) {
            return;
        }
        
        $data = $event->get_data();
        $userchildid = $data['objectid'];
        $flag = $data['crud'];
        helper::role_assignment_process($userchildid, $flag);
    }
    
    /**
     * Обработчик события \core\event\user_deleted (удаление пользователя)
     * @param \core\event\user_deleted $event
     */
    public static function user_deleted(\core\event\user_deleted $event)
    {

    }
}