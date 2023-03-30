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
 * Activetime report. Settings.
 *
 * @package    report_activetime
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

require_once($CFG->dirroot . '/report/activetime/locallib.php');

// Just a link to course report.
$ADMIN->add('reports', new admin_externalpage(
    'report_activetime', 
    get_string('pluginname', 'report_activetime'),
    $CFG->wwwroot . "/report/activetime/index.php?id=0", 
    'report/activetime:viewall')
);

// Настройка отображаемых блоком полей
$customfields = report_activetime_get_customfields_list();
$userfields = report_activetime_get_userfields_list();
$settings->add(
    new admin_setting_configmultiselect(
        'report_activetime/userfields',
        get_string('settings_userfields', 'report_activetime'),
        get_string('settings_userfields_desc', 'report_activetime'),
        [],
        array_merge($userfields, $customfields)
        )
    );

// Настройка отображения данных в отчете
$dataorientation = [
    'vertical' => get_string('vertical_dataorientation', 'report_activetime'), 
    'horizontal' => get_string('horizontal_dataorientation', 'report_activetime')
];
$settings->add(
    new admin_setting_configselect(
        'report_activetime/dataorientation',
        get_string('settings_dataorientation', 'report_activetime'),
        get_string('settings_dataorientation_desc', 'report_activetime'),
        'vertical',
        $dataorientation
    )
);

