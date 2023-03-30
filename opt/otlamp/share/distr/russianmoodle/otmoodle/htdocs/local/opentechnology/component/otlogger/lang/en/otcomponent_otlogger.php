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
 *  Strings for OT log system
 * 
 *  @package    local_opentechnology
 *  @subpackage otcomponent_otlog
 *  @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$string['pluginname'] = 'OTlogger';

// Admin settings
$string['log_enabled'] = 'Logging by OTlogger enabled';
$string['log_enabled_description'] = 'Enable logging by OTlogger';
$string['new_log_configuration_name'] = 'Logging configuration name';
$string['log_method_enabled'] = 'Logging configuration enabled';
$string['log_method_enabled_description'] = 'Enable this logging configuration. You may combine different logging configurations.';
$string['log_method_configuration'] = 'Configuration properties';
$string['receiver'] = 'Log receiver';
$string['receiver_description'] = 'Select necessary log receiver';
$string['filter'] = 'Log filter';
$string['filter_noselection'] = 'Nothing selected';
$string['filter_description'] = 'Choose necessary log types';
$string['add_log_configuration'] = 'Add one more configuration';
$string['adding_configurations'] = 'Add new logger configuration';
$string['editing_configurations'] = 'Edit existing logger configuration: {$a}';
$string['delete_configuration'] = 'Delete configuration';

// Log receiver names
$string['error_log'] = 'error_log';

// Capabilities
$string['local/opentechnology:manage_log_parameters'] = 'Manage logging parameters'; 

// Errors
$string['error_empty_configuration_name'] = 'Configuration name cannot be empty.';
$string['error_duplicate_configuration_name'] = 'Configuration name must ba unique.';