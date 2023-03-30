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
 * Витрина курсов. Объявление перехватываемых событий.
 *
 * @package    local
 * @subpackage crw
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


defined('MOODLE_INTERNAL') || die();

$observers = [
                // Событие создания курса
                [
                                'eventname' => '\core\event\course_created',
                                'callback' => '\local_crw\observer::course_created',
                ],
                // Событие обновления курса
                [
                                'eventname' => '\core\event\course_updated',
                                'callback' => '\local_crw\observer::course_updated',
                ],
                // Событие удаления курса
                [
                                'eventname' => '\core\event\course_deleted',
                                'callback' => '\local_crw\observer::course_deleted',
                ]
];
