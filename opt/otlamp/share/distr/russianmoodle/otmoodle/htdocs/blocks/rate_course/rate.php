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
 * Rate this course
 *
 * @package    block
 * @subpackage rate_course
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 * Code was Rewritten for Moodle 2.X By Atar + Plus LTD for Comverse LTD.
 * @license http://www.gnu.org/copyleft/gpl.html GNU Public License
 */

require_once( '../../config.php' );
require_once( $CFG->dirroot .'/lib/pagelib.php' );

$courseid = required_param( 'courseid', PARAM_INT );
//  Load the course.
$course = $DB->get_record('course', array('id'=>$courseid));
global $PAGE;
$context = context_course::instance($courseid);
$PAGE->set_context($context);
$PAGE->set_url('/blocks/rate_course/rate.php', array('courseid'=>$courseid));
$title = get_string('giverating', 'block_rate_course');

$courseurl = new moodle_url('/course/view.php', ['id' => $courseid]);
$links = [
    ['name' => $course->shortname, 'link' => $courseurl, 'type' => 'misc'],
    ['name' => $title, 'link' => '', 'type' => 'misc']
];

if (is_array($links) && count($links)>0) {
    foreach ($links as $nav) {
        if (array_key_exists('name', $nav)) {
            if (array_key_exists('link', $nav) && !empty($nav['link'])) {
                $navelement = $nav['link'];
            } else {
                $navelement = null;
            }
            $PAGE->navbar->add($nav['name'],$navelement);
        }
    }
}
$PAGE->set_title($title);
$PAGE->set_heading($title);

echo $OUTPUT->header();

//  Require user to be logged in to view this page.
if ((!isloggedin() || isguestuser())) {
    notice_yesno(get_string('noguestuseage', 'block_rate_course').'<br /><br />'.get_string('liketologin'),
    $CFG->wwwroot.'/login/index.php', get_referer(false));
    echo $OUTPUT->footer();
    exit();
}
require_capability('block/rate_course:rate', $context);

echo "<div class=\"rate_course_rate\">";
$block = block_instance('rate_course');
$block->display_rating($course->id);

$existing_answer = $DB->get_record('block_rate_course',
        array('course'=>$course->id, 'userid'=>$USER->id));
if ($existing_answer) {
    $rate_text = get_string('completed', 'block_rate_course');
} else {
    $rate_text = get_string('intro', 'block_rate_course');
}
echo "<div><p>$rate_text</p></div>";

// Now output the form.
echo '<form method="post" action="'.
        $CFG->wwwroot.'/blocks/rate_course/update.php"><input name="id" type="hidden" value="'.$course->id.'" /><p>';

for ($i = 1; $i <= 5; $i++) {
    $checked = '';
    if (isset($existing_answer) && ($existing_answer !== false)) {
        if ($existing_answer->rating == $i) {
                $checked = 'checked="checked"';
        }
    }

    echo '<input type="radio" name="grade" ';
    if ($existing_answer) {
        echo 'disabled="disabled" ';
    }
    echo 'value="'.$i.'" '.$checked.' alt="Rating of '.$i.'"  />'.$i.' ';
}

echo '</p><p><input type="submit" value="'.get_string('submit', 'block_rate_course').'"';
if ($existing_answer) {
    echo 'disabled';
}
echo '/></p></form>';

echo '</div>';

echo $OUTPUT->footer($course);
