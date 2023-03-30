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
 * Общие языковые строки
 */
$string['pluginname'] = 'Mutual assessment';
$string['modulename'] = 'Mutual assessment';
$string['modulename_help'] = 'Mutual assessment module allows students to assess each other through distribution or scoring. The module supports peer-to-peer grading of course participants or members of a local group (or grouping). The grading results are displayed in the Score Report and are included in the course\'s current grades journal.';
$string['modulenameplural'] = 'Mutual assessment';
$string['pluginadministration'] = 'Mutual assessment administration';
$string['no_graded_users'] = 'No users available for grading';
$string['instruction_for_grader_mutual_0'] = '<p>To set points for the participants in the course/group, use the form below.<br/> You need to distribute {$a->points} point among all participants.</p>';
$string['instruction_for_grader_mutual_1'] = '<p>To set points for the participants in the course/group, use the form below.<br/> You need to distribute {$a->points} points among all participants.</p>';
$string['instruction_for_grader_mutual_2'] = '<p>To set points for the participants in the course/group, use the form below.<br/> You need to distribute {$a->points} points among all participants.</p>';
$string['instruction_for_grader_mutual'] = '<p>To set points for the participants in the course/group, use the form below.<br/> You need to distribute {$a->points} points among all participants.</p>';
$string['instruction_for_grader_range_0'] = '<p> To set points for the participants in the course/group, use the form below.<br/> You need to put from {$a->min} to {$a->max} points for each participant.</p>';
$string['instruction_for_grader_range_1'] = '<p> To set points for the participants in the course/group, use the form below.<br/> You need to put from {$a->min} to {$a->max} points for each participant.</p>';
$string['instruction_for_grader_range_2'] = '<p> To set points for the participants in the course/group, use the form below.<br/> You need to put from {$a->min} to {$a->max} points for each participant.</p>';
$string['instruction_for_grader_range'] = '<p> To set points for the participants in the course/group, use the form below.<br/> You need to put from {$a->min} to {$a->max} points for each participant.</p>';
$string['grades_already_set'] = 'You graded all participants';
$string['your_total_points_0'] = 'Currently, other participants have given you {$a} point';
$string['your_total_points_1'] = 'Currently, other participants have given you {$a} points';
$string['your_total_points_2'] = 'Currently, other participants have given you {$a} points';
$string['your_total_points'] = 'Currently, other participants have given you {$a} points';
$string['report_link_text'] = 'Go to report';
$string['report'] = 'Points report';
$string['warning_info'] = 'At the moment, not all data refresh tasks have been completed. The data in the report may not be up to date.
                           <br/>When all tasks are complete and the data is up to date, this message will not be displayed.';
$string['points_summ'] = 'Points summ';
$string['grade'] = 'Grade';
$string['empty_report'] = 'None of the participants have not yet graded';
$string['noanycapability'] = 'You do not have any capabilities to work with the course module';
$string['no_enrol_or_capability'] = 'For grading, you must be enrolled to the course and have the capability to grade others';
$string['modulenameplural'] = 'Mutual assessments';
$string['deletepoints'] = 'Delete all grades';
$string['deletestatuses'] = 'Reset graders statuses';
$string['graders_caption'] = 'Graders';
$string['gradeds_caption'] = 'Rated';
$string['refresh'] = 'Refresh grades';
$string['refresh_form_header'] = 'Refresh grades';
$string['refresh_form_submit'] = 'Refresh now';
$string['refresh_task_form_header'] = 'Refresh grades ad hoc task';
$string['refresh_task_form_submit'] = 'Add task';
$string['task_already_added'] = 'The task has already been planned, wait for its completion';
$string['task_added'] = 'The task is planned';
$string['process_refresh_live_ended'] = 'Grades refresh complete';
$string['process_refresh_cron_started'] = 'Grades update tasks added';
$string['process_refresh_not_required'] = 'No data update required as no grades have been posted yet';
$string['status'] = 'Status';
$string['status_notrequired'] = 'Grading not required';
$string['status_completed'] = 'Grading completed';
$string['status_notcompleted'] = 'Grading not completed';
$string['deletevote'] = 'Delete the voting result of a participant {$a->fullname}';
$string['deletegroupvote'] = 'Delete the voting result of a participant {$a->fullname} in group {$a->groupname}';
$string['deletevote_desc'] = 'Deletion will cause the grader to cancel the completion of the course module and recalculate the grades of other participants.
                              <br/>Voting history will not be saved and data cannot be restored.';
$string['deletevote_success'] = 'Voting result for user {$a} was deleted';
$string['deletegroupvote_success'] = 'Voting result for user {$a} in group {$a->groupname} was deleted';
$string['deletevote_failed'] = 'An error occurred while deleting the voting result for user {$a}. Please contact technical support.';
$string['deletegroupvote_failed'] = 'An error occurred while deleting the voting result for user {$a} in group {$a->groupname}. Please contact technical support.';

/**
 * Форма редактирования элемента курса
 */
$string['strategy_mutualassessment'] = 'Mutual assessment';
$string['title'] = 'Title';
$string['description'] = 'Description';
$string['strategy'] = 'Strategy';
$string['strategy_help'] = 'The selected assessment strategy affects the formation of groups and the calculation of grades. After grading any of the participants, changing the module strategy is not possible.';
$string['completionsetgrades'] = 'Student must grade other participants';
$string['save_grades'] = 'Save grades';
$string['leftpoints'] = 'It remains to distribute';
$string['strategy_mutual'] = 'Mutual assessment: distribution of points';
$string['strategy_range'] = 'Mutual assessment: selection from a range of points';
$string['gradingmode'] = 'Grading mode';
$string['absolute_gradingmode'] = 'Absolute';
$string['relative_gradingmode'] = 'Relative';
$string['gradingmode_help'] = 'Absolute assessment - the amount of points set by the user by other participants, but not more than the maximum grade for an element of the course. Relative assessment - the ratio of the total points scored by the user by other participants to the maximum possible number of points that he can score theoretically.';
$string['minpoints_label'] = 'The lowest possible number of points';
$string['maxpoints_label'] = 'The maximum possible number of points';

/**
 * Форма сохранения баллов
 */
$string['grades_saved_successfully'] = 'Grades saved successfully';

/**
 * Настройки плагина
 */
$string['settings_savegraderhistory'] = 'Keep voting history when changing the composition of participants?';
$string['settings_savegraderhistory_desc'] = 'By default, when a user is removed from a group or course, their voting history is saved. When you re-subscribe or return to the group, previously assigned points and grades will be restored. If you want to delete the history, set the appropriate setting.';
$string['setting_strategy_range_min'] = 'Default value for the lowest possible number of points';
$string['setting_strategy_range_min_desc'] = 'This value will be automatically substituted in the edit form of the course element as the default value in the corresponding field';
$string['setting_strategy_range_max'] = 'Default value for the maximum possible number of points';
$string['setting_strategy_range_max_desc'] = 'This value will be automatically substituted in the edit form of the course element as the default value in the corresponding field';
$string['settings_page_general'] = 'General settings';
$string['settings_category_strategies'] = 'Strategies';
$string['live_efficiency'] = 'Live';
$string['cron_efficiency'] = 'In the background';
$string['settings_efficiencyofrefresh'] = 'Efficiency of data updating when conditions change';
$string['settings_efficiencyofrefresh_desc'] = 'When the composition of participants is changed (unassigning/assigning a role, deleting user enrolment, adding/removing a member of a group), the module implements a recalculation of ratings, voting statuses and completion statuses of the Mutual assessment module.
                                                <br/>With a large number of course participants, this process can take a significant amount of time and slow down the work.
                                                <br/>In case of slowdown, it is recommended to use background recalculation mode.
                                                <br/>In the background, when a situation arises that requires updating the information, the system will schedule a task to recalculate the data, which will be performed in the next cycle of background tasks processing.
                                                <br/>Until the task is completed, a notification will be shown in the score report that the current data may not be relevant and the task to update the data is scheduled for execution.';

/**
 * Ошибки
 */
$string['error_invalid_grader'] = 'An error occurred while receiving the participant evaluation form: the grader was not installed';
$string['error_invalid_grade_must_be_greater_than_zero'] = 'Invalid value entered: score must be greater than zero';
$string['error_all_points_are_not_distributed'] = 'You did not distribute all the points between the participants';
$string['error_invalid_userpoints_summ'] = 'Invalid user score';
$string['error_failed_to_save_points'] = 'Failed to save points';
$string['error_cannot_load_the_grade_item'] = 'N/A';
$string['error_undefined_otmutualassessment_id'] = 'Could not find module by id or id not passed';
$string['error_failed_to_set_status'] = 'Failed to set status {$a->status} to user with id {$a->userid}';
$string['error_invalid_strategy'] = 'Failed to load assessment strategy';
$string['error_mod_form_strategy_can_not_be_changed'] = 'Some of the grades for the module have already been set; changing the strategy with the given grades is impossible';
$string['error_mod_form_gradingmode_can_not_be_changed'] = 'Some of the grades for the module have already been set; changing the grading mode with the given grades is impossible';
$string['error_mod_form_groupmode_can_not_be_changed'] = 'Some of the grades for the module have already been set; changing the group mode with the given grades is impossible';
$string['error_mod_form_invalid_min_value'] = 'You cannot specify a minimum value less than that specified in the global settings of the course module';
$string['error_mod_form_invalid_max_value'] = 'You cannot specify a maximum value greater than that specified in the global settings of the course module';
$string['error_invalid_grade_must_be_grater_than_min_value'] = 'The minimum value cannot be less {$a}';
$string['error_invalid_grade_must_be_less_than_max_value'] = 'The maximum value cannot be greater {$a}';
$string['error_invalid_grade_must_be_not_empty'] = 'You can not specify an empty value, you need to rate the participant';
$string['error_mod_form_min_must_be_less_max'] = 'The minimum value must be less than the maximum';

/**
 * Сообщения
 */

/**
 * Права
 */
$string['otmutualassessment:gradeothers'] = 'The capability to grade other participants in a course or group';
$string['otmutualassessment:begradedbyothers'] = 'The capability to be graded by other participants in a course or group';
$string['otmutualassessment:addinstance'] = 'The capability to add to the course element Mutual assessment';
$string['otmutualassessment:viewgrades'] = 'The capability view grades';
$string['nopermissions'] = 'You do not have the capability to do this: {$a}';
$string['otmutualassessment:refreshgrades'] = 'The capability to refresh grades';
$string['otmutualassessment:managesettings'] = 'The capability to change the global settings of the module';
$string['otmutualassessment:deletevotes'] = 'The capability to delete the results of voting of participants';

/**
 * События
 */
$string['grade_updated'] = 'Grade updated';
$string['grader_status_updated'] = 'Grader status updated';
$string['refresh_grades'] = 'Refresh grades';

/**
 * Задачи
 */
$string['task_refresh_grades_title'] = 'Refresh grades in the Mutual Assessment module';

