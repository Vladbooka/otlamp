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
 * @package    availability_otcomparison
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$string['pluginname'] = 'Ограничение доступа по результату сравнения поля профиля с датой или числом';
$string['description'] = 'Допускает пользователей согласно результату сравнения поля профиля с указанным значением';
$string['title'] = 'Сравнение дат и чисел в профиле пользователя';

$string['choose_source'] = 'Выберите поле профиля...';
$string['choose_preprocessor'] = 'Выберите вариант сравнения...';
$string['choose_operator'] = 'Выберите оператор сравнения...';

$string['preprocessor_date'] = 'Дата';
$string['preprocessor_days'] = 'Полных дней с указанной до текущей даты';
$string['preprocessor_int'] = 'Сравнение целого числа';

$string['operator_less_than'] = '< (меньше)';
$string['operator_more_than'] = '> (больше)';
$string['operator_equal_to'] = '== (равно)';
$string['operator_not_equal_to'] = '!= (не равно)';
$string['operator_less_than_or_equal'] = '<= (меньше или равно)';
$string['operator_more_than_or_equal'] = '>= (больше или равно)';

$string['date_example'] = 'Примеры форматов даты: {$a}';
$string['days_explanation'] = 'Допускается отрицательное значение при вводе количества дней. <br/>Такой подход может быть использован, когда дата в профиле должна быть больше текущей.';

$string['retrieve_value_failed'] = 'неизвестно (не удалось получить значение)';
$string['retrieve_source_failed'] = 'поле "$a" (не удалось получить)';
$string['invalid_date'] = '? (не верно указана дата)';
$string['invalid_int'] = '? (не верно указано целое число)';
$string['error_selectsource'] = 'Необходимо указать поле профиля (источник сравнения).';
$string['error_selectoperator'] = 'Необходимо выбрать оператор сравнения.';
$string['error_selectpreprocessor'] = 'Необходимо выбрать вариант сравнения.';
$string['error_fillvalue'] = 'Необходимо заполнить значение.';
$string['error_invalidfilledvalue'] = 'Значение указано не верно.';

$string['description_date'] = '"{$a->source}" {$a->operator} {$a->amount}';
$string['description_days'] = 'Количество дней между "{$a->source}" и текущим моментом {$a->operator} {$a->amount}';
$string['description_int'] = '"{$a->source}" {$a->operator} {$a->amount}';

$string['timecreated'] = 'Дата создания профиля';
$string['firstaccess'] = 'Дата первой активности';
$string['lastlogin'] = 'Дата предыдущей авторизации';
$string['currentlogin'] = 'Дата текущей авторизации';
$string['lastaccess'] = 'Дата последней активности';
$string['timemodified'] = 'Дата последнего редактирования профиля';