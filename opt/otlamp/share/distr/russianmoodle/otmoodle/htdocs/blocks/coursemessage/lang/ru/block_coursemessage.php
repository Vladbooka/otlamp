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
 * Блок мессенджера курса. Языковые переменные.
 *
 * @package    block_coursemessage
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$string['pluginname'] = 'Спросить преподавателя';

$string['coursemessage:send'] = 'Отправка сообщений';
$string['coursemessage:addinstance'] = 'Добавление блока';
$string['coursemessage:myaddinstance'] = 'Добавление блока в личный кабинет';

/** Конфигурация блока **/
$string['config_header'] = 'Настройки блока';
$string['config_userfields'] = 'Идентификаторы полей пользователя для отображения';
$string['config_userfields_desc'] = 'Описание настройки идентификаторов для отображения';
$string['config_userfields_desc_help'] = 'По умолчанию по каждому контакту курса отображается ФИО пользователя и фотография,
                 если требуется отобразить данные из полей пользователя достаточно указать их оригинальные названия через запятую.<br>
                 Список оригинальных названий полей: username, firstname, lastname, email, icq, skype, yahoo, aim, msn, phone1, phone2, institution, department, address, city, country,
                 firstaccess, lastaccess, description, timecreated, timemodified, lastnamephonetic, firstnamephonetic, middlename, alternatename';
$string['config_display_header'] = 'Отобразить шапку блока';
$string['config_display_header_desc'] = 'Отобразить шапку блока с заголовком';
$string['config_recipientselectionmode'] = 'Выбор метода определения получателей сообщения';
$string['config_useglobal'] = 'Использовать глобальную настройку';
$string['config_sendtoall'] = 'Отправлять всем контактам';
$string['config_allowuserselect'] = 'Позволить выбрать контакт пользователю самостоятельно';
$string['config_automaticcontact'] = 'Автоматическое определение контакта';
$string['config_senduserinfo'] = 'Дополнять сообщение учащегося сведениями о курсе и группе';
$string['config_recipientselectionmode_desc'] = 'Описание методов определения получателей сообщения';
$string['config_recipientselectionmode_desc_help'] = 'Автоматическое определение контакта - система будет распределять нагрузку между преподавателями, 
                отправляя сообщение контактам курса поочередно. В случае группового режима, распределение нагрузки будет между преподавателями из групп студента.<br>
                Отправлять всем контактам - Сообщение будет отправлено всем контактам курса с учетом группового режима. 
                Если пользователь не состоит в группах, а в настройках курса активен групповой режим - получателями сообщения будут только те контакты курса, которые не состоят в группах.<br>
                Позволить выбрать контакт пользователю самостоятельно - пользователь сможет выбрать одного получателя сообщения из контактов курса с учетом группового режима.<br>';

/** Global configuration **/
$string['config_block_coursemessage_recipientselectionmode'] = 'Выбор метода определения получателей сообщения';
$string['config_block_coursemessage_recipientselectionmode_desc'] = 'Описание методов определения получателей сообщения:<br>
                Автоматическое определение контакта - система будет распределять нагрузку между преподавателями,
                отправляя сообщение контактам курса поочередно. В случае группового режима, распределение нагрузки будет между преподавателями из групп студента.<br>
                Отправлять всем контактам - Сообщение будет отправлено всем контактам курса с учетом группового режима.
                Если пользователь не состоит в группах, а в настройках курса активен групповой режим - получателями сообщения будут только те контакты курса, которые не состоят в группах.<br>
                Позволить выбрать контакт пользователю самостоятельно - пользователь сможет выбрать одного получателя сообщения из контактов курса с учетом группового режима.<br>';

/** Блок **/
$string['description_automaticcontact'] = 'Используйте форму ниже, для отправки сообщения (преподаватель будет определен автоматически)';
$string['description_allowuserselect'] = 'Выберите преподавателя из списка для отправки сообщения';
$string['description_sendtoall'] = 'Используйте форму ниже, для отправки сообщения всем приведенным в списке преподавателям';
$string['no_contacts'] = 'Нет контактов';


/** Форма **/
$string['form_send_message_desc'] = 'Вопрос:';
$string['form_send_submit'] = 'Отправить';
$string['form_send_signature_course'] = '<hr> Курс: {$a->course}';
$string['form_send_signature_all'] = '<hr> Курс: {$a->course} <br> Группа: {$a->groups}';

/** Уведомления **/
$string['message_form_send_message_send_success'] = 'Сообщение отправлено';

/** Ошибки **/
$string['error_form_send_message_send_error'] = 'Во время отправки сообщения произощли ошибки';
$string['error_form_send_receiver_not_set'] = 'Получатель не установлен';
$string['error_form_empty_message'] = 'Пустое сообщение';
$string['error_form_send_capability'] = 'У Вас нет прав на отправку сообщений';
