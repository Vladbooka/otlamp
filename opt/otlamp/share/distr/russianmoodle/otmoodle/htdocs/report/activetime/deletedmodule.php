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
 * Заглушка для удаленных элементов курса
 *
 * @package    report_activetime
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once('../../config.php');

// course id
$courseid = required_param('courseid', PARAM_INT);

// cm id
$cmid = optional_param('cmid', 0, PARAM_INT);

$url = new moodle_url("/report/activetime/deletedmodule.php", ['cmid' => $cmid, 'courseid' => $courseid]);
$courseurl = new moodle_url('/report/activetime/index.php', ['id' => $courseid]);

$PAGE->set_url($url);
$PAGE->set_pagelayout('report');

require_login($courseid);

$PAGE->set_context(context_course::instance($courseid));

$PAGE->set_title(get_string('cm_was_deleted', 'report_activetime'));
$PAGE->set_heading(get_string('cm_was_deleted', 'report_activetime'));

$output = $PAGE->get_renderer('report_activetime');

echo $output->header();
echo $output->heading(get_string('cm_was_deleted', 'report_activetime'));
echo html_writer::link($courseurl, get_string('back_to_report', 'report_activetime'));
echo $output->footer();