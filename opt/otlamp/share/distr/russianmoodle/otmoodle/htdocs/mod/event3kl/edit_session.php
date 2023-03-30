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

require_once("../../config.php");
require_once("lib.php");

use mod_event3kl\form\edit_session;

$sessionid = optional_param('sessionid', NULL, PARAM_INT);
$event3klid = optional_param('event3klid', NULL, PARAM_INT);
$groupid = optional_param('groupid', NULL, PARAM_INT);

// проверки доступности формы. В случае ошибки - эксепшн
list('event3kl' => $event3kl) = edit_session::form_prechecks($sessionid, $event3klid, $groupid);
require_login($event3kl->obtain_course_instance(), false, $event3kl->obtain_cm());
require_capability('mod/event3kl:managesessions', $event3kl->obtain_module_context());

$pagedata = [];
if (!is_null($sessionid)) {
    $pagedata['sessionid'] = $sessionid;
}
if (!is_null($event3klid)) {
    $pagedata['event3klid'] = $event3klid;
}
if (!is_null($groupid)) {
    $pagedata['groupid'] = $groupid;
}
$pageurl = new \moodle_url('/mod/event3kl/edit_session.php', $pagedata);
$PAGE->set_url($pageurl);
$PAGE->set_title(get_string('edit_session', 'mod_event3kl'));
$PAGE->set_heading($event3kl->get('name') . ': ' . get_string('edit_session', 'mod_event3kl'));
$PAGE->navbar->add(get_string('edit_session', 'mod_event3kl'));

$form = new edit_session($pageurl->out(false), $pagedata);
$form->process();
$html = $form->render();

// Шапка страницы
echo $OUTPUT->header();
// Рендер формы
echo $html;
// Подвал страницы
echo $OUTPUT->footer();

