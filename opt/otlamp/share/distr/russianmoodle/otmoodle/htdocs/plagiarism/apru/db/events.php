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
 * Плагин определения заимствований "Антиплагиат". Перехватываемые события.
 *
 * @package    plagiarism
 * @subpackage apru
 * @category   event
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$observers = [
// Глобальные события курса
    // Событие очистки курса
    [
        'eventname' => '\core\event\course_reset_ended',
        'callback'  => '\plagiarism_apru\observer::course_reset',
    ],
// События модуля курса "Задание"
    // Событие блокировки задания преподавателем
    [
        'eventname' => '\mod_assign\event\submission_locked',
        'callback'  => '\plagiarism_apru\observer::assignment_locked',
    ],
    // Событие отправки|блокировки задания студентом(В зависимости от настройки элемента)
    [
        'eventname' => '\mod_assign\event\assessable_submitted',
        'callback'  => '\plagiarism_apru\observer::assessable_submitted',
    ],
    // Событие отправки|блокировки задания студентом(В зависимости от настройки элемента)
    [
        'eventname' => '\assignsubmission_file\event\assessable_uploaded',
        'callback'  => '\plagiarism_apru\observer::assessable_uploaded',
    ]
];