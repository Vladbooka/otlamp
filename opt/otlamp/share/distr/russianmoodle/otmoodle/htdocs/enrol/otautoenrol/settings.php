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
 * otautoenrol enrolment plugin.
 *
 * This plugin automatically enrols a user onto a course the first time they try to access it.
 *
 * @package    enrol
 * @subpackage otautoenrol
 * @date       July 2013
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

if ($ADMIN->fulltree) {

    $settings->add(new admin_setting_heading('enrol_otautoenrol_settings', '', get_string('pluginname_desc', 'enrol_otautoenrol')));

    $settings->add(
        new admin_setting_configcheckbox(
            'enrol_otautoenrol/defaultenrol',
            get_string('defaultenrol', 'enrol'),
            get_string('defaultenrol_desc', 'enrol'),
            0)
    );

    if (!during_initial_install()) {
        $options = get_default_enrol_roles(context_system::instance());
        $student = get_archetype_roles('student');
        $student = reset($student);
        $settings->add(
                new admin_setting_configselect(
                    'enrol_otautoenrol/defaultrole',
                    get_string('defaultrole', 'enrol_otautoenrol'),
                    get_string('defaultrole_desc', 'enrol_otautoenrol'),
                    $student->id,
                    $options
                )
        );
    }

    $settings->add(
        new admin_setting_configcheckbox(
            'enrol_otautoenrol/removegroups',
            get_string('removegroups', 'enrol_otautoenrol'),
            get_string('removegroups_desc', 'enrol_otautoenrol'),
            1
        )
    );
    
    $options = [
        0 => get_string('low_server', 'enrol_otautoenrol'),
        1 => get_string('medium_server', 'enrol_otautoenrol'),
        2 => get_string('powerfull_server', 'enrol_otautoenrol')
    ];
    
    // Реакция срабатывания
    $settings->add(
            new admin_setting_configselect(
                    'enrol_otautoenrol/servertype',
                    get_string('servertype', 'enrol_otautoenrol'),
                    get_string('servertype_desc', 'enrol_otautoenrol'),
                    0,
                    $options
                    )
            );
}
