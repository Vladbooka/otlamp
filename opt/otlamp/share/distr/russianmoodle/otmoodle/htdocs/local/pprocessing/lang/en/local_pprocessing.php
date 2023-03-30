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
 * Языковые строки
 *
 * @package    local_pprocessing
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Common language strings
 */
$string['pluginname'] = 'Panel to handling use cases';
$string['yes'] = 'Yes';
$string['no'] = 'No';

/**
 * Warnings and Errors
 */
$string['script_conflict'] = '<b>Attention!</b><br>Two scripts are active in the system that implement the assignment / removal of roles "Remove assigned roles" and "Assign or remove roles to users according to criteria".
                               It is possible that the scenario "Remove Assigned Roles" will remove a role from a user, and "Assign or remove a role to users according to criteria" will reassign it,
                                it will not lead to errors but will create unnecessary load on the system.';

/**
 * Capabilities
 */
$string['pprocessing:receive_notifications'] = 'Right to receive notifications';
$string['messageprovider:notifications'] = 'Notification of use cases';
$string['messageprovider:service_messages'] = 'Case Processing Panel: Service Notifications';

/**
 * Tasks
 */
$string['task_asap'] = 'As soon as possible handling';
$string['task_hourly'] = 'Handling use cases hourly task';
$string['task_daily'] = 'Handling use cases daily task';
$string['task_weekly'] = 'Handling use cases weekly task';
$string['task_monthly'] = 'Handling use cases monthly task';

$string['event_hourly_executed'] = 'Handling use cases hourly event';
$string['event_daily_executed'] = 'Handling use cases daily event';
$string['event_weekly_executed'] = 'Handling use cases weekly event';
$string['event_monthly_executed'] = 'Handling use cases monthly event';
$string['event_iteration_initialized'] = 'New event based iteration was initialized';

/**
 * Script headers
 */
$string['spelling_mistake_header'] = 'Notification of spelling mistake';
$string['student_enrolled_header'] = 'Notification of the student about the subscription to the course';
$string['teacher_enrolled_header'] = 'Notification of the teacher about the subscription to the course with the right to evaluate other users';
$string['user_registered_recently__header'] = 'Notification of the user about recently registration';
$string['user_registered_long_ago__header'] = 'Notification of the user about the long ago registration';
$string['user_registered_long_ago_deleting__header'] = 'Removing never authorized users, since registration was two months';
$string['role_unassign__header'] = 'Deleting role assignments';
$string['send_user_password__header'] = 'Sending notifications with a password to users loaded into the system';
$string['sync_user_cohorts__header'] = 'Synchronize users with cohorts';
$string['sync_user_cohorts_task__header'] = 'Synchronize users with cohorts on a schedule';
$string['send_user_db_password__header'] = 'Saving and sending passwords from an external database to users uploaded to the system';
$string['export_grades_header'] = 'Sending grades to an external database';
$string['export_grades_header_desc'] = '';
$string['empty_connections_export_grades_header'] = 'Sending grades to an external database';
$string['empty_connections_export_grades_header_desc'] = 'To configure the script, you need to <a href="/local/opentechnology/dbconnection_management.php">create a connection to an external database</a>';
$string['export_grades_schedule_header'] = 'Export existing grades to an external database on a schedule';
$string['export_grades_schedule_header_desc'] = 'The script is intended for unloading the grades existing in the system (those grades, the events of which occurred before the activation of the scenario "Sending grades to an external database"). The script\'s operating mode is governed by the settings of the "Sending grades to an external database" script.';
$string['empty_connections_export_grades_schedule_header'] = 'Export existing grades to an external database on a schedule';
$string['empty_connections_export_grades_schedule_header_desc'] = 'To configure the script, you need to <a href="/local/opentechnology/dbconnection_management.php">create a connection to an external database</a>';

/**
 * Scripting activation headers
 */
$string['action_status'] = 'Enable action execution';
$string['role_unassign_status'] = 'Enable deactivation of roles';
$string['send_user_password_status'] = 'Enable password notifications';
$string['sync_user_cohorts_status'] = 'Enable sync';
$string['sync_user_cohorts_task_status'] = 'Enable scheduled sync';
$string['send_user_password_status_desc'] = 'For the scenario to work, you need to <a href="/admin/tool/task/scheduledtasks.php?action=edit&task=core%5Ctask%5Csend_new_user_passwords_task">disable</a> practical tasks Send new user passwords (\core\task\send_new_user_passwords_task)';
$string['send_user_db_password_status'] = 'Enable saving passwords from an external database';
$string['send_user_db_password_status_desc'] = 'The script requires <a href="/admin/tool/task/scheduledtasks.php?action=edit&task=core%5Ctask%5Csend_new_user_passwords_task">disable</a> completing the task Sending passwords to new users (\core\task\send_new_user_passwords_task).
                                                The password in the external database must be saved in plain text.
                                                The authentication method settings will be used to get the password <a href="/admin/settings.php?section=authsettingdb">External database</a>';
$string['settings_export_grades_schedule_status'] = 'Enable export existing grades on a schedule';
$string['settings_export_grades_schedule_status_desc'] = '';

/**
 * Advanced script settings
 */
$string['settings_recievers'] = 'Select recipients';
$string['settings_recievers_desc'] = 'If the field is empty, all site administrators will receive notifications. Recipients are selected by right of local / pprocessing: receive_notifications in the context of the main page. To set the spelling of spelling errors, go to Administration -> Appearance -> Themes -> LMS 3KL -> Profile (select your profile) -> General Settings.
                                     <br>To select a provider through which error messages will be sent, you need to change the "Use Case Processing Panel" -> "Use-Case Notifications" setting in the user notification settings, or change the global notification settings in the "Administration" -> "Plugins" -> "Delivery Methods Messages" -> "Default Settings for Message Delivery Methods"';
$string['message_subject'] = 'Notification Header';
$string['message_full'] = 'Full notice text';
$string['message_short'] = 'Short notice text';
$string['message_status'] = 'Enable notification sending';
$string['settings_role_unassign_context'] = 'Level of context for finding role assignments';
$string['settings_role_unassign_context_desc'] = 'Removing assignments will only occur in the selected level of context';
$string['settings_role_unassign_role'] = 'Assigned role';
$string['settings_role_unassign_role_desc'] = 'Removing assignments will only occur from the specified role';
$string['settings_role_unassign_context_none'] = 'Select a level of context...';
$string['settings_role_unassign_context_system'] = 'System';
$string['settings_role_unassign_context_coursecat'] = 'Category';
$string['settings_role_unassign_context_course'] = 'Course';
$string['settings_role_unassign_context_module'] = 'Module';
$string['settings_role_unassign_context_user'] = 'User';
$string['settings_role_unassign_context_block'] = 'Block';
$string['settings_role_unassign_role_none'] = 'Select a role...';

$string['setting_send_user_password_message_subject'] = 'Notification Header';
$string['setting_send_user_password_message_full'] = 'Full notice text';
$string['setting_send_user_password_message_short'] = 'Short notice text';
$string['newusernewpassword_message_full'] = 'Hi %{user.fullname}!

                                              A new account has been created for you at \'%{site.fullname}\' and you have been issued with a new temporary password.

                                              Your current login information is now:
                                              username: %{user.username}
                                              password: %{generated_code}
                                              (you will have to change your password when you login for the first time)

                                              To start using \'%{site.fullname}\', login at %{site.loginurl}

                                              In most mail programs, this should appear as a blue link which you can just click on. If that doesn\'t work, then cut and paste the address into the address line at the top of your web browser window.

                                              Cheers from the \'%{site.fullname}\' administrator, %{site.signoff}';
$string['newusernewpassword_message_subject'] = 'New user account';
$string['newusernewpassword_message_short'] = 'Username: %{user.username}, password: %{generated_code}';
$string['settings_send_user_password_auth_forcepasswordchange'] = 'Force password change';
$string['settings_send_user_password_auth_forcepasswordchange_desc'] = 'If this checkbox is ticked, the user will be prompted to change their password on their next login';

$string['setting_send_user_db_password_message_subject'] = 'Notification Header';
$string['setting_send_user_db_password_message_full'] = 'Full notice text';
$string['setting_send_user_db_password_message_short'] = 'Short notice text';
$string['newusernewdbpassword_message_full'] = 'Hi %{user.fullname}!

                                              A new account has been created for you at \'%{site.fullname}\' and you have been issued with a new temporary password.

                                              Your current login information is now:
                                              username: %{user.username}
                                              password: %{extdbpassworld}
                                              (you will have to change your password when you login for the first time)

                                              To start using \'%{site.fullname}\', login at %{site.loginurl}

                                              In most mail programs, this should appear as a blue link which you can just click on. If that doesn\'t work, then cut and paste the address into the address line at the top of your web browser window.

                                              Cheers from the \'%{site.fullname}\' administrator, %{site.signoff}';
$string['newusernewdbpassword_message_subject'] = 'New user account';
$string['newusernewdbpassword_message_short'] = 'Username: %{user.username}, password: %{extdbpassworld}';
$string['settings_send_user_db_password_auth_forcepasswordchange'] = 'Force password change';
$string['settings_send_user_db_password_auth_forcepasswordchange_desc'] = 'If this checkbox is ticked, the user will be prompted to change their password on their next login';
$string['settings_send_user_db_password_macro_write'] = 'Specify the text that will be sent to the user. You can also use the following available macro substitutions:<br>
                                                %{extdbpassworld} - Password from an external database;<br>
                                                %{site.fullname} - Full name of the site;<br>
                                                %{site.shortname} - the Short name of the site;<br>
                                                %{site.summary} a Description of the site;<br>
                                                %{site.loginurl} - Link to the authorization page;<br>
                                                %{site.url} - Link to the site;<br>
                                                %{site.signoff} - the site Administrator.<br>
                                                <i>Additional macro substitutions of user data:</i><br>
                                                %{user.fullname} - users full name;<br>
                                                %{user.username} - Username;<br>
                                                %{user.firstname} - first Name;<br>
                                                %{user.lastname} - last Name;<br>
                                                %{user. email} - email Address;<br>
                                                %{user.city} - the City;<br>
                                                %{user.country} - Country;<br>
                                                %{user.lang} - Preferred language;<br>
                                                %{user.description} - Description;<br>
                                                %{user.url} - Web page;<br>
                                                %{user.idnumber} - an Individual number;<br>
                                                %{user.institution} - Institution (organization);<br>
                                                %{user.department} - Department;<br>
                                                %{user.phone1} - phone Number;<br>
                                                %{user.phone2} - Mobile phone;<br>
                                                %{user.address} - Address;';
$string['send_user_db_password_send_message'] = 'Sending a message to the user';
$string['send_user_db_password_send_message_desc'] = 'If this option is active, the user will be sent the following message';
$string['settings_password_type'] = 'Password type in external database';
$string['settings_password_type_desc'] = 'This script supports 2 password options: <br> 1. Text - the password that is stored in clear text <br> 2.MD5 - hash of the password in MD5 format';
$string['pass_plaintext'] = 'Text';
$string['pass_md5'] = 'Hash MD5';

$string['choose_cohorts_field'] = 'Choose the profile field...';
$string['settings_sync_user_cohorts_task'] = 'Enable scheduled synchronization';
$string['settings_sync_user_cohorts_task_desc'] = 'If you need to synchronize existing users in the system, enable this setting. Setup will not work when synchronization is off.';
$string['settings_user_cohorts'] = 'Select the profile field that contains the list of user cohorts';
$string['settings_user_cohorts_desc'] = '';
$string['cohortid'] = 'ID';
$string['cohortname'] = 'Name';
$string['cohortidnumber'] = 'Idnumber';
$string['settings_cohort_identifier'] = 'The cohort identifier should be considered';
$string['settings_cohort_identifier_desc'] = 'Select a field to identify the cohort that will be used when searching for cohorts during synchronization';
$string['settings_cohorts_manage_mode'] = 'Manual managment of cohorts';
$string['settings_cohorts_manage_mode_desc'] = 'The setting allows you to set the system\'s response to editing the composition of cohorts manually. There are 3 scenarios available:
                                                 <br> - the system allows you to add / exclude users manually to cohorts that are not specified in the profile field
                                                 <br> - the system prohibits to add / exclude a user manually to any cohorts';
$string['cohortsmanagemodes_enable'] = 'Enable';
$string['cohortsmanagemodes_disable'] = 'Disable';
$string['setting_sync_user_cohorts_task_desc'] = 'The script writes users to cohorts according to a schedule, according to the pointer settings in the main scenario, "Synchronize users with cohorts".
                                                 <br/>Scheduled work requires the main script to be configured and active.
                                                 <br/>If this scenario is disabled, the user will be added to cohorts according to the user profile according to the main scenario.';
$string['settings_send_user_password_additional_password_settings'] = 'Advanced password settings';
$string['settings_send_user_password_additional_password_settings_desc'] = 'Allows you to set password settings different from password policies in Moodle';
$string['settings_send_user_password_p_maxlen'] = 'Password length';
$string['settings_send_user_password_p_maxlen_desc'] = 'It is not recommended to set a password length of less than 4 characters';
$string['settings_send_user_password_p_numnumbers'] = 'Numeric characters';
$string['settings_send_user_password_p_numnumbers_desc'] = 'Number of numeric characters in a password';
$string['settings_send_user_password_p_numsymbols'] = 'Symbols';
$string['settings_send_user_password_p_numsymbols_desc'] = 'Number of symbols (&, #, ÷, *...) in a password';
$string['settings_send_user_password_p_lowerletters'] = 'Lowercase letters';
$string['settings_send_user_password_p_lowerletters_desc'] = 'Number of lowercase letters in a password';
$string['settings_send_user_password_p_upperletters'] = 'Uppercase letters';
$string['settings_send_user_password_p_upperletters_desc'] = 'Number of uppercase letters in a password';

$string['settings_unenrol_cohorts_by_date_header'] = 'Removing cohort-enrols by date from the custom fields of a cohort';
$string['settings_unenrol_cohorts_by_date_header_desc'] = '';
$string['settings_empty_cohort_config_unenrol_cohorts_by_date_header_desc'] = 'The configuration of custom fields required for the script to work is not configured. To enable the script, configure the cohort_yaml custom fields in the <a href="/admin/settings.php?section=mcov_settings">Custom Fields</a> plugin';
$string['settings_unenrol_cohorts_by_date_status'] = 'Enable enrol deletion scenario';
$string['settings_unenrol_cohorts_by_date_status_desc'] = '';
$string['settings_unenrol_cohorts_by_date_unenroldate'] = 'A custom field that stores the cohort’s unenrol date';
$string['settings_unenrol_cohorts_by_date_unenroldate_desc'] = '';

$string['settings_delete_cohorts_by_date_header'] = 'Removing cohorts by date from the custom fields of a global group';
$string['settings_delete_cohorts_by_date_header_desc'] = '';
$string['settings_empty_cohort_config_delete_cohorts_by_date_header_desc'] = 'The configuration of custom fields required for the script to work is not configured. To enable the script, configure the cohort_yaml custom fields in the <a href="/admin/settings.php?section=mcov_settings">Custom Fields</a> plugin';
$string['settings_delete_cohorts_by_date_status'] = 'Enable cohort deletion scenario';
$string['settings_delete_cohorts_by_date_status_desc'] = '';
$string['settings_delete_cohorts_by_date_deldate'] = 'A custom field that stores the cohort’s remove date';
$string['settings_delete_cohorts_by_date_deldate_desc'] = '';

$string['settings_delete_quiz_attempts_by_date_header'] = 'Delete finished quiz attempts older than a specified date';
$string['settings_delete_quiz_attempts_by_date_header_desc'] = '';
$string['settings_delete_quiz_attempts_by_date_status'] = 'Enable delete finished quiz attempts';
$string['settings_delete_quiz_attempts_by_date_status_desc'] = 'After enabling the script, all unsuccessful completed test attempts, since the completion of which more time has passed than specified in the script settings, will be deleted.
                                                                <br/> Success will be determined according to the test settings:
                                                                <ul><li> when the grading method is set to "First attempt", the first test attempt will be considered a successful attempt, all other completed attempts, from the moment of completion of which more time has passed than specified in the script settings, will be deleted
                                                                    <li> when the "Last attempt" scoring method is set, the last testing attempt will be considered a successful attempt, all other completed attempts, since the completion of which more time has passed than specified in the script settings, will be deleted
                                                                    <li> if the grading method is "Average", none of the attempts will be deleted
                                                                    <li> with the "Highest grade" rating method set, the first test attempt with the highest grade among all attempts will be considered a successful attempt, all other completed attempts, since the completion of which more time has passed than specified in the scenario settings, will be deleted
                                                                </ul> The default script runs once a day. Deleting test attempts can affect the final grade for the test and course (depending on the settings in the system).';
$string['settings_delete_quiz_attempts_by_date_relativedate'] = 'From the moment the attempt is finished, more';
$string['settings_delete_quiz_attempts_by_date_relativedate_desc'] = '';

$string['settings_export_grades_connection'] = 'Database connection';
$string['settings_export_grades_connection_desc'] = 'For the script to work, you must select a connection to the database where the grades will be sended. You can create a database connection at <a href="/local/opentechnology/dbconnection_management.php">External databases connection management</a>';
$string['settings_export_grades_table'] = 'Table name';
$string['settings_export_grades_table_desc'] = 'Specify the name of the table in the external database where the grades will be sended';
$string['settings_export_grades_llh_courseid'] = 'Course ID';
$string['settings_export_grades_llh_courseid_desc'] = '';
$string['settings_export_grades_llh_coursefullname'] = 'Course fullname';
$string['settings_export_grades_llh_coursefullname_desc'] = '';
$string['settings_export_grades_llh_courseshortname'] = 'Course shortname';
$string['settings_export_grades_llh_courseshortname_desc'] = '';
$string['settings_export_grades_llh_finalgrade'] = 'Course grade';
$string['settings_export_grades_llh_finalgrade_desc'] = '';
$string['settings_export_grades_llh_lastupdate'] = 'Course grade date';
$string['settings_export_grades_llh_lastupdate_desc'] = '';
$string['settings_export_grades_llhcm_cmid'] = 'Course Module ID';
$string['settings_export_grades_llhcm_cmid_desc'] = '';
$string['settings_export_grades_llhm_modname'] = 'Short course module name';
$string['settings_export_grades_llhm_modname_desc'] = '';
$string['settings_export_grades_llhm_name'] = 'Full course module name';
$string['settings_export_grades_llhm_name_desc'] = '';
$string['settings_export_grades_llh_userid'] = 'User ID';
$string['settings_export_grades_llh_userid_desc'] = '';
$string['settings_export_grades_llhcm_finalgrade'] = 'Module grade';
$string['settings_export_grades_llhcm_finalgrade_desc'] = '';
$string['settings_export_grades_llhcm_timemodified'] = 'Module grade date';
$string['settings_export_grades_llhcm_timemodified_desc'] = '';
$string['settings_export_grades_llh_activetime'] = 'Course activetime';
$string['settings_export_grades_llh_activetime_desc'] = '';
$string['settings_export_grades_llhcm_activetime'] = 'Module activetime';
$string['settings_export_grades_llhcm_activetime_desc'] = '';
$string['settings_export_grades_data_mapping'] = 'Data Mapping ({$a})';
$string['settings_export_grades_data_mapping_desc'] = '';
$string['settings_export_grades_user_fullname'] = 'User fullname';
$string['settings_export_grades_user_fullname_desc'] = '';
$string['do_not_send'] = 'Do not send';
$string['settings_export_grades_primarykey1'] = 'User ID field';
$string['settings_export_grades_primarykey1_desc'] = 'The field in the external database where the user ID is expected to be stored';
$string['settings_export_grades_foreignkey1'] = 'User ID';
$string['settings_export_grades_foreignkey1_desc'] = 'User profile field to be used as a user ID to find records in an external database';
$string['settings_export_grades_primarykey2'] = 'Course module ID field';
$string['settings_export_grades_primarykey2_desc'] = 'Field in the external database where the course module ID is expected to be stored';
$string['settings_export_grades_foreignkey2'] = 'Course Module ID';
$string['settings_export_grades_foreignkey2_desc'] = 'Local learning history field to be used as a course module ID to find records in an external database';
$string['settings_export_grades_primarykey3'] = 'Course ID field';
$string['settings_export_grades_primarykey3_desc'] = 'Field in the external database where the course ID is expected to be stored';
$string['settings_export_grades_foreignkey3'] = 'Course ID';
$string['settings_export_grades_foreignkey3_desc'] = 'Local learning history field to be used as a course ID to find records in an external database';
$string['llhcm_cmid'] = 'Course module identifier';
$string['llh_courseid'] = 'Course identifier';
$string['settings_export_grades_grade_format'] = 'Grade format for import';
$string['settings_export_grades_grade_format_desc'] = 'Choose format you need to import the grades from the LMS';
$string['settings_export_grades_status'] = 'Enable grades export to an external database';
$string['settings_export_grades_status_desc'] = '';
$string['settings_export_grades_date_format'] = 'Grade timemodified format';
$string['settings_export_grades_date_format_desc'] = '';
$string['dateformat_timestamp'] = 'timestamp';
$string['dateformat_date'] = 'date (YYYY-MM-DD)';
$string['dateformat_datetime'] = 'datetime (YYYY-MM-DD HH:MM:SS)';
$string['settings_export_grades_grade_itemtype'] = 'What grades should be exported?';
$string['settings_export_grades_grade_itemtype_desc'] = '';
$string['gradeitemtype_mod'] = 'Course modules grades';
$string['gradeitemtype_course'] = 'Course grades';
$string['gradeitemtype_all'] = 'All grades';
$string['settings_export_grades_grade_itemmodule'] = 'What module grades should be exported?';
$string['settings_export_grades_grade_itemmodule_desc'] = '';
$string['gradeitemmodule_all'] = 'All modules';
$string['gradeitemmodule_quiz'] = 'Only quizes';
$string['do_not_relate'] = 'Do not relate';
$string['settings_export_grades_description_composite_keys'] = 'Records in the external database are identified when the grades are updated using a composite key of the form "User ID + Course Module ID" (grades for course modules) or "User ID + Course ID" (course grades). To upload and update grades, the appropriate composite keys must be configured (for course module grades, for course grades, or both to download all grades). Using the settings below, you can configure the generation of the required composite keys.';
$string['settings_export_grades_description_mapping_fields'] = 'Using the settings below, you can specify which user data and his training history in which fields of the external database should be saved. For the script to work, saving composite keys is required.';

/**
 * Common settings
 */
$string['common_settings_header'] = 'Common settings';
$string['disable_logging'] = 'Disable logging of the scenarios execution process';
$string['disable_logging_desc'] = '';

/**
 * mtrace
 */
$string['quiz_mtrace'] = 'quiz processed (id = {$a->id}, cmid = {$a->cmid}) at {$a->mtracetime}';
$string['user_mtrace'] = 'user processed (id = {$a->id}) at {$a->mtracetime}';
$string['attempt_mtrace'] = 'attempt processed (id = {$a->id}, quiz = {$a->quiz}) at {$a->mtracetime}';

// Массовое назначение ролей пользователям
$string['settings_assign_role_according_criteria_header'] = 'Assign or remove roles to users according to criteria';
$string['settings_assign_role_according_criteria_header_desc'] = '';
$string['settings_assign_role_according_criteria_status'] = 'Enable bulk role assignment';
$string['settings_assign_role_according_criteria_status_desc'] = 'the script Starts when the user is created and edited.
                                                                    If the user meets the specified criteria in the profile field, the user will be assigned the specified role in the specified context. if the criteria are not met and the specified role is present in the specified context, the role assignment will be removed.';
$string['settings_assign_role_according_criteria_user_field'] = 'select profile field';
$string['settings_assign_role_according_criteria_user_field_desc'] = '';
$string['settings_assign_role_according_criteria_field_ratio_variant'] = 'Selecting a relationship to a value in the profile field';
$string['settings_assign_role_according_criteria_field_ratio_variant_desc'] = '';
$string['settings_assign_role_according_criteria_user_field_value'] = 'profile field Value';
$string['settings_assign_role_according_criteria_user_field_value_desc'] = '';
$string['settings_assign_role_according_criteria_context_level'] = 'Selecting the context level for the role assignment';
$string['settings_assign_role_according_criteria_context_level_desc'] = 'If the context level "Category" is selected, you need to save the settings, which will allow you to select a category from the drop-down list that appears';;
$string['settings_assign_role_according_criteria_assigned_role'] = 'Selecting the role to assign';
$string['settings_assign_role_according_criteria_assigned_role_desc'] = 'You must select a role that matches the selected context';
$string['settings_assign_role_according_criteria_category'] = 'Selecting categories for role assignment';
$string['settings_assign_role_according_criteria_category_desc'] = 'this option only works with the context level set to "category"';

$string['assign_role_according_criteria_fieldratiovariant_equal'] = 'Matches';
$string['assign_role_according_criteria_fieldratiovariant_notequal'] = 'Does not match';
$string['assign_role_according_criteria_fieldratiovariant_contain'] = 'Contains';
$string['assign_role_according_criteria_fieldratiovariant_notcontain'] = 'Does not contain';

