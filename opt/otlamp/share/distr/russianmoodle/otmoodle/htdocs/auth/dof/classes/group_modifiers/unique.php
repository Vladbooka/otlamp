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
 * Групповой модификатор - поле проверки уникальности
 * 
 * Валидацию придется проводить в process так-как данные из формы не полны.
 *
 * @package    auth
 * @subpackage dof
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace auth_dof\group_modifiers;

use auth_dof\group_modifiers_base;
use core\notification;
use moodle_url;

class unique extends group_modifiers_base
{
    /**
     * Получение языковой строки модификатора
     *
     * @return string
     */
    public static function get_name_string() {
        return get_string('group_mod_unique', 'auth_dof');
    }
    
    /**
     * 
     * {@inheritDoc}
     * @see \auth_dof\group_modifiers_base::process()
     */
    public function process($formdata, $prepareuf) {
        list($usedonstep1, $usedonstep2, $uniquefldlist) = $this->get_uniquemod_fields_and_step();
        if (! empty($uniquefldlist)) {
            if (($this->step == 1 && $usedonstep1 && ! $usedonstep2)
                || ($this->step == 2 && $usedonstep2))
            {
                $uids = $this->get_user_id_of_equal_fields($uniquefldlist, (array)$prepareuf);
                if ($uids) {
                    if (debugging()) {
                        foreach ($uids as $uid) {
                            debugging('User id with equal fields:' . $uid->id , DEBUG_DEVELOPER);
                        }
                    }
                    notification::error(get_string('similar_data_found', 'auth_dof'));
                    redirect(new moodle_url('/login/signup.php', ['step' => $this->step]));
                }
            }
        }
        return [];
    }
    
    /**
     * Возвращает ид пользователей с аналогичными значениями полей в сдо
     * 
     * @param array $fldlist - список полей к проверке
     * @param array $userfields - поля пользователя со значениями
     * @return array
     */
    private function get_user_id_of_equal_fields($fldlist, $userfields) {
        global $DB;
        $userfld = [];
        $profilefld = [];
        $sql = '';
        $params = [];
        foreach ($fldlist as $ufield) {
            if (stripos($ufield, 'user_field_') === 0) {
                $field = substr($ufield, 11);
                if (array_key_exists($field, $userfields)) {
                    $userfld[] = "$field = :$ufield";
                    $params[$ufield] = $userfields[$field];
                } else {
                    print_error('No user field "' . $field . '" data transferred in "unique" modifier');
                }
            } elseif (stripos($ufield, 'user_profilefield_') === 0) {
                $field =  substr($ufield, 18);
                if (array_key_exists('profile_field_' . $field, $userfields)) {
                    $profilefld[] = '(uif.shortname = :' . $ufield . '_name AND uind.data = :' . $ufield . '_val)';
                    $params[$ufield . '_val'] = $userfields['profile_field_' . $field];
                    $params[$ufield . '_name'] = $field;
                } else {
                    print_error('No user profilefield "' . $field . '" data transferred in "unique" modifier');
                }
            } else {
                print_error('Field "' . $field . '" not supported in unique mod');
            }
        }
        if ($profilefld) {
            $sqlprofilefld = "SELECT uind.userid AS id
                                FROM {user_info_field} uif
                           LEFT JOIN {user_info_data} uind ON uif.id = uind.fieldid
                               WHERE " . implode(' AND ', $profilefld);
        }
        if ($userfld) {
            $sql .= "SELECT u.id AS id FROM {user} u ";
            if ($profilefld) {
                $sql .= "INNER JOIN (" . $sqlprofilefld . ") profile ON profile.id = u.id ";
            }
            $sql .= "WHERE " . implode(' AND ', $userfld);
        }
        if (! $sql) {
            $sql = $sqlprofilefld;
        }
        return $DB->get_records_sql($sql, $params);
    }
    
    /**
     * Возврашает список полей с модификатором unique и флаг использования на 1,2 шаге
     * 
     * @return ['флаг первого шага', 'флаг второго шага', 'список полей']
     */
    private function get_uniquemod_fields_and_step() {
        $usedonstep1 = false;
        $usedonstep2 = false;
        $uniquefldlist = [];
        foreach ($this->user_cfg_fields as $fldname => $fldcfg) {
            if (isset($fldcfg['mod'])
                && is_array($modcfg = json_decode($fldcfg['mod'], true))
                && ! empty($modcfg['unique']))
            {
                if ($fldcfg['display'] == 1) {
                    $usedonstep1 = true;
                } elseif ($fldcfg['display'] == 2) {
                    $usedonstep2 = true;
                } else {
                    continue;
                }
                $uniquefldlist[] = $fldname;
            }
        }
        return [$usedonstep1, $usedonstep2, $uniquefldlist];
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
}