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
require_once($CFG->dirroot . '/mod/event3kl/classes/manage_providers_form.php');

$action = optional_param('action', null, PARAM_TEXT);
$code = optional_param('code', null, PARAM_TEXT);
$name = optional_param('name', null, PARAM_RAW);
// Установка параметров страницы
$params = [];
if (!is_null($action)) {
    $params['action'] = $action;
}
if (!is_null($code)) {
    $params['code'] = $code;
}
if (!is_null($name)) {
    $params['name'] = $name;
}
$url = new moodle_url('/mod/event3kl/manage_providers.php', $params);
$PAGE->set_url($url);

admin_externalpage_setup('event3kl_manage_providers');

$PAGE->set_context(null);
$PAGE->set_pagelayout('admin');
$PAGE->set_title(get_string('manage_providers', 'mod_event3kl'));
$PAGE->set_heading(get_string('manage_providers', 'mod_event3kl'));

$html = '';

$form = new \mod_event3kl\manage_providers_form($url, $params);
$form->process();
$html .= $form->render();

echo $OUTPUT->header();

echo $html;

echo $OUTPUT->footer();
