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
 * Auth_dof plugin upgrade code
 *
 * @package    auth_dof
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot .'/auth/dof/locallib.php');
require_once($CFG->dirroot . '/user/editlib.php');

/**
 * Function to upgrade auth_email.
 * @param int $oldversion the version we are upgrading from
 * @return bool result
 */
function xmldb_auth_dof_upgrade($oldversion) {
    global $DB;

    if ($oldversion < 2017101700) {
        // Convert info in config plugins from auth/dof to auth_dof.
        upgrade_fix_config_auth_plugin_names('dof');
        upgrade_fix_config_auth_plugin_defaults('dof');
        upgrade_plugin_savepoint(true, 2017101700, 'auth', 'dof');
    }
    
    if ($oldversion < 2017121200)
    {
        // Апдейт lang с ru_utf8 (такого больше не существует) на ru
        $sql = "UPDATE {user} SET lang = 'ru' WHERE auth = 'dof' AND lang = 'ru_utf8'";
        $DB->execute($sql);
    }
    
    if ($oldversion < 2021052400)
    {
        $formfields = useredit_get_required_name_fields();
        $oldsignupfields = array_merge(['username', 'email', 'phone', 'password'], $formfields, ['middlename']);
        $requiredfields = ['username', 'email', 'phone2', 'password', 'firstname', 'lastname'];
        foreach ($oldsignupfields as $key => $fldname) {
            if (get_config('auth_dof', 'signupfield_' . $fldname)) {
                unset_config('signupfield_' . $fldname, 'auth_dof');
                if ($fldname == 'phone') {
                    $oldsignupfields[$key] = 'phone2'; 
                }
            } else {
                unset($oldsignupfields[$key]);
            }
        }
        // Если ранее логин отсутствовал, требуется добавить его в конец
        // это позволит генерировать на основании телефона или email
        $genusername = false;
        if (! in_array('username', $oldsignupfields)) {
            $oldsignupfields[] = 'username';
            $genusername = true;
        }
        // На данный момент нет оснований для генерации пароля но могут появится в дальнейшем,
        // поэтому добавим его в конец если ранее пароль отсутствовал
        $genpassword = false;
        if (! in_array('password', $oldsignupfields)) {
            $oldsignupfields[] = 'password';
            $genpassword = true;
        }
        auth_dof_init_defaults_fields($oldsignupfields, $requiredfields);
        // Для поля логина включим модификатор "генерируемое поле"
        if ($genusername) {
            auth_dof_set_field_modifier_cfg('user_field_username', 'generated', true);
        }
        // Для поля пароля включим модификатор "генерируемое поле"
        if ($genpassword) {
            auth_dof_set_field_modifier_cfg('user_field_password', 'generated', true);
        }
    }

    return true;
}
