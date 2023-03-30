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
 * Отчет по результатам SCORM. Страница получения базового отчета по статистике.
 *
 * @package    report
 * @subpackage scorm
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace report_scorm;

require ('../../config.php');
require_once($CFG->dirroot.'/report/scorm/lib.php');
require_once($CFG->dirroot.'/mod/scorm/locallib.php');
require_once($CFG->dirroot.'/report/scorm/locallib.php');
require_once($CFG->dirroot.'/mod/scorm/report/reportlib.php');
require_once($CFG->libdir.'/grouplib.php');
require_once($CFG->libdir.'/pdflib.php');
require_once ($CFG->libdir.'/adminlib.php');

defined('MOODLE_INTERNAL') || die;

use report_scorm\reports\report;
use context_system;
use moodle_url;

// ID модулей курса SCORM для формирования отчета
$format = required_param('format', PARAM_RAW_TRIMMED);
$cmids = required_param('cmids', PARAM_RAW_TRIMMED);
$type = required_param('type', PARAM_RAW_TRIMMED);

// Отформатируем
$formated_cmids = explode(',', $cmids);

// Требуется авторизация в системе
require_login();

// Инициализация менеджера работы со SCORM
$html = '';
// Опции для отчета
$options = [];

// Группировка для сводного отчета
$group_field = optional_param('group_field', NULL, PARAM_TEXT);

if ( ! empty($group_field) )
{
    $options['group_field'] = $group_field;
}

// Установка параметров страницы
$PAGE->set_pagelayout('report');
$PAGE->set_context(context_system::instance());
$pageurl = new moodle_url('/report/scorm/export.php', [
    'format' => $format,
    'type' => $type,
    'cmids' => $cmids
]);
$PAGE->set_url($pageurl);
$PAGE->set_title(get_string($format . '_title', 'report_scorm'));

$reports_check = ['full_report', 'short_report'];
if ( empty($type) || 
        ! in_array($type, ['pdf', 'xls', 'html'])  || 
        ( empty($cmids) && in_array($format, $reports_check) ) )
{
    redirect(new moodle_url('/report/scorm/index.php'));
}

// Получаем класс фабрики
$factory = new report();
$document = $factory->get_writer($formated_cmids, $format, $type, $options);

if ( ! empty($document) )
{// Получен документ
    if ( $type == 'html' )
    {
        $html .= $document->get_html();
    } else
    {
        $document->generate_data(false);
    }
} else 
{// Не хватает данных, редиректим обратно
    redirect(new moodle_url('/report/scorm/index.php'));
}

echo $OUTPUT->header();
echo $OUTPUT->heading(get_string($format . '_title', 'report_scorm'));

echo $html;

echo $OUTPUT->footer();