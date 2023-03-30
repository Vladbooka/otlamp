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
 * Сводка по курсам. Главная страница отчета
 *
 * @package    report_ot_courseoverview
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace report_ot_courseoverview\main;

use report_ot_courseoverview\report_helper;
use context_system;
use html_table;
use html_writer;
use moodle_url;

require_once('../../config.php');
require_once($CFG->dirroot.'/lib/statslib.php');
require_once($CFG->libdir.'/adminlib.php');
require_once($CFG->dirroot.'/report/ot_courseoverview/locallib.php');

$report = optional_param('report', STATS_REPORT_ACTIVE_COURSES, PARAM_INT);
$time = optional_param('time', 0, PARAM_INT);
$numcourses = optional_param('numcourses', 20, PARAM_INT);

// Тип экспорта (Пока только XLS)
$export_type = optional_param('export', '', PARAM_RAW_TRIMMED);

// HTML код
$html = '';

if ( empty($CFG->enablestats) )
{
    if ( has_capability('moodle/site:config', context_system::instance()) )
    {
        redirect("$CFG->wwwroot/$CFG->admin/search.php?query=enablestats", get_string('mustenablestats', 'admin'), 3);
    } else
    {
        print_error('statsdisable');
    }
}

admin_externalpage_setup('reportot_courseoverview', '', null, '', ['pagelayout' => 'report']);

$course = get_site();
stats_check_uptodate($course->id);

$strreports = get_string('reports');
$strcourseoverview = get_string('courseoverview');

$reportoptions = stats_get_report_options($course->id, STATS_MODE_RANKED);

$earliestday = $DB->get_field_sql('SELECT MIN(timeend) FROM {stats_daily}');
$earliestweek = $DB->get_field_sql('SELECT MIN(timeend) FROM {stats_weekly}');
$earliestmonth = $DB->get_field_sql('SELECT MIN(timeend) FROM {stats_monthly}');

if (empty($earliestday)) $earliestday = time();
if (empty($earliestweek)) $earliestweek = time();
if (empty($earliestmonth)) $earliestmonth = time();

$now = stats_get_base_daily();
$lastweekend = stats_get_base_weekly();
$lastmonthend = stats_get_base_monthly();

$timeoptions = stats_get_time_options($now,$lastweekend,$lastmonthend,$earliestday,$earliestweek,$earliestmonth);

if ( empty($timeoptions) )
{
    print_error('nostatstodisplay', 'error', $CFG->wwwroot.'/course/view.php?id='.$course->id);
}

$html .= html_writer::start_tag('form', ['action' => 'index.php', 'method' => 'post']);
$html .= html_writer::start_tag('div');

$table = new html_table();
$table->align = ['left','left','left','left','left','left'];

$reporttypemenu = html_writer::label(get_string('statsreporttype'), 'menureport', false, ['class' => 'accesshide']);
$reporttypemenu .= html_writer::select($reportoptions, 'report', $report, false);
$timeoptionsmenu = html_writer::label(get_string('time'), 'menutime', false, ['class' => 'accesshide']);
$timeoptionsmenu .= html_writer::select($timeoptions, 'time', $time, false);

$table->data[] = [
    get_string('statsreporttype'),
    $reporttypemenu,
    get_string('statstimeperiod'),
    $timeoptionsmenu,
    html_writer::label(
            get_string('numberofcourses'),
            'numcourses',
            false,
            ['class' => 'accesshide']
            )
    . html_writer::empty_tag(
            'input',
            ['type' => 'text', 'id' => 'numcourses', 'name' => 'numcourses', 'size' => '4', 'maxlength' => '4', 'value' => $numcourses]
            ),
    html_writer::empty_tag(
            'input',
            ['type' => 'submit', 'value' => get_string('view')]
            )
    . html_writer::empty_tag(
            'input',
            ['name' => 'export', 'type' => 'submit', 'value' => get_string('export_xls', 'report_ot_courseoverview')]
            )
];

$html .= html_writer::table($table);
$html .= html_writer::end_tag('div');
$html .= html_writer::end_tag('form');

$html .= $OUTPUT->heading($reportoptions[$report]);

if ( ! empty($report) && ! empty($time) )
{
    $param = stats_get_parameters($time, $report, SITEID, STATS_MODE_RANKED);
    if ( ! empty($param->sql) )
    {
        $sql = $param->sql;
    } else
    {
        $sql = "SELECT courseid,".$param->fields."
                  FROM {".'stats_'.$param->table."}
                 WHERE timeend >= $param->timeafter AND stattype = 'activity' AND roleid = 0
              GROUP BY courseid
                       $param->extras
              ORDER BY $param->orderby";
    }
    
    $courses = $DB->get_records_sql($sql, $param->params, 0, $numcourses);
    
    if ( empty($courses) )
    {
        $html .= $OUTPUT->notification(get_string('statsnodata'));

    } else
    {
        ob_start();
        report_ot_courseoverview_print_chart($report, $time, $numcourses);
        $html .= ob_get_clean();
//         // Ссылка на получение графа
//         $img_src = new moodle_url(
//                 '/report/ot_courseoverview/reportsgraph.php',
//                 [
//                     'time' => $time,
//                     'report' => $report,
//                     'numcourses' => $numcourses
//                 ]
//                 );
        
//         $img = html_writer::img($img_src->out(false), get_string('courseoverviewgraph'));
//         $html .= html_writer::div($img, 'graph');

        $table = new html_table();
        $table->align = ['left', 'center', 'center', 'center', 'center', 'center', 'center', 'center'];
        
        // Заголовки таблицы
        $table->head = [
            get_string('course_fullname', 'report_ot_courseoverview'),
            get_string('course_admins', 'report_ot_courseoverview'),
            get_string('course_id', 'report_ot_courseoverview'),
            get_string('course_groups', 'report_ot_courseoverview'),
            get_string('course_category', 'report_ot_courseoverview'),
            $param->line1
            
        ];
        if ( ! empty($param->line2) )
        {
            $table->head[] = $param->line2;
        }
        if ( ! empty($param->line3) )
        {
            $table->head[] = $param->line3;
        }
        
        foreach  ($courses as $c)
        {
            $a = [];
            // Название курса
            $course_link = new moodle_url('/course/view.php', ['id' => $c->courseid]);
            $a[] = html_writer::link(
                    $course_link->out(false),
                    $DB->get_field('course', 'fullname', ['id' => $c->courseid])
                    );

            // Администраторы курса
            $a[] = report_helper::get_course_administrators($c->courseid);
            
            // Идентификатор курса
            $a[] = $c->courseid;
            
            // Глобальные группы
            $a[] = report_helper::get_course_cohorts($c->courseid);
            
            // Категория курса
            $a[] = report_helper::get_course_categories($c->courseid);
            
            // Доп поля из стандартного отчета
            $a[] = $c->line1;
            if ( isset($c->line2) )
            {
                $a[] = $c->line2;
            }
            if ( isset($c->line3) )
            {
                $a[] = round($c->line3, 2);
            }
            $table->data[] = $a;
        }
        $html .=  html_writer::table($table);
    }
}

// Обработчик экспорта
report_helper::export($export_type, $table);

// Печать хидера
echo $OUTPUT->header();

// Отображение информации
echo $html;

// Печать футера
echo $OUTPUT->footer();


