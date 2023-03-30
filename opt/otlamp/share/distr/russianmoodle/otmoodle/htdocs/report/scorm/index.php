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
 * Отчет по результатам SCORM. ТОчка входа в отчет.
 *
 * @package    report
 * @subpackage scorm
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require ('../../config.php');
require_once ($CFG->libdir . '/adminlib.php');

// ID модуля курса SCORM
$cmid = optional_param('cmid', 0, PARAM_INT);

// Требуется авторизация в системе
require_login();

// Конфигурация страницы
admin_externalpage_setup(
    'reportscormindex', '', null, '', ['pagelayout' => 'report']);

// Форма настроек
$customdata = new stdClass();
$customdata->cmid = $cmid;
$form = new report_scorm\reportchoose_form($PAGE->url, $customdata);
$form->process();

echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('pluginname', 'report_scorm'));

$form->display();

echo $OUTPUT->footer();