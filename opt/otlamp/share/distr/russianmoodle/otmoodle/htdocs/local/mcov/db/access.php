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
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle. If not, see <http://www.gnu.org/licenses/>.


/**
 * Настраиваемые поля. Доступ
 *
 * @package    local_mcov
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$capabilities = [

    // Право на редактирование значений доп.полей глобальных групп
    'local/mcov:edit_cohorts_cov' => [
        'captype' => 'write',
        'contextlevel' => CONTEXT_SYSTEM,
        'archetypes' => [
            'manager' => CAP_ALLOW,
            'editingteacher' => CAP_PREVENT,
            'teacher' => CAP_PREVENT,
            'student' => CAP_PREVENT,
            'user' => CAP_PREVENT,
            'guest' => CAP_PREVENT,
        ]
    ],

    // Право на редактирование значений доп.полей пользователей
    'local/mcov:edit_users_cov' => [
        'captype' => 'write',
        'contextlevel' => CONTEXT_SYSTEM,
        'archetypes' => [
            'manager' => CAP_ALLOW,
            'editingteacher' => CAP_PREVENT,
            'teacher' => CAP_PREVENT,
            'student' => CAP_PREVENT,
            'user' => CAP_PREVENT,
            'guest' => CAP_PREVENT,
        ]
    ],

    // Право на редактирование значений доп.полей своего профиля
    'local/mcov:edit_users_cov_my' => [
        'captype' => 'write',
        'contextlevel' => CONTEXT_SYSTEM,
        'archetypes' => [
            'manager' => CAP_ALLOW,
            'editingteacher' => CAP_PREVENT,
            'teacher' => CAP_PREVENT,
            'student' => CAP_PREVENT,
            'user' => CAP_PREVENT,
            'guest' => CAP_PREVENT,
        ]
    ],
    
    // Право на редактирование значений доп.полей локальных групп
    'local/mcov:edit_groups_cov' => [
        'captype' => 'write',
        'contextlevel' => CONTEXT_COURSE,
        'archetypes' => [
            'manager' => CAP_ALLOW,
            'editingteacher' => CAP_ALLOW,
            'teacher' => CAP_PREVENT,
            'student' => CAP_PREVENT,
            'user' => CAP_PREVENT,
            'guest' => CAP_PREVENT,
        ],
        'clonepermissionsfrom' => 'moodle/course:managegroups'
    ]
    
];