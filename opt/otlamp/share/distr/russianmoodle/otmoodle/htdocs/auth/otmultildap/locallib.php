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
 * Internal library of functions for module auth_otmultildap
 *
 * @package    auth_otmultildap
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Get a list of system roles assignable by the current or a specified user, including their localised names.
 *
 * @param integer|object $user A user id or object. By default (null) checks the permissions of the current user.
 * @return array $roles, each role as an array with id, shortname, localname, and settingname for the config value.
 */
function get_otmultildapldap_assignable_role_names($user = null) {
    $roles = array();

    if ($assignableroles = get_assignable_roles(context_system::instance(), ROLENAME_SHORT, false, $user)) {
        $systemroles = role_fix_names(get_all_roles(), context_system::instance(), ROLENAME_ORIGINAL);
        foreach ($assignableroles as $shortname) {
            foreach ($systemroles as $systemrole) {
                if ($systemrole->shortname == $shortname) {
                    $roles[] = array('id' => $systemrole->id,
                                     'shortname' => $shortname,
                                     'localname' => $systemrole->localname,
                                     'settingname' => $shortname . 'context');
                    break;
                }
            }
        }
    }

    return $roles;
}

/**
 * @desc Метод скопирован из lib/authlib.php с небольшими изменениями, так как ток формирует языковые строки не так, как надо
 * 
 * Helper function used to print locking for auth plugins on admin pages.
 * @param stdclass $settings Moodle admin settings instance
 * @param string $auth authentication plugin shortname
 * @param array $userfields user profile fields
 * @param string $helptext help text to be displayed at top of form
 * @param boolean $mapremotefields Map fields or lock only.
 * @param boolean $updateremotefields Allow remote updates
 * @param array $customfields list of custom profile fields
 * @since Moodle 3.3
 */
function otmultildap_display_auth_lock_options($settings, $auth, $userfields, $helptext, $mapremotefields, $updateremotefields, $customfields = array()) {
    global $DB;
    
    // Introductory explanation and help text.
    if ($mapremotefields) {
        $settings->add(new admin_setting_heading($auth.'/data_mapping', new lang_string('auth_data_mapping', 'auth'), $helptext));
    } else {
        $settings->add(new admin_setting_heading($auth.'/auth_fieldlocks', new lang_string('auth_fieldlocks', 'auth'), $helptext));
    }
    
    // Generate the list of options.
    $lockoptions = array ('unlocked'        => get_string('unlocked', 'auth'),
        'unlockedifempty' => get_string('unlockedifempty', 'auth'),
        'locked'          => get_string('locked', 'auth'));
    $updatelocaloptions = array('oncreate'  => get_string('update_oncreate', 'auth'),
        'onlogin'   => get_string('update_onlogin', 'auth'));
    $updateextoptions = array('0'  => get_string('update_never', 'auth'),
        '1'  => get_string('update_onupdate', 'auth'));
    
    // Generate the list of profile fields to allow updates / lock.
    if (!empty($customfields)) {
        $userfields = array_merge($userfields, $customfields);
        $customfieldname = $DB->get_records('user_info_field', null, '', 'shortname, name');
    }
    
    foreach ($userfields as $field) {
        // Define the fieldname we display to the  user.
        // this includes special handling for some profile fields.
        $check = explode('__', $field);
        if ( array_key_exists(1, $check) )
        {
            $fieldname = $check[0];
        } else 
        {
            $fieldname = $field;
        }
        $fieldnametoolong = false;
        if ($fieldname === 'lang') {
            $fieldname = get_string('language');
        } else if (!empty($customfields) && in_array($field, $customfields)) {
            // If custom field then pick name from database.
            $fieldshortname = str_replace('profile_field_', '', $fieldname);
            $fieldname = $customfieldname[$fieldshortname]->name;
            if (core_text::strlen($fieldshortname) > 67) {
                // If custom profile field name is longer than 67 characters we will not be able to store the setting
                // such as 'field_updateremote_profile_field_NOTSOSHORTSHORTNAME' in the database because the character
                // limit for the setting name is 100.
                $fieldnametoolong = true;
            }
        } else if ($fieldname == 'url') {
            $fieldname = get_string('webpage');
        } else {
            $fieldname = get_string($fieldname);
        }
        
        // Generate the list of fields / mappings.
        if ($fieldnametoolong) {
            // Display a message that the field can not be mapped because it's too long.
            $url = new moodle_url('/user/profile/index.php');
            $a = (object)['fieldname' => s($fieldname), 'shortname' => s($field), 'charlimit' => 67, 'link' => $url->out()];
            $settings->add(new admin_setting_heading($auth.'/field_not_mapped_'.sha1($field), '',
                    get_string('cannotmapfield', 'auth', $a)));
        } else if ($mapremotefields) {
            // We are mapping to a remote field here.
            // Mapping.
            $settings->add(new admin_setting_configtext("auth_{$auth}/field_map_{$field}",
            get_string('auth_fieldmapping', 'auth', $fieldname), '', '', PARAM_RAW, 30));
            
            // Update local.
            $settings->add(new admin_setting_configselect("auth_{$auth}/field_updatelocal_{$field}",
            get_string('auth_updatelocalfield', 'auth', $fieldname), '', 'oncreate', $updatelocaloptions));
            
            // Update remote.
            if ($updateremotefields) {
                $settings->add(new admin_setting_configselect("auth_{$auth}/field_updateremote_{$field}",
                get_string('auth_updateremotefield', 'auth', $fieldname), '', 0, $updateextoptions));
            }
            
            // Lock fields.
            $settings->add(new admin_setting_configselect("auth_{$auth}/field_lock_{$field}",
            get_string('auth_fieldlockfield', 'auth', $fieldname), '', 'unlocked', $lockoptions));
            
        } else {
            // Lock fields Only.
            $settings->add(new admin_setting_configselect("auth_{$auth}/field_lock_{$field}",
            get_string('auth_fieldlockfield', 'auth', $fieldname), '', 'unlocked', $lockoptions));
        }
    }
}
