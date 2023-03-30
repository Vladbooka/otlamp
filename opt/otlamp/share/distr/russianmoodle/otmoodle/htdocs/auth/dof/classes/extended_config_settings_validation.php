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
 * Класс, расширяющий валидацию настроек.
 *
 * @package    auth_dof
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot .'/auth/dof/locallib.php');

/**
  * Класс, расширяющий валидацию настройки "Способ доставки сообщений"
  *
  * @package    auth_dof
  * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
  */
class auth_dof_sendmethod extends admin_setting_configmultiselect
{
    /**
     * Переопределение метода родительского класса для проверки валидности настроек
     * {@inheritDoc}
     * @see admin_setting_configselect::write_setting()
     */
    public function write_setting($data)
    {
        // Поле email не настроено на странице настроек пользовательских полей формы
        if( in_array('email', $data) 
            && get_config('auth_dof', 'fld_user_field_email_display') == 0)
        {
            return get_string('settings_error_signupfield_email_must_be_shown', 'auth_dof');
        }
        // Поле phone2 не настроено на странице настроек пользовательских полей формы
        if( in_array('otsms', $data) 
            && get_config('auth_dof', 'fld_user_field_phone2_display') == 0)
        {
            return get_string('settings_error_signupfield_phone_must_be_shown', 'auth_dof');
        }
        // Выполнение родительского метода
        $savestatus = parent::write_setting($data);
        // если сохранение прошло успешно - сохраним настройку включенного модификатора "обязательное поле"
        // изменение данного модификатора будет не возможным так-как он заблокирован на форме абсолютными настройками
        if (empty($savestatus)) {
            if( in_array('email', $data)) {
                auth_dof_set_field_modifier_cfg('fld_user_field_email_mod', 'required', true);
                auth_dof_set_field_modifier_cfg('fld_user_field_email_mod', 'generated', false);
            }
            if( in_array('otsms', $data)) {
                auth_dof_set_field_modifier_cfg('fld_user_field_phone2_mod', 'required', true);
                auth_dof_set_field_modifier_cfg('fld_user_field_phone2_mod', 'generated', false);
            }  
        }
        return $savestatus;
    }
}