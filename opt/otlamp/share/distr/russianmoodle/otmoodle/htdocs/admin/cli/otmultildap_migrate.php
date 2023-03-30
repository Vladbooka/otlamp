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
 * This script fixed incorrectly deleted users.
 *
 * @package    core
 * @subpackage cli
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

define('CLI_SCRIPT', true);

require(__DIR__.'/../../config.php');
require_once($CFG->libdir.'/clilib.php');
require_once($CFG->dirroot.'/user/lib.php');


// Now get cli options.
list($options, $unrecognized) = cli_get_params(array('help'=>false, 'ldapnum'=>null, 'otmultildap'=>null, 'filter'=>null, 'value'=>null),
    array('h'=>'help', 'n'=>'ldapnum', 'i'=>'otmultildap', 'f'=>'filter','v'=>'value'));

if ($unrecognized) {
    $unrecognized = implode("\n  ", $unrecognized);
    cli_error(get_string('cliunknowoption', 'admin', $unrecognized));
}

if ($options['help']) {
    $help =
        "Migrate users from ldap-based plugins into otmultildap.

        Options:
        -h, --help            Print out this help.
        -n, --ldapnum         Number of ldap-based plugin. Use num > 1 for plugin-duplicates or something else for original ldap.
        -i, --otmultildap     otmultildap-config name to assign users. Please, type here existing config. Verification is not developed.
        -f, --filter          User attribute (profile field) to filter users by. In case of a custom profile field of text type start the field name with 'profile_field_' prefix.
        -v, --value           Filter value.

        Example:
        \$sudo -u www-data /usr/bin/php admin/cli/otmultildap_migrate.php --ldapnum=2 --otmultildap=localclients --filter=institution --value=ouruniversity
        
        \$sudo -u www-data /usr/bin/php admin/cli/otmultildap_migrate.php --ldapnum=1 --otmultildap=localclients --filter=profile_field_custominstitution --value=ourcustomuniversity
        ";

    echo $help;
    die;
}

if (is_null($options['ldapnum']) || is_null($options['otmultildap']))
{
    cli_error("'ldapnum' and 'otmultildap' options are required");
}

if(isset($options['filter']) && !isset($options['value']) OR
   !isset($options['filter']) && isset($options['value']))
{
    cli_error("'filter' and 'value' options cannot be set separately. If you set one of them, set the other one as well.");
}

$ldapbasedplugin = 'ldap';
if ($options['ldapnum'] > 1)
{
    $ldapbasedplugin .= $options['ldapnum'];
}

cli_heading('Migrate users with \''.$ldapbasedplugin.'\' auth to otmultildap \''.$options['otmultildap'].'\' configuration.');

$error = false;


if(isset($options['filter']) && isset($options['value']))
{
    //Если заданы параметры фильтрации по полю профиля
    if (strpos($options['filter'], 'profile_field_') === 0)
    {
        //Если кастомное поле профиля
        $filter = substr($options['filter'], 14);
        
        if(!$customfieldid = $DB->get_record('user_info_field', ['shortname' => $filter],'id'))
        {
            cli_error('There is no ' .  $options['filter'] . ' custom field. Double-check the field name.');
        }
        $sqlparamsdata = [
            'fieldid' => $customfieldid->id,
            'data' => $options['value']
        ];
        $sql = 'SELECT DISTINCT userid FROM {user_info_data} WHERE fieldid = :fieldid AND ' .
        $DB->sql_compare_text('data') . ' = ' . $DB->sql_compare_text(':data');
        $userids = $DB->get_records_sql($sql, $sqlparamsdata);

        list($sqluserids, $whereuserids) = $DB->get_in_or_equal($userids);
        list($sqlauth, $whereauth) = $DB->get_in_or_equal($ldapbasedplugin);
            
        foreach ($whereuserids as $whereuserid){
            $conditions[] = $whereuserid->userid;
        }
        $conditions[] = $whereauth[0];
        $sql = 'SELECT id FROM {user} WHERE id ' . $sqluserids . ' AND auth ' . $sqlauth;
        $users = $DB->get_records_sql($sql, $conditions);
        
    } else {
        //Если стандартное поле профиля из таблицы user
        $conditions = [
            'auth' => $ldapbasedplugin,
            $options['filter'] => $options['value']
        ];
        $users = $DB->get_records('user', $conditions, '', 'id');
    }
    
} else {
    //Если фильтрация не задана
    $conditions = [
        'auth' => $ldapbasedplugin
    ];
    $users = $DB->get_records('user', $conditions, '', 'id');
}

if (!empty($users))
{
    foreach($users as $user)
    {
        try {
            set_user_preference('ldap_configuration_code', $options['otmultildap'], $user->id);
            $user->auth = 'otmultildap';
            user_update_user($user, false);
            echo ".";
        } catch (moodle_exception $e)
        {
            $error = true;
            echo PHP_EOL.$e->getMessage().PHP_EOL;
        }
    }
}
if ($error)
{
    cli_error('An error occurred while the script was running.');
}
exit(0);
