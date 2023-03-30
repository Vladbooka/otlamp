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
 * Блок комментарий преподавателя. Языковые переменные.
 *
 * @package    block
 * @subpackage block_quiz_teacher_feedback
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// SYSTEM LINES
$string['pluginname'] = 'Teacher\'s comment';
$string['quiz_teacher_feedback:addinstance'] = 'Add a new block “Teacher’s Comments”';
$string['quiz_teacher_feedback:myaddinstance'] = 'Add a new block “Teacher’s Commentary” to the page / my (My courses, My account, Dashboard)';
$string['quiz_teacher_feedback:use'] = 'Use the block “Teacher’s commentary”';

$string['no'] = 'No';
$string['yes'] = 'Yes';
        
// SETTINGS
$string['config_header'] = 'Block Settings';
$string['config_user_attempt_control_title'] = 'Test progress control';
$string['config_user_attempt_control_enable'] = 'Enabled';
$string['config_user_attempt_control_disable'] = 'Disabled';
$string['config_user_attempt_control_title_help'] = 'Description';
$string['config_user_attempt_control_title_help_help'] = 'This option allows you to control the passing of the test. The student cannot go to the next page until the teacher manually confirms the current answer. ';
$string['config_question_slot'] = 'Slot:';
        
$string['config_request_mode'] = 'Mode for sending a response to a check';
$string['config_request_mode_immidiately'] = 'Immediately send';
$string['config_request_mode_choose_default_yes'] = 'Ask (send by default)';
$string['config_request_mode_choose_default_no'] = 'Ask (do not send by default)';

$string['config_replace_checkbox'] = 'Move the checkbox to send a response to the check in the form to send a response to a question';
        
// CUSTOM LINES
$string['title'] = 'Instructor\'s comment';

$string['feedback_form_feedback'] = 'Comment';
$string['feedback_form_feedback_help'] = '';
$string['feedback_form_feedback_help_help'] = '';
$string['feedback_form_grade'] = 'Rating (from {$ a-> maxmark})';
$string['feedback_form_completed_title'] = 'Question status';
$string['feedback_form_notcompleted'] = 'The question is not completed';
$string['feedback_form_completed'] = 'Question completed';
$string['feedback_form_control_on'] = 'Monitor';
$string['feedback_form_control_off'] = 'Do not monitor';
$string['feedback_form_control_questions'] = 'Question Control';
$string['feedback_form_submit'] = 'Save';

// Information
$string['feedback_info_current_notcompleted'] = 'The question is not confirmed by the teacher';
$string['feedback_info_current_completed'] = 'The question was confirmed by the teacher';
$string['feedback_info_current_grade'] = 'Current rating:';
$string['feedback_info_current_grade_not_set'] = 'Not specified';
$string['feedback_info_unfinished_attempts'] = 'Pending attempts';
$string['feedback_info_current_grade_not_set'] = 'Not specified';
$string['feedback_info_users_attempts'] = 'Attempts';
$string['feedback_info_all_students'] = 'All learners';
$string['feedback_info_filter_group'] = 'Filter by group';
$string['feedback_info_questions_to_grade'] = 'Question Status';
$string['feedback_info_button_to_view_attempt'] = 'attempt';

// Modal window
$string['feedback_info_modal_header'] = 'Information on passing the test';
$string['feedback_info_modal_notyet_question'] = 'The teacher has not yet confirmed the answer to the previous question!';
$string['feedback_info_modal_check_questions'] = 'The teacher has confirmed all your answers!';

// Question Status
$string['button_graded'] = 'Confirmed by the teacher';
$string['button_not_answered'] = 'No answer';
$string['button_in_process'] = 'Verification Required';
$string['button_in_process_with_grade'] = 'Reconfirmation Required (Score: {$a})';

$string['question_header_grade'] = '(Score: {$a})';

$string['send_request'] = 'Submit verification response';
$string['send_rerequest'] = 'Send response to re-check';

$string['feedbacksaveok'] = 'Data saved successfully';

