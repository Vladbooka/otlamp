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
 * Модификатор - поисковое поле
 *
 * @package    auth
 * @subpackage dof
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace auth_dof\modifiers;

use auth_dof\modifiers_base;

class search extends modifiers_base
{
    public static function get_name_string() {
        return get_string('mod_search', 'auth_dof');
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
     * Формирует условия поисковых модификаторов по данным полученым из формы
     * 
     * @param array $fieldscfg
     * @param array $formdata
     * @param int $srcid
     * @return []
     */
    public static function get_conditions(array $fieldscfg, array $formdata, int $srcid) {
        $conditions = [];
        foreach ($fieldscfg as $fldname => $fldcfg) {
            if (isset($fldcfg['srcfld']) && 
                is_array($modcfg = json_decode($fldcfg['srcfld'], true)) &&
                ! empty($modcfg[$srcid])) 
            {
                $fldname = parent::get_form_field_name($fldname);
                if (array_key_exists($fldname, $formdata)) {
                    $conditions[$modcfg[$srcid]] = $formdata[$fldname];
                }
            }
        }
        return $conditions;
    }
    
    /**
     * 
     * {@inheritDoc}
     * @see \auth_dof\modifiers_base::process()
     */
    public function process($user, &$prepareuf) {
        $fldname = $this->get_form_field_name($this->fldname);
        if (isset($user->{$fldname})) {
            $prepareuf->{$fldname} = $user->{$fldname};
        } else {
            print_error('No field "' . $fldname . '" data returned from reg form in "search" modifier');
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
        // Поисковые поля могут отображаться только на первом этапе в форме регистрации
        if ($data['fld_' . $fldname . '_display'] == 2) {
            $errors['group_' . $fldname]  = get_string(
                'search_field_only_on_step1', 'auth_dof');
        }

        $srcfldcfg = false;
        if (array_key_exists('fld_' . $fldname . '_srcfld', $data)) {
            $srcfldcfg = $data['fld_' . $fldname . '_srcfld'];
        }
        if (is_array($srcfldcfg)) {
            foreach ($srcfldcfg as $srcfield) {
                if (empty($srcfield)) {
                    // Для модификатора "Поисковое поле" должно соответствовать хотя бы одно поле из источников
                    $errors['group_' . $fldname]  = get_string(
                        'search_field_need_source_comparison', 'auth_dof');
                }
            }
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
    public static function display_on_settings_form(string $fldname, array $srcconfigfields) {
        if (! $srcconfigfields) {
            return false;
        }
        return true;
    }
    
   /**
    * Заменяет данные формы значениями из внешнего источника
    * 
    * @param object $data - обьект в котором данные будут заменены
    * @param array $fieldscfg
    * @param NULL|false|array $externalrecord
    */
    public static function replase_form_data_by_src_values(object $data, array $fieldscfg, $externalrecord) {
        foreach ($fieldscfg as $fldname => $fldcfg) {
            if (isset($fldcfg['mod'])
                && is_array($modcfg = json_decode($fldcfg['mod'], true))
                && ! empty($modcfg['search']))
            {
                $formfldname = parent::get_form_field_name($fldname);
                $data->{$formfldname} = parent::get_src_field_value(
                    $fldname, $fieldscfg, $externalrecord);
            }
        } 
    }
}