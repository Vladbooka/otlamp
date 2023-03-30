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
 * Блок Надо проверить. Веб-сервисы
 *
 * @package    block_notgraded
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


namespace auth_dof;

defined('MOODLE_INTERNAL') || die();

require_once("$CFG->libdir/externallib.php");

use external_api;
use external_function_parameters;
use external_value;
use external_multiple_structure;
use external_single_structure;
use core_user;

class external extends external_api
{
    /**
     * Returns description of method parameters
     * @return external_function_parameters
     */
    public static function create_user_parameters()
    {
        global $CFG;
        
        return new external_function_parameters([
            'userdata' => new external_single_structure([
                'username' => new external_value(
                    core_user::get_property_type('username'),
                    'Username policy is defined in Moodle security config.',
                    VALUE_OPTIONAL
                ),
                'password' => new external_value(
                    core_user::get_property_type('password'),
                    'Plain text password consisting of any characters',
                    VALUE_OPTIONAL
                ),
                'createpassword' => new external_value(
                    PARAM_BOOL,
                    'True if password should be created and mailed to user.',
                    VALUE_OPTIONAL
                ),
                'firstname' => new external_value(
                    core_user::get_property_type('firstname'),
                    'The first name(s) of the user',
                    VALUE_OPTIONAL
                ),
                'lastname' => new external_value(
                    core_user::get_property_type('lastname'),
                    'The family name of the user',
                    VALUE_OPTIONAL
                ),
                'email' => new external_value(
                    core_user::get_property_type('email'),
                    'A valid and unique email address',
                    VALUE_OPTIONAL
                ),
                'idnumber' => new external_value(
                    core_user::get_property_type('idnumber'),
                    'An arbitrary ID code number perhaps from the institution',
                    VALUE_DEFAULT,
                    ''
                ),
                'lang' => new external_value(
                    core_user::get_property_type('lang'),
                    'Language code such as "en", must exist on server',
                    VALUE_DEFAULT,
                    core_user::get_property_default('lang'),
                    core_user::get_property_null('lang')
                ),
                'calendartype' => new external_value(
                    core_user::get_property_type('calendartype'),
                    'Calendar type such as "gregorian", must exist on server',
                    VALUE_DEFAULT,
                    $CFG->calendartype
                ),
                'theme' => new external_value(
                    core_user::get_property_type('theme'),
                    'Theme name such as "standard", must exist on server',
                    VALUE_OPTIONAL
                ),
                'timezone' => new external_value(
                    core_user::get_property_type('timezone'),
                    'Timezone code such as Australia/Perth, or 99 for default',
                    VALUE_OPTIONAL
                ),
                'mailformat' => new external_value(
                    core_user::get_property_type('mailformat'),
                    'Mail format code is 0 for plain text, 1 for HTML etc',
                    VALUE_OPTIONAL
                ),
                'description' => new external_value(
                    core_user::get_property_type('description'),
                    'User profile description, no HTML',
                    VALUE_OPTIONAL
                ),
                'city' => new external_value(
                    core_user::get_property_type('city'),
                    'Home city of the user',
                    VALUE_OPTIONAL
                ),
                'country' => new external_value(
                    core_user::get_property_type('country'),
                    'Home country code of the user, such as AU or CZ',
                    VALUE_OPTIONAL
                ),
                'firstnamephonetic' => new external_value(
                    core_user::get_property_type('firstnamephonetic'),
                    'The first name(s) phonetically of the user',
                    VALUE_OPTIONAL
                ),
                'lastnamephonetic' => new external_value(
                    core_user::get_property_type('lastnamephonetic'),
                    'The family name phonetically of the user',
                    VALUE_OPTIONAL
                ),
                'middlename' => new external_value(
                    core_user::get_property_type('middlename'),
                    'The middle name of the user',
                    VALUE_OPTIONAL
                ),
                'alternatename' => new external_value(
                    core_user::get_property_type('alternatename'),
                    'The alternate name of the user',
                    VALUE_OPTIONAL
                ),
                'preferences' => new external_multiple_structure(
                    new external_single_structure([
                        'name'  => new external_value(PARAM_RAW, 'The name of the preference'),
                        'value' => new external_value(PARAM_RAW, 'The value of the preference')
                    ]),
                    'User preferences',
                    VALUE_OPTIONAL
                ),
                'customfields' => new external_multiple_structure(
                    new external_single_structure([
                        'type'  => new external_value(PARAM_ALPHANUMEXT, 'The name of the custom field'),
                        'value' => new external_value(PARAM_RAW, 'The value of the custom field')
                    ]),
                    'User custom fields (also known as user profil fields)',
                    VALUE_OPTIONAL
                )
            ])
        ]);
    }
    
    /**
     * Returns count notgraded items
     * @return string welcome message
     */
    public static function create_user($userdata)
    {
        global $CFG, $DB;
        require_once($CFG->dirroot."/lib/weblib.php");
        require_once($CFG->dirroot."/user/lib.php");
        require_once($CFG->dirroot."/user/editlib.php");
        require_once($CFG->dirroot."/user/profile/lib.php"); // Required for customfields related function.
        
        // Ensure the current user is allowed to run this function.
        $context = \context_system::instance();
        self::validate_context($context);
        require_capability('moodle/user:create', $context);
        
        $params = self::validate_parameters(self::create_user_parameters(), ['userdata' => $userdata]);
        
        
        $transaction = $DB->start_delegated_transaction();
        
        $authplugin = get_auth_plugin('dof');
        $user = (object)$params['userdata'];
        list($user, $userpassword) = $authplugin->process_user_signup($user);
        $authplugin->process_user_signup_confirmation($user, $userpassword, false);
        
        // Custom fields.
        if (!empty($user->customfields)) {
            foreach ($user->customfields as $customfield) {
                // Profile_save_data() saves profile file it's expecting a user with the correct id,
                // and custom field to be named profile_field_"shortname".
                $user->{"profile_field_".$customfield['type']} = $customfield['value'];
            }
            profile_save_data($user);
        }
        
        // Preferences.
        if (!empty($user->preferences)) {
            foreach ($user->preferences as $preference) {
                $user->{'preference_'.$preference['name']} = $preference['value'];
            }
            useredit_update_user_preference($user);
        }
        
        $transaction->allow_commit();
        
        return [
            'id' => $user->id,
            'username' => $user->username
        ];
    }
    
    /**
     * Returns description of method result value
     * @return external_single_structure
     */
    public static function create_user_returns()
    {
        return new external_single_structure([
            'id'       => new external_value(core_user::get_property_type('id'), 'user id'),
            'username' => new external_value(core_user::get_property_type('username'), 'user name'),
        ]);
    }
}