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
 * Language strings to be used by report/logs
 *
 * @package    report_log
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Общие
 */
$string['pluginname'] = 'Объединенный отчет по результатам прохождения элементов курса';
$string['no_report_data'] = 'Отчет не сформирован';
$string['error_data_getting'] = 'Данные не получены';
$string['error_access_denied'] = 'Доступ запрещен';
$string['empty_report'] = 'Нет данных для отображения в отчете';
$string['anonymous'] = ' (анонимно)';
$string['statex'] = 'Статус ответа {$a}';
$string['attempt_completion_incomplete'] = 'Не сдано';
$string['attempt_completion_complete'] = 'Сдано';
$string['attempt_completion_unknown'] = 'Неизвестно';
$string['attempt_completion_header'] = 'Выполнение';

/**
 * Права
 */
$string['mods_data:view'] = 'Просматривать объединенный отчет по результатам прохождения элементов курса';
$string['mods_data:view_quiz_attempt_report'] = 'Просматривать отчет по индивидуальной попытке прохождения элемента курса';
$string['mods_data:view_self_report_data'] = 'Просматривать персональный отчет';

/**
 * Форма фильтрации
 */
$string['form_filter_courses'] = 'Выберите курсы';
$string['for_all_courses'] = 'По всем курсам';
$string['form_filter_courses_title'] = 'Фильтрация по курсам';
$string['from_filter_users'] = 'Выберите пользователей';
$string['for_all_users'] = 'По всем пользователям';
$string['form_filter_users_title'] = 'Фильтрация по пользователям';
$string['form_filter_section_title'] = 'Фильтрация данных';
$string['form_set_filter_users'] = 'Отфильтровать данные';
$string['form_set_filter_courses'] = 'Отфильтровать данные';
$string['no_modules_for_display'] = 'Не найдено модулей по указанным параметрам. Попробуйте изменить параметры фильтрации.';
$string['choose_field'] = 'Выберите поле';
$string['fields'] = 'Поля';
$string['mods_criterias_title'] = 'Критерии отбора элементов курса';
$string['completion'] = 'Сдал/не сдал';
$string['completion_all'] = 'Все';
$string['completion_completed'] = 'Сдал';
$string['completion_notcompleted'] = 'Не сдал';
$string['attempts'] = 'Попытки';
$string['attempts_all'] = 'Все';
$string['attempts_best'] = 'Лучшие';
$string['period'] = 'Период';
$string['startdate'] = 'Начало';
$string['enddate'] = 'Конец';
$string['attemptsinperiod'] = 'Включить в отчет';
$string['attemptsinperiod_all'] = 'все попытки за период';
$string['attemptsinperiod_finished'] = 'завершенные попытки за период';
$string['allusers'] = 'Все пользователи ({$a})';
$string['allfilteredusers'] = 'Все отфильтрованные ({$a->count}/{$a->total})';
$string['nofilteredusers'] = 'Пользователей не найдено (0/{$a})';
$string['allselectedusers'] = 'Все выбранные ({$a->count}/{$a->total})';
$string['noselectedusers'] = 'Пользователи не выбраны';
$string['available'] = 'Доступные';
$string['selected'] = 'Выбранные';
$string['users'] = 'Пользователи';
$string['users_help'] = 'Все пользователи, которые соответствуют активным фильтрам, будут перечислены в поле. Если ни один фильтр не установлен, то будут перечислены все пользователи сайта.';
$string['addsel'] = 'Добавить к выбранным';
$string['removesel'] = 'Убрать из выбранных';
$string['addall'] = 'Добавить всех';
$string['removeall'] = 'Убрать всех';
$string['selectedlist'] = 'Список выбранных пользователей...';
$string['selectedlist_help'] = 'Пользователи могут быть добавлены или удалены из списка выбранных пользователей. Для этого щелкните на именах пользователей и нажмите соответствующую кнопку. Чтобы выбрать несколько пользователей, щелкните на именах пользователей, удерживая клавишу Ctrl или Apple.';


$string['userfields_title'] = 'Поля профиля';
$string['customuserfields_title'] = 'Кастомные поля профиля';
$string['dofpersonfields_title'] = 'Поля персоны деканата';
$string['modulefields_title'] = 'Отчеты элементов курса';
$string['submit_title'] = 'Отчет по пользователям';
$string['export_format'] = 'Формат отчета';
$string['export_format_xls'] = 'Excel';
$string['export_format_pdf'] = 'PDF';
$string['export_format_html'] = 'HTML';
$string['export_submit'] = 'Сформировать';
$string['submit_self_title'] = 'Персональный отчет';
$string['type_self'] = 'Персональный';
$string['type_all'] = 'Общий';
$string['general_title_submit'] = 'Формирование отчета';
$string['choose_field'] = 'Выберите поле';
$string['fields'] = 'Поля';

$string['form_filter_groups_title'] = 'Фильтрация по локальным группам';
$string['form_filter_groups'] = 'Выберите локальные группы';
$string['for_all_groups'] = 'Для всех локальных групп';
$string['form_set_filter_groups'] = 'Отфильтровать данные';

/**
 * Валидация формы
 */
$string['invalid_period'] = 'Дата начала периода не может быть больше даты конца';

/**
 * Таски
 */
$string['task_collectdata_title'] = 'Задача по формированию кеша отчета';

/**
 * Настройки отчета
 */
$string['settings_enablecron'] = 'Включить кеширование отчета';
$string['settings_enablecron_desc'] = 'После включения опции, данные, попадающие в отчет, будут кешироваться.';
$string['choice_checkedfields'] = 'Выберите поля...';
$string['settings_checkedfields'] = 'Поля, которые по умолчанию будут отмечены в форме генерации отчета';
$string['settings_checkedfields_desc'] = 'Выбранные поля будут автоматически отмечены в форме генерации отчета';
$string['settings_defaultperiod'] = 'Период сбора данных по умолчанию';
$string['settings_defaultperiod_desc'] = 'Указанный период будет по умолчанию указан в форме генерации отчета';
$string['settings_quiz_attempt_user_fields'] = 'Поля, которые будут выведены в отчете по индивидуальной попытке прохождения элемента';
$string['settings_quiz_attempt_user_fields_desc'] = 'Указанные поля будут выведены в секции информации о пользователе в отчете по индивидуальной попытке прохождения элемента';
$string['settings_allowedmistakes'] = 'Допустимое количество ошибок';
$string['settings_allowedmistakes_desc'] = 'Допустимое количество ошибок будет указано в форме отчета по индивидуальной попытке прохождения элемента';
$string['incomplete_mode'] = 'Считать элемент не выполненным';
$string['complete_mode'] = 'Считать элемент выполненным';
$string['ignore_mode'] = 'Не добавлять элемент в отчет';
$string['unknown_mode'] = 'Добавлять элемент в отчет';
$string['settings_completionmode'] = 'Поведение при выключенном отслеживании выполнения';
$string['settings_completionmode_desc'] = 'Данная настройка задает режим поведения для элементов, находящихся в курсе с выключенным отслеживанием выполнения';
$string['settings_quiz_attempt_report_default_format'] = 'Формат отчета по индивидуальной попытке прохождения элемента';
$string['quiz_attempt_report_pdf_format'] = 'PDF';
$string['quiz_attempt_report_html_format'] = 'HTML';
$string['settings_quiz_attempt_report_default_format_desc'] = 'Формат отчета по индивидуальной попытке прохождения элемента';
$string['settings_attempt_completion_critetia'] = 'Критерий определения успешности попытки прохождения элемента';
$string['settings_attempt_completion_critetia_desc'] = 'Указанный критерий будет использован при рассчете успешности попытки прохождения элемента. Отметка об успешности прохождения будет добавлена в отчеты.';
$string['attempt_completion_gradepass'] = 'Проходной балл';
$string['attempt_completion_completion'] = 'Выполнения элемента';
$string['attempt_completion_gradepasspriority'] = 'Приоритет проходного балла';
$string['attempt_completion_completionpriority'] = 'Приоритет выполнения элемента';
$string['settings_xls_orientation'] = 'Ориентация отчета в формате xls';
$string['settings_xls_orientation_desc'] = 'Ориентация отчета в формате xls';
$string['xls_orientation_h'] = 'Горизонтальная (по пользователю)';
$string['xls_orientation_v'] = 'Вертикальная (по попытке)';

/**
 * Генерация pdf формы по попытке прохождения
 */
$string['quiz_attempt_report_link_header'] = 'Отчет';
$string['quiz_attempt_pdf_report_link_title'] = 'Скачать';
$string['quiz_attempt_html_report_link_title'] = 'Просмотреть';
$string['back_to_main_report_link_title'] = 'К отчету по курсу';
$string['attempt_not_found'] = 'Попытка с идентификатором {$a->id} не найдена';
$string['attempt_not_found_html'] = 'Не удалось найти данные по запрошенной попытке';
$string['quiz_name_caption'] = 'Предмет тестирования';
$string['attempt_date_caption'] = 'Дата и время проведения тестирования';
$string['question_number_table_caption'] = '№';
$string['question_name_table_caption'] = 'Вопрос';
$string['question_answer_table_caption'] = 'Ответ';
$string['question_result_table_caption'] = 'Результат';
$string['quiz_attempt_report'] = 'Отчет по индивидуальной попытке прохождения элемента курса';
$string['allowedmistakes'] = 'Допустимое количество ошибок: {$a->allowedmistakes}';
$string['admittedmistakes'] = 'Допущено ошибок: {$a->admittedmistakes}';
$string['completionstate_incomplete'] = 'Результат тестирования: НЕ СДАНО';
$string['completionstate_complete'] = 'Результат тестирования: СДАНО';
$string['completionstate_unknown'] = 'Результат тестирования: НЕИЗВЕСТНО';
$string['quiz_attempt_report_bottom'] = '<p>При проведении тестирования нарушений его порядка не зафиксировано</p>
                                         <p>Ответственный за проведение тестирования, ____________________/ ______________________________/</p>
                                         <p>Тестируемый ____________________/{$a->fullname}/</p>';


