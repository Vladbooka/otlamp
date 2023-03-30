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
 * Language strings.
 *
 * @package    availability_activetime
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$string['pluginname'] = 'Ограничение доступа по времени изучения курса';
$string['description'] = 'Управление доступом в зависимости от продолжительности времени изучения курса';
$string['title'] = 'Время изучения курса';

$string['option_min'] = 'должно быть &#x2265';
$string['label_min'] = 'Минимальное время, затраченное на изучение курса';
$string['option_max'] = 'должно быть <';
$string['label_max'] = 'Максимальное время, затраченное на изучение курса';
$string['requires_any'] = 'Вы должны хотя бы раз войти в курс';
$string['requires_max'] = 'Вы должны затратить на изучение курса меньше {$a}';
$string['requires_min'] = 'Вы должны провести за изучением курса больше {$a}';
$string['requires_notany'] = 'Вы не должны входить в курс';
$string['requires_notgeneral'] = 'Вы выполнили условия по затратам времени на изучение курса';
$string['requires_range'] = 'Вы не выполнили условия по затратам времени на изучение курса';
$string['error_backwardrange'] = 'Максимальное значение должно быть больше минимального';
$string['error_invalidnumber'] = 'Указан не верный формат данных';