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
 * Блок комментарий преподавателя. Класс рендера.
 * 
 * @package    block
 * @subpackage block_quiz_teacher_feedback
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once($CFG->dirroot . '/blocks/quiz_teacher_feedback/locallib.php');
require_once($CFG->dirroot . '/mod/quiz/locallib.php');

class block_quiz_teacher_feedback_renderer extends plugin_renderer_base 
{
    /**
     * Отобразить секцию отзыва
     * 
     * Секция с отзывом преподавателя по указанной попытке прохождения вопроса
     * 
     * @param question_attempt $qa - Попытка прохождения вопроса
     * @param block_quiz_teacher_feedback $block - Экземпляр блока
     * @param boolean $flag - Флаг, показывающий, что вопрос оцениваемый
     * 
     * @return string - HTML-код секции
     */
    public function feedback(question_attempt $qa, block_quiz_teacher_feedback $block, $flag = true) 
    {
        $html = '';
        
        // Получение текущего отзыва на попытку прохождения вопроса
        $feedback = block_quiz_teacher_feedback_get_feedback($qa->get_database_id());
        
        // Проверяем, что слот под контролем
        $slot = $qa->get_slot();
        
        // Контроль прохождения вопроса
        if (  $block->is_slot_should_be_graded($qa) )
        {// Контроль прохождения теста включен
            
            if ( ! empty($feedback->completed) )
            {// Вопрос подтвержден преподавателем
                $html .= html_writer::div(
                    get_string('feedback_info_current_completed', 'block_quiz_teacher_feedback'), 
                    'complete'
                );
            } else 
            {// Вопрос не подтвержден
                $html .= html_writer::div(
                    get_string('feedback_info_current_notcompleted', 'block_quiz_teacher_feedback'),
                    'complete'
                );
            }
        }
        
        // Отзыв
        if ( isset($feedback->feedback) )
        {
            $html .= html_writer::div($feedback->feedback, 'feedback');
        } else 
        {
            $html .= html_writer::div('', 'feedback');
        }
        
        // Поддержка ручного оценивания попытки прохождения вопроса
        $ismanualgraded = $qa->get_question()->qtype->is_manual_graded();
        if ( $ismanualgraded  )
        {
            // Получение текущей оценки
            $attributeclass = '';
            $currentgrade = '';
            if ( isset($feedback->grade) && $feedback->grade !== null )
            {// Текущая оценка указана
                $attributeclass = 'set';
                $currentgrade = format_float($feedback->grade, 2, true, true);
            }
            
            // Подготовка элементов
            $currentgrade = html_writer::span($currentgrade, 'current');
            $title = get_string(
                'feedback_info_current_grade', 'block_quiz_teacher_feedback');
            $maxgrade = html_writer::span($qa->format_max_mark(2), 'max');
            $notset = html_writer::span(
                get_string('feedback_info_current_grade_not_set', 'block_quiz_teacher_feedback'), 
                'notset'
            );
            
            // Формирование html-блока оценки
            $html .= html_writer::div(
                $title.' '.$currentgrade.$maxgrade.$notset,
                'grade '.$attributeclass
            );
        }
        
        // Данные для Ajax-обновления информации об отзыве преподавателя
        $qaid = $qa->get_database_id();
        $qubaid = $qa->get_usage_id();
        $slot = $qa->get_slot();
        $token = md5(sesskey().$qaid.$qubaid.$slot);
        
        // Обертка для секции отзыва с данными для периодического ajax-обновления данных
        $html = html_writer::div($html, 'qtc_feedback', 
            [
                'id' => 'qtc_'.$qaid,
                'data-qa' => $qaid,
                'data-qubaid' => $qubaid,
                'data-slot' => $slot,
                'data-token' => $token
            ]
        );
        
        return $html;
    }
}