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
 * Плагин "Надо проверить". Перехватываемые события.
 *
 * @package    block
 * @subpackage notgraded
 * @category   event
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$observers = [
    // Задание. Работа представлена.
    [
        'eventname' => '\mod_assign\event\assessable_submitted',
        'callback'  => '\block_notgraded\observer::mod_assign_assessable_submitted',
    ],
    // Задание. Представленный ответ был оценен
    [
        'eventname' => '\mod_assign\event\submission_graded',
        'callback'  => '\block_notgraded\observer::mod_assign_submission_graded',
    ],
    // Тест. Попытка завершена и отправлена на оценку
    [
        'eventname' => '\mod_quiz\event\attempt_submitted',
        'callback'  => '\block_notgraded\observer::mod_quiz_attempt_submitted',
    ],
    // Тест. Вопрос оценен вручную
    [
        'eventname' => '\mod_quiz\event\question_manually_graded',
        'callback'  => '\block_notgraded\observer::mod_quiz_question_manually_graded',
    ],
    // Ядро. Роль назначена
    [
        'eventname' => '\core\event\role_assigned',
        'callback'  => '\block_notgraded\observer::core_role_assigned'
    ],
    // Ядро. Назначение роли снято
    [
        'eventname' => '\core\event\role_unassigned',
        'callback'  => '\block_notgraded\observer::core_role_unassigned'
    ],
];