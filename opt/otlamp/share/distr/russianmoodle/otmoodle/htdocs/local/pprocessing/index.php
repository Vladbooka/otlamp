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
 * Главная страница плагина
 *
 * @package    local_pprocessing
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . '/../../config.php');

// include_once $CFG->libdir.'/adminlib.php';
// admin_externalpage_setup('local_pprocessing');

$PAGE->set_url('/local/pprocessing/index.php');
$PAGE->set_course($SITE);

echo $OUTPUT->header();

$params = [
    'status' => 'active',
    'finalgrade' => 100.00000,
    'rawgrade' => 100.00000,
    'rawgrademin' => 0.00000,
    'rawgrademax' => 100.00000,
    'rawscaleid' => null,
    'scalesnapshot' => null,
    'itemtype' => 'mod',
    'itemmodule' => 'quiz',
    'iteminstance' => 85,
    'cmid' => 973
];
// Формирование события об изменении истории оценки
$eventdata = [
    'objectid' => 335,
    'courseid' => 81,
    'contextid' => 2305,
    'relateduserid' => 115,
    'other' => $params
];
$event = \local_learninghistory\event\cm_grade_history_updated::create($eventdata);
$event->trigger();

echo $OUTPUT->footer();