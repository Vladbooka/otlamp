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
 * Отчет по неопубликованным заданиям. Интерфейс просмотра.
 *
 * @package    report
 * @subpackage notreleased_assignments
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require('../../config.php');
require_once($CFG->libdir.'/adminlib.php');

$url = new moodle_url("/report/notreleased_assignments/index.php");

$PAGE->set_url($url);
$PAGE->set_pagelayout('report');

require_login();
$context = context_system::instance();
$PAGE->set_context($context);

require_capability('report/notreleased_assignments:view', $context);

admin_externalpage_setup('reportnotreleased_assignments');

$PAGE->set_title($SITE->shortname .': '. get_string('pluginname', 'report_notreleased_assignments'));

$html = '';

$html .= get_string('report_description', 'report_notreleased_assignments');

$report_notreleased_assignments = new report_notreleased_assignments_renderable();
$output = $PAGE->get_renderer('report_notreleased_assignments');

$html .= $output->render($report_notreleased_assignments);

echo $output->header();
echo $output->heading(get_string('pluginname', 'report_notreleased_assignments'));

echo $html;

echo $output->footer();
