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
 * Языковые строки
 *
 * @package    availability_assignfeedback
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$string['pluginname'] = 'Ограничение доступа по наличию отзыва на задание';
$string['title'] = 'Отзыв к заданию';
$string['description'] = 'Использует наличие/отсутствие отзыва в виде комментария к заданию для управления доступом';

$string['inassign'] = 'В задании ';
$string['needfeedback'] = ' пользователем получен отзыв ';
$string['chooseassign'] = 'Выберите задание...';
$string['chooseassignfeedback'] = 'Выберите тип отзыва...';
$string['error_selectcmid'] = 'Необходимо указать задание в ограничении доступа по наличию отзыва к заданию.';
$string['error_selectfeedbackcode'] = 'Необходимо указать тип отзыва в ограничении доступа по наличию отзыва к заданию.';

$string['unknown_assign'] = '[ Задание не найдено ]';
$string['unknown_feedbacktype'] = '[ Тип отзыва не найден в задании ]';
$string['requires_feedback'] = 'Для задания "{$a->assignname}" требуется наличие отзыва "{$a->feedbacktype}"';
$string['no_feedback_required'] = 'Для задания "{$a->assignname}" не должно быть отзывов "{$a->feedbacktype}"';