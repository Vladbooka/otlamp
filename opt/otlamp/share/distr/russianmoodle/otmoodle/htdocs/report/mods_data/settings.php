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

require_once($CFG->dirroot.'/report/mods_data/locallib.php');

defined('MOODLE_INTERNAL') || die;

// Just a link to course report.
$ADMIN->add('reports', new admin_externalpage(
    'report_mods_data', 
    get_string('pluginname', 'report_mods_data'),
    $CFG->wwwroot . "/report/mods_data/index.php?id=0", 
    'report/mods_data:view')
);

// Настройка для включения кеширования данных в отчете
$yesno = [
    0 => get_string('no'),
    1 => get_string('yes')
];
$settings->add(
    new admin_setting_configselect(
        'report_mods_data/enablecron',
        get_string('settings_enablecron', 'report_mods_data'),
        get_string('settings_enablecron_desc', 'report_mods_data'),
        0,
        $yesno
    )
);
$dof = report_mods_data_get_dof();
$userfields = $customfields = $customfieldsselect = [];
if( ! is_null($dof) )
{
    $userfields = $dof->modlib('ama')->user(false)->get_userfields_list();
    $customfields = $dof->modlib('ama')->user(false)->get_user_custom_fields();
    foreach($customfields as $customfield)
    {
        $customfieldsselect['profile_field_' . $customfield->shortname] = $customfield->name;
    }
}
$fields = array_merge($userfields, $customfieldsselect);

$settings->add(
    new admin_setting_configmultiselect(
        'report_mods_data/checkedfields',
        get_string('settings_checkedfields', 'report_mods_data'),
        get_string('settings_checkedfields_desc', 'report_mods_data'),
        ['firstname', 'lastname'],
        $fields
    )
);

$settings->add(
    new admin_setting_configduration(
        'report_mods_data/defaultperiod',
        get_string('settings_defaultperiod', 'report_mods_data'),
        get_string('settings_defaultperiod_desc', 'report_mods_data'),
        ['v' => 365, 'u' => 86400]//3600 * 24 * 365
    )
);

$completionmodes = [
    'incomplete' => get_string('incomplete_mode', 'report_mods_data'),
    'complete' => get_string('complete_mode', 'report_mods_data'),
    'ignore' => get_string('ignore_mode', 'report_mods_data'),
    'unknown' => get_string('unknown_mode', 'report_mods_data')
];
$settings->add(
    new admin_setting_configselect(
        'report_mods_data/completionmode',
        get_string('settings_completionmode', 'report_mods_data'),
        get_string('settings_completionmode_desc', 'report_mods_data'),
        'incomplete', 
        $completionmodes
    )
);

$settings->add(
    new admin_setting_configmultiselect(
        'report_mods_data/quiz_attempt_user_fields',
        get_string('settings_quiz_attempt_user_fields', 'report_mods_data'),
        get_string('settings_quiz_attempt_user_fields_desc', 'report_mods_data'),
        ['institution', 'department'], 
        $fields
    )
);

// $settings->add(
//     new admin_setting_configtext(
//         'report_mods_data/allowedmistakes', 
//         get_string('settings_allowedmistakes', 'report_mods_data'),
//         get_string('settings_allowedmistakes_desc', 'report_mods_data'),
//         '-1',
//         PARAM_INT
//     )
// );

$formats = [
    'pdf' => get_string('quiz_attempt_report_pdf_format', 'report_mods_data'), 
    'html' => get_string('quiz_attempt_report_html_format', 'report_mods_data')
];
$settings->add(
    new admin_setting_configselect(
        'report_mods_data/quiz_attempt_report_default_format',
        get_string('settings_quiz_attempt_report_default_format', 'report_mods_data'),
        get_string('settings_quiz_attempt_report_default_format_desc', 'report_mods_data'),
        'pdf',
        $formats
    )
);

$criterias = [
    'gradepass' => get_string('attempt_completion_gradepass', 'report_mods_data'),
    'completion' => get_string('attempt_completion_completion', 'report_mods_data'),
    'gradepasspriority' => get_string('attempt_completion_gradepasspriority', 'report_mods_data'),
    'completionpriority' => get_string('attempt_completion_completionpriority', 'report_mods_data')
];
$settings->add(
    new admin_setting_configselect(
        'report_mods_data/attempt_completion_critetia',
        get_string('settings_attempt_completion_critetia', 'report_mods_data'),
        get_string('settings_attempt_completion_critetia_desc', 'report_mods_data'),
        'completion',
        $criterias
    )
);

$orientations = [
    'h' => get_string('xls_orientation_h', 'report_mods_data'),
    'v' => get_string('xls_orientation_v', 'report_mods_data')
];
$settings->add(
    new admin_setting_configselect(
        'report_mods_data/xls_orientation',
        get_string('settings_xls_orientation', 'report_mods_data'),
        get_string('settings_xls_orientation_desc', 'report_mods_data'),
        'h',
        $orientations
    )
);
