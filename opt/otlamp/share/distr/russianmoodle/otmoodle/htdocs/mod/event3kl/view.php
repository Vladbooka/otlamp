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

global $PAGE;

$id = required_param('id', PARAM_INT);

list ($course, $cm) = get_course_and_cm_from_cmid($id, 'event3kl');

require_login($course, true, $cm);

$context = \context_module::instance($cm->id);

require_capability('mod/event3kl:view', $context);

$event3kl = new \mod_event3kl\event3kl($cm->instance);

$url = new \moodle_url('/mod/event3kl/view.php', ['id' => $id]);
$PAGE->set_url($url);
$PAGE->set_title($event3kl->get('name'));
$PAGE->set_heading($event3kl->get('name'));

// Update module completion status.
$event3kl->set_module_viewed();

// Шапка страницы
echo $OUTPUT->header();

// render the page.
echo $event3kl->render_view($OUTPUT);

echo $OUTPUT->footer();
