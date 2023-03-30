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
 * Конструктор форм. Языковые строки.
 *
 * @package    block_otcustomform
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
$string['pluginname'] = 'Конструктор форм';
$string['title'] = 'Конструктор форм';
$string['otcustomform:addinstance'] = 'Добавлять новый блок «Конструктор форм»';
$string['otcustomform:myaddinstance'] = 'Добавлять новый блок «Конструктор форм» на страницу /my (Мои курсы, Личный кабинет, Dashboard)';
$string['otcustomform:viewresponses'] = 'Просматривать отправленные данные';

$string['header_users_responses'] = 'Список пользователей';

$string['view_responses'] = 'Посмотреть ответы';

$string['fullname'] = 'ФИО';
$string['lastfilltime'] = 'Дата последней отправленной формы';
$string['actions'] = 'Действия';

$string['view_all_user_responses'] = 'Просмотр отправленных форм пользователя';

$string['response_info'] = 'Форма зафиксирована: «{$a}»';

$string['no_login_user'] = 'Неавторизованный пользователь';

$string['invalid_formid'] = 'Отсутствует форма. Пожалуйста, обратитесь к администратору СДО';
$string['invalid_uid'] = 'Неверный идентификатор пользователя';

$string['block_name'] = "Название блока";
$string['hide_header'] = "Скрывать заголовок блока";
$string['formsaved'] = 'Данные отправлены успешно';
$string['formsavefailed'] = 'Во время сохранения данных произошла ошибка. Попробуйте еще раз';
$string['form_layout'] = 'Код разметки формы';
$string['form_layout_help'] = "
Ниже приведен пример корректной разметки для создания кастомной формы:

class:<br>
&nbsp;&nbsp;header:<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;type: 'header'<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;label: 'Заполните анкету'<br>
&nbsp;&nbsp;lastname:<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;type: 'text'<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;label: 'Фамилия'<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;rules: [required]<br>
&nbsp;&nbsp;firstname:<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;type: 'text'<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;label: 'Имя'<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;rules: [required]<br>
&nbsp;&nbsp;middlename:<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;type: 'text'<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;label: 'Отчество'<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;rules: [required]<br>
&nbsp;&nbsp;sex:<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;type: 'select'<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;label: 'Пол'<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;options: ['Мужской', 'Женский']<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;rules: [required]<br>
&nbsp;&nbsp;birthday:<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;type: 'date'<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;filter: 'int'<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;label: 'Дата рождения'<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;options: {'startyear' : 1970, 'stopyear' : 2018}<br>
&nbsp;&nbsp;citizenship:<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;type: 'country'<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;label: 'Гражданство'<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;rules: [required]<br>
&nbsp;&nbsp;city:<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;type: 'text'<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;label: 'Страна, город'<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;rules: [required]<br>
&nbsp;&nbsp;address:<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;type: 'textarea'<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;label: 'Место жительства (указать почтовый индекс, адрес прописки)'<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;options: {'rows' : 3}<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;rules: [required]<br>
&nbsp;&nbsp;phonecell:<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;type: 'text'<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;label: 'Контактный сотовый телефон'<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;rules: [required]<br>
&nbsp;&nbsp;email:<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;type: 'text'<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;label: 'Контактный e-mail'<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;rules: [required]<br>
&nbsp;&nbsp;permitprocessingpersonaldata:<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;type: 'checkbox'<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;label: 'Согласен(а) передать на обработку личные данные'<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;rules: [required]<br>
&nbsp;&nbsp;submit:<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;type: 'submit'<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;label: 'Отправить данные'
";

