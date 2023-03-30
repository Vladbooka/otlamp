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
 * Плагин подписки через форму связи с менеджером.
 * Языковой файл
 *
 * @package    enrol
 * @subpackage sitecall
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$string['pluginname'] = 'Запрос на зачисление';
$string['sitecall:config'] = 'Настраивать Запрос на зачисление';
$string['sitecall:manage'] = 'Управлять подписками запроса на зачисление';

$string['messageprovider:sitecall_request'] = 'Запрос на зачисление в курс';

$string['status'] = 'Включить подписку';
$string['messageteacher'] = 'Текст письма преподавателям';
$string['messagestudent'] = 'Текст письма студенту';
$string['messageteacher_help'] = 'Текст письма, которое отправляется преподавателям курса. Следующие данные могут быть добавлены в сообщение: <br/>
        {FIRSTNAME} - Имя пользователя<br/>
        {LASTNAME} - Фамилия пользователя<br/>
        {USERID} - ID пользователя<br/>
        {EMAIL} - E-mail пользователя<br/>
        {PHONE} - Номер телефона<br/>
        {COURSEID} - ID курса<br/>
        {COURSEFULLNAME} - Полное название курса<br/>
        {COURSESHORTNAME} - Краткое название курса<br/>
        {COMMENT} - Комментарий<br/>
        {ORIGINS} - Источники, откуда пользователь узнал о курсе<br/>
        {ORGNAME} - Название организации<br/>
        ';
$string['messagestudent_help'] = 'Отправляется только зарегистрированным пользователям, желающим подписаться на курс. Следующие данные могут быть добавлены в сообщение: <br/>
        {FIRSTNAME} - Имя пользователя<br/>
        {LASTNAME} - Фамилия пользователя<br/>
        {USERID} - ID пользователя<br/>
        {EMAIL} - E-mail пользователя<br/>
        {PHONE} - Номер телефона<br/>
        {COURSEID} - ID курса<br/>
        {COURSEFULLNAME} - Полное название курса<br/>
        {COURSESHORTNAME} - Краткое название курса<br/>
        {COMMENT} - Комментарий<br/>
        {ORIGINS} - Источники, откуда пользователь узнал о курсе<br/>
        {ORGNAME} - Название организации<br/>
        ';
$string['messageteacher_send'] = 'Отправлять уведомление о новом запросе преподавателям курса';
$string['messagestudent_send'] = 'Отправлять уведомление о запросе студенту, зарегистрированному в системе';

// Блок подписки пользователя
$string['enrolformbutton'] = 'Заказать курс';

// Уведомления
$string['newrequest'] = 'Новый запрос на подписку';




$string['newenrolrequest'] = '
        Новый запрос на подписку по курсу "{$a->course}" 
        Данные по пользователю:
        Имя: "{$a->lastname} "$a->firstname}";
        ID пользователя: "{$a->userid}";
        ID курса: "{$a->courseid}"; 
        Email: "{$a->email}";
        Телефон: "{$a->phone}"; 
        Комментарий: "{$a->comment}"; 
		Название организации: "{$a->orgname}";
		Из каких источников Вы узнали о нас: "{$a->origins}"; 
		
';
$string['newenrolrequesthtml'] = '
        <p><b>Новый запрос на подписку по курсу "{$a->course}"</b></p>
        <p>Данные по пользователю:</p>
		<ul>
        <li>Имя: "{$a->lastname} {$a->firstname}"; 
        <li>ID пользователя: "{$a->userid}"; 
        <li>ID курса: "{$a->courseid}"; 
        <li>Email: "{$a->email}"; 
        <li>Телефон: "{$a->phone}"; 
        <li>Комментарий: "{$a->comment}"; 
		<li>Название организации: "{$a->orgname}"; 
		<li>Из каких источников Вы узнали о нас: "{$a->origins}"; 
		</ul>
';

$string['ok'] = 'Ок';
$string['msg_sender_name'] = 'Имя отправителя:';
$string['msg_phone'] = 'Телефон:';
$string['msg_message'] = 'Сообщение:';
$string['phone_error_text'] = 'Укажите телефон';
$string['phone_ok_text'] = 'Спасибо!';
$string['firstname_error_text'] = 'Укажите имя';
$string['firstname_ok_text'] = 'Спасибо!';
$string['lastname_error_text'] = 'Укажите фамилию';
$string['lastname_ok_text'] = 'Спасибо!';
$string['course_enrolment'] = 'Подписка на курс';
$string['request_course'] = 'Заказать курс';
$string['enrolling_course'] = 'Вы записываетесь на курс:';
$string['enter_personl_details'] = 'Введите Ваши персональные данные:';
$string['lastname_label'] = 'Фамилия:';
$string['lastname_placeholder'] = 'Фамилия';
$string['name_label'] = 'Имя:';
$string['name_placeholder'] = 'Имя';
$string['phone_label'] = 'Телефон:';
$string['phone_placeholder'] = 'Ваш номер телефона';
$string['email_label'] = 'Электронная почта:';
$string['email_placeholder'] = 'Ваш email';
$string['org_name_label'] = 'Название организации:';
$string['sources_label'] = 'Из каких источников Вы узнали о нас:';
$string['comment_label'] = 'Прочие пожелания';
$string['form_cancel'] = 'Отмена';
$string['course_enrolment'] = 'Подписка на курс';
$string['close'] = 'Закрыть';
$string['enrol_course'] = 'Записаться на курс';
$string['enrol_header_extra'] = 'Отправьте заявку и в ближайшее время наш менеджер свяжется с вами для записи на курс.';
$string['comment'] = 'Комментарий';
$string['form_submit'] = 'Отправить';
$string['no_form_error'] = 'Извините, сообщения через сайт временно не принимаются.';
$string['form_success_text'] = 'Спасибо! Ваши данные отправлены. Скоро с вами свяжется наш специалист.';
$string['form_success_header'] = 'Сообщение принято!';
$string['form_request_error'] = 'Извините, сообщения через сайт временно не принимаются. Свяжитесь с нами по телефону +7 495 229 30 72';
