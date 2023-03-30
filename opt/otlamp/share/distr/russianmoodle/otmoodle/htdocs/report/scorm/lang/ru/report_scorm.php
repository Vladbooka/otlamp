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
 * Отчет по результатам SCORM. Языковые строки.
 *
 * @package    report
 * @subpackage scorm
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// Базовые строки
$string['pluginname'] = 'Отчет по модулям SCORM';
$string['scorm:view'] = 'Получать общий отчет по SCORM';
$string['scorm:editmodsettings'] = 'Редактировать настройки отчета по модулю SCORM';
$string['scorm:viewstatistic'] = 'Получать статистику по модулю SCORM';

// Глобальные настройки отчета
$string['settings_passpercent'] = 'Проходной процент выполнения SCORM (по умолчанию для всех модулей курса)';
$string['settings_passpercent_desc'] = 'Процент выполнения оцениваемых элементов SCORM-пакета, при котором считается, что пользователь успешно завершил элемент курса';

// Настройки отчета для SCORM модулей курса
$string['settings_form_title'] = 'Настройки отчета по модулю SCORM';
$string['settings_form_header'] = 'Настройки отчета по модулю SCORM';
$string['settings_form_description'] = '';
$string['settings_form_passpercent'] = 'Проходной процент выполнения SCORM';
$string['settings_form_passpercent_desc'] = 'Установка процента выполнения элемента курса для прохождения';
$string['settings_form_passpercent_placeholder'] = '{$a->defaultpersent}';
$string['settings_form_passpercent_postfix'] = '%';
$string['settings_form_gradeelements_description'] = 'Данные по оцениваемым элементам SCORM-пакета. В указанных полях необходимо заполнить идентификаторы элементов и их вес. Итоговый результат прохождения представляет собой сумму всех весов выполненных элементов SCORM-пакета.';
$string['settings_form_gradeelement_id'] = 'Идентификатор элемента SCORM';
$string['settings_form_gradeelement_id_desc'] = '';
$string['settings_form_gradeelement_weight'] = 'Вес элемента';
$string['settings_form_gradeelement_weight_desc'] = '';
$string['settings_form_gradeelement_addrow'] = 'Добавить строку';
$string['settings_form_gradeelement_submit'] = 'Сохранить';
$string['settings_form_gradeelement_gradetype_view'] = 'Просмотр вопроса';
$string['settings_form_gradeelement_gradetype_correct_answer'] = 'Правильный ответ на вопрос';

// Отображение отчета
$string['cmsettings_link'] = 'Настройки отчета по прохождению SCORM';
$string['report_cm_link'] = 'Отчет по прохождению SCORM';
$string['full_report_title'] = 'Детальный отчет по статистике';
$string['short_report_title'] = 'Сводный отчет по статистике';
$string['basic_report_title'] = 'Базовый отчет по статистике';
$string['reportchoose_form_header'] = 'Получение отчета';
$string['reportchoose_form_select_report_main'] = 'Базовый отчет';
$string['reportchoose_form_select_report_shortstatistic'] = 'Сводная статистика по прохождению';
$string['reportchoose_form_select_report_fullstatistic'] = 'Детальная статистика по прохождению';
$string['reportchoose_form_select_report_title'] = 'Тип отчета';
$string['reportchoose_form_select_group_field'] = 'Сгруппировать по';
$string['reportchoose_form_submit'] = 'Сформировать отчет';
$string['reportchoose_form_export_format_pdf'] = 'PDF';
$string['reportchoose_form_export_format_xls'] = 'Excel';
$string['reportchoose_form_export_format_html'] = 'HTML';

// Поля отчета
$string['report_scorm_header_finishtime']='Дата';
$string['report_scorm_header_material']='Материал';
$string['report_scorm_header_username']='Пользователь';
$string['report_scorm_header_organization']='Организация';
$string['report_scorm_header_group']='Группа';
$string['report_scorm_header_quizresult']='Баллы';
$string['report_scorm_header_quizstatus']='Статус';
$string['report_scorm_header_quizstatus_pass']='Сдал';
$string['report_scorm_header_quizstatus_fail']='Не сдал';
$string['report_scorm_header_progress']='Просмотрено';
$string['report_scorm_header_totaltime']='Потрачено';
$string['report_scorm_header_department'] = 'Отдел';
$string['report_scorm_header_city'] = 'Город';

$string['full_report_header_username']='Логин';
$string['full_report_header_email']='Email';
$string['full_report_header_lastname']='Фамилия';
$string['full_report_header_firstname']='Имя';
$string['full_report_header_coursename']='Курс';
$string['full_report_header_passstatus']='Статус прохождения';
$string['full_report_header_passpersent']='Процент прохождения';
$string['full_report_header_city']='Город';
$string['full_report_header_passstatus_pass']='Сдал';
$string['full_report_header_passstatus_fail']='Не сдал';

$string['short_report_header_city']='Город';
$string['short_report_header_department'] = 'Отдел';
$string['short_report_header_course']='Курс';
$string['short_report_header_passpersent']='Процент сдавших';
