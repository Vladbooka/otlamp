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
 * Сводка по пользователям. Главная страница отчета
 *
 * @package    report_ot_usersoverview
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace report_ot_usersoverview\main;

use report_ot_usersoverview\report_helper;
use context_system;
use html_table;
use html_writer;

require_once('../../config.php');
require_once($CFG->libdir.'/adminlib.php');

global $OUTPUT, $SESSION;

$page = optional_param('page', 0, PARAM_INT);
$perpage  = optional_param('perpage', 50, PARAM_INT);
$institution = optional_param('institution', 'all', PARAM_RAW_TRIMMED);
$department = optional_param('department', '', PARAM_RAW_TRIMMED);
$exportrequired = optional_param('export_submit', false, PARAM_BOOL);
$switchshowgroup = optional_param('switchshowgroups', false, PARAM_BOOL);
$exportformat = $exportrequired ? optional_param('export', '', PARAM_RAW_TRIMMED) : '';

if ( ! property_exists($SESSION, 'report_ot_usersoverview__showgroups') )
{
    $SESSION->report_ot_usersoverview__showgroups = false;
}
if ( $switchshowgroup )
{
    $SESSION->report_ot_usersoverview__showgroups = !$SESSION->report_ot_usersoverview__showgroups;
}

// получение контекста системы
$syscontext = context_system::instance();

// проверка прав доступа
require_capability('report/ot_usersoverview:view', $syscontext);
require_capability('report/ot_usersoverview:view_detail', $syscontext);

admin_externalpage_setup('report_ot_usersoverview', '', null, '', ['pagelayout' => 'report']);

// HTML код
$html = '';

$table = new html_table();
$table->align = ['left','left','left','left','left','left'];

$select_options = report_helper::get_all_existing_institutions();

$html_form = '';
$html_form .= html_writer::start_tag('form',
        [
            'action' => 'users.php',
            'method' => 'post'
        ]);

$select_options = report_helper::get_export_types_select();
$html_form_download = html_writer::div(get_string('download_label', 'report_ot_usersoverview'), 'ot_usersoverview_label');
$html_form_download .= html_writer::select($select_options, 'export', '', '');
$html_form_download .= html_writer::empty_tag('input', ['type' => 'hidden', 'name' => 'institution', 'value' => $institution]);
$html_form_download .= html_writer::empty_tag('input', ['type' => 'hidden', 'name' => 'department', 'value' => $department]);
$html_form_download .= html_writer::empty_tag('input',
        [
            'name' => 'export_submit',
            'type' => 'submit',
            'value' => get_string('export', 'report_ot_usersoverview')
        ]);
$html_form .= html_writer::div($html_form_download, 'ot_usersoverview_downloader');

$html_form .= html_writer::end_tag('form');
$html .= html_writer::link(
        new \moodle_url('/report/ot_usersoverview/index.php', ['field' => $institution]), 
        get_string('goback', 'report_ot_usersoverview'),
        ['class' => 'btn button report_ot_usersoverview_button']);
$html .= html_writer::link(
        new \moodle_url('/report/ot_usersoverview/users.php', ['institution' => $institution, 'switchshowgroups' => true]), 
        $SESSION->report_ot_usersoverview__showgroups ? get_string('group_off', 'report_ot_usersoverview') : get_string('group_on', 'report_ot_usersoverview'),
        ['class' => 'btn button report_ot_usersoverview_button']);
$html .= $html_form;

$table = new html_table();
$table->align = ['left', 'left', 'left'];
$table->attributes = ['class' => 'ot_usersoverview_table'];

// Получение данных
$data = \report_ot_usersoverview\report_helper::get_data_detail($institution, $department);

$table->head = [
    get_string('firstname', 'report_ot_usersoverview'),
    get_string('lastname', 'report_ot_usersoverview'),
    get_string('department', 'report_ot_usersoverview'),
];
if ( $SESSION->report_ot_usersoverview__showgroups )
{
    for ( $i = 0; $i < $data->groupsnumber; $i++ )
    {
        $table->head[] = get_string('group', 'report_ot_usersoverview', $i + 1 );
    }
}

$countrows = 0;
if ( ! is_null($data->data) )
{
    $url = new \moodle_url('/user/profile.php');
    // заполнение данными
    foreach ( $data->data->number_enrols_all_users as $userid => $userdata )
    {
        $url->param('id', $userid);
        $userrow = [];
        $userrow = [
            html_writer::link($url->out(false), $userdata->firstname, ['class' => 'report_ot_usersoverview_clickable_link']),
            html_writer::link($url->out(false), $userdata->lastname, ['class' => 'report_ot_usersoverview_clickable_link']),
            $userdata->department
        ];
        if ( $SESSION->report_ot_usersoverview__showgroups )
        {
            foreach ( $userdata->ot_usersoverview_cohortids as $id )
            {
                $userrow[] = $data->cohorts[$id]->name;
            }
            for ( $i = count($userdata->ot_usersoverview_cohortids); $i < $data->groupsnumber; $i++ )
            {
                $userrow[] = '';
            }
        }
        $table->data[] = $userrow;
    }
    $countrows = count($table->data);
    if ( !$exportformat )
    {
        $table->data = array_slice($table->data, $page * $perpage, $perpage);
    }
}

$html .=  html_writer::table($table);

$baseurl = new \moodle_url('/report/ot_usersoverview/users.php', ['institution' => $institution, 'department' => $department, 'perpage' => $perpage]);
$html .= $OUTPUT->paging_bar($countrows, $page, $perpage, $baseurl);

// Обработчик экспорта
report_helper::export($exportformat, $table);

// Печать хидера
echo $OUTPUT->header();

// Отображение информации
echo $html;

// Печать футера
echo $OUTPUT->footer();


