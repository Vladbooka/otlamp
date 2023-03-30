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
 * Модуль Взаимная оценка. Перехватываемые события.
 *
 * @package    mod
 * @subpackage otmutualassessment
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$observers = [
    // Событие обновления оценок пользователем
    [
        'eventname' => '\core\event\group_member_added',
        'callback'  => '\mod_otmutualassessment\observer::group_member_added',
    ],
    [
        'eventname' => '\core\event\group_member_removed',
        'callback'  => '\mod_otmutualassessment\observer::group_member_removed',
    ],
    [
        'eventname' => '\core\event\role_assigned',
        'callback'  => '\mod_otmutualassessment\observer::role_assigned',
    ],
    [
        'eventname' => '\core\event\role_unassigned',
        'callback'  => '\mod_otmutualassessment\observer::role_unassigned',
    ],
    [
        'eventname' => '\core\event\user_enrolment_deleted',
        'callback'  => '\mod_otmutualassessment\observer::user_enrolment_deleted',
    ],
];