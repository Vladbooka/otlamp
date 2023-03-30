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
 * This script unregister site from Moodle.net.
 *
 * @package    core
 * @subpackage cli
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

define('CLI_SCRIPT', true);

require(__DIR__.'/../../config.php');
require_once($CFG->libdir.'/clilib.php');
require_once($CFG->libdir.'/filelib.php');
require_once($CFG->libdir.'/classes/hub/registration.php');


// Now get cli options.
list($options, $unrecognized) = cli_get_params(
    array(
        'help' => false,
        'identifier' => null
    ),
    array(
        'h' => 'help',
        'i' => 'identifier'
    )
    );

if ($unrecognized) {
    $unrecognized = implode("\n  ", $unrecognized);
    cli_error(get_string('cliunknowoption', 'admin', $unrecognized));
}

if ($options['help']) {
    $help = "
    Unregister site from Moodle.net.
        
    Options:
    -h, --help                          Print out this help.
    -i, --identifier                    If the specified identifier is found, it will be deleted.
        
    Example:
    \$sudo -u www-data /usr/bin/php admin/cli/unset_site_identifier.php -i=gRvqxQkOxHYgRafQ21Cc9nr5J84gDtPIlms.demo.opentechnology.ru
";
    
    cli_writeln($help);
    exit(0);
}

// #### Search for user
try {
    $identifier = get_config('core', 'siteidentifier');
    $wasregistered = \core\hub\registration::is_registered();
    if (is_null($options['identifier']) || $options['identifier'] == $identifier) {
        // Идентификатор не задан или совпадает с найденным в системе - удаляем его
        if (\core\hub\registration::unregister(true, true)) {
            if (unset_config('siteidentifier')) {
                cli_writeln(get_string('unset_site_identifier_successfull', 'local_opentechnology'));
            } else {
                cli_writeln(get_string('unset_site_identifier_failed', 'local_opentechnology'));
            }
        } else {
            cli_writeln(get_string('site_identifier_not_found', 'local_opentechnology'));
        }
    }
    
    if ($wasregistered) {
        if (!\core\hub\registration::is_registered()) {
            cli_writeln(get_string('unregister_successfull', 'local_opentechnology'));
        } else {
            cli_writeln(get_string('unregister_failed', 'local_opentechnology'));
        }
    }
} catch(Exception $ex) {
    
    cli_error($ex->getMessage(), 101);
}

exit(0);
