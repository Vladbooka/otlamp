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
$string['pluginadministration'] = 'Управление плагином Занятие';

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
$string['mod/event3kl:participateevent'] = 'Принимать участие в занятии';
$string['mod/event3kl:speakatevent'] = 'Выступать на занятии';
$string['mod/event3kl:managesessions'] = 'Управлять сессиями занятия';

/**
 * Ошибки
 */
$string['error'] = 'Что-то пошло не так...';
$string['otapi_data_not_configured'] = 'Серийный номер плагина не определен';
$string['request_not_successful'] = 'В результате запроса получена ошибка';
$string['response_not_isset'] = 'Результат запроса не содержит требуемый ответ';
$string['error_start_session'] = 'Не удалось начать занятие';
$string['error_get_participate_link'] = 'Не удалось получить ссылку для присоединения к занятию';
$string['error_opendate_format'] = 'Использование свободной даты не сопоставимо с настроенным форматом занятия';
$string['error_relative_to_enrolment_format'] = 'Использование даты, формируемой относительно даты старта подписки не сопоставимо с настроенным форматом занятия';
$string['error_vacantseat_format'] = 'Использование времени по заявке не сопоставимо с настроенным форматом занятия';
$string['error_individual_num_sessions'] = 'Должна существовать одна и только одна сессия при индивидуальном формате. Обнаружено иное.';
$string['error_no_view_capabilities'] = 'Нет данных для отображения в соответствии с вашими правами и текущими настройками';
$string['error_session_out_of_date'] =  'Попытка отредактировать посещаемость неактуальной сессии.';
$string['error_datemode_not_suitable'] = 'Выбранный способ определения даты не сопоставим с выбранным форматом занятия';

/**
 * Настройки плагина
 */
$string['settings'] = 'Настройки';
$string['settings_session_lifetime_label'] = 'Время жизни сессии';
$string['settings_session_lifetime_desc'] = 'Для случаев, когда сессию занятия забывают завершить вручную, предусмотрен механизм, завершающий принудительно сессии, с даты старта которых прошло времени больше указанного значения';
$string['manage_providers'] = 'Управление провайдерами';

/**
 * Таски
 */
$string['finishing_outdated_sessions'] = 'Завершение устаревших сессий';
$string['process_sessions_pending_records'] = 'Обработать сессии, ожидающие выкачивания записей';

/**
 * Настройки инстанса провайдера
 */
$string['add_provider_select_label'] = '';
$string['add_provider_submit_value'] = 'Добавить';
$string['add_provider_group_label'] = 'Добавление нового провайдера';
$string['edit_provider'] = 'Редактировать';
$string['delete_provider'] = 'Удалить';
$string['there_are_no_providers'] = 'Не найдено провайдеров для добавления';
$string['add_provider'] = 'Добавить провайдера';
$string['editing_provider'] = 'Редактирование провайдера {$a}';
$string['deleting_provider'] = 'Удаление провайдера {$a}';
$string['add_provider_instance_failed'] = 'Не удалось добавить провайдера';

/**
 * Настройки инстанса модуля курса
 */
$string['event3klname'] = 'Название';
$string['description'] = 'Описание';
$string['external_provider_display_name'] = 'Внешний провайдер';
$string['facetoface_provider_display_name'] = 'Очное занятие';
$string['providertype'] = 'Тип занятия';
$string['providerinstance'] = 'Провайдер';
$string['common_format_display_name'] = 'Общее';
$string['individual_format_display_name'] = 'Индивидуальное';
$string['manual_format_display_name'] = 'Подгруппы';
$string['formattype'] = 'Формат занятия';
$string['datemodetype'] = 'Способ указания даты';
$string['absolute_datemode_display_name'] = 'Абсолютная дата';
$string['relative_to_course_datemode_display_name'] = 'Относительно даты старта курса';
$string['relative_to_group_datemode_display_name'] = 'Относительно даты старта группы';
$string['relative_to_enrolment_datemode_display_name'] = 'Относительно даты старта подписки';

$string['opendate_datemode_display_name'] = 'Свободное время';
$string['vacantseat_datemode_display_name'] = 'Время по заявке';

$string['date_calculation'] = 'Расчет даты';
$string['set_date_absolute_datemode'] = 'Укажите дату';
$string['add_relative_to_course_datemode'] = 'Добавить промежуток времени';
$string['add_relative_to_group_datemode'] = 'Добавить промежуток времени';
$string['add_relative_to_enrolment_datemode'] = 'Добавить промежуток времени';
$string['set_day_of_week_relative_to_course_datemode'] = 'Указать день недели';
$string['set_day_of_week_relative_to_group_datemode'] = 'Указать день недели';
$string['set_day_of_week_relative_to_enrolment_datemode'] = 'Указать день недели';
$string['datemode_edit_confirmed'] = 'Я проверил все настройки и уверен, что хочу отредактировать даты';
$string['set_time'] = 'Указать время';
$string['delete_modifier'] = 'Удалить';
$string['apply_modifier'] = 'Применить';

/**
 * Статусы сессий
 */
$string['session_status_plan'] = 'План';
$string['session_status_active'] = 'Активно';
$string['session_status_finished'] = 'Завершено';

/**
 * Данные сессии
 */
$string['enter_session'] = 'Присоединиться к занятию';
$string['session_members'] = 'Участники';
$string['groupname'] = 'Группа: {$a}';
$string['records'] = 'Записи';
$string['pending_records'] = 'Загрузка записей занятия еще не завершена. После завершения, записи должны будут появиться здесь.';

/**
 * Согласование даты для Свободного времени (opendate)
 */
$string['opendate_offer'] = 'Предложите удобную вам дату';
$string['opendate_request_process_success'] = 'Запрос даты совершён успешно';
$string['opendate_request_process_error'] = 'Запрос даты не был совершён, произошла ошибка';
$string['opendate_offered_date'] = 'Предложенная к согласованию дата: {$a}';
$string['opendate_date_not_offered'] = 'Дата не предложена к согласованию';
$string['opendate_approve'] = 'Подтвердить выбранную дату';
$string['opendate_reject'] = 'Отклонить выбранную дату';
$string['opendate_coordination_approve_success'] = 'Согласование даты выполнено успешно';
$string['opendate_coordination_reject_success'] = 'Дата успешно отклонена';
$string['opendate_coordination_process_error'] = 'Обработка формы согласования даты завершилась ошибкой';
$string['opendate_request_header'] = 'Предложение даты';
$string['opendate_coordination_header'] = 'Согласование даты';

/**
 * Выбор подгруппы (сессии) для участия в ней
 */
$string['vacantseat_select'] = 'Выбрать подгруппу';
$string['vacantseat_process_error'] = 'Не удалось записаться в подгруппу';
$string['vacantseat_process_success'] = 'Запись в подгруппу прошла успешно';
$string['vacantseat_select_header'] = 'Выбор подгруппы';

/**
 * Создание/Редактирование сессии
 */
$string['form_session_name'] = 'Название';
$string['form_session_startdate'] = 'Дата начала';
$string['form_session_maxmembers'] = 'Максимально допустимое число участников (0 - не ограничено)';
$string['edit_session'] = 'Редактирование сессии';
$string['edit_sessions'] = 'Редактирование сессий';

/**
 * Удаление сессии
 */
$string['delete_session_title'] = 'Удаление сессии';
$string['delete_session_body'] = 'Данное действие приведет к необратимому удалению сессии';
$string['delete_session_cancel'] = 'Отмена';
$string['delete_session'] = 'Удалить';

/**
 * Форма добавления участника сессии
 */
$string['add_member_header'] = 'Добавление участника';
$string['add_member'] = 'Участник';
$string['add_member_submit'] = 'Добавить';

/**
 * Уведомление преподавателю о новом запросе даты
 */
$string['message__new_opendate_request__subject'] = 'Новый запрос на согласование даты проведения занятия в курсе "{$a->coursefullname}"';
$string['message__new_opendate_request__smallmessage'] = 'Новый запрос на согласование даты проведения занятия: {$a->confirmationlink}';
$string['message__new_opendate_request__fullmessage'] = '<p>Пользователь {$a->userfullname} предложил дату проведения занятия
"{$a->event3klfullname}" в курсе "{$a->coursefullname}": {$a->offereddate}. </p>
<p>Для согласования даты перейдите по ссылке: <a href="{$a->confirmationlink}">{$a->confirmationlink}</a></p>';

/**
 * Уведомление о подтверждении даты для участников
 */
$string['message__opendate_request_confirmed__subject'] = 'Подтверждена дата проведения занятия в курсе "{$a->coursefullname}"';
$string['message__opendate_request_confirmed__smallmessage'] = 'Подтверждена дата проведения занятия "{$a->event3klfullname}":  {$a->eventdate}';
$string['message__opendate_request_confirmed__fullmessage'] = '<p>Пользователь {$a->userfullname} подтвердил дату проведения
занятия "{$a->event3klfullname}" в курсе "{$a->coursefullname}".</p>
<p>Дата проведения занятия: {$a->eventdate}. </p> <p>Перейти к занятию: <a href="{$a->event3kllink}">{$a->event3kllink}</a></p>';

/**
 * Уведомление о подтверждении даты для спикеров
 */
$string['message__opendate_request_confirmed_for_speakers__subject'] = 'Подтверждена дата проведения занятия в курсе "{$a->coursefullname}"';
$string['message__opendate_request_confirmed_for_speakers__smallmessage'] = 'Подтверждена дата проведения занятия "{$a->event3klfullname}":  {$a->eventdate}';
$string['message__opendate_request_confirmed_for_speakers__fullmessage'] = '<p>Пользователь {$a->userfullname} подтвердил дату проведения
сессии "{$a->sessionname}" занятия "{$a->event3klfullname}" в курсе "{$a->coursefullname}".</p>
<p>Дата проведения занятия: {$a->eventdate}. </p> <p>Перейти к занятию: <a href="{$a->event3kllink}">{$a->event3kllink}</a></p>';

/**
 * Уведомление об отклонении даты для участников
 */
$string['message__opendate_request_rejected__subject'] = 'Отклонено предложение даты проведения занятия в курсе "{$a->coursefullname}"';
$string['message__opendate_request_rejected__smallmessage'] = 'Предложенние даты проведения занятия "{$a->event3klfullname}" отклонено. Предложить другую дату: {$a->event3kllink}';
$string['message__opendate_request_rejected__fullmessage'] = '<p>Пользователь {$a->userfullname} отклонил предложение даты проведения
занятия "{$a->event3klfullname}" в курсе "{$a->coursefullname}".</p>
<p>Предложить новую дату: <a href="{$a->event3kllink}">{$a->event3kllink}</a></p>';

/**
 *   Названия уведомлений
 */
$string['messageprovider:new_opendate_request'] = 'Уведомление о новом предложении даты  проведения занятия';
$string['messageprovider:opendate_request_rejected'] = 'Уведомление об отклоненном предложении даты  проведения занятия';
$string['messageprovider:opendate_request_confirmed'] = 'Уведомление о согласовании даты  проведения занятия';
$string['messageprovider:opendate_request_confirmed_for_speakers'] = 'Уведомление о согласовании даты проведения занятия для спикеров';

/**
 *  Посещаемость
 */
$string['session_participants'] = 'Участники сессии: {$a->sessionname}';
$string['attendance_description'] = 'Список участников сессии с указанием посещаемости занятия. Обратите внимание, что редактировать посещаемость возможно только тогда, когда сессия находится в активном статусе или завершена.';
$string['attendance_not_attended'] = 'Не присутствовал(а) на занятии';
$string['attendance_is_attended'] = 'Присутствовал(а) на занятии';
$string['delete_member'] = 'Удалить участника';

/**
 *  Права
 */
$string['event3kl:addinstance'] = 'Добавлять модуль "Занятие" в курс';
$string['event3kl:view'] = 'Просматривать модуль курса';
$string['event3kl:viewproviderslist'] = 'Просматривать список провайдеров';
$string['event3kl:editprovidersettings'] = 'Редактировать настройки провайдера';
$string['event3kl:participateevent'] = 'Принимать участие в мероприятии в роли учащегося';
$string['event3kl:speakatevent'] = 'Выступать на мероприятии';
$string['event3kl:managesessions'] = 'Управлять сессиями';
$string['event3kl:managesessionattendance'] = 'Проставлять посещаемость сессии';

// События в календаре
$string['calendar_event_name'] = '{$a->name} {$a->sessionname}';

// Модалка остановки сессии
$string['stop_session_modal_title'] = 'Завершение сессии {$a->sessionname}';
$string['stop_session_modal_body'] = 'После завершения сессии возобновить её будет невозможно. Вы уверены, что хотите продолжить?';
$string['stop_event_session'] = 'Завершить сессию';