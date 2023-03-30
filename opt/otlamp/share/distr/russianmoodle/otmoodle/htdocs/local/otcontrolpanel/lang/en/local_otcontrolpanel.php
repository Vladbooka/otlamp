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
 * Панель управления СЭО 3KL. Языковые строки.
 *
 * @package    local_otcontrolpanel
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$string['pluginname'] = 'LMS 3KL control panel';
$string['config'] = 'Configuration';
$string['view_noname'] = '{$a->entityname} #{$a->viewcode}';
$string['starttext'] = '<div> This tool is designed to view and perform bulk actions on various system entities. </div>
<div> The tool is not optimized for working with a large number of records. </div>
<div> To continue working with the tool, select the tab. </div>';
$string['filterform_header'] = 'Filter form';
$string['filterform_applied'] = 'Filter form was applied {$a}';
$string['filterform_cancel'] = '(cancel)';


$string['otcontrolpanel:view_data'] = 'View data in the LMS 3KL control panel';
$string['otcontrolpanel:config'] = 'Config the LMS 3KL control panel for any user';
$string['otcontrolpanel:config_my'] = 'Config the LMS 3KL control panel for self';
$string['otcontrolpanel:take_actions'] = 'Take actions using LMS 3KL control panel';


$string['e_user'] = 'Users';
$string['e_user_fld_fullname'] = 'Fullname';


$string['e_cohort'] = 'Cohorts';
$string['e_cohort_r_course'] = 'Courses with cohort, that was synchronized with the course through enrol "Cohort"';
$string['e_cohort_r_user'] = 'Users who are members of a cohort';
$string['e_cohort_a_enrol_to_courses'] = 'Enrol to courses';
$string['e_cohort_a_enrol_to_courses_fe_courses'] = 'Specify the courses for which the selected cohorts should be enrolled';
$string['e_cohort_a_enrol_to_courses_fe_roleid'] = 'Role';
$string['e_cohort_a_enrol_to_courses_fe_groupmode'] = 'Synchronization mode with local groups';
$string['e_cohort_a_enrol_to_courses_fe_groupmode_nogroup'] = 'No local group';
$string['e_cohort_a_enrol_to_courses_fe_groupmode_samename'] = 'Local group of the same name';
$string['e_cohort_a_enrol_to_courses_fe_creategroup'] = 'Create a local group if it doesn\'t exist';
$string['e_cohort_a_enrol_to_courses_fe_submit'] = 'Enrol';
$string['e_cohort_a_enrol_to_courses_report_message'] = 'An instance ({$a->instanceid}) has been created for the course "{$a->coursefullname}" for the cohort "{$a->cohortname}"';
$string['e_cohort_a_enrol_to_courses_err_nocourses'] = 'You must select at least one course for enrolling';
$string['e_cohort_a_enrol_to_courses_err_no_site'] = 'Enrollment for the course "{$a->coursefullname}" could not be created because this is the home page';
$string['e_cohort_a_enrol_to_courses_err_context_failed'] = 'The cohort "{$a->cohortname}" will not be subscribed to course "{$a->coursefullname}" because it is placed in an inaccessible context.';
$string['e_cohort_a_unenrol_from_courses'] = 'Unenrolling selected cohorts from courses';
$string['e_cohort_a_unenrol_from_courses_fe_courses'] = 'Specify the courses to unenroll the selected cohorts';
$string['e_cohort_a_unenrol_from_courses_fe_delete_empty_group'] = 'Delete a linked group in a course if there are no members left';
$string['e_cohort_a_unenrol_from_courses_fe_submit'] = 'Unenroll';
$string['e_cohort_a_unenrol_from_courses_err'] = 'Error: {$a}';
$string['e_cohort_a_unenrol_from_courses_err_nocourses'] = 'You must select at least one course for unenrolling';
$string['e_cohort_a_unenrol_from_courses_report_message'] = 'An enrol ({$a->instanceid}) has been deleted for the course "{$a->coursefullname}" for the cohort "{$a->cohortname}"';

$string['e_course'] = 'Courses';
$string['e_course_fld_categoryname'] = 'Category name';
$string['e_course_fld_categorypath'] = 'Category path';
$string['e_course_fld_coursepath'] = 'Course path';
$string['e_course_r_cohort'] = 'Cohorts, that was synchronized with the course through enrol "Cohort"';
$string['e_course_r_students'] = 'Users enrolled in a course with gradebook role';
$string['e_course_r_contacts'] = 'Course contacts';
$string['e_course_r_certissues'] = 'Certificates issued in the course';
$string['e_course_r_userscompleted'] = 'Users, who completed the course';
$string['e_course_r_assign_submission'] = 'Assign submissions of course';
$string['e_course_r_assign_submission_first_attempt'] = 'First attempts of assign submissions of course';
$string['e_course_a_enrol_cohorts'] = 'Enrol cohorts';
$string['e_course_a_enrol_cohorts_fe_cohorts'] = 'Specify cohorts that should be connected to the selected courses';
$string['e_course_a_enrol_cohorts_fe_roleid'] = 'Role';
$string['e_course_a_enrol_cohorts_fe_groupmode'] = 'Synchronization mode with local groups';
$string['e_course_a_enrol_cohorts_fe_groupmode_nogroup'] = 'No local group';
$string['e_course_a_enrol_cohorts_fe_groupmode_samename'] = 'Local group of the same name';
$string['e_course_a_enrol_cohorts_fe_creategroup'] = 'Create a local group if it doesn\'t exist';
$string['e_course_a_enrol_cohorts_fe_submit'] = 'Enrol';
$string['e_course_a_enrol_cohorts_err_nocohorts'] = 'You must select at least one cohort for enrolling';
$string['e_course_a_enrol_cohorts_err_no_site'] = 'Enrollment for the course "{$a->coursefullname}" could not be created because this is the home page';
$string['e_course_a_enrol_cohorts_err_context_failed'] = 'The cohort "{$a->cohortname}" will not be subscribed to course "{$a->coursefullname}" because it is placed in an inaccessible context.';
$string['e_course_a_enrol_cohorts_report_message'] = 'An enrol ({$a->instanceid}) has been created for the course "{$a->coursefullname}" for the cohort "{$a->cohortname}"';
$string['e_course_a_unenrol_cohorts'] = 'Unenrolling cohorts from selected courses';
$string['e_course_a_unenrol_cohorts_fe_cohorts'] = 'Specify the cohorts to unenroll from the selected courses';
$string['e_course_a_unenrol_cohorts_fe_delete_empty_group'] = 'Delete a linked group in a course if there are no members left';
$string['e_course_a_unenrol_cohorts_fe_submit'] = 'Unenroll';
$string['e_course_a_unenrol_cohorts_err'] = 'Error: {$a}';
$string['e_course_a_unenrol_cohorts_err_nocohorts'] = 'You must select at least one cohort for unenrolling';
$string['e_course_a_unenrol_cohorts_report_message'] = 'An enrol ({$a->instanceid}) has been deleted for the course "{$a->coursefullname}" for the cohort "{$a->cohortname}"';


$string['e_enrol'] = 'Enrols';


$string['e_certissues'] = 'Issued certificates';


$string['e_assign_submission'] = 'Assign submissions';
$string['e_assign_submission_fld_assignuserattempt'] = 'Attempt';


$string['action_execute'] = 'Execute an action';
$string['action_no_rows_selected'] = 'No rows selected';
$string['action_select_rows'] = 'Select rows on which you want to perform an action';
$string['selected_objects_header'] = 'Selected objects ({$a->objects_count})';
$string['choose_action_header'] = 'Action selection';
$string['choose_action_noactions'] = 'Unfortunately, no action is yet provided for this entity.';
$string['choose_action_field'] = 'Select the action you would like to perform on the selected rows';
$string['choose_action_submit'] = 'Select';
$string['action_settings_header'] = 'Action settings';
$string['no_records_to_display'] = 'Records not found';
$string['no_columns_to_display'] = 'Misconfigured columns';
$string['shortstring_show_all'] = 'Show all';
$string['action_report_warning'] = 'In order for the changes made to be reflected in the table, refresh the page';
$string['action_report_header'] = 'Results of action execution';


$string['config_is_loading'] = 'Loading configuration...';
$string['display_entity'] = 'Display entity';
$string['entity_fields'] = 'Main fields';
$string['entity_relation_related_entity'] = 'Fields of related entity: {$a}';
$string['entity_relation'] = '{$a}';
$string['config_restore_defaults'] = 'Restore defaults';
$string['views'] = 'Tabs';
$string['view_header_edit'] = 'Editing';
$string['view_displayname'] = 'Display name';
$string['view_save_changes'] = 'Save';
$string['view_cancel_changes'] = 'Cancel';
$string['adding_new_view'] = 'New tab';
$string['add_new_view'] = 'Add';

