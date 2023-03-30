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
 * Version information
*
* @package    report_coursesize
* @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
*/

$string['backupsize'] = 'Размер резервной копии';
$string['catsystemuse'] = 'Системные файлы и категории, ипользуемые вне курсов и не являющиеся пользовательскими - {$a}.';
$string['catsystembackupuse'] = 'Системные файлы и категории, ипользуемые резервной копией - {$a}.';
$string['coursesize'] = 'Размер курса';
$string['coursebytes'] = '{$a->bytes} байт использовано курсом {$a->shortname}';
$string['coursebackupbytes'] = '{$a->backupbytes} байт использовано курсом для резервной копии {$a->shortname}';
$string['coursereport'] = 'Сводка типов плагинов - это может быть ниже, чем список основных курсов, и, вероятно, более точный.';
$string['coursesize:view'] = 'Видеть отчет о размере курса';
$string['diskusage'] = 'Используемое дисковое пространство';
$string['nouserfiles'] = 'Пользовательских файлов нет в списке.';
$string['pluginname'] = 'Размер курса';
$string['sizerecorded'] = '(Записано {$a})';
$string['sizepermitted'] = '(Возможно использовать {$a}Мб)';
$string['sitefilesusage'] = 'Отчет об использовании файлов';
$string['totalsitedata'] = 'Общее использование данных: {$a}';
$string['userstopnum'] = 'Пользователи (топ {$a})';
$string['emptycourseshidden'] = 'Курсы, которые не используют файловые хранилища, были исключены из этого отчета.';
$string['coursesize_desc'] = 'Этот отчет содержит только приблизительные значения, если файл использовался несколько раз в течение курса или в нескольких курсах, отчет учитывает каждый экземпляр, хотя Moodle хранит только одну физическую версию на диске.';
$string['sharedusage'] = 'Совместное использование';
$string['coursesummary'] = '(просмотреть статистику)';
$string['sharedusagecourse'] = 'Приблизительно {$a} делится с другими курсами.';
$string['privacy:metadata'] = 'Плагин размера курса не хранит никаких личных данных.';