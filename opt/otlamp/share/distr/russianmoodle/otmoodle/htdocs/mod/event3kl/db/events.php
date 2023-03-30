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
 * Перехватываемые события.
 *
 * @package    mod
 * @subpackage event3kl
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$observers = [
    [
        // Модуль курса создан
        'eventname' => '\core\event\course_module_created',
        'callback'  => '\mod_event3kl\event3kl::handle_course_module_created',
    ],
    [
        // Модуль курса обновлен
        'eventname' => '\core\event\course_module_updated',
        'callback'  => '\mod_event3kl\event3kl::handle_course_module_updated',
    ],
    [
        // Член группы добавлен
        'eventname' => '\core\event\group_member_added',
        'callback'  => '\mod_event3kl\observer::group_member_added',
    ],
    [
        // Пользователь убран из группы
        'eventname' => '\core\event\group_member_removed',
        'callback'  => '\mod_event3kl\observer::group_member_removed',
    ],
    [
        // Роль назначена
        'eventname' => '\core\event\role_assigned',
        'callback'  => '\mod_event3kl\observer::role_assigned',
    ],
    [
        // Назначение роли снято
        'eventname' => '\core\event\role_unassigned',
        'callback'  => '\mod_event3kl\observer::role_unassigned',
    ],
    [
        // подписка на курс удалена
        'eventname' => '\core\event\user_enrolment_deleted',
        'callback'  => '\mod_event3kl\observer::user_enrolment_deleted',
    ],
    [
        // локальная группа удалена
        'eventname' => '\core\event\group_deleted',
        'callback'  => '\mod_event3kl\observer::group_deleted',
    ],
    [
        // локальная группа добавлена
        'eventname' => '\core\event\group_created',
        'callback'  => '\mod_event3kl\observer::group_created',
    ],
];
