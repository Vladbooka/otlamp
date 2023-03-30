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
 * Способ авторизации автоматически связывающий кураторов и их подопечных на основе данных из пользовательских полей профиля
 *
 * @package    auth_enrolmentor
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$string['auth_enrolmentordescription'] = 'Этот способ авторизации автоматически связывает кураторов и их подопечных на основе данных из пользовательских полей профиля';
$string['pluginname'] = 'Автоматическая подписка кураторов';

$string['enrolmentor_disabled'] = 'Плагин "'. $string['pluginname'] . '" отключен. Переназначение ролей не выполнено.';
$string['enrolmentor_settingrole'] = 'Выберите назначаемую роль';
$string['enrolmentor_settingrolehelp'] = 'Выберите из выпадающего списка роль, которая будет назначена куратору в контексте пользователя';
$string['enrolmentor_settingcompare'] = 'Идентификатором куратора следует считать';
$string['enrolmentor_settingcomparehelp'] = 'Выберите поле для идентификации куратора, которое потребуется прописывать в поле профиля его подопечного для автоматического связывания';
$string['enrolmentor_settingprofile_field'] = 'Поле профиля пользователя';
$string['enrolmentor_settingprofile_fieldhelp'] = 'Выберите поле профиля пользователя, в котором будут указываться его кураторы. Можно указывать несколько значений через запятую';
$string['enrolmentor_settingprofile_field_heading'] = 'Не найдено ни одного поля профиля пользователя';
$string['enrolmentor_settingdelimeter'] = 'Разделитель';
$string['enrolmentor_settingdelimeter_desc'] = 'В поле можно указать несколько идентификаторов кураторов одновременно, идентификаторы должны быть разделены выбранным символом разделителя.';
$string['enrolmentor_settingupdatementors'] = 'Обновить кураторов';
$string['enrolmentor_settingupdatementors_desc'] = 'Проставить галочку, чтобы принудительно обновить всех кураторов для неудалённых и незаблокированных пользователей. После сохранения проставленная галочка убирается, а задача на обновление ставится в очередь.';
$string['updatementors_task_title'] = 'Обновление всех кураторов для всех пользователей';
$string['updatementors_task_added'] = 'Задача обновления кураторов добавлена';
$string['updatementors_task_added_paramupdated'] = 'Задача обновления кураторов добавлена в связи с изменением настроек';
