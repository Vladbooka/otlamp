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
 * Display information about all the mod_otresourcelibrary modules in the requested course.
 */

require_once('../../config.php');
require_once('lib.php');
require_once("$CFG->libdir/adminlib.php");


$action = optional_param('action', NULL, PARAM_RAW);
$sourcecode = optional_param('sourcecode', NULL, PARAM_RAW);
$sourcename = optional_param('sourcename', NULL, PARAM_RAW);
// Установка параметров страницы
$urlparams = [];
if (!is_null($action))
{
    $urlparams['action'] = $action;
}
if (!is_null($sourcecode))
{
    $urlparams['sourcecode'] = $sourcecode;
}
if (!is_null($sourcename))
{
    $urlparams['sourcename'] = $sourcename;
}
$url = new moodle_url('/mod/otresourcelibrary/manage_sources.php', $urlparams);
$PAGE->set_url($url);

admin_externalpage_setup('tool_otrl_manage_sources');

$PAGE->set_context(null);
$PAGE->set_pagelayout('incourse');
$PAGE->set_title(get_string('manage_source', 'otresourcelibrary'));
$PAGE->set_heading(get_string('manage_source', 'otresourcelibrary'));

$html = '';


$formactionparams = [];
if (!is_null($action))
{
    $formactionparams['action'] = $action;
}
if (!is_null($sourcecode))
{
    $formactionparams['sourcecode'] = $sourcecode;
}
if (!is_null($sourcename))
{
    $formactionparams['sourcename'] = $sourcename;
}
$formaction = new moodle_url('/mod/otresourcelibrary/manage_sources.php', $formactionparams);
$sourceform = new \mod_otresourcelibrary\source_form($formaction, [
    'action' => $action,
    'sourcecode' => $sourcecode,
    'sourcename' => $sourcename,
]);
$sourceform->process();
$html .= $sourceform->render();

echo $OUTPUT->header();

echo $html;

echo $OUTPUT->footer();
