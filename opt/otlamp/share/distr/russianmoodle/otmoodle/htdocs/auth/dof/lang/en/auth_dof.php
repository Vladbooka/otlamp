<?php
////////////////////////////////////////////////////////////////////////////
//                                                                        //
// NOTICE OF COPYRIGHT                                                    //
//                                                                        //
// Dean`s Office for Moodle                                               //
// Электронный деканат                                                    //
// <http://deansoffice.ru/>                                               //
//                                                                        //
//                                                                        //
// This program is free software: you can redistribute it and/or modify   //
// it under the terms of the GNU General Public License as published by   //
// the Free Software Foundation, either version 3 of the Licensen.        //
//                                                                        //
// This program is distributed in the hope that it will be useful,        //
// but WITHOUT ANY WARRANTY; without even the implied warranty of         //
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the          //
// GNU General Public License for more details.                           //
//                                                                        //
// You should have received a copy of the GNU General Public License      //
// along with this program.  If not, see <http://www.gnu.org/licenses/>.  //
//                                                                        //
////////////////////////////////////////////////////////////////////////////

/**
 * Плагин аутентификации Деканата. Языковые переменные.
 *
 * @package    auth_dof
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// Базовые переменные
$string['pluginname'] = 'Authentication ERM 3KL';
$string['auth_settings_title'] = 'Authorization with Free Deans Office sync';
$string['auth_dofdescription'] = 'Authorization for <a href=\'http://deansoffice.ru\' target=\'_blank\'>Free Deans Office</a> plugin';
$string['messageprovider:dualauthsendmethod'] = 'Verification key for two-factor authentication';

// Настройки
$string['settings_page_general'] = 'General settings';
$string['settings_signupfields_header'] = 'Form fields settings';
$string['settings_recaptcha'] = 'Adds a form to confirm the visual / audio element on the registration page. This protects your site against spammers. For more information, see http://www.google.com/recaptcha. ';
$string['settings_recaptcha_label'] = 'Enable reCAPTCHA';
$string['settings_passwordfieldtype_label'] = 'What type of field to use to enter a password?';
$string['settings_passwordfieldtype'] = '';
$string['passwordfieldtype_passwordunmask'] = 'Field with the ability to view the password entered';
$string['passwordfieldtype_password'] = 'The field without the ability to view the password entered';
$string['settings_passwordrepeat_label'] = 'Add a field to repeat the password?';
$string['settings_passwordrepeat'] = '';
$string['settings_confirmation_label'] = 'Enable account confirmation by email?';
$string['settings_confirmation'] = '';
$string['settings_auth_after_reg_label'] = 'Instant authorization after registration';
$string['settings_auth_after_reg_desc'] = 'This option is only available in conjunction with the email account verification option. If enabled, the user is authorized immediately after registration. But for the next authorization, he will still need to complete the confirmation.';
$string['settings_title'] = 'Settings';
$string['settings_dof_departmentid_label'] = 'Department';
$string['settings_dof_departmentid'] = 'DOF department which used for adding ner persons ';
$string['settings_sendmethod_label'] = 'Message delivery method';
$string['settings_sendmethod'] = 'Send method for delivering a user registration data';
$string['settings_signupfields_hide'] = 'Hide';
$string['settings_signupfields_show'] = 'Show';
$string['settings_dual_auth'] = 'Two-factor authentication';
$string['settings_enable_dual_auth_label'] = 'Enable two-factor authentication';
$string['settings_enable_dual_auth'] = 'Enable';
$string['settings_code_live_time_label'] = 'Key lifetime';
$string['settings_code_live_time'] = 'Recommended value: 2 - 30 minutes';
$string['settings_number_of_allowed_code_entry_attempts_label'] = 'Number of allowed attempts to enter a verification code';
$string['settings_number_of_allowed_code_entry_attempts_desc'] = 'This parameter limits the number of possible attempts to enter a verification code, preventing possible enumeration by intruders.';

$string['limiting_registration_attempts'] = 'Limiting external search attempts when registering with provisional lists';
$string['settings_enable_limiting_registration_attempts_label'] = 'Enable limiting external search attempts';
$string['settings_plist_reg_retry_time_label'] = 'Time to reset attempts';
$string['settings_plist_reg_attempts_label'] = 'Number of allowed data search attempts';
$string['settings_enable_limiting_registration_attempts_desc'] = 'Enable';
$string['settings_plist_reg_retry_time_desc'] = 'If the user has used all attempts to search for data in external sources, he will have to wait for the restoration of attempts for the time specified in this parameter.';
$string['settings_plist_reg_attempts_desc'] = 'Determines the number of attempts to search for data in an external source';

$string['settings_error_signupfield_email_must_be_shown'] = 'This is a required field for a specific messaging method';
$string['settings_error_signupfield_phone_must_be_shown'] = 'This is a required field for a specific messaging method';

$string['dof_departments_not_add'] = 'Do not add users to DOF';
$string['dof_departments_not_found'] = 'DEPARTMENT NOT FOUND';
$string['dof_departments_version_error'] = 'DOF department storage update required';
$string['send_method_not_set'] = 'Send method not set';
$string['send_method_not_found'] = 'Send method processor not found';

$string['settings_dof_registrationtype_label'] = 'Selecting the registration type';
$string['settings_dof_registrationtype'] = 'The following registration methods are provided: <br/>
                                           <b>Tentative Lists </b> - Implements user registration using data from an external source.
                                           To register on the preliminary lists, you need: <br/>
                                           <ul>
                                                <li>Add at least one external source to the page <a href="/auth/dof/external_sources_settings.php"> external data source settings </a> </li>
                                                <li>Configure "Search" and "Translated" fields on the page <a href="/auth/dof/registration_fields_settings.php"> custom registration form fields settings </a> </li>
                                           </ul>
                                           <b>Self-registration</b> - the classic registration method, the user independently fills in the fields indicated on the page <a href="/auth/dof/registration_fields_settings.php"> custom registration form fields settings </a> by the administrator. <br/>';
$string['registration_fields_settings'] = 'Registration form custom fields settings';
$string['external_sources_settings'] = 'External data sources settings page';
$string['registration_settings'] = 'Registration settings';
$string['additional_fields_settings'] = 'Additional settings for registration fields';

$string['src_connection'] = 'Name for connecting to an external source';
$string['src_table'] = 'External source table';
$string['src_config_header'] = 'External source:  {$a->src_name} ({$a->cfg_name})';
$string['not_selected']  = 'Not selected';
$string['src_fields'] = 'External source fields';
$string['form_save_success'] = 'Form save success';
$string['form_has_errors'] = 'Form validation errors occurred, saving failed';
$string['delete_src'] = 'Are you sure you want to delete this source?';
$string['field_removed_from_reg_form'] = 'Field "{$a}" will no longer use the registration form when using the LMS 3KL registration plugin';
$string['field_add_to_reg_form'] = 'Field "{$a}" added to the first step of the registration form of the registration plugin LMS 3KL';

$string['fld_display'] = 'Field display mode';
$string['ext_src_compare'] = 'Comparison with external sources';
$string['form_has_chenges'] = 'Sort order changed, form must be saved';

$string['display_none'] = 'Display none';
$string['display_on_step_1'] = 'Display on step 1';
$string['display_on_step_2'] = 'Display on step 2';
$string['need_visible_field_on_step1'] = 'At the first step of registration, at least one field must be displayed';

$string['src_db'] = 'External database';
$string['db_connection_configs_list'] = 'List of configured external database connections';
$string['db_table'] = 'Table in an external database';
$string['db_connection_configs_list_desc'] = 'For src to work, you must select a connection to the database. You can create a database connection at <a href="/local/opentechnology/dbconnection_management.php">External databases connection management</a>';

$string['user_fields_header'] = 'User fields';
$string['mod_broadcast'] = 'Broadcast';
$string['mod_generated'] = 'Generated field';
$string['mod_required'] = 'Required field';
$string['mod_hidden'] = 'Hidden field';
$string['mod_search'] = 'Search field';

$string['group_mod_unique'] = 'Unique field type';

$string['add_source_header'] = 'Add sourse';
$string['select_source'] = 'Select source';
$string['add_source_btn'] = 'Add';
$string['get_src_fields_btn'] = 'Get fields';

$string['no_need_use_in_source_fields'] = 'This type of field should not be associated with fields from sources';
$string['error_get_src_fields'] = 'Error while getting fields from external source';
$string['plist_registration_attempts'] = 'Attempts are exhausted {$a}';
$string['check_attempts_exhausted_all_wait'] = '{$a} minutes left until recovery.';

$string['generated_field_cannnot_be_search'] = 'The search field cannot be generated';
$string['generated_field_cannnot_be_broadcast'] = 'The field being translated cannot be generated';
$string['generated_field_cannnot_be_unique'] = 'The uniqueness check field cannot be generated';

$string['search_field_only_on_step1'] = 'Search fields can only be displayed at the first stage in the registration form';
$string['search_field_need_source_comparison'] = 'When using the modifier "Search field" must be configured with all external external sources';

$string['broadcast_field_need_source_comparison'] = 'When using the modifier "Translated Field" must be configured with all external external sources';
$string['broadcast_field_only_on_step2'] = 'The broadcast fields can only be displayed at the second stage in the registration form';
$string['broadcast_field_need_search_fields'] = 'For the broadcast fields to work, at least one search field is required at the first stage of registration';

$string['hidden_field_cannnot_be_search'] = 'The search field cannot be hidden';
$string['hidden_field_requirements'] = 'A field without the "Generated Field" or "Translated Field" modifier cannot be hidden';

// Форма регистрации
$string['error_signup_disabled'] = 'Signup disabled';
$string['error_signup_username_not_generated'] = 'Signup error';
$string['registration'] = 'Registration';
$string['createaccount'] = 'Signup';
$string['phone_not_valid'] = 'Phone number not valid';
$string['phone_exists'] = 'Current phone number is already exists';
$string['otsms_send_success_message'] = 'A registration data has been sent on Your phone number';
$string['otsms_send_error_message'] = 'There are errors occurred during sending registration data';
$string['otsms_send_error_title'] = 'SMS sending error';
$string['otsms_send_success_title'] = 'Sending SMS with registration data';
$string['email_send_success_message'] = 'A registration data has been sent on Your email';
$string['email_send_error_message'] = 'There are errors occurred during sending registration data';
$string['email_send_error_title'] = 'Email sending error';
$string['email_send_success_title'] = 'Sending email with registration data';
$string['send_error_title'] = 'There are errors occurred during sending registration data';
$string['send_success_title'] = 'Sending registration data was success';

$string['src_no_queryresult'] = 'Failed to get data from external source: {$a}';
$string['src_many_entries_by_conditions'] = 'More than one record was found in the external source "{$a}" by the passed conditions.';
$string['src_no_entries_by_conditions'] = 'No records were found in the external source "{$a}" by the passed conditions.';
$string['no_records_found'] = 'No records found matching the conditions, contact your administrator.';
$string['no_valid_broadcast_fields'] = 'Data from an external source has not been validated, contact your administrator.';
$string['similar_data_found'] = 'User with similar data is already registered in the system, contact the administrator';
$string['src_connection_error'] = 'Connection error {$a}';
$string['field_value_too_long'] = '
The length of a field received from an external source is greater than the maximum allowed length for "{$a}"';

$string['fff_datetime_not_valid'] = 'The date is outside the specified period';
$string['fff_menu_not_valid'] = 'The value is not in the list of possible values ​​specified in the settings.';
$string['fff_checkbox_not_valid'] = 'Checkbox value is not valid';

// Форма двойной авторизации
$string['dual_auth'] = 'Authorization confirmation';
$string['dual_auth_text'] = 'Enter the received key:';
$string['no_user_id'] = 'User ID not passed';
$string['auth_time_expiried'] = 'Authorization key lifetime expired';
$string['wrong_code'] = 'Invalid verification key entered';
$string['exhausted_all_attempts'] = 'All attempts to enter the verification code are exhausted';
$string['dualauth_error_code_missed'] = 'An error occurred during authorization';
$string['confirm'] = 'Confirm';

$string['subject_verification_code'] = 'Verification code';
$string['verification_code_full'] = '
Hello {$ a-> firstname}!
During authorization on the site \'{$a->sitename} \' a verification code was created for you:

{$a->code}

To complete authorization follow the link {$a->link}

Regards, Site Administrator \'{$a->sitename} \'';
$string['verification_code_short'] = 'Verification code: {$a->code}';

$string['newuserfull'] = ' Hello, {$a->firstname}!
There is a new account has been created on site \'{$a->sitename}\' .
You can login using these data:

Username: {$a->username}
Password: {$a->newpassword}

To start using \'{$a->sitename}\'
please, click to this link {$a->link}

\'{$a->sitename}\', {$a->signoff}';
$string['newusershort'] = 'Login: {$a->username}'."\n".'Password: {$a->newpassword}';
$string['passwordrepeat'] = 'Re-enter password';
$string['missingpasswordrepeat'] = 'Fill in the field';
$string['error_password_mismatch'] = 'Entered passwords do not match';
$string['auth_emailnoemail'] = 'Tried to send you an email but failed!';

$string['event_auth_confirmed'] = "User registration was confirmed";