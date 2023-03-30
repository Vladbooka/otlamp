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
 * Модификатор - обязательное поле
 *
 * @package    auth
 * @subpackage dof
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace auth_dof\modifiers;

use auth_dof\modifiers_base;
use auth_dof\form_fields_factory;
use stdClass;

class required extends modifiers_base
{
    public static function get_name_string() {
        return get_string('mod_required', 'auth_dof');
    }
    
    /**
     *
     * {@inheritDoc}
     * @see \auth_dof\modifiers_base::is_field_data_returned()
     */
    public function is_field_data_returned() {
        return false;
    }
    
    /**
     * 
     * {@inheritDoc}
     * @see \auth_dof\modifiers_base::definition()
     */
    public function definition(form_fields_factory $fofifa) {
        $fofifa->required = 1;
    }
    
    /**
     * Валидация настроек на странице "Настройки полей формы регистрации"
     *
     * @param array $data
     * @param string $fldname
     */
    public static function settings_validation(array $data, string $fldname) {
        return [];
    }
    
    /**
     * Определяет будет ли можификатор отображаться на форме настроек
     *
     * @param string $fldname
     * @param array $srcconfigfields
     * @return boolean
     */
    static function display_on_settings_form(string $fldname, array $srcconfigfields) {
        return true;
    }
    
    /**
     * 
     * {@inheritDoc}
     * @see \auth_dof\modifiers_base::process()
     */
    public function process(stdClass $user, stdClass &$prepareuf) {}
}