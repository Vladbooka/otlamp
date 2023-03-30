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
 * Отчет по результатам SCORM. Языковые строки.
 *
 * @package    report
 * @subpackage scorm
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// Base lines
$string['pluginname'] = 'Report on SCORM modules';
$string['scorm:view'] = 'Receive a common SCORM report';
$string['scorm:editmodsettings'] = 'Edit report settings by SCORM module';
$string['scorm:viewstatistic'] = 'Get statistics on the SCORM module';

// Global Report Settings
$string['settings_passpercent'] = 'Passing percentage of SCORM execution (default for all course modules)';
$string['settings_passpercent_desc'] = 'The percentage of executing the scorable elements of the SCORM package, in which it is assumed that the user has successfully completed the course element';

// Report settings for SCORM course modules
$string['settings_form_title'] = 'Report Settings for the SCORM Module';
$string['settings_form_header'] = 'Report settings for the SCORM module';
$string['settings_form_description'] = '';
$string['settings_form_passpercent'] = 'Running percentage of SCORM execution';
$string['settings_form_passpercent_desc'] = 'Set the percentage of the course item to complete';
$string['settings_form_passpercent_placeholder'] = '{$a->defaultpersent}';
$string['settings_form_passpercent_postfix'] = '%';
$string['settings_form_gradeelements_description'] = 'Data for the scorable elements of the SCORM package. In these fields, you must fill in the item IDs and their weight. The final result of passing is the sum of all weights of the executed SCORM package elements. ';
$string['settings_form_gradeelement_id'] = 'SCORM item identifier';
$string['settings_form_gradeelement_id_desc'] = '';
$string['settings_form_gradeelement_weight'] = 'Element weight';
$string['settings_form_gradeelement_weight_desc'] = '';
$string['settings_form_gradeelement_addrow'] = 'Add line';
$string['settings_form_gradeelement_submit'] = 'Save';
$string['settings_form_gradeelement_gradetype_view'] = 'View question';
$string['settings_form_gradeelement_gradetype_correct_answer'] = 'Correct answer';

// Displaying the report
$string['cmsettings_link'] = 'SCORM Report Settings';
$string['report_cm_link'] = 'SCORM Report';
$string['full_report_title'] = 'Detailed statistics report';
$string['short_report_title'] = 'Summary statistics report';
$string['basic_report_title'] = 'Basic statistics report';
$string['reportchoose_form_header'] = 'Receiving a report';
$string['reportchoose_form_select_report_main'] = 'Basic Report';
$string['reportchoose_form_select_report_shortstatistic'] = 'Summary statistics on the passage';
$string['reportchoose_form_select_report_fullstatistic'] = 'Detailed statistics on the passage';
$string['reportchoose_form_select_report_title'] = 'Report Type';
$string['reportchoose_form_select_group_field'] = 'Group by';
$string['reportchoose_form_submit'] = 'Generate report';
$string['reportchoose_form_export_format_pdf'] = 'PDF';
$string['reportchoose_form_export_format_xls'] = 'Excel';
$string['reportchoose_form_export_format_html'] = 'HTML';

// Report Fields
$string['report_scorm_header_finishtime'] = 'Date';
$string['report_scorm_header_material'] = 'Material';
$string['report_scorm_header_username'] = 'User';
$string['report_scorm_header_organization'] = 'Organization';
$string['report_scorm_header_group'] = 'Group';
$string['report_scorm_header_quizresult'] = 'Points';
$string['report_scorm_header_quizstatus'] = 'Status';
$string['report_scorm_header_quizstatus_pass'] = 'Deal';
$string['report_scorm_header_quizstatus_fail'] = 'Not passed';
$string['report_scorm_header_progress'] = 'Viewed';
$string['report_scorm_header_totaltime'] = 'Spent';
$string['report_scorm_header_department'] = 'Department';
$string['report_scorm_header_city'] = 'City';

$string['full_report_header_username'] = 'Login';
$string['full_report_header_email'] = 'Email';
$string['full_report_header_lastname'] = 'Name';
$string['full_report_header_firstname'] = 'Name';
$string['full_report_header_coursename'] = 'Course';
$string['full_report_header_passstatus'] = 'Pass Status';
$string['full_report_header_passpersent'] = 'Percent passing';
$string['full_report_header_city'] = 'City';
$string['full_report_header_passstatus_pass'] = 'Deal';
$string['full_report_header_passstatus_fail'] = 'Not passed';

$string['short_report_header_city'] = 'City';
$string['short_report_header_department'] = 'Department';
$string['short_report_header_course'] = 'Course';
$string['short_report_header_passpersent'] = 'Percentage of depositors';
