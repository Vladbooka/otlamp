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
 * Блок мессенджера курса. Доступ.
 *
 * @package    block_coursemessage
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$capabilities = [
    // Добавление блока на страницу MY
    'block/coursemessage:myaddinstance' => [
                    'captype' => 'write',
                    'contextlevel' => CONTEXT_SYSTEM,
                    'archetypes' => [
                                    'guest'          => CAP_PREVENT,
                                    'student'        => CAP_PREVENT,
                                    'teacher'        => CAP_PREVENT,
                                    'editingteacher' => CAP_PREVENT,
                                    'coursecreator'  => CAP_PREVENT,
                                    'manager'        => CAP_ALLOW
                    ],
                    'clonepermissionsfrom' => 'moodle/my:manageblocks'
    ],
    // Добавление блока
    'block/coursemessage:addinstance' => [
                    'riskbitmask' => RISK_SPAM | RISK_XSS,
                    'captype' => 'write',
                    'contextlevel' => CONTEXT_BLOCK,
                    'archetypes' => [
                                    'manager' => CAP_ALLOW
                    ],
                    'clonepermissionsfrom' => 'moodle/site:manageblocks'
    ],
    // Отправка сообщений
    'block/coursemessage:send' => [
                    'riskbitmask' => RISK_SPAM | RISK_XSS,
                    'captype' => 'write',
                    'contextlevel' => CONTEXT_COURSE,
                    'archetypes' => [
                                    'guest'          => CAP_PREVENT,
                                    'student'        => CAP_ALLOW,
                                    'teacher'        => CAP_ALLOW,
                                    'editingteacher' => CAP_ALLOW,
                                    'coursecreator'  => CAP_ALLOW,
                                    'manager'        => CAP_ALLOW
                    ]
    ],
];
