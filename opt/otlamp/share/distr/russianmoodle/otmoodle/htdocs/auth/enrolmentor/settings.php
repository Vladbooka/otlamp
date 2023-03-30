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
 * Auto enrol mentors, parents or managers based on a custom profile field.
 *
 * @package    auth
 * @subpackage enrolmentor
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

use auth_enrolmentor\helper;

global $USER;

require_once($CFG->dirroot.'/user/profile/lib.php');
require_once($CFG->dirroot.'/auth/enrolmentor/classes/helper.php');
require_once($CFG->dirroot.'/auth/enrolmentor/classes/task/updatementors.php');

if ($ADMIN->fulltree) {

    // Get all roles and put their id's nicely into the configuration.
    $roles = get_all_roles();
    $i = 1;
    foreach($roles as $role) {
        $rolename[$i] = $role->shortname;
        $roleid[$i] = $role->id;
        $i++;
    }
    $rolenames = array_combine($roleid, $rolename);
    $defaultrole = array_search('curator', $rolenames);
    if( $defaultrole === false )
    {// Если роль куратор отстуствует, оставим как было раньше - менеджер
        $defaultrole = 1;
    }
    $profilefields = helper::get_profile_fields();
    if( array_key_exists('profile_field_curator', $profilefields) )
    {// Если есть кастомное поле куратор, выставим его по умолчанию
        $defaultprofilefield = 'profile_field_curator';
    } elseif( array_key_exists('alternatename', $profilefields) ) 
    {// Если нет, то поставим по умолчанию альтенативное имя
        $defaultprofilefield = 'alternatename';
    } else 
    {// Если оба поля не найдены, оставим как было раньше
        $defaultprofilefield = '';
    }
    $setting = new admin_setting_configselect(
        'auth_enrolmentor/role',
        get_string('enrolmentor_settingrole', 'auth_enrolmentor'),
        get_string('enrolmentor_settingrolehelp', 'auth_enrolmentor'),
        $defaultrole,
        $rolenames
        );
    //Если заадние на обновление кураторов ещё не добавлено, то добавить его при изменении параметра
    $setting->set_updatedcallback(function(){
        if (!helper::is_task_added())
        {
            helper::add_task();
        }
    });
    $settings->add($setting);
    $setting = new admin_setting_configselect(
        'auth_enrolmentor/compare',
        get_string('enrolmentor_settingcompare', 'auth_enrolmentor'),
        get_string('enrolmentor_settingcomparehelp', 'auth_enrolmentor'),
        'username',
        [
            'username' => 'username',
            'email' => 'email',
            'id' => 'id',
            'idnumber' => 'idnumber'
        ]
        );
    //Если заадние на обновление кураторов ещё не добавлено, то добавить его при изменении параметра
    $setting->set_updatedcallback(function(){
        if (!helper::is_task_added())
        {
            helper::add_task();
        }
    });
    $settings->add($setting);
    $setting = new admin_setting_configselect(
        'auth_enrolmentor/delimeter',
        get_string('enrolmentor_settingdelimeter', 'auth_enrolmentor'),
        get_string('enrolmentor_settingdelimeter_desc', 'auth_enrolmentor'),
        ',',
        [
            ',' => ',',
            ';' => ';',
            '|' => '|'
        ]
    );
    //Если заадние на обновление кураторов ещё не добавлено, то добавить его при изменении параметра
    $setting->set_updatedcallback(function(){
        if (!helper::is_task_added())
        {
            helper::add_task();
        }
    });
    $settings->add($setting);
    if( $profilefields ) 
    {
        $setting = new admin_setting_configselect(
                'auth_enrolmentor/profile_field', 
                get_string('enrolmentor_settingprofile_field', 'auth_enrolmentor'), 
                get_string('enrolmentor_settingprofile_fieldhelp', 'auth_enrolmentor'), 
                $defaultprofilefield, 
                $profilefields
        );
        //Если заадние на обновление кураторов ещё не добавлено, то добавить его при изменении параметра
        $setting->set_updatedcallback(function(){
            if (!helper::is_task_added())
            {
                helper::add_task();
            }
        });
        $settings->add($setting);
    } else 
    {
        $setting = new admin_setting_heading(
                'auth_enrolmentor/profile_fieldheading', 
                get_string('enrolmentor_settingprofile_field_heading', 'auth_enrolmentor'),
                get_string('enrolmentor_settingprofile_field_heading', 'auth_enrolmentor'));
        $settings->add($setting);
    }
    $setting = new admin_setting_configcheckbox(
        'auth_enrolmentor/updatementors',
        get_string('enrolmentor_settingupdatementors', 'auth_enrolmentor'),
        get_string('enrolmentor_settingupdatementors_desc', 'auth_enrolmentor'),
        '0'
        );
    //Если заадние на обновление кураторов ещё не добавлено, то добавить его при изменении параметра
    $setting->set_updatedcallback(function(){
        
        set_config('updatementors', '0', 'auth_enrolmentor');
        
        if (!helper::is_task_added())
        {
            helper::add_task(false);
        }
    });
        $settings->add($setting);
}