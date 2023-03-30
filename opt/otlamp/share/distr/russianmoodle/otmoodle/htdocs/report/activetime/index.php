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
 * 
 *
 * @package    report_activetime
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once('../../config.php');
require_once($CFG->dirroot . '/report/activetime/classes/renderable.php');
require_once($CFG->dirroot . '/report/activetime/form.php');
require_once($CFG->libdir . '/adminlib.php');

// course id
$courseid = optional_param('id', 0, PARAM_INT);

if( empty($courseid) ) 
{
    $site = get_site();
    $courseid = $site->id;
}

$url = new moodle_url("/report/activetime/index.php", ['id' => $courseid]);

$PAGE->set_url($url);
$PAGE->set_pagelayout('report');

// Get course details.
$course = null;
if ($courseid) {
    $course = $DB->get_record('course', array('id' => $courseid), '*', MUST_EXIST);
    require_login($course);
    $context = context_course::instance($course->id);
    // Проверяем права
    require_capability('report/activetime:view', $context);
} else {
    require_login();
    $context = context_system::instance();
    $PAGE->set_context($context);
    // Проверяем права
    require_capability('report/activetime:viewall', $context);
}

if( empty($course) || ($course->id == $SITE->id) ) 
{
    $PAGE->set_title($SITE->shortname .': '. get_string('pluginname', 'report_activetime'));
} else {
    $PAGE->set_title($course->shortname .': '. get_string('pluginname', 'report_activetime'));
    $PAGE->set_heading($course->fullname);
}

$output = $PAGE->get_renderer('report_activetime');
//Создаем объект отчета
$reportactivetime = new report_activetime_renderable($course);
// Передаем объект отчета в форму фильтрации
$customdata = [
    'report' => $reportactivetime
];
// Создаем объект формы фильтрации отчета
$filter = new report_activetime_filter_form($url, $customdata);
// Запускаем обработчик формы фильтрации отчета
$filter->process();

echo $output->header();
echo $output->heading(get_string('pluginname', 'report_activetime'));
// Отображаем форму фильтрации отчета
$filter->display();
// Отображаем отчет в html формате
echo $output->render($reportactivetime);
echo $output->footer();