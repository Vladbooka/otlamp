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
 * @package    local_authcontrol
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// Базовые языковые переменные
$string['pluginname'] = 'Панель управления доступом в СДО';
$string['local_authcontrol'] = 'Главная страница';
$string['additional_authcontrol_coursesettings'] = 'Панель управления доступом в СДО';

$string['settings_general'] = 'Настройки панели управления';

$string['settings_form_control_enable'] = 'Включить подсистему контроля доступа в СДО';
$string['settings_form_control_enable_desc'] = 'Включение подсистемы приведет к запрету доступа всех пользователей СДО, не имеющих на данный момент открытый доступ к курсу или модулю курса';
$string['settings_form_control_enable_session'] = 'Включить блокировку множественного входа';
$string['settings_form_control_enable_session_desc'] = 'Включение опции приведет к запрету множественного входа, все последующие попытки входа пользователя в СДО будут отклонены';

// Форма выбора курса
$string['form_courses_select'] = 'Выберите курс';
$string['form_courses_empty'] = 'Отсутствуют курсы в СДО';
$string['form_courses_error_empty'] = 'Не выбран курс';
$string['form_courses_error_capability'] = 'Нет прав на просмотр курса';
$string['form_courses_load_info'] = 'Загрузить панель';

// Главная форма
$string['form_main_actions'] = 'Действие над выбранными пользователями';
$string['form_main_submit'] = 'Выполнить';
$string['form_main_access_on'] = 'Открыть доступ';
$string['form_main_access_off'] = 'Закрыть доступ';
$string['form_main_access_off'] = 'Закрыть доступ';
$string['form_main_access_choose_course'] = 'Модуль курса';
$string['form_main_access_course_context'] = 'Область доступа';
$string['form_main_access_notific_empty_modules'] = 'В курсе отсутствуют элементы';
$string['form_main_access_notific_empty_students'] = 'В курсе отсутствуют студенты';
$string['form_main_access_empty_chose_students'] = 'Пожалуйста, выберите пользователей';
$string['form_main_access_student_on'] = 'Открыт';
$string['form_main_access_student_off'] = 'Закрыт';
$string['form_main_access_student_area_empty'] = 'Отсутствует';
$string['form_main_access_student_area_course'] = 'Курс: {$a}';
$string['form_main_access_student_area_module'] = 'Модуль: {$a}';
$string['form_main_access_settings'] = 'Настройки';
$string['form_main_access_reset_password'] = 'Сбросить пароль';
$string['form_main_access_reset_sessions'] = 'Сбросить сессии';
$string['form_main_access_close_access'] = 'Закрыть доступ';
$string['form_main_enter_password'] = 'Введите пароль';
$string['form_main_process_save_success'] = 'Данные успешно сохранены';
$string['form_main_process_save_fail'] = 'При сохранении данных возникла ошибка';
$string['form_main_course_hidden'] = 'Выбранный курс скрыт';
$string['form_main_button_change'] = 'Изменить';
$string['form_main_choice_course'] = 'Курс';
$string['form_main_choice_module'] = 'Модуль курса';


// Поля таблица
$string['fio'] = 'ФИО';
$string['login'] = 'Логин';
$string['group'] = 'Группа';
$string['role'] = 'Роль';
$string['actions'] = 'Действия';
$string['status'] = 'Статус';
$string['access_area'] = 'Доступ';
$string['context'] = 'Контекст';
$string['course'] = 'Курс';
$string['module'] = 'Модуль';

// Сообщения
$string['course_not_exists'] = 'Курс не существует';
$string['course_not_access'] = 'Нет доступа к курсу';
$string['course_not_access_panel'] = 'Нет доступа к панели управления доступом в СДО';

// AJAX
$string['ajax_error_invalid_param'] = 'Нет прав или недостаточно данных для выполнения ajax запроса';
$string['ajax_error_invalid_token'] = 'Получен невалидный токен';
$string['ajax_sessions_kill_success'] = 'Сессии успешно сброшены';
$string['ajax_sessions_kill_fail'] = 'Во время сброса сессий возникла ошибка';
$string['ajax_password_used_before'] = 'Пароль уже использовался для текущего пользователя';
$string['ajax_password_success'] = 'Пароль успешно изменен';
$string['ajax_password_fail'] = 'Во время смены пароля возникла ошибка';
$string['ajax_password_user_not_found'] = 'Не найден пользователь в базе';
$string['ajax_password_fail_password_policy'] = 'Неверный пароль. Проверьте политику паролей';
$string['ajax_access_success'] = 'Доступ закрыт';
$string['ajax_access_fail'] = 'Во время закрытия доступа возникла ошибка';

// ACCESS
$string['authcontrol:use'] = 'Использовать панель';
$string['authcontrol:view'] = 'Видеть список пользователей';
$string['authcontrol:access_control'] = 'Находиться под контролем плагина';

// Страница с сообщением о превышении лимита активных пользователей
$string['back_home'] = 'Back to main page';
$string['title_onlineusersoverlimit'] = 'Exceeded available load';
$string['message_onlineusersoverlimit'] = 'We apologize for any inconvenience.
You have exceeded the maximum number of active users .
You will be able to continue the work after the load is reduced.';


