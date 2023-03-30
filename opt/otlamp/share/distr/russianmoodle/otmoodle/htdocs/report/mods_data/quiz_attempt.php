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
 * Форма с данными по попытке в pdf формате.
 *
 * @package    report_mods_data
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(dirname(realpath(__FILE__)) . '/../../config.php');
require_once($CFG->dirroot . '/report/mods_data/classes/quiz_attempt_report.php');

$id = required_param('id', PARAM_INT);
$format = optional_param('format', 'pdf', PARAM_TEXT);

global $PAGE, $OUTPUT;
$url = new moodle_url("/report/mods_data/quiz_attempt.php", ['id' => $id, 'format' => $format]);

$PAGE->set_url($url);
$PAGE->set_pagelayout('report');

$qareport = new \mods_data\quiz_attempt_report($id, $format);

require_login($qareport->course);
$context = context_course::instance($qareport->course->id);
$PAGE->set_context($context);

require_capability('report/mods_data:view_quiz_attempt_report', $context);

$qareport->make_report();

if( is_null($qareport) )
{
    $html = get_string('attempt_not_found_html', 'report_mods_data');
    
    echo $OUTPUT->header();
    echo $OUTPUT->heading(get_string('pluginname', 'report_mods_data'));
    echo $html;
    echo $OUTPUT->footer();
}

switch($format)
{
    case 'html':
        $html = $qareport->send_html();
        echo $OUTPUT->header();
        echo $OUTPUT->heading(get_string('quiz_attempt_report', 'report_mods_data'));
        echo $html;
        echo $OUTPUT->footer();
        break;
    case 'pdf':
    default:
        $qareport->send_pdf();
        exit();
        break;
}





