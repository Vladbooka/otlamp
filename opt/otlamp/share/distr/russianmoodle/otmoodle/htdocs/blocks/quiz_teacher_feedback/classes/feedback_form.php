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
 * Блок комментарий преподавателя. Класс формы комментария проподавателя.
 * 
 * @package    block
 * @subpackage block_quiz_teacher_feedback
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace block_quiz_teacher_feedback;

use moodle_url;
use question_attempt;
use stdClass;
use moodleform;
use context_user;
use core\notification;

require_once($CFG->dirroot.'/blocks/quiz_teacher_feedback/locallib.php');

class feedback_form extends moodleform
{
    /**
     * Попытка прохождения вопроса
     * 
     * @var question_attempt
     */
    private $qa = null;
    
    /**
     * {@inheritDoc}
     * @see moodleform::definition()
     */
    public function definition() 
    {
        global $USER;
        
        $mform = & $this->_form;
        if ( ! empty($this->_customdata->qa) )
        {// Передан ID вопроса
            $this->qa = $this->_customdata->qa;
            
            // Получить текущий комментарий
            $feedback = block_quiz_teacher_feedback_get_feedback($this->qa->get_database_id());
            // Поддержка ручного оценивания
            $ismanualgraded = $this->qa->get_question()->qtype->is_manual_graded();
            
            $fieldname = $this->qa->get_behaviour_field_name('qtc_qaid');
            $mform->addElement('hidden', $fieldname, $this->qa->get_database_id());
            $mform->setType($fieldname, PARAM_INT);
            
            if ( $this->_customdata->block->is_slot_under_controlled($this->qa) )
            {
                $fieldname = $this->qa->get_behaviour_field_name('qtc_completed');
                $select = [
                    0 => get_string('feedback_form_notcompleted', 'block_quiz_teacher_feedback'),
                    1 => get_string('feedback_form_completed', 'block_quiz_teacher_feedback')
                ];
                $mform->addElement(
                        'select',
                        $fieldname,
                        get_string('feedback_form_completed_title', 'block_quiz_teacher_feedback'),
                        $select,
                        ['id' => uniqid('completed')]
                        );
                $mform->setType($fieldname, PARAM_INT);
                if ( ! empty($feedback->completed) )
                {
                    $mform->setDefault($fieldname, (bool)$feedback->completed);
                } 
            }
            
            // Поле отзыва об ответе студента
            $fieldname = $this->qa->get_behaviour_field_name('qtc_feedback');
            $mform->addElement(
                'editor',
                $fieldname,
                get_string('feedback_form_feedback', 'block_quiz_teacher_feedback'),
                ['id' => uniqid('feedback')],
                [
                    'trusttext' => true,
                    'subdirs' => false,
                    'maxfiles' => 0,
                    'context' => context_user::instance($USER->id),
                    'noclean' => true
                ]
            );
            $mform->setType($fieldname, PARAM_RAW_TRIMMED);
            // Установка текущего значения поля
            $default = ['text' => '', 'format' => FORMAT_HTML];
            if ( isset($feedback->feedback) )
            {
                $default['text'] = $feedback->feedback;
            }
            if ( isset($feedback->feedbackformat) )
            {
                $default['format'] = $feedback->feedbackformat;
            }
            $mform->setDefault($fieldname, $default);
            
            // Поле оценки
            $fieldname = $this->qa->get_behaviour_field_name('qtc_grade');
            if ( $this->qa->get_max_mark() && $ismanualgraded )
            {
                // Оценка
                $a = new stdClass();
                $a->maxmark = $this->qa->format_max_mark(2);
                $mform->addElement(
                    'text',
                    $fieldname,
                    get_string('feedback_form_grade', 'block_quiz_teacher_feedback', $a),
                    ['id' => uniqid('grade')]
                );
                $mform->setType($fieldname, PARAM_RAW_TRIMMED);
                $default = '';
                if ( isset($feedback->grade) )
                {
                    $default = format_float($feedback->grade, 2, true, true);
                }
                $mform->setDefault($fieldname, $default);
            }
            
            // Кнопка сохранения комментария
            $buttonarray = [];
            $fieldname = $this->qa->get_behaviour_field_name('qtc_submit');
            $buttonarray[] = $mform->createElement(
                'submit',
                $fieldname,
                get_string('feedback_form_submit', 'block_quiz_teacher_feedback')
            );
            $mform->addGroup($buttonarray, 'submit', '', [' '], false);
        } else 
        {// Отобразить ошибку
           debugging(get_string('error_qa_not_set', 'block_quiz_teacher_feedback'), DEBUG_DEVELOPER);
        }
    }
    
    /**
     * Проверка на стороне сервера
     *
     * @param array data - данные из формы
     * @param array files - файлы из формы
     *
     * @return array - массив ошибок
     */
    public function validation($data, $files)
    {
        $errors = [];
    
        $fieldname = $this->qa->get_behaviour_field_name('qtc_grade');
        if ( isset($data[$fieldname]) )
        {
            $error = $this->qa->validate_manual_mark($data[$fieldname]);
            
            if ( $error )
            {
                $errors[$fieldname] = $error;
            }
        }
        
        // Возвращаем ошибки, если они возникли
        return $errors;
    }
    
    /**
     * Обработчик формы
     */
    public function process()
    {
        global $CFG, $DB;
        
        if ( $this->is_submitted() AND confirm_sesskey() AND
             $this->is_validated() AND $formdata = $this->get_data()
           )
        {// Форма отправлена и проверена
            $qafieldname = $this->qa->get_behaviour_field_name('qtc_qaid');
            if ( property_exists($formdata, $qafieldname) &&
                 ! empty($formdata->$qafieldname) && 
                 ! empty($this->qa) && 
                   $formdata->$qafieldname == $this->qa->get_database_id() 
               )
            {
                // Комментарий
                $fieldname = $this->qa->get_behaviour_field_name('qtc_feedback');
                $feedback = '';
                $feedbackformat = FORMAT_HTML;
                if ( ! empty($formdata->$fieldname) )
                {
                    $value = $formdata->$fieldname;
                    $feedback = $value['text'];
                    $feedbackformat = $value['format'];
                }
                // Оценка
                $ismanualgraded = $this->qa->get_question()->qtype->is_manual_graded();
                $grade = null;
                if ( $ismanualgraded )
                {
                    $fieldname = $this->qa->get_behaviour_field_name('qtc_grade');
                    if ( isset($formdata->$fieldname) && strlen($formdata->$fieldname) )
                    {
                        $grade = (float)str_replace(',', '.', $formdata->$fieldname);
                    } else 
                    {
                        $grade = '';
                    }
                }
                
                // Завершение ответа
                $fieldname = $this->qa->get_behaviour_field_name('qtc_completed');
                $completed = false;
                if ( ! empty($formdata->$fieldname) )
                {
                    $completed = true;
                }
                
                $record = new stdClass();
                $record->feedback = $feedback;
                $record->feedbackformat = $feedbackformat;
                $record->grade = $grade;
                $record->completed = $completed;
                $record->qaid = $formdata->$qafieldname;
                
                // Сохранение состояния комментария по вопросу
                if ( $feedback = block_quiz_teacher_feedback_save_feedback($record) )
                {
                    notification::success(get_string('feedbacksaveok', 'block_quiz_teacher_feedback'));
                }
            }
        }
    }
}
