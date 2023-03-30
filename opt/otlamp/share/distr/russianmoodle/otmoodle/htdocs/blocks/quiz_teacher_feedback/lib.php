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

/**
 * Блок комментарий преподавателя
 *
 * @package    block_quiz_teacher_feedback
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


/**
 * Получение формы с фильтрацией в качестве фрагмента
 *
 * @param array $args Список именованных аргументов для загрузчика фрагмента
 * @return string
 */
function block_quiz_teacher_feedback_output_fragment_filtered_group($args)
{
    global $PAGE;
    
    $PAGE->set_context(context_system::instance());
    
    $block = block_quiz_teacher_feedback_get_block($args['instanceid']);
    $renderer = $PAGE->get_renderer('block_quiz_teacher_feedback');
    $cm = get_coursemodule_from_id('quiz', $args['quizid']);
    $customdata = new \stdClass();
    $customdata->students_data = $block->get_questions_data($cm, null, $renderer);
    $customdata->courseid = $cm->course;
    $customdata->quizid = $args['quizid'];
    
    $data = (array)json_decode($args['formdata']);
    
    // Объявление формы с попытками студентов
    $mform = new \block_quiz_teacher_feedback\feedback_links(null, $customdata, 'post', '', null, true, $data);
    
    // Рендеринг формы
    return $mform->render();
}