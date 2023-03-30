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
 * Авторизация (регистрация) СЭО 3KL. Перехватываемые события.
 * 
 * @package    auth
 * @subpackage dof
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$observers = [
    // Событие обновления поля
    [
        'eventname' => '\core\event\user_info_field_updated',
        'callback' => '\auth_dof\observer::user_info_field_updated'
    ],
    // Событие добавления поля
    [
        'eventname' => '\core\event\user_info_field_created',
        'callback' => '\auth_dof\observer::user_info_field_created'
    ],
    // Событие удаление поля
    [
        'eventname' => '\core\event\user_info_field_deleted',
        'callback' => '\auth_dof\observer::user_info_field_deleted'
    ]
];