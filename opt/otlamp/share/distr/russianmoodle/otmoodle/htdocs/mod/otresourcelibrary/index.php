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

$id = optional_param('id', 0, PARAM_INT);    // Course Module ID
$url = new moodle_url('/mod/otresourcelibrary/index.php', ['id' => $id]);
$PAGE->set_url($url);
if ($id) {
    if (!$course = $DB->get_record('course', ['id' => $id])) {
        print_error('course is misconfigured');  // NOTE As above
    }
} else {
    $course = get_site();
}
// Требуется вход в систему
require_course_login($course);
// Установка параметров страницы
$PAGE->set_pagelayout('incourse');
$PAGE->set_title($course->shortname);
$PAGE->set_heading($course->fullname);



$html = 'Привет мир';

echo $OUTPUT->header();

echo $html;

echo $OUTPUT->footer();
