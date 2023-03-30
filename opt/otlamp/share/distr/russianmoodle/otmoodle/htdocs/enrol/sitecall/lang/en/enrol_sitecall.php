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
$string['msg_sender_name'] = 'Sender name:';
$string['msg_phone'] = 'Phone:';
$string['msg_message'] = 'Message:';
$string['phone_error_text'] = 'Enter the phone';
$string['phone_ok_text'] = 'Thank you!';
$string['firstname_error_text'] = 'Enter the name';
$string['firstname_ok_text'] = 'Thank you!';
$string['lastname_error_text'] = 'Enter the lastname';
$string['lastname_ok_text'] = 'Thank you!';
$string['course_enrolment'] = 'Course enrolment';
$string['request_course'] = 'Request the course';
$string['enrolling_course'] = 'You are enrolling on a course:';
$string['enter_personl_details'] = 'Enter your personal details:';
$string['lastname_label'] = 'Lastname:';
$string['lastname_placeholder'] = 'Lastname';
$string['name_label'] = 'Name:';
$string['name_placeholder'] = 'Name';
$string['phone_label'] = 'Phone:';
$string['phone_placeholder'] = 'Your phone number';
$string['email_label'] = 'Email:';
$string['email_placeholder'] = 'Your email';
$string['org_name_label'] = 'Organization name:';
$string['sources_label'] = 'From what sources did you hear about us:';
$string['comment_label'] = 'Comments';
$string['form_cancel'] = 'Cancel';
$string['course_enrolment'] = 'Course enrolment';
$string['close'] = 'Close';
$string['enrol_course'] = 'Enrol on the course';
$string['enrol_header_extra'] = 'Our manager will contact you soon after you submit your request to enrol you on the course.';
$string['comment'] = 'Comment';
$string['form_submit'] = 'Submit';
$string['no_form_error'] = 'Sorry. Messages are temporary not accepted via the site.';
$string['form_success_text'] = 'Thank you! Your request is submitted. Our manager will contact you soon.';
$string['form_success_header'] = 'The request is submitted!';
$string['form_request_error'] = 'Sorry. Messages are temporary not accepted via the site. Contact us by phone +7 495 229 30 72';
