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
 * Строки для компонента 'learninghistory', язык 'ru'
 *
 * @package    local_learninghistory
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$string['pluginname'] = 'История обучения';
$string['learninghistory:viewmylearninghistory'] = 'Просматривать свою собственную историю обучения';
$string['learninghistory:viewlearninghistoryofdeleted'] = 'Просматривать историю обучения для удаленных курсов';
$string['learninghistory:viewuserslearninghistory'] = 'Просматривать историю обучения других пользователей';

$string['index_learninghistory'] = 'История обучения';
$string['table_course'] = 'Курс';
$string['table_startdate'] = 'Дата начала';
$string['table_enddate'] = 'Дата окончания';
$string['table_finalgrade'] = 'Итоговая оценка';
$string['table_error'] = 'Ошибка';
$string['table_hiddencourse'] = 'Курс скрыт';
$string['table_accessdenied'] = 'Доступ закрыт';
$string['index_hiddencourses'] = 'Скрытых строк:';
$string['course_not_found'] = 'Курс не найден: ';
$string['until_the_course_is_closed'] = 'До конца изучения курса осталось:';
$string['total_time_in_course'] = 'Общее время обучения в курсе:';
$string['atlastupdate'] = 'Актуально на: {$a}';
$string['ajax_monitoring_active'] = 'идет учет времени';
$string['ajax_monitoring_inactive'] = 'приостановлен учет времени';
$string['ajax_monitoring_disabled'] = 'учет времени ведется только при совершении активных действий в системе';

$string['activetime_refresh'] = 'Обновление времени непрерывного обучения в курсе';
$string['activetime_refresh_form_description'] = 'Если вы обновили настройки в курсах и хотите пересчитать время непрерывного обучения с учетом новых настроек, добавьте задачу на пересчет с помощью этого инструмента'; 
$string['activetime_refresh_form_add_task'] = 'Добавить одноразовую задачу на обновление непрерывного времени обучения курса';
$string['task_not_added'] = 'Во время добавления задачи произошли ошибки';
$string['task_added'] = 'Задача добавлена';
$string['task_not_added'] = 'Задача не добавлена';

/**
 * Форма доп настроек
 */
$string['activetime_settings'] = 'Отслеживание времени непрерывного обучения';
$string['activetime_settings_title'] = 'Настройки отслеживания времени непрерывного обучения в курсе';
$string['activetime_enable'] = 'Включить отслеживание';
$string['activetime_enable_help'] = 'Включение данной настройки запустит процесс отслеживания времени непрерывного обучения в курсе для обучающихся';
$string['nopermission'] = 'Для редактирования настроек недостаточно прав. Обратитесь к администратору системы.';
$string['mainlogs_enable'] = 'По действиям';
$string['additionallogs_enable'] = 'Ajax-мониторинг';
$string['mode'] = 'Режим работы';
$string['mode_help'] = 'При выборе режима работы "По действиям" не будет возникать дополнительной нагрузки при отслеживании, но данные будут менее точными. При выборе режима "Ajax-мониторинг" нагрузка на систему возрастет, но данные будут более точными.';
$string['delay'] = 'Задержка между проверками в режиме "Ajax-мониторинг"';
$string['delay_help'] = 'Данная настройка задает интервал между проверками состояния. Влияет на нагрузку: чем меньше интервал, тем больше нагрузка на систему.';
$string['timer'] = 'Отображать в курсе таймер';
$string['timer_help'] = 'При включенной настройке в курсе будет показан таймер с указанием оставшегося временем на изучение курса';
$string['available_time'] = 'Время на изучение курса';
$string['available_time_help'] = 'Если указано время на изучение курса, в таймере будет отображаться оставшееся время, если указано 0 - будет отображаться общее время, проведенное за изучением курса.';
$string['timer_enable'] = 'Включить отображение таймера в курсе';
$string['timer_refresh'] = 'Частота обновления таймера';
$string['timer_refresh_help'] = 'Таймер будет обновлять данные с указанной периодичностью. Рекомендуется указывать интервал не меньше, чем интервал запуска cron.';
$string['not_valid_delay'] = 'Значение задержки между запросами должно быть не меньше 20 сек.';
$string['not_valid_timer_refresh'] = 'Значение частоты обнолвения таймера должно быть больше нуля';
$string['region'] = 'Зона для отображения таймера';
$string['region_help'] = 'Разные темы оформления поддерживают разные зоны для размещения блоков. Вы можете указать в какой зоне необходимо отобразить таймер.';
$string['side-pre'] = 'Левая колонка';
$string['side-post'] = 'Правая колонка';
$string['delaybetweenlogs'] = 'Максимальный засчитываемый период (пауза) между действиями пользователя';
$string['delaybetweenlogs_help'] = 'Данная настройка влияет на принятие решения о включении времени между двумя ближайшими логами в общий расчет времени обучения. Если время между двумя ближайшими логами больше указанного, считается, что пользователь не был активен в системе, и время не идет в зачет. Рекомендуем выставлять значение данной настройки больше, чем значение настройки "Задержка между проверками в режиме Ajax-мониторинг" (разница в 10 сек. будет оптимальной).';
$string['not_valid_delaybetweenlogs'] = 'Максимальное время между логами должно быть больше задержки между проверками';

/**
 * Задачи
 */
$string['task_activetime_update_title'] = 'Обновление значения непрерывного времени обучения в курсе';
$string['task_init_tables_data_title'] = 'Наполнение хранилищ первичными данными';
$string['task_fill_data_title'] = 'Наполнение хранилищ данными';

/**
 * События
 */
$string['event_activetime_updated_title'] = 'Изменение времени активности в курсе';
$string['event_activetime_updated_desc'] = 'Для пользователя с id={$a->userid} было изменено время активности в курсе с id={$a->courseid}';
$string['event_cm_grade_history_updated_title'] = 'Изменена история оценки за модуль курса';
$string['event_cm_grade_history_updated_desc'] = 'Для пользователя с id={$a->userid} была изменена запись в истории оценок модулей курса';
$string['event_course_grade_history_updated_title'] = 'Изменена история оценки за курс';
$string['event_course_grade_history_updated_desc'] = 'Для пользователя с id={$a->userid} была изменена запись в истории оценок за курс';

/**
 * Права
 */
$string['learninghistory:activetimemanage'] = 'Право управлять настройками отслеживания времени непрерывного обучения в курсе';
