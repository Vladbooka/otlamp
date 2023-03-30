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

// Поле для селекта
$field_value = optional_param('field', 'all', PARAM_RAW_TRIMMED);

// Скачивание файла
$export_on = optional_param('export_submit', false, PARAM_BOOL);

// сброс кеша
$purgecache = optional_param('purgecache_submit', false, PARAM_BOOL);

// Формат экспорта
if ( $export_on )
{
    $export_type = optional_param('export', '', PARAM_RAW_TRIMMED);
} else 
{
    $export_type = '';
}

// получение контекста системы
$syscontext = context_system::instance();

require_capability('report/ot_usersoverview:view', $syscontext);

// право на просмотр всех регионов
$hasadmincap = has_capability('report/ot_usersoverview:view_all', $syscontext);

// проверка прав доступа
$canviewdetail = has_capability('report/ot_usersoverview:view_detail', $syscontext);

// Проверка прав
if ( ! $hasadmincap )
{
    require_capability('report/ot_usersoverview:view_my', $syscontext);
}

// право на сброс кеша
$canresetcache = has_capability('report/ot_usersoverview:reset_cache', $syscontext);
if ( $purgecache && $canresetcache )
{
    // сброс кеша
    report_helper::purgecaches();
    
    // сбор всех данных
    report_helper::collectcache();
}
admin_externalpage_setup('report_ot_usersoverview', '', null, '', ['pagelayout' => 'report']);

// HTML код
$html = '';

$table = new html_table();
$table->align = ['left','left','left','left','left','left'];

$select_options = report_helper::get_all_existing_institutions();

$html_form = '';
$html_form .= html_writer::start_tag('form', 
        [
            'action' => 'index.php',
            'method' => 'post',
            'class' => 'ot_usersoverview_form'
        ]);

$html_form .= html_writer::div(get_string('field_custom_filter', 'report_ot_usersoverview'), 'ot_usersoverview_label');
$html_form .= html_writer::select($select_options, 'field', $field_value, '');

$html_form .= html_writer::empty_tag(
        'input', 
        [
            'type' => 'submit',
            'value' => get_string('view'),
            'class' => 'btn btn-primary'
        ]);
// кнопка сброса кеша
if ( $canresetcache )
{
    $html_form .= html_writer::empty_tag('input',
            [
                'name' => 'purgecache_submit',
                'type' => 'submit',
                'value' => get_string('purgecache', 'report_ot_usersoverview'),
                'class' => 'btn btn-primary'
            ]);
}

$select_options = report_helper::get_export_types_select();
$html_form_download = html_writer::div(get_string('download_label', 'report_ot_usersoverview'), 'ot_usersoverview_label');
$html_form_download .= html_writer::select($select_options,'export', '', '');
$html_form_download .= html_writer::empty_tag('input',
        [
            'name' => 'export_submit',
            'type' => 'submit',
            'value' => get_string('export', 'report_ot_usersoverview'),
            'class' => 'btn btn-primary'
        ]);
$html_form .= html_writer::div($html_form_download, 'ot_usersoverview_downloader');

$html_form .= html_writer::end_tag('form');
$html .= $html_form;

// ссылка на список пользователей
$usersdetailinfo = new \moodle_url('/report/ot_usersoverview/users.php', ['institution' => $field_value]);

$table = new html_table();
$table->align = ['left', 'center', 'center', 'center', 'center', 'center', 'center', 'center'];
$table->attributes = ['class' => 'ot_usersoverview_table'];

// Заголовки таблицы
$table->head = [
    get_string('department'),
    get_string('number_users', 'report_ot_usersoverview'),
    get_string('number_enrols_all', 'report_ot_usersoverview'),
    get_string('number_enrols_active', 'report_ot_usersoverview'),
    get_string('number_enrols_completed', 'report_ot_usersoverview'),
    get_string('number_enrols_failed', 'report_ot_usersoverview'),
];

// Получение данных
$info = \report_ot_usersoverview\report_helper::get_data($field_value);
foreach ( $info as $row ) 
{
    $usersdetailinfo->param('department', $row->clean_name);
    $table->data[] = [
        $row->name, 
        $export_on || !$canviewdetail ? $row->number_users : \html_writer::link($usersdetailinfo->out(false), $row->number_users, ['class' => 'report_ot_usersoverview_clickable_link']), 
        $row->number_enrols_all, 
        $row->number_enrols_active, 
        $row->number_enrols_completed, 
        $row->number_enrols_failed
    ];
}

$html .=  html_writer::table($table);

// Обработчик экспорта
report_helper::export($export_type, $table);

// Печать хидера
echo $OUTPUT->header();

// Отображение информации
echo $html;

// Печать футера
echo $OUTPUT->footer();


