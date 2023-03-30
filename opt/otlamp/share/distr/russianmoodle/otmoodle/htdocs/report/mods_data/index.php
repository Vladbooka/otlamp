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
 * Displays different views of the logs.
 *
 * @package    report_log
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require('../../config.php');
require_once($CFG->dirroot.'/course/lib.php');
require_once($CFG->dirroot.'/report/mods_data/locallib.php');
require_once($CFG->libdir.'/adminlib.php');
require_once($CFG->dirroot.'/lib/tablelib.php');

$courseid = optional_param('id', 0, PARAM_INT);

if( empty($courseid) )
{
    $site = get_site();
    $courseid = $site->id;
}

$url = new moodle_url("/report/mods_data/index.php", ['id' => $courseid]);

$PAGE->set_url($url);
$PAGE->set_pagelayout('report');

// Get course details.
$course = null;
if ($courseid) {
    $course = $DB->get_record('course', array('id' => $courseid), '*', MUST_EXIST);
    require_login($course);
    $context = context_course::instance($course->id);
} else {
    require_login();
    $context = context_system::instance();
    $PAGE->set_context($context);
}

report_mods_data_require_any_capability(['report/mods_data:view', 'report/mods_data:view_self_report_data'], $context);

$html = '';

if (empty($course) || ($course->id == $SITE->id)) {
    $PAGE->set_title($SITE->shortname .': '. get_string('pluginname', 'report_mods_data'));
} else {
    $PAGE->set_title($course->shortname .': '. get_string('pluginname', 'report_mods_data'));
    $PAGE->set_heading($course->fullname);
}

$reportmods_data = new report_mods_data_renderable($course);
$output = $PAGE->get_renderer('report_mods_data');

$html .= $output->render($reportmods_data);

echo $output->header();
echo $output->heading(get_string('pluginname', 'report_mods_data'));

echo $html;

echo $output->footer();
