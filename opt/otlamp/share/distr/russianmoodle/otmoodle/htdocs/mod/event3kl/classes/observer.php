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
 * Модуль Занятие. Перехватчик событий системы.
 *
 * @package    mod
 * @subpackage event3kl
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_event3kl;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot .'/mod/event3kl/locallib.php');

use core_plugin_manager;
use Exception;
use context_module;
use context_course;

/**
 * Обработчик событий
 */
class observer
{
    /**
     * Обработчик события добавления члена группы курса
     * @param \core\event\group_member_added $event
     */
    public static function group_member_added(\core\event\group_member_added $event) {
        $installedmods = core_plugin_manager::instance()->get_installed_plugins('mod');
        if (empty($installedmods['event3kl'])) {
            return;
        }
        mod_event3kl_actualize_sessions_in_course($event->courseid);
    }
    
    /**
     * Обработчик события удаления члена группы курса
     * @param \core\event\group_member_removed $event
     */
    public static function group_member_removed(\core\event\group_member_removed $event) {
        $installedmods = core_plugin_manager::instance()->get_installed_plugins('mod');
        if (empty($installedmods['event3kl'])) {
            return;
        }
        mod_event3kl_actualize_sessions_in_course($event->courseid);
    }
    
    /**
     * Обработчик события назначения роли
     * @param \core\event\role_assigned $event
     */
    public static function role_assigned(\core\event\role_assigned $event) {
        $installedmods = core_plugin_manager::instance()->get_installed_plugins('mod');
        if (empty($installedmods['event3kl'])) {
            return;
        }
        if (empty($event->courseid)) {
            return;
        }
        if (!is_enrolled(context_course::instance($event->courseid), $event->relateduserid)) {
            return;
        }
        mod_event3kl_actualize_sessions_in_course($event->courseid);
    }
    
    /**
     * Обработчик события снятия роли
     * @param \core\event\role_unassigned $event
     */
    public static function role_unassigned(\core\event\role_unassigned $event) {
        $installedmods = core_plugin_manager::instance()->get_installed_plugins('mod');
        if (empty($installedmods['event3kl'])) {
            return;
        }
        if (empty($event->courseid)) {
            return;
        }
        if (!is_enrolled(context_course::instance($event->courseid), $event->relateduserid)) {
            return;
        }
        mod_event3kl_actualize_sessions_in_course($event->courseid);
    }
    
    /**
     * Обработчик события удаления подписки на курс
     * @param \core\event\user_enrolment_deleted $event
     */
    public static function user_enrolment_deleted(\core\event\user_enrolment_deleted $event) {
        $installedmods = core_plugin_manager::instance()->get_installed_plugins('mod');
        if (empty($installedmods['event3kl'])) {
            return;
        }
        if (empty($event->courseid)) {
            return;
        }
        mod_event3kl_actualize_sessions_in_course($event->courseid);
    }
    
    /**
     * Обработчик события удаления локальной группы
     * @param \core\event\group_deleted $event
     */
    public static function group_deleted(\core\event\group_deleted $event) {
        $installedmods = core_plugin_manager::instance()->get_installed_plugins('mod');
        if (empty($installedmods['event3kl'])) {
            return;
        }
        if (! empty($event->context)){
            $courseid = $event->context->instanceid;
        } elseif (! empty($event->courseid)){
            $courseid = $event->courseid;
        } else {
            return;
        }
        mod_event3kl_actualize_sessions_in_course($courseid);
    }
    
    /**
     * Обработчик события добавления локальной группы
     * @param \core\event\group_created $event
     */
    public static function group_created(\core\event\group_created $event) {
        $installedmods = core_plugin_manager::instance()->get_installed_plugins('mod');
        if (empty($installedmods['event3kl'])) {
            return;
        }
        if (! empty($event->context)){
            $courseid = $event->context->instanceid;
        } elseif (! empty($event->courseid)){
            $courseid = $event->courseid;
        } else {
            return;
        }
        mod_event3kl_actualize_sessions_in_course($courseid);
    }
}
