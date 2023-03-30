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
 * Отчет о пользовательских данных. Главная страница плагина.
 *
 * @package    report
 * @subpackage userdata
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require ('../../config.php');
require_once ($CFG->libdir . '/adminlib.php');

// Требуется авторизация
require_login();

// Получение ID курса для фильтрации пользователей
$courseid = optional_param('courseid', 0, PARAM_INT);

if ( $courseid > 0 )
{
    // Установка контекста
    $context = context_course::instance($courseid);
    require_capability('report/userdata:view_enrolled', $context);
} else
{
    // Установка контекста
    $context = context_system::instance();
    require_capability('report/userdata:view', $context);
}

$addvars = [];
$heading = get_string('pluginname', 'report_userdata');
if( ! empty($courseid) )
{
    $course = get_course($courseid);
    if( ! empty($course) )
    {
        $addvars['courseid'] = $courseid;
        $heading = get_string('coursereport', 'report_userdata', $course->fullname);
    }
}

// Настройки старницы
$PAGE->set_pagelayout('report');
$PAGE->set_context($context);
$PAGE->set_url('/report/userdata/index.php', ['courseid' => $courseid]);
$PAGE->set_title($heading);
$PAGE->set_heading($heading);
$PAGE->set_pagelayout('report');

// Шапка страницы
echo $OUTPUT->header();

$html = '';
// Ссылка на формирование отчета в формате pdf
$pdflink = new moodle_url('/report/userdata/pdf.php',$addvars);
$html .= html_writer::link(
    $pdflink,
    get_string('report_userdata_export_to_pdf','report_userdata'),
    ['class' => 'btn btn-primary button']
) . PHP_EOL;

// Ссылка на формирование отчета в формате xls
$xlslink = new moodle_url('/report/userdata/xls.php',$addvars);
$html .= html_writer::link(
    $xlslink,
    get_string('report_userdata_export_to_xls','report_userdata'),
    ['class' => 'btn btn-primary button']
) . PHP_EOL;
echo html_writer::div($html);

// Футер страницы
echo $OUTPUT->footer();