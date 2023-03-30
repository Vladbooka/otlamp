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
 * Отчет по результатам SCORM. Локальные настройки плагина.
 *
 * @package    report
 * @subpackage scorm
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require(dirname(__FILE__).'/../../config.php');
require_once($CFG->dirroot.'/report/scorm/lib.php');

defined('MOODLE_INTERNAL') || die;

// ID модуля курса SCORM
$cmid = required_param('cmid', PARAM_INT);
$countrows = optional_param('countrows', 0, PARAM_INT);

$context = context_module::instance($cmid);
$cm = get_coursemodule_from_id('scorm', $cmid);

// Требуется авторизация в системе
require_login();
// Требуется наличие права
require_capability('report/scorm:editmodsettings', $context);

// Установка параметров страницы
$PAGE->set_pagelayout('admin');
$PAGE->set_cm($cm);
$pageurl = new moodle_url('/report/scorm/cmsettings.php', ['cmid' => $cmid]);
$PAGE->set_url($pageurl);
$PAGE->set_title(get_string('settings_form_title', 'report_scorm'));

// Форма настроек
$customdata = new stdClass();
$customdata->cmid = $cmid;
$customdata->countrows = $countrows;
$form = new report_scorm\settings_form($pageurl, $customdata);
$form->process();

echo $OUTPUT->header();

$form->display();

echo $OUTPUT->footer();