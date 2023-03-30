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
 * @package    local_opentechnology
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$string['pluginname'] = 'LMS 3KL technical support';
$string['get'] = 'get';
$string['save'] = 'save';

$string['pageheader'] = 'Obtaining a serial number';
$string['otkey'] = 'secret key';
$string['otserial'] = 'serial number';

$string['get_otserial'] = 'Get serial number';
$string['get_otserial_fail'] = 'Attempt to get LMS 3KL serial number failed. Server reported an error: {$a}';
$string['reset_otserial'] = "Reset serial number";
$string['already_has_otserial'] = 'You already have the serial number, there is no need to get another one.';
$string['otserial_check_ok'] = 'Serial number is valid.';
$string['otserial_check_fail'] = 'Serial number is invalid. Server reported error: {$a}. Try calling to technical support.';
$string['otserial_tariff_wrong'] = "Current tariff is unavailabla for this product. Please contact to technical support service.";


// Service
$string['otservice'] = 'Tariff: <u>{$a}</u>';
$string['otservice_send_order'] = "Submit the request on service";
$string['otservice_renew'] = 'Submit the request on renewal';
$string['otservice_change_tariff'] = 'Submit the request on tariff change';

$string['otservice_expired'] = 'Tariff time expired. If you want to reactivate the support, please contact with OpenTechnology manager.';
$string['otservice_active'] = 'Tariff is active and expires at {$a}';
$string['otservice_unlimited'] = 'Tariff valid for unlimited';

$string['opentechnology:see_manager_hints'] = 'See hints for the manager';
$string['opentechnology:see_coursecreator_hints'] = 'See hints for the course editor';
$string['opentechnology:see_editingteacher_hints'] = 'See hints for the teacher';
$string['opentechnology:see_student_hints'] = 'See hints for the student';
$string['opentechnology:reset_site_identifier'] = 'Reset site identifier';
$string['opentechnology:view_about'] = 'View installation technical information';
$string['nopermissions'] = 'You do not have the capability to do this: {$a}';

$string['shortcode:courseid'] = 'Course ID';
$string['shortcode:coursefullname'] = 'Course fullname';
$string['shortcode:currentyear'] = 'Current year';
$string['shortcode:currentmonthnumberzero'] = 'Numeric representation of a month, with leading zeros';
$string['shortcode:currentmonthstr'] = 'A full textual representation of a month';
$string['shortcode:currentdaynumberzero'] = 'Day of the month, 2 digits with leading zeros';
$string['shortcode:currentdaynumber'] = 'Day of the month, with a space preceding single digits';
$string['shortcode:currentdaystr'] = 'A full textual representation of the day of the week';
$string['shortcode:release3kl'] = 'Release of LMS 3KL';

// Классы настроек
$string['admin_setting_button_text'] = 'Set';
$string['admin_setting_dialogue_header'] = 'Settings';
$string['frontend_handler_not_found'] = 'The specified field handler was not found. Maybe you forgot to put the file ({$a}).';

// Reset site identifier
$string['reset_site_identifier'] = 'Reset site identifier';
$string['about'] = 'Technical information';
$string['reset_site_identifier_title'] = 'Reset site identifier';
$string['about_title'] = 'Technical information';
$string['reset_form_submit'] = 'Reset identifier';
$string['unregister_successfull'] = 'Unregister was successfull';
$string['reset_site_identifier_successfull'] = 'Reset site identifier was successfull';
$string['unset_site_identifier_successfull'] = 'Unset site identifier was successfull';
$string['unregister_failed'] = 'Errors occurred while unregistering';
$string['reset_site_identifier_failed'] = 'Errors occurred during reset site identifier';
$string['unset_site_identifier_failed'] = 'Errors occurred during unset site identifier';
$string['site_identifier_not_found'] = 'The specified site identifier was not found';

// About
$string['system_info'] = 'System information';
$string['system_info_desc'] = '';
$string['moodle_version'] = 'Moodle version';
$string['moodle_release'] = 'Moodle release';
$string['our_build'] = '3kl build';
$string['maturity'] = 'Maturity 3kl';
$string['database_size'] = 'Database size';
$string['moodledata_size'] = 'Moodledata size';
$string['useful_volume'] = 'Useful volume';
$string['go_to_report_coursesize'] = 'Go to course size report *';
$string['report_coursesize_comment'] = '* The total size for the courses may not match the size for this module. The Course Report does not include files used outside of courses.';
$string['moodle_size_limit'] = 'Moodle size limit';
$string['moodle_size_limit_disabled'] = 'disabled';
$string['moodle_size_limit_enabled'] = 'absolute';
$string['moodle_size_limit_exceeded'] = 'File upload limitation enabled';
$string['users_count'] = 'Users count';
$string['online_users_count'] = 'Online users count';
$string['courses_count'] = 'Courses count';

// Errors
$string['error_failed_to_get_moodledata_size'] = 'Could not determine moodledata size';
$string['error_failed_to_get_database_size'] = 'Could not determine database size';
$string['error_failed_to_get_useful_volume'] = 'Could not determine useful volume size';
$string['error_failed_to_get_free_diskspace'] = 'Could not determine available disk space';

$string['about_was_replaced'] = 'You have been moved to the LMS 3KL technical support section, as technical information was moved here.';


$string['otserial_settingspage_visiblename'] = 'Tariff';
$string['otserial_settingspage_otserial'] = 'Serial number';
$string['otserial_settingspage_issue_otserial'] = 'Obtaining serial number';
$string['otserial_settingspage_otservice'] = 'Tariff: <u>{$a}</u>';

$string['otserial_exception_already_has_serial'] = 'Serial number has already been received';
$string['otserial_exception_not_configured'] = 'Missing required settings';
$string['otserial_exception_status_ko'] = 'Wrong status returned';
$string['otserial_exception_unknown'] = 'Unknown error';
$string['otserial_exception_expirytime_wrong'] = 'The validity period of the tariff plan is not configured correctly. Please contact technical support.';


$string['otserial_error_get_otserial_fail'] = 'Attempt to get LMS 3KL serial number failed. Server reported an error: {$a}';
$string['otserial_error_otserial_check_fail'] = 'Serial number is invalid. Server reported error: {$a}. Try calling to technical support.';
$string['otserial_error_tariff_wrong'] = "Current tariff is unavailabla for this product. Please contact to technical support service.";
$string['otserial_error_otservice_expired'] = 'Tariff time expired. If you want to reactivate the support, please contact with OpenTechnology manager.';

$string['otserial_notification_otserial_check_ok'] = 'Serial number is valid.';
$string['otserial_notification_otservice_active'] = 'Tariff is active and expires at {$a}';
$string['otserial_notification_otservice_unlimited'] = 'Tariff valid for unlimited';

$string['diskspace_monitoring'] = 'Available server disk space monitoring';
$string['diskspace_comment'] = 'The server must always have at least 20% free space. Exhaustion of free space leads to an immediate server freeze, and in unfortunate circumstances - to data loss. Do not reboot the server that has frozen due to lack of space - the DBMS must write the unsaved data to disk, otherwise you will lose your database. We recommend storing the database, Moodle files, temporary files and backups on different partitions of the drive.';
$string['partition_purpose'] = 'Partition purpose';
$string['free_diskspace_bytes'] = 'Available (GB)';
$string['free_diskspace_percentage'] = 'Available (%)';
$string['additional_info'] = 'Additional product information';
$string['our_release'] = 'LMS 3KL version:';
$string['admins_additional_info'] = 'Additional information for manager';
$string['dg_not_specified'] = 'Not specified';
$string['network_interface_parameters'] = 'Server network interface parameters';
$string['default_gateway'] = 'Default gateway';
$string['dns_server_list'] = 'Nameserver list';
$string['if_name'] = 'Name';
$string['inet_addr'] = 'IPv4';
$string['net_mask'] = 'Net mask';

// Менеджер подключений к внешним БД

$string['dbconnection_management'] = 'External databases connection management';
$string['dbconnection_name'] = 'Connection name (should be unique)';
$string['dbconnection_new'] = 'Create new connection';
$string['dbconnection_delete'] = 'Delete this connection';
$string['dbconnection_host'] = 'Host';
$string['dbconnection_type'] = 'Database';
$string['dbconnection_database'] = 'DB name';
$string['dbconnection_user'] = 'DB user';
$string['dbconnection_pass'] = 'Password';
$string['dbconnection_setupsql'] = 'SQL setup command';
$string['dbconnection_extencoding'] = 'External db encoding';
$string['dbconnection_name_should_not_be_empty'] = 'connection name shouldn\'t be empty';
$string['dbconnection_check_connection'] = 'Check connection';
$string['dbconnection_check_connection_successful'] = 'Connection successful';
$string['dbconnection_check_connection_failed'] = 'Connection failed. Error message: {$a}';
$string['dbconnection_back_to_dbconnections'] = 'Back to connections';
$string['connection'] = 'Connection';

// Права
$string['opentechnology:manage_db_connections'] = 'Configure a connection to a source';


// Условия доступа
$string['ac_add'] = 'Добавить';
$string['ac_remove'] = 'Удалить';

$string['empty_string'] = '(Empty string)';

$string['logical_group_and'] = 'Логическая группа "И"';
$string['logical_group_and_desc'] = 'Все условия внутри группы должны выполняться';
$string['logical_group_and_userdesc'] = 'Все условия внутри группы должны выполняться {$a}';
$string['logical_group_or'] = 'Логическая группа "ИЛИ"';
$string['logical_group_or_desc'] = 'Хотя бы одно условие внутри группы должно выполниться';
$string['logical_group_or_userdesc'] = 'Хотя бы одно условие внутри группы должно выполниться {$a}';

$string['comparison_operator_eq'] = 'Равно';
$string['comparison_operator_eq_desc'] = 'Первый аргумент должен быть равен второму';
$string['comparison_operator_eq_userdesc'] = 'Значение {$a->arg1} должно быть равно значению {$a->arg2}';
$string['comparison_operator_gt'] = 'Больше';
$string['comparison_operator_gt_desc'] = 'Первый аргумент должен быть больше второго';
$string['comparison_operator_gt_userdesc'] = 'Значение {$a->arg1} должно быть больше значения {$a->arg2}';
$string['comparison_operator_gte'] = 'Больше или равно';
$string['comparison_operator_gte_desc'] = 'Первый аргумент должен быть больше или равен второму';
$string['comparison_operator_gte_userdesc'] = 'Значение {$a->arg1} должно быть больше или равно значению {$a->arg2}';
$string['comparison_operator_lt'] = 'Меньше';
$string['comparison_operator_lt_desc'] = 'Первый аргумент должен быть меньше второго';
$string['comparison_operator_lt_userdesc'] = 'Значение {$a->arg1} должно быть меньше значения {$a->arg2}';
$string['comparison_operator_lte'] = 'Меньше или равно';
$string['comparison_operator_lte_desc'] = 'Первый аргумент должен быть меньше или равен второму';
$string['comparison_operator_lte_userdesc'] = 'Значение {$a->arg1} должно быть меньше или равно значению {$a->arg2}';
$string['comparison_operator_neq'] = 'Не равно';
$string['comparison_operator_neq_desc'] = 'Первый аргумент должен быть не равен второму';
$string['comparison_operator_neq_userdesc'] = 'Значение {$a->arg1} должно быть не равно значению {$a->arg2}';

$string['replacement_userfield'] = 'Поле пользователя';
$string['replacement_profilefield'] = 'Поле профиля';
$string['replacement_string'] = 'Ввод с клавиатуры';
