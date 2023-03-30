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
 * Блок комментарий преподавателя. Класс формы комментария преподавателя (вкладка с незавершенными попытками).
 *
 * @package    block
 * @subpackage block_quiz_teacher_feedback
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace block_quiz_teacher_feedback;

use moodleform;
use html_writer;
use moodle_url;

require_once($CFG->libdir.'/grouplib.php');

/**
 * Форма вывода вкладки с ссылками на страницы с вопросами, на которых остановились пользователи
 */

class feedback_links extends moodleform
{
    /**
     * Количество символов для обрезки названия вопроса
     *
     * @param int $count
     */
    private $count = 39;
    
    public function definition()
    {
        $mform =& $this->_form;

        if ( property_exists($this->_customdata, 'students_data') &&
                ! empty($this->_customdata->students_data) &&
                property_exists($this->_customdata, 'quizid') &&
                ! empty($this->_customdata->quizid))
        {// Отобразить хидер
            $header = $mform->createElement(
                    'html',
                    '<div class="quiz_block_teacher_feedback_header" data-quizid="' . $this->_customdata->quizid . '">' .
                        get_string('feedback_info_users_attempts', 'block_quiz_teacher_feedback').
                    '</div>'
                    );
            $mform->addGroup([$header], 'main_header_group', '', '');
            
            
            $groups = groups_get_all_groups($this->_customdata->courseid);
            $groupschoices = [
                0 => get_string('feedback_info_all_students', 'block_quiz_teacher_feedback')
            ];
            if ($groups) {
                // Print out the HTML
                foreach ($groups as $group) {
                    $groupschoices[$group->id] = $group->name;
                }
            };
            $mform->addElement('select', 'filter_group', get_string('feedback_info_filter_group', 'block_quiz_teacher_feedback'),
                $groupschoices, ['class' => 'qtf_filter_group']);
            
        }
    }
    
    public function definition_after_data()
    {
        $mform =& $this->_form;
        
        $groupid = 0;
        $groupusers = [];
        if($formdata = $this->get_data())
        {
            if (!empty($formdata->filter_group))
            {
                $groupid = $formdata->filter_group;
                $groupusers = groups_get_members($groupid, 'u.id');
            }
        }
        
        foreach ( $this->_customdata->students_data as $id => $student_data )
        {// Фомируем ссылку на вопрос попытки
            if(array_key_exists($id, $groupusers) || $groupid == 0)
            {
                // Сформируем кнопку с ссылкой на попытку студента (на последний вопрос, если он есть)
                $url = new moodle_url('/mod/quiz/review.php',
                    [
                        'attempt' => $student_data->attempt_id,
                        'showall' => 0,
                        'page' => $student_data->current_page
                    ]);
                $html = '<a class="clearfix clear quiz_teacher_feedback_pix_wrapper" href="'.$url.'"><div class="btn btn-primary quiz_teacher_feedback_pix" data-attempt="' . $student_data->attempt_id .'">' .
                    get_string('feedback_info_button_to_view_attempt', 'block_quiz_teacher_feedback').
                    '</div></a>';
                    $mform->addElement('header', 'header_' . $id . '_' . $student_data->attempt_id, $student_data->fullname . $html);
                    $mform->setExpanded('header_' . $id . '_' . $student_data->attempt_id, false);
                    
                    // Покажем попытку пользователя
                    $this->show_attempt($student_data);
            }
        }
    }
    
    /**
     * Отобразить статусы вопросов студента
     *
     * @param stdClass $student_data - данные студента для отображения
     *
     * @return void
     */
    private function show_attempt($student_data)
    {
        $mform =& $this->_form;
        
        $html = '';
        $html .= html_writer::div(
                get_string('feedback_info_questions_to_grade', 'block_quiz_teacher_feedback'),
                'quiz_block_teacher_feedback_wrapper_header',
                [
                    'data-attemptid' => $student_data->attempt_id,
                    'data-studentid' => $student_data->studentid
                ]
                );
        foreach ($student_data->to_grade_pages as $page)
        {
            $html .= html_writer::start_div(
                    'quiz_teacher_feedback_elements_wrapper'
                    );
            $html .= html_writer::link(
                    new moodle_url('/mod/quiz/review.php',
                    [
                        'attempt' => $student_data->attempt_id,
                        'showall' => 0,
                        'page' => $page->page_id
                    ]),
                    '',
                    [
                        'class' => 'quiz_teacher_feedback_button ' . $page->class,
                        'data-hint' => $page->hint,
                        'data-pageid' => $page->page_id,
                        'data-status' => $page->status,
                        'data-answered' => $page->answered
                    ]
                    );
            $html .= html_writer::div($this->cut_the_string($page->name_of_slot), 'quiz_block_teacher_feedback_name_of_slot');
            $html .= html_writer::end_div();
        }
        $mform->addElement('html', $html);
    }
    
    /**
     * Функция, обрезающая длинные названия
     *
     * @param int $string - строка для обрезки
     *
     * @return string - вернем обрезанную строку
     */
    private function cut_the_string($string)
    {
        // Проверка
        if ( empty($string) )
        {
            return '';
        }
        
        $array = explode('</span>', $string);
        if ( ! empty($array[1]) )
        {
            $value = strip_tags($array[1]);
            if ( mb_strlen($value) > $this->count )
            {
                $new_value = $array[0];
                $new_value .= mb_strcut($value, 0, ($this->count - 3));
                $new_value .= '...';
                $new_value .= '</h5>';
                return $new_value;
            } else
            {
                return $string;
            }
        }
        
        return '';
    }
}
