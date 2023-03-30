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
 * Авторизация (регистрация) СЭО 3KL. Обозреватель событий.
 * 
 * @package    auth
 * @subpackage dof
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace auth_dof;

use core\notification;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot .'/auth/dof/locallib.php');

/**
 * Обработчик событий для auth_dof
 */
class observer 
{
    /**
     * Перехват события изменения поля профиля
     *
     * @param \core\event\user_info_field_updated $event - Объект события
     * 
     * @return void
     */
    public static function user_info_field_updated(\core\event\user_info_field_updated $event)
    {
        global $DB;
        // Получение данных события
        $data = $event->get_data();
        if ($field = $DB->get_record('user_info_field', ['id' => $data['objectid']])) {
            if (property_exists($field,'signup')) {
                $signup = $field->signup ? 1 : 0;
                $other = $data['other'];
                $displaymode = get_config('auth_dof', 'fld_user_profilefield_' . $other['shortname'] . '_display');
                if ($signup) {
                    if ($displaymode == 0) {
                        set_config('fld_user_profilefield_' . $other['shortname'] . '_display', 1, 'auth_dof');
                        notification::info(
                            get_string('field_add_to_reg_form', 'auth_dof', $other['name']));
                    }
                } else {
                    if ($displaymode != 0 || $displaymode === false) {
                        set_config('fld_user_profilefield_' . $other['shortname'] . '_display', 0, 'auth_dof');
                        notification::info(
                            get_string('field_removed_from_reg_form', 'auth_dof', $other['name']));
                    }
                }
            }
        }
    }
    
    /**
     * Перехват события создания поля
     *
     * @param \core\event\user_info_field_created $event - Объект события
     *
     * @return void
     */
    public static function user_info_field_created(\core\event\user_info_field_created $event)
    {
        global $DB;
        // Получение данных события
        $data = $event->get_data();
        if ($field = $DB->get_record('user_info_field', ['id' => $data['objectid']])) {
            if (property_exists($field,'signup')) {
                $signup = $field->signup ? 1 : 0;
                if ($signup) {
                    $other = $data['other'];
                    $numfields = count(auth_dof_prepare_fields(null, ['order']));
                    $numfields++;
                    auth_dof_add_field('user_profilefield_' . $other['shortname'], $numfields, 1);
                    notification::info(
                        get_string('field_add_to_reg_form', 'auth_dof', $other['name']));
                }
            }
        }
    }
    
    /**
     * Перехват события удаления поля
     *
     * @param \core\event\user_info_field_deleted $event - Объект события
     *
     * @return void
     */
    public static function user_info_field_deleted(\core\event\user_info_field_deleted $event)
    {
        // Получение данных события
        $data = $event->get_data();
        $other = $data['other'];
        // Удаляет все настройки поля
        auth_doff_delete_field('user_profilefield_' . $other['shortname']);
    } 
}
