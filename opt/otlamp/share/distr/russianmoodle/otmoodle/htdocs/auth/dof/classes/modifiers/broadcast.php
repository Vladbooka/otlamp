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
 * Модификатор - транслируемое поле
 *
 * @package    auth
 * @subpackage dof
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace auth_dof\modifiers;

use auth_dof\modifiers_base;
use auth_dof\form_fields_factory;

class broadcast extends modifiers_base
{
    /**
     * Получение языковой строки модификатора
     *
     * @return string
     */
    public static function get_name_string() {
        return get_string('mod_broadcast', 'auth_dof');
    }
    
    /**
     * 
     * {@inheritDoc}
     * @see \auth_dof\modifiers_base::is_field_data_returned()
     */
    public function is_field_data_returned() {
        return true;
    }
    
    /**
     * 
     * {@inheritDoc}
     * @see \auth_dof\modifiers_base::definition()
     */
    public function definition(form_fields_factory $fofifa) {
        $fofifa->locked = 1;
        try {
            $fofifa->defaultdata = parent::get_src_field_value(
                $this->fldname, $this->user_cfg_fields, $this->external_record);
        } catch (\Exception $e) {
            print_error('Broadcast modifier: ' . $e->getMessage());
        }
    }
    
    /**
     *
     * {@inheritDoc}
     * @see \auth_dof\modifiers_base::process()
     */
    public function process($user, &$prepareuf) {
        try {
            $fldname = $this->get_form_field_name($this->fldname);
            $prepareuf->{$fldname} = parent::get_src_field_value(
                $this->fldname, $this->user_cfg_fields, $this->external_record);
        } catch (\Exception $e) {
            print_error('Broadcast modifier: ' . $e->getMessage());
        }
    }
    
    /**
     * Валидация настроек на странице "Настройки полей формы регистрации"
     * 
     * @param array $data
     * @param string $fldname
     */
    public static function settings_validation(array $data, string $fldname) {
        $errors = [];
        // Транслируемые поля могут отображаться только на втором этапе в форме регистрации
        if ($data['fld_' . $fldname . '_display'] == 1) {
            $errors['group_' . $fldname]  = get_string(
                'broadcast_field_only_on_step2', 'auth_dof');
        }
        
        $srcfldcfg = false;
        if (array_key_exists('fld_' . $fldname . '_srcfld', $data)) {
            $srcfldcfg = $data['fld_' . $fldname . '_srcfld'];
        }
        if (is_array($srcfldcfg)) {
            foreach ($srcfldcfg as $srcfield) {
                if (empty($srcfield)) {
                    // Для модификатора "Транслируемое поле" должны соответствовать поля из источников
                    $errors['group_' . $fldname]  = get_string(
                        'broadcast_field_need_source_comparison', 'auth_dof');
                }
            }
        }
        
        $hassearchfields = false;
        foreach ($data as $configfldname => $value) {
            if (stripos($configfldname, 'fld_') === 0) {
                $matches = [];
                preg_match('/fld_([A-Za-z0-9_]+)(_.+)/', $configfldname, $matches);
                list(, $fieldname, $settingtype) = $matches;
                if ($settingtype == '_mod'
                    && $data['fld_' . $fieldname . '_display'] == 1
                    && !empty($value['search']))
                {
                    $hassearchfields = true;
                }
            }
        }
        if (! $hassearchfields) {
            // Для работы транслируемых полей требуется как минимум одно поисковое поле
            // на первом этапе регистрации
            $errors['group_' . $fldname]  = get_string(
                'broadcast_field_need_search_fields', 'auth_dof');
        }
        return $errors;
    }
    
    /**
     * Определяет будет ли можификатор отображаться на форме настроек
     * 
     * @param string $fldname
     * @param array $srcconfigfields
     * @return boolean
     */
    static function display_on_settings_form(string $fldname, array $srcconfigfields) {
        if (! $srcconfigfields) {
            return false;
        }
        return true;
    }
}