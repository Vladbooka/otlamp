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

$string['auth_enrolmentordescription'] = 'This method auto enrols parents based on a profile field';
$string['pluginname'] = 'Autoenrol Parents';

$string['enrolmentor_disabled'] = $string['pluginname'] . ' plugin is disabled. Role reassignment is not executed.';
$string['enrolmentor_settingrole'] = 'Select the role to assign here';
$string['enrolmentor_settingrolehelp'] = 'Click on the drop down menu to select a role';
$string['enrolmentor_settingcompare'] = 'Compare these values';
$string['enrolmentor_settingcomparehelp'] = 'This unique value will be compared against the profile field selected below';
$string['enrolmentor_settingprofile_field'] = 'You will compare the unique value above against this profile field';
$string['enrolmentor_settingprofile_fieldhelp'] = 'Select the profile field that says who your mentor is';
$string['enrolmentor_settingprofile_field_heading'] = 'Please add a custom profile field';
$string['enrolmentor_settingdelimeter'] = 'Delimeter';
$string['enrolmentor_settingdelimeter_desc'] = 'You can specify several curator identifiers. Identifiers must be separated by the chosen delimeter symbol.';
$string['enrolmentor_settingupdatementors'] = 'Update mentors';
$string['enrolmentor_settingupdatementors_desc'] = 'Tick this box to force update all mentors of all users not deleted and not suspended. The tick gets removed and the update task put on a waiting list when the settings are saved.';
$string['updatementors_task_title'] = 'All mentors for all users update';
$string['updatementors_task_added'] = 'Mentor update task is added';
$string['updatementors_task_added_paramupdated'] = 'Mentor update task is added due to changes in settings';
