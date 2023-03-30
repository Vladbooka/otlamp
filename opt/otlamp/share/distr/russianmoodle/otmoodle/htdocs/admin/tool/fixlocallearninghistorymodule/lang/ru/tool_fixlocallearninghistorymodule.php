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
 * @subpackage fixlocallearninghistorymodule
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$string['pluginname'] = 'Инструмент восстановления отчета "Время, затраченное на изучение курса"';
$string['pageheader'] = 'Инструмент восстановления целостности хранилища отчета "Время, затраченное на изучение курса"';
$string['form_description'] = 'Данный инструмент создает одноразовую задачу на восстановление 
целостности таблицы local_learninghistory_module, которая будет исполнена при следующем запуске планировщика задач.';
$string['form_doit'] = 'Добавить задачу на восстановление';
$string['task_ok'] = 'Задача на восстановление таблицы отчета успешно добавлена.';
$string['task_error'] = 'Добавить задачу на восстановление не удалось';
$string['task_exist'] = 'Задача уже была ранее добавлена в планировщика задач и ожидает исполнения';
