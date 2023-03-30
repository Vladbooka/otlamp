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
 * Общие строки
 */
$string['pluginname'] = 'Занятие';
$string['modulename'] = 'Занятие';
$string['modulename_help'] = 'Модуль Занятие предназначен для упрощенной организации очных и вебинарских занятий';
$string['modulenameplural'] = 'Занятия';
$string['pluginadministration'] = 'Plugin Event administration';

/**
 * Связь с OT API
 */
$string['api_response_ok'] = 'Соединение с API установлено.';

/**
 * Права
 */
$string['mod/event3kl:viewproviderslist'] = 'Право просматривать список провайдеров';
$string['mod/event3kl:editprovidersettings'] = 'Право редактировать настройки провайдера';
$string['mod/event3kl:addinstance'] = 'Право добавлять модуль в курс';
$string['mod/event3kl:view'] = 'Право просматривать модуль курса';
$string['mod/event3kl:participateevent'] = 'Participate event';
$string['mod/event3kl:speakatevent'] = 'Speak at event';
$string['mod/event3kl:managesessions'] = 'Manage event sessions';

/**
 * Ошибки
 */
$string['error'] = 'Что-то пошло не так...';
$string['otapi_data_not_configured'] = 'Plugin serial number not defined';
$string['request_not_successful'] = 'The request received an error';
$string['response_not_isset'] = 'The query result does not contain the required response';
$string['error_start_session'] = 'Failed to start event';
$string['error_get_participate_link'] = 'Failed to get a link to join the event';
$string['error_opendate_format'] = 'Open date usage is not compatible with configured event format';
$string['error_relative_to_enrolment_format'] = 'The use of the date generated relative to the start date of the enrolment is not comparable to the configured event format';
$string['error_vacantseat_format'] = 'Vacant seat is not compatible to the configured event format';
$string['error_individual_num_sessions'] = 'There should be one and only one session in an individual format. Discovered otherwise.';
$string['error_no_view_capabilities'] = 'No data to display in accordance with your rights and current settings';
$string['error_session_out_of_date'] =  'Trying to set attendance of an outdated session';
$string['error_datemode_not_suitable'] = 'This datemode is not suitable for selected event format';

/**
 * Настройки плагина
 */
$string['settings'] = 'Settings';
$string['settings_session_lifetime_label'] = 'Session lifetime';
$string['settings_session_lifetime_desc'] = 'For cases when the session is forgotten to be completed manually, a mechanism is provided for forcibly terminating sessions, from the start date of which time has passed more than the specified value';
$string['manage_providers'] = 'Provider management';

/**
 * Таски
 */
$string['finishing_outdated_sessions'] = 'Finishing outdated sessions';
$string['process_sessions_pending_records'] = 'Process session pending download records';

/**
 * Настройки инстанса провайдера
 */
$string['add_provider_select_label'] = '';
$string['add_provider_submit_value'] = 'Add';
$string['add_provider_group_label'] = 'Add new provider';
$string['edit_provider'] = 'Edit';
$string['delete_provider'] = 'Delete';
$string['there_are_no_providers'] = 'No providers found to add';
$string['add_provider'] = 'Add provider';
$string['editing_provider'] = 'Editing provider {$a}';
$string['deleting_provider'] = 'Deleting provider {$a}';
$string['add_provider_instance_failed'] = 'Failed to add provider';

/**
 * Настройки инстанса модуля курса
 */
$string['event3klname'] = 'Name';
$string['description'] = 'Description';
$string['external_provider_display_name'] = 'External provider';
$string['facetoface_provider_display_name'] = 'Face-to-face';
$string['providertype'] = 'Class type';
$string['providerinstance'] = 'Provider';
$string['common_format_display_name'] = 'Common';
$string['individual_format_display_name'] = 'Individual';
$string['manual_format_display_name'] = 'Subgroups';
$string['formattype'] = 'Class format';
$string['datemodetype'] = 'Date mode type';
$string['absolute_datemode_display_name'] = 'Absolute date';
$string['relative_to_course_datemode_display_name'] = 'Relative to course start date';
$string['relative_to_group_datemode_display_name'] = 'Relative to group start date';
$string['relative_to_enrolment_datemode_display_name'] = 'Relative to enrolment start date';

$string['opendate_datemode_display_name'] = 'Open date';
$string['vacantseat_datemode_display_name'] = 'Vacant seat';

$string['date_calculation'] = 'Date calculation';
$string['set_date_absolute_datemode'] = 'Specify the date';
$string['add_relative_to_course_datemode'] = 'Add time interval';
$string['add_relative_to_group_datemode'] = 'Add time interval';
$string['add_relative_to_enrolment_datemode'] = 'Add time interval';
$string['set_day_of_week_relative_to_group_datemode'] = 'Specify the day of the week';
$string['set_day_of_week_relative_to_course_datemode'] = 'Specify the day of the week';
$string['set_day_of_week_relative_to_enrolment_datemode'] = 'Specify the day of the week';
$string['datemode_edit_confirmed'] = 'I\'ve checked all the settings and I\'m sure I want to edit the dates';
$string['set_time'] = 'Specify the time';
$string['delete_modifier'] = 'Delete';
$string['apply_modifier'] = 'Apply';

/**
 * Статусы сессий
 */
$string['session_status_plan'] = 'Plan';
$string['session_status_active'] = 'Active';
$string['session_status_finished'] = 'Finished';

/**
 * Данные сессии
 */
$string['enter_session'] = 'Enter event';
$string['session_members'] = 'Members';
$string['groupname'] = 'Group: {$a}';
$string['records'] = 'Records';
$string['pending_records'] = 'Downloading activity records is not yet complete. Once completed, the records should appear here.';

/**
 * Согласование даты для Свободного времени (opendate)
 */
$string['opendate_offer'] = 'Offer a date/time convenient for you.';
$string['opendate_request_process_success'] = 'Date request completed successfully';
$string['opendate_request_process_error'] = 'Date request was not completed, an error occurred';
$string['opendate_offered_date'] = 'Date proposed for approval: {$a}';
$string['opendate_date_not_offered'] = 'Date not proposed for approval';
$string['opendate_approve'] = 'Approve selected date';
$string['opendate_reject'] = 'Reject selected date';
$string['opendate_coordination_approve_success'] = 'Date successfully approved';
$string['opendate_coordination_reject_success'] = 'Date successfully rejected';
$string['opendate_coordination_process_error'] = 'Processing of the date coordination form failed';
$string['opendate_request_header'] = 'Date request';
$string['opendate_coordination_header'] = 'Date coordination';


/**
 * Выбор подгруппы (сессии) для участия в ней
 */
$string['vacantseat_select'] = 'Join session';
$string['vacantseat_process_error'] = 'Failed to join session';
$string['vacantseat_process_success'] = 'Joining the session was successful';
$string['vacantseat_select_header'] = 'Session selection';

/**
 * Создание/Редактирование сессии
 */
$string['form_session_name'] = 'Name';
$string['form_session_startdate'] = 'Start date';
$string['form_session_maxmembers'] = 'Maximum allowed number of members (0 - unlimited)';
$string['edit_session'] = 'Editing a session';
$string['edit_sessions'] = 'Editing sessions';

/**
 * Удаление сессии
 */
$string['delete_session_title'] = 'Session deletion';
$string['delete_session_body'] = 'This action will lead to the irreversible deletion of the session.';
$string['delete_session_cancel'] = 'Cancel';
$string['delete_session'] = 'Delete';

/**
 * Форма добавления участника сессии
 */
$string['add_member_header'] = 'Adding a member';
$string['add_member'] = 'Member';
$string['add_member_submit'] = 'Add';

/**
 * Уведомление преподавателю о новом запросе даты
 */
$string['message__new_opendate_request__subject'] = 'New date request for opendate event in course "{$a->coursefullname}"';
$string['message__new_opendate_request__smallmessage'] = 'New date offering request for opendate event: {$a->confirmationlink}';
$string['message__new_opendate_request__fullmessage'] = '<p>User {$a->userfullname} offered new date for opendate event
"{$a->event3klfullname}" in course "{$a->coursefullname}": {$a->offereddate}. </p>
<p>To coordinate event date follow this link: <a href="{$a->confirmationlink}">{$a->confirmationlink}</a></p>';

/**
 * Date confirmation notification
 */
$string['message__opendate_request_confirmed__subject'] = 'Offered date for opendate event in course "{$a->coursefullname}" was approved';
$string['message__opendate_request_confirmed__smallmessage'] = 'Offered date for opendate event "{$a->event3klfullname}" was approved:  {$a->eventdate}';
$string['message__opendate_request_confirmed__fullmessage'] = '<p>User {$a->userfullname} approved date for opendate event
"{$a->event3klfullname}" in course "{$a->coursefullname}".</p>  <p>Event date: {$a->eventdate}. </p>
<p>View the event: <a href="{$a->event3kllink}">{$a->event3kllink}</a></p>';

/**
 * Date confirmation notification for event speakers
 */
$string['message__opendate_request_confirmed_for_speakers__subject'] = 'Offered date for opendate event in course "{$a->coursefullname}" was approved';
$string['message__opendate_request_confirmed_for_speakers__smallmessage'] = 'Offered date for opendate event "{$a->event3klfullname}" was approved:  {$a->eventdate}';
$string['message__opendate_request_confirmed_for_speakers__fullmessage'] = '<p>User {$a->userfullname} approved date for session "{$a->sessionname}"
for opendate event "{$a->event3klfullname}" in course "{$a->coursefullname}".</p>  <p>Event date: {$a->eventdate}. </p>
<p>View the event: <a href="{$a->event3kllink}">{$a->event3kllink}</a></p>';

/**
 * Date  rejection notification
 */
$string['message__opendate_request_rejected__subject'] = 'Offered date for opendate event in course "{$a->coursefullname}" was rejected';
$string['message__opendate_request_rejected__smallmessage'] = 'Offered date for opendate event "{$a->event3klfullname}" was rejected. Offer another date:  {$a->event3kllink}';
$string['message__opendate_request_rejected__fullmessage'] = '<p>User {$a->userfullname} rejected date for opendate
event "{$a->event3klfullname}" in course "{$a->coursefullname}".</p>
<p>Offer another date: <a href="{$a->event3kllink}">{$a->event3kllink}</a></p>';

/**
 *   Notifications' names
 */
$string['messageprovider:new_opendate_request'] = 'New opendate event date request notification';
$string['messageprovider:opendate_request_rejected'] = 'Opendate event date offer rejection notification';
$string['messageprovider:opendate_request_confirmed'] = 'Opendate event date offer confirmation notification';
$string['messageprovider:opendate_request_confirmed_for_speakers'] = 'Opendate event date offer confirmation notification for speakers';

/**
 *  Session attendance
 */
$string['session_participants'] = 'Session participants: {$a->sessionname}';
$string['attendance_description'] = 'Event session members list with event attendance information.
Note that editing attendance information is only possible while session is active or finished';
$string['attendance_not_attended'] = 'Did not attend the event';
$string['attendance_is_attended'] = 'Attended the event';
$string['delete_member'] = 'Delete session member';

/**
 *  Capabilities
 */
$string['event3kl:addinstance'] = 'Add a new event3kl module';
$string['event3kl:view'] = 'View event3kl module instance';
$string['event3kl:viewproviderslist'] = 'View providers\' list';
$string['event3kl:editprovidersettings'] = 'Edit provider settings';
$string['event3kl:participateevent'] = 'Take part in the event';
$string['event3kl:speakatevent'] = 'Speak at event';
$string['event3kl:managesessions'] = 'Manage event sessions';
$string['event3kl:managesessionattendance'] = 'Manage session attendance data';

// Calendar events
$string['calendar_event_name'] = '{$a->name} {$a->sessionname}';

// Модалка остановки сессии
$string['stop_session_modal_title'] = 'Stop event session {$a->sessionname}';
$string['stop_session_modal_body'] = 'After the session is completed, it will be impossible to resume it. Are you sure you want to continue? ';
$string['stop_event_session'] = 'Stop event session';
