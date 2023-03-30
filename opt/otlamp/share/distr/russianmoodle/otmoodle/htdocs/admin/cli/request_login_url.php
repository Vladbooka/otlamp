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
 * This script  generates url, that contains token, that allows to login automatically.
 *
 * @package    core
 * @subpackage cli
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

define('CLI_SCRIPT', true);

require(__DIR__.'/../../config.php');
require_once($CFG->libdir.'/clilib.php');


// Now get cli options.
list($options, $unrecognized) = cli_get_params(
    array(
        'help' => false,
        'username' => null,
        'verbose' => false,
        'enableauth' => false
    ),
    array(
        'h' => 'help',
        'u' => 'username',
        'v' => 'verbose',
        'e' => 'enableauth'
    )
);

if ($unrecognized)
{
    $unrecognized = implode("\n  ", $unrecognized);
    cli_error(get_string('cliunknowoption', 'admin', $unrecognized));
}

if ($options['help'])
{
    $help = "
    Generates url, that contains token, that allows to login automatically.

    Options:
    -h, --help            Print out this help.
    -u, --username        Username (login) to find out user to login
    -v, --verbose         Describes the script execution process in detail.
    -e, --enableauth      Force enable auth plugin 'auth_userkey' if it is not

    Example:
    \$sudo -u www-data /usr/bin/php admin/cli/request_login_url.php --username=opentechnology
";

    cli_writeln($help);
    exit(0);
}

if (is_null($options['username']))
{
    cli_error("Option 'username' is required.", 100);
}


// #### Search for user
try {
    
    if ($options['verbose'])
    {
        cli_logo(3);
        cli_writeln('');
        cli_writeln(' - Search for a user by username \''.$options['username'].'\' has been started.');
    }

    // /lib/classes/user.php
    $user = core_user::get_user_by_username($options['username'], '*', null, MUST_EXIST);
    if ($user == false || !property_exists($user, 'id'))
    {
        throw new Exception('The expected property of user object was not found.');
    }
    
} catch(Exception $ex) {
    
    cli_error('User not found. ' . $ex->getMessage(), 101);
}


// #### Validating user
try {
    
    if ($options['verbose'])
    {
        cli_writeln(' - User was found, id = '.$user->id.'. Trying to check if the user is active.');
    }
    
    core_user::require_active_user($user);
    
} catch(Exception $ex) {
    
    cli_error('User not active. ' . $ex->getMessage(), 102);
}


// #### Validating auth plugin
try {

    if ($options['verbose'])
    {
        cli_writeln(' - User is active. Trying to get auth plugin \'auth_userkey\'');
    }
    
    // /lib/moodle.lib
    if (!is_enabled_auth('userkey'))
    {// плагин выключен
        
        if (!empty($options['enableauth']))
        {// требуется принудительно включить плагин
            
            if (empty($CFG->auth))
            {
                $enabledplugins = [];
            } else
            {
                $enabledplugins = explode(',', $CFG->auth);
            }
            if (!in_array('userkey', $enabledplugins))
            {
                $enabledplugins[] = 'userkey';
            }
            set_config('auth', implode(',', $enabledplugins));
            
        } else
        {
            cli_error('Auth plugin is disabled.', 103);
        }
    }
    
    // попытка подключения плагина для исключения возможных проблем при авторизации
    $auth = get_auth_plugin('userkey');
    
} catch(Exception $ex) {
    
    cli_error('Auth plugin cannot be used. ' . $ex->getMessage(), 104);
}


// #### Creating user key
try {
    
    if ($options['verbose'])
    {
        cli_writeln(' - The required plugin \'auth_userkey\' exists and enabled. Trying to get login url.');
    }
    
    // Имитируем создание ключа, как в плагине auth_userkey,
    // но с интересующими нас параметрами, не зависимыми от настроек плагина
    $userkey = create_user_key(
        'auth/userkey',
        $user->id,
        $user->id,
        false,
        time() + 60
    );
    
} catch (Exception $ex) {
    
    cli_error('Creating user key failed. ' . $ex->getMessage(), 105);
}


// #### Creating login url
try {
    
    if ($options['verbose'])
    {
        cli_writeln(' - and login url is...');
        cli_writeln('');
        cli_write('   ');
    }
    
    cli_writeln($CFG->wwwroot . '/auth/userkey/login.php?key=' . $userkey);
    
    if ($options['verbose'])
    {
        cli_writeln('');
    }

} catch (Exception $ex) {
    
    cli_error('Creating login url failed. ' . $ex->getMessage(), 106);
}
    

exit(0);
