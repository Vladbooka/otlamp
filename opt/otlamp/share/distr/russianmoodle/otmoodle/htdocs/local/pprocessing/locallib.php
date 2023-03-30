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

require_once(dirname(realpath(__FILE__))."/../../config.php");

function local_pprocessing_get_role($roleid)
{
    global $DB;
    return $DB->get_record('role', ['id' => $roleid]);
}

function local_pprocessing_get_role_assignment($roleid, $userid, $contextid)
{
    global $DB;
    return $DB->get_record('role_assignments', ['roleid' => $roleid, 'userid' => $userid, 'contextid' => $contextid]);
}

/**
 * Возвращает объект dof
 * @return NULL|dof_control
 */
function local_pprocessing_get_dof()
{
    global $CFG;
    $dof = null;
    if( file_exists($CFG->dirroot . '/blocks/dof/locallib.php') )
    {
        require_once($CFG->dirroot . '/blocks/dof/locallib.php');
        global $DOF;
        $dof = & $DOF;
    }
    return $dof;
}

/**
 * Этот метод скопирован с ядрового метода удаления попытки и кастрирован в части обновления оценок.
 * Его можно использовать только в том случае, если после удаления остаются попытки и среди них есть лучшая.
 * В противном случае нужно использовать метод ядра quiz_delete_attempt($attempt, $quiz)
 * @param mixed $attempt an integer attempt id or an attempt object
 *      (row of the quiz_attempts table).
 * @param object $quiz the quiz object.
 */
function local_pprocessing_quiz_delete_attempt_without_update_grades($attempt, $quiz) {
    global $DB, $CFG;
    require_once($CFG->dirroot . '/question/engine/lib.php');
    require_once($CFG->dirroot . '/mod/quiz/locallib.php');
    
    if (is_numeric($attempt)) {
        if (!$attempt = $DB->get_record('quiz_attempts', array('id' => $attempt))) {
            return;
        }
    }
    
    if ($attempt->quiz != $quiz->id) {
        debugging("Trying to delete attempt $attempt->id which belongs to quiz $attempt->quiz " .
            "but was passed quiz $quiz->id.");
        return;
    }
    
    if (!isset($quiz->cmid)) {
        $cm = get_coursemodule_from_instance('quiz', $quiz->id, $quiz->course);
        $quiz->cmid = $cm->id;
    }
    
    question_engine::delete_questions_usage_by_activity($attempt->uniqueid);
    $DB->delete_records('quiz_attempts', array('id' => $attempt->id));
    
    // Log the deletion of the attempt if not a preview.
    if (!$attempt->preview) {
        $params = array(
            'objectid' => $attempt->id,
            'relateduserid' => $attempt->userid,
            'context' => context_module::instance($quiz->cmid),
            'other' => array(
                'quizid' => $quiz->id
            )
        );
        $event = \mod_quiz\event\attempt_deleted::create($params);
        $event->add_record_snapshot('quiz_attempts', $attempt);
        $event->trigger();
    }
}