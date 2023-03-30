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
 * Страница просмотра списка участников сессии.
 *
 * Содержит форму редактирование посещаемости сессии
 */
use \mod_event3kl\session;
use \mod_event3kl\event3kl;
use \mod_event3kl\form\edit_attendance;
use mod_event3kl\form\add_session_member;

require_once('../../config.php');
require_once('lib.php');

$sessionid = required_param('id', PARAM_INT);

$pageurl = new moodle_url('/mod/event3kl/session_members.php', ['id' => $sessionid]);
$PAGE->set_url($pageurl);

// Получаем сессию, ивент, модуль, курс
$session = $session = new session($sessionid);
$event3kl = $session->obtain_event3kl();
$cm = $event3kl->obtain_cm();
$course = $event3kl->obtain_course_instance();

// Контекст модуля курса
// $context = context_module::instance($cm->id);

require_login($course, false, $cm);

$a = new stdClass();
$a->sessionname = $session->get('name');
$PAGE->set_title(get_string('session_participants', 'mod_event3kl', $a));
// $modulename = $event3kl->get('name');
$PAGE->set_heading(get_string('session_participants', 'mod_event3kl', $a));
$PAGE->navbar->add(get_string('session_participants', 'mod_event3kl', $a));


$html = '';

$customdata = ['sessionid' => $sessionid];
$editattendanceform = new edit_attendance($pageurl, $customdata);
$editattendanceform->process();
$html .= $editattendanceform->render();

// TODO: если формат manual, а дейтмод не vacantseat предоставить возможность менеджеру
// добавлять участников в сессию (тех, которые потенциально могут быть участниками в этой группе
// согласно get_event_users и ещё не является участником, то есть отсутствует в session_member)
$customdata = ['sessionid' => $sessionid];
$addmemberform = new add_session_member($pageurl, $customdata);
$addmemberform->process();
$html .= $addmemberform->render();

echo $OUTPUT->header();

echo $html;

echo $OUTPUT->footer();