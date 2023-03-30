<?php
///////////////////////////////////////////////////////////////////////////
//                                                                       //
// This file is part of Moodle - http://moodle.org/                      //
// Moodle - Modular Object-Oriented Dynamic Learning Environment         //
//                                                                       //
// Moodle is free software: you can redistribute it and/or modify        //
// it under the terms of the GNU General Public License as published by  //
// the Free Software Foundation, either version 3 of the License, or     //
// (at your option) any later version.                                   //
//                                                                       //
// Moodle is distributed in the hope that it will be useful,             //
// but WITHOUT ANY WARRANTY; without even the implied warranty of        //
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the         //
// GNU General Public License for more details.                          //
//                                                                       //
// You should have received a copy of the GNU General Public License     //
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.       //
//                                                                       //
///////////////////////////////////////////////////////////////////////////

require_once("$CFG->libdir/externallib.php");
require_once($CFG->dirroot . '/mod/quiz/report/default.php');
require_once($CFG->dirroot . '/mod/quiz/report/otoverview/report.php');
require_once($CFG->dirroot . '/mod/quiz/locallib.php');
require_once($CFG->dirroot . '/mod/quiz/report/reportlib.php');

/**
 * Получение формы с фильтрацией в качестве фрагмента
 *
 * @param array $args Список именованных аргументов для загрузчика фрагмента
 * @return string
 */
function quiz_otoverview_output_fragment_get_charts($args)
{
    global $PAGE, $DB;
    
    $PAGE->set_context(context_system::instance());
    if ( !$cm = get_coursemodule_from_id('quiz', $args['quizid']) )
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
    return $report->display($quiz, $cm, $course, true, (array)json_decode($args['questionattemptsids']), true);
}