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
 * Файл языковых строк
 *
 * @package    tool
 * @subpackage gradesintegrity
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$string['pluginname'] = 'Инструмент восстановления целостности таблиц оценок';
$string['pageheader'] = 'Восстановление целостности таблиц оценок';
$string['form_description'] = 'Утилита позволяет восстановить целостность базы данных в части таблиц, связанных с оценками.
                               Для запуска нажмите на кнопку ниже.';
$string['form_doit'] = 'Выполнить восстановление';
$string['gi_deleted_successfully'] = 'Запись из таблицы grade_items с id={$a->id} успешно удалена.';
$string['gi_deleting_failed'] = 'Не удалось удалить запись из таблицы grade_items с id={$a->id}.';
$string['gg_deleted_successfully'] = 'Запись из таблицы grade_grades с id={$a->id} успешно удалена.';
$string['not_found_records_part_one'] = 'Не найдено записей в таблице grade_items, несогласованных с таблицей course_modules';
$string['not_found_records_part_two'] = 'Не найдено записей в таблице grade_grades, несогласованных с таблицей grade_items';