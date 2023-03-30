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
 * Lang strings.
 *
 * Language strings to be used by report/activetime
 *
 * @package    report_activetime
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// Общие
$string['activetime:view'] = 'Просматривать отчет по затраченному времени на изучение курса';
$string['pluginname'] = 'Время, затраченное на изучение курса';

// Настройки плагина
$string['settings_userfields'] = 'Укажите поля, которые необходимо отобразить в отчете';
$string['settings_userfields_desc'] = '';
$string['custom_field'] = '{$a} (настраиваемое поле)';
$string['vertical_dataorientation'] = 'Вертикально';
$string['horizontal_dataorientation'] = 'Горизонтально';
$string['settings_dataorientation'] = 'Отображение данных по элементам в отчете';
$string['settings_dataorientation_desc'] = '';

// Форма фильтрации
$string['enrolmode'] = 'Каких пользователей отобразить?';
$string['enrolmode_all'] = 'Всех';
$string['enrolmode_active'] = 'Только текущих';
$string['enrolmode_archive'] = 'Отписанных';
$string['modulemode'] = 'Какие элементы курса отобразить?';
$string['modulemode_all'] = 'Все';
$string['modulemode_active'] = 'Только текущие';
$string['modulemode_archive'] = 'Удаленные';
$string['userfieldsfilter'] = 'По полю профиля';
$string['module'] = 'По элементам курса';
$string['user'] = 'По пользователям';
$string['choose_field'] = 'Выберите поле...';
$string['mode_display'] = 'Настройки режима отображения';
$string['filter'] = 'Фильтрация данных';
$string['for_all_modules'] = 'По всем элементам';
$string['for_all_users'] = 'По всем пользователям';
$string['get_report'] = 'Сформировать отчет';

// Таблица отчета
$string['caption_username'] = 'Пользователь';
$string['caption_modulename'] = 'Элемент курса';
$string['caption_activetime_mod'] = 'Время, затраченное на прохождение модуля';
$string['caption_activetime_mod_summ'] = 'Суммарное время по отображаемым модулям';
$string['caption_activetime_course'] = 'Время, затраченное на изучение курса';
$string['caption_attempts'] = 'Количество попыток';
$string['caption_completion'] = 'Отметка о выполнении';
$string['caption_element'] = 'Элемент {$a->count}';
$string['caption_coursename'] = 'Курс';
$string['caption_categoryname'] = 'Категория';
$string['caption_grade'] = 'Оценка';
$string['caption_coursegrade'] = 'Оценка за курс';
$string['time_hint'] = 'Time is taken into account in astronomical hours of the actual interaction of the listener with the e-course. The time spent on educational activities outside the e-learning system, the time in other e-courses, in the mobile application, as well as the time spent on reading and working with files downloaded from the system in off-line mode are not included in this report.';
$string['no_report_data'] = 'Отчет не сформирован. Чтобы сформировать отчет выберите необходимые параметры в форме выше и нажмите на кнопку "Сформировать отчет"';

// Страница удаленного модуля
$string['cm_was_deleted'] = 'Элемент модуля был удален из курса';
$string['back_to_report'] = 'Вернуться в отчету';

// Форма экспорта
$string['export_type'] = 'Выберите формат экспорта';
$string['export_submit'] = 'Экспортировать';
$string['export_header'] = 'Экспорт отчета';

// Права
$string['activetime:view'] = 'Просматривать отчет по курсу';
$string['activetime:viewall'] = 'Просматривать отчет по системе';

