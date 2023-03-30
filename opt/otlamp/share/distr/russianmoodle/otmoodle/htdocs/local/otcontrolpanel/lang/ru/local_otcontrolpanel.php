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
 * Панель управления СЭО 3KL. Языковые строки.
 *
 * @package    local_otcontrolpanel
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$string['pluginname'] = 'Панель управления СЭО 3KL';
$string['config'] = 'Конфигурация';
$string['view_noname'] = '{$a->entityname} #{$a->viewcode}';
$string['starttext'] = '<div>Данный инструмент предназначен для просмотра и выполнения массовых действий над различными объектами системы.</div>
<div>Инструмент не оптимизирован для работы с большим количеством записей.</div>
<div>Для продолжения работы с инструментом выберите вкладку.</div>';
$string['filterform_header'] = 'Фильтрация';
$string['filterform_applied'] = 'Применена фильтрация {$a}';
$string['filterform_cancel'] = '(отменить)';


$string['otcontrolpanel:view_data'] = 'Просматривать данные в панели управления СЭО 3KL';
$string['otcontrolpanel:config'] = 'Настраивать панель управления СЭО 3KL для любых пользователей';
$string['otcontrolpanel:config_my'] = 'Настраивать панель управления СЭО 3KL для себя';
$string['otcontrolpanel:take_actions'] = 'Совершать действия через панель управления СЭО 3KL';


$string['e_user'] = 'Пользователи';
$string['e_user_fld_fullname'] = 'ФИО';


$string['e_cohort'] = 'Глобальные группы';
$string['e_cohort_r_course'] = 'Курсы, имеющие связанную с глобальной группой запись на курс "Синхронизация с глобальной группой"';
$string['e_cohort_r_user'] = 'Пользователи, являющиеся участниками глобальной группы';
$string['e_cohort_a_enrol_to_courses'] = 'Запись выбранных глобальных групп на курсы';
$string['e_cohort_a_enrol_to_courses_fe_courses'] = 'Укажите курсы, на которые должны быть записаны выбранные глобальные группы';
$string['e_cohort_a_enrol_to_courses_fe_roleid'] = 'Роль';
$string['e_cohort_a_enrol_to_courses_fe_groupmode'] = 'Режим синхронизации с локальными группами';
$string['e_cohort_a_enrol_to_courses_fe_groupmode_nogroup'] = 'Без локальной группы';
$string['e_cohort_a_enrol_to_courses_fe_groupmode_samename'] = 'Одноименная локальная группа';
$string['e_cohort_a_enrol_to_courses_fe_creategroup'] = 'Создать локальную группу если её нет';
$string['e_cohort_a_enrol_to_courses_fe_submit'] = 'Записать';
$string['e_cohort_a_enrol_to_courses_err_nocourses'] = 'Необходимо выбрать хотя бы один курс для записи';
$string['e_cohort_a_enrol_to_courses_err_no_site'] = 'Запись на курс "{$a->coursefullname}" не будет создана, так как это главная страница сайта';
$string['e_cohort_a_enrol_to_courses_err_context_failed'] = 'Глобальная группа "{$a->cohortname}" не будет подписана на курс "{$a->coursefullname}", так как размещена в недоступном контексте.';
$string['e_cohort_a_enrol_to_courses_report_message'] = 'Создана запись ({$a->instanceid}) на курс "{$a->coursefullname}" для глобальной группы "{$a->cohortname}"';
$string['e_cohort_a_unenrol_from_courses'] = 'Отчисление выбранных глобальных групп из курсов';
$string['e_cohort_a_unenrol_from_courses_fe_courses'] = 'Укажите курсы, в которых следует удалить подписки выбранных глобальных групп';
$string['e_cohort_a_unenrol_from_courses_fe_delete_empty_group'] = 'Удалить связанную группу в курсе, если в ней не осталось участников';
$string['e_cohort_a_unenrol_from_courses_fe_submit'] = 'Отчислить';
$string['e_cohort_a_unenrol_from_courses_err'] = 'Ошибка: {$a}';
$string['e_cohort_a_unenrol_from_courses_err_nocourses'] = 'Необходимо выбрать хотя бы один курс для отписки';
$string['e_cohort_a_unenrol_from_courses_report_message'] = 'Удалена запись ({$a->instanceid}) на курс "{$a->coursefullname}" глобальной группы "{$a->cohortname}"';



$string['e_course'] = 'Курсы';
$string['e_course_fld_categoryname'] = 'Название категории';
$string['e_course_fld_categorypath'] = 'Путь категории';
$string['e_course_fld_coursepath'] = 'Путь курса';
$string['e_course_r_cohort'] = 'Глобальные группы, синхронизированные с курсом через запись на курс "Синхронизация с глобальной группой"';
$string['e_course_r_students'] = 'Пользователи, записанные на курс в оцениваемой роли';
$string['e_course_r_contacts'] = 'Контакты курса';
$string['e_course_r_certissues'] = 'Сертификаты, выпущенные в курсе в указанном периоде';
$string['e_course_r_userscompleted'] = 'Пользователи, завершившие курс';
$string['e_course_r_assign_submission'] = 'Ответы на задание в курсе';
$string['e_course_r_assign_submission_first_attempt'] = 'Первые попытки ответов на задание в курсе';
$string['e_course_a_enrol_cohorts'] = 'Запись глобальных групп на выбранные курсы';
$string['e_course_a_enrol_cohorts_fe_cohorts'] = 'Укажите глобальные группы, которые должны быть подключены к выбранным курсам';
$string['e_course_a_enrol_cohorts_fe_roleid'] = 'Роль';
$string['e_course_a_enrol_cohorts_fe_groupmode'] = 'Режим синхронизации с локальными группами';
$string['e_course_a_enrol_cohorts_fe_groupmode_nogroup'] = 'Без локальной группы';
$string['e_course_a_enrol_cohorts_fe_groupmode_samename'] = 'Одноименная локальная группа';
$string['e_course_a_enrol_cohorts_fe_creategroup'] = 'Создать локальную группу если её нет';
$string['e_course_a_enrol_cohorts_fe_submit'] = 'Записать';
$string['e_course_a_enrol_cohorts_err_nocohorts'] = 'Необходимо выбрать хотя бы одну глобальную группу для записи';
$string['e_course_a_enrol_cohorts_report_message'] = 'Создана запись ({$a->instanceid}) на курс "{$a->coursefullname}" для глобальной группы "{$a->cohortname}"';
$string['e_course_a_enrol_cohorts_err_no_site'] = 'Запись на курс "{$a->coursefullname}" не будет создана, так как это главная страница сайта';
$string['e_course_a_enrol_cohorts_err_context_failed'] = 'Глобальная группа "{$a->cohortname}" не будет подписана на курс "{$a->coursefullname}", так как размещена в недоступном контексте.';
$string['e_course_a_unenrol_cohorts'] = 'Отчисление из выбранных курсов глобальных групп';
$string['e_course_a_unenrol_cohorts_fe_cohorts'] = 'Укажите глобальные группы, для которых следует удалить подписки в выбранных курсах';
$string['e_course_a_unenrol_cohorts_fe_delete_empty_group'] = 'Удалить связанную группу в курсе, если в ней не осталось участников';
$string['e_course_a_unenrol_cohorts_fe_submit'] = 'Отчислить';
$string['e_course_a_unenrol_cohorts_err'] = 'Ошибка: {$a}';
$string['e_course_a_unenrol_cohorts_err_nocohorts'] = 'Необходимо выбрать хотя бы одну глобальную группу для отписки';
$string['e_course_a_unenrol_cohorts_report_message'] = 'Удалена запись ({$a->instanceid}) на курс "{$a->coursefullname}" глобальной группы "{$a->cohortname}"';


$string['e_enrol'] = 'Записи на курс';


$string['e_certissues'] = 'Выданные сертификаты';


$string['e_assign_submission'] = 'Ответы на задание';
$string['e_assign_submission_fld_assignuserattempt'] = 'Попытка';


$string['action_execute'] = 'Выполнить действие';
$string['action_no_rows_selected'] = 'Не выбрано ни одной строки';
$string['action_select_rows'] = 'Выберите строки, над которыми хотите произвести действие';
$string['selected_objects_header'] = 'Выбранные объекты ({$a->objects_count})';
$string['choose_action_header'] = 'Выбор действия';
$string['choose_action_noactions'] = 'К сожалению, для данной сущности пока не предусмотрено никаких действий';
$string['choose_action_field'] = 'Выберите действие, которое хотели бы выполнить для выбранных строк';
$string['choose_action_submit'] = 'Выбрать';
$string['action_settings_header'] = 'Настройки действия';
$string['no_records_to_display'] = 'Не найдено записей';
$string['no_columns_to_display'] = 'Не настроены колонки для отображения';
$string['shortstring_show_all'] = 'Показать все';
$string['action_report_warning'] = 'Для того, чтобы внесенные изменения отразились в таблице, обновите страницу';
$string['action_report_header'] = 'Результаты исполнения действия';


$string['config_is_loading'] = 'Загрузка конфигурации...';
$string['entity_fields'] = 'Основные поля';
$string['entity_relation_related_entity'] = 'Поля связанной сущности: {$a}';
$string['entity_relation'] = '{$a}';
$string['config_restore_defaults'] = 'Вернуть настройки по умолчанию';
$string['views'] = 'Вкладки';
$string['view_header_edit'] = 'Редактирование';
$string['view_displayname'] = 'Отображаемое имя';
$string['view_save_changes'] = 'Сохранить';
$string['view_cancel_changes'] = 'Отменить';
$string['adding_new_view'] = 'Добавление новой вкладки';
$string['add_new_view'] = 'Добавить';


