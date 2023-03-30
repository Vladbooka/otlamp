#!/usr/bin/php
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
 * @package    block
 * @subpackage dof
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

define('CLI_SCRIPT', true);

// Подключаем конфигурационные файлы MOODLE
require_once(dirname(realpath(__FILE__)).'/../locallib.php');
require_once($CFG->libdir.'/clilib.php');

list($options, $unrecognized) = cli_get_params(
    [
		'help' => false
	], 
    [
		'h' => 'help'
	]
);

if ($unrecognized)
{
    $unrecognized = implode("\n  ", $unrecognized);
    cli_error(get_string('cliunknowoption', 'admin', $unrecognized), 2);
}

if ($options['help'])
{
    $help =
"Setup all dof plugins. No parameters required.

Options:
-h, --help            	Print out this help

Example:
\$ php plugin_setup.php
";

    echo $help;
    exit(0);
}
$DOF->mtrace(1,"Plugin setup starts");
$result = $DOF->plugin_setup();
$DOF->mtrace(1,"Plugin setup ends with result: " . ((bool)$result?"true":"false") );

exit(0);

