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
 * Web service
 *
 * @package    quiz_otoverview
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace quiz_otoverview\services;

defined('MOODLE_INTERNAL') || die();

require_once("$CFG->libdir/externallib.php");
require_once($CFG->dirroot . '/mod/quiz/report/default.php');
require_once($CFG->dirroot . '/mod/quiz/report/otoverview/report.php');
require_once($CFG->dirroot . '/mod/quiz/locallib.php');
require_once($CFG->dirroot . '/mod/quiz/report/reportlib.php');


use context_system;
use external_api;
use external_description;
use external_function_parameters;
use external_multiple_structure;
use external_value;
use quiz_otoverview_report;

class get_attempts_data extends external_api
{
    /**
     * Returns description of method parameters
     * @return external_function_parameters
     */
    public static function get_attempts_data_parameters()
    {
        $quizid = new external_value(
            PARAM_INT,
            'quiz id',
            VALUE_REQUIRED
        );
        $params = [
            'quizid' => $quizid,
            'questionattemptsids' => new external_multiple_structure(new external_value(PARAM_INT, 'quiz attempts ids'), 'List of quiz attempts ids')
        ];
        
        return new external_function_parameters($params);
    }
    
    /**
     * Returns description of method result value
     * @return external_description
     */
    public static function get_attempts_data_returns()
    {
        return new external_value(PARAM_RAW_TRIMMED, 'Result');
    }
    
    /**
     * Returns form updated after attempt to accept mastercourse
     * @return string html form
     */
    public static function get_attempts_data($quizid, $questionattemptsids) 
    {
        global $PAGE, $DB;
        if ( empty($quizid) || empty($questionattemptsids) )
        {
            return "";
        }
        $PAGE->set_context(context_system::instance());
        if ( !$cm = get_coursemodule_from_id('quiz', $quizid) ) 
        {
            print_error('invalidcoursemodule');
        }
        if ( !$course = $DB->get_record('course', ['id' => $cm->course]) ) 
        {
            print_error('coursemisconf');
        }
        if ( !$quiz = $DB->get_record('quiz', ['id' => $cm->instance]) ) 
        {
            print_error('invalidcoursemodule');
        }
        
        $report = new quiz_otoverview_report();
        $report->display($quiz, $cm, $course, true, $questionattemptsids);

        $resultdata = [];
        foreach ( $report->table->data as $attemptid => $row )
        {
            foreach ( $row as $value )
            {
                $resultdata[$attemptid][] = $value;
            }
        }
        
        return json_encode($resultdata);
    }
}
