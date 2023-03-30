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
 * Страница редактирования комментария преподавателя
 *
 * @package    quiz_otoverview
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once('../../../../config.php');
require_once($CFG->dirroot . '/mod/quiz/locallib.php');
require_once($CFG->dirroot . '/mod/quiz/report/reportlib.php');
require_once($CFG->dirroot . '/mod/quiz/report/default.php');
require_once($CFG->dirroot . '/mod/quiz/report/otoverview/report.php');
require_once($CFG->dirroot . '/mod/quiz/report/otoverview/locallib.php');

global $PAGE, $DB;

$attemptid = required_param('attempt', PARAM_INT);
$slot = required_param('slot', PARAM_INT);

$PAGE->set_url('/mod/quiz/report/otoverview/comment.php', ['attempt' => $attemptid, 'slot' => $slot]);

$attemptobj = quiz_create_attempt_handling_errors($attemptid);

// проверка прав
require_login($attemptobj->get_course(), false, $attemptobj->get_cm());
$attemptobj->require_capability('mod/quiz:grade');

if ( $attemptobj->is_finished() )
{
    print_error('attemptfinished', 'quiz_otoverview');
}
$student = $DB->get_record('user', ['id' => $attemptobj->get_userid()]);

$contextmodule = context_module::instance($attemptobj->get_cmid());
$blockcontext = find_block_in_quiz($contextmodule);
if ( empty($blockcontext) )
{
    print_error('blockdoesntexists', 'quiz_otoverview');
}
/**
 * @var block_quiz_teacher_feedback $block
 */
$block = block_instance_by_id($blockcontext->instanceid);
if ( empty($block) )
{
    print_error('blockdoesntexists', 'quiz_otoverview');
}
$questionattempt = $attemptobj->get_question_attempt($slot);

// получение формы
$feedbackform = $block->get_comment_form($questionattempt, $PAGE->url);
$feedbackform->process();

$PAGE->set_pagelayout('popup');
$PAGE->set_title(get_string('manualgradequestion', 'quiz', [
        'question' => format_string($attemptobj->get_question_name($slot)),
        'quiz' => format_string($attemptobj->get_quiz_name()), 'user' => fullname($student)]));
$PAGE->set_heading($attemptobj->get_course()->fullname);
$output = $PAGE->get_renderer('mod_quiz');

// Prepare summary information about this question attempt.
$summarydata = [];

// Student name.
$userpicture = new user_picture($student);
$userpicture->courseid = $attemptobj->get_courseid();
$summarydata['user'] = [
    'title'   => $userpicture,
    'content' => new action_link(new moodle_url('/user/view.php', array(
            'id' => $student->id, 'course' => $attemptobj->get_courseid())),
            fullname($student, true)),
];

// Quiz name.
$summarydata['quizname'] = [
    'title'   => get_string('modulename', 'quiz'),
    'content' => format_string($attemptobj->get_quiz_name()),
];

// Question name.
$summarydata['questionname'] = [
    'title'   => get_string('question', 'quiz'),
    'content' => $attemptobj->get_question_name($slot),
];

$options = $attemptobj->get_display_options(true);
$options->hide_all_feedback();
$options->history = 0;

echo $output->header();
echo $output->review_summary_table($summarydata, 0);
echo $attemptobj->render_question_for_commenting($slot);
echo $feedbackform->render();
echo $output->footer();
