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
 * Плагин определения заимствований Руконтекст. Связь событий и методов.
 *
 * @package    plagiarism
 * @subpackage rucont
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$observers = [
    [// Общее событие при добавлении ответа на элемент курса "Задание"
        'eventname' => '\assignment_upload\event\assessable_uploaded',
        'callback'  => '\plagiarism_rucont\observer::assignment_uploaded'
    ],
    [// Событие сохранения ответа для оценки преподавателем
        'eventname' => '\assignment_upload\event\assessable_submitted',
        'callback'  => '\plagiarism_rucont\observer::assessable_submitted',
    ],
    [// Событие добавления текста в виде ответа на элемент курса "Задание"
        'eventname' => '\assignsubmission_onlinetext\event\assessable_uploaded',
        'callback'  => '\plagiarism_rucont\observer::assignsubmission_text_uploaded',
    ],
    [// Событие добавления Файла в виде ответа на элемент курса "Задание"
        'eventname' => '\assignsubmission_file\event\assessable_uploaded',
        'callback'  => '\plagiarism_rucont\observer::assignsubmission_file_uploaded',
    ],
    [// Событие удаления курса
        'eventname' => '\core\event\course_reset_ended',
        'callback'  => '\plagiarism_rucont\observer::course_reset',
    ]
];