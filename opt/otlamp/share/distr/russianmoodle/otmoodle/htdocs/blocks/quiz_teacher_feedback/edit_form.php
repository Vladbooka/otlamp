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
 * Блок комментарий преподавателя. Настройки экземпляра блока.
 * 
 * @package    block
 * @subpackage block_quiz_teacher_feedback
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

class block_quiz_teacher_feedback_edit_form extends block_edit_form 
{
    /**
     * Объявление дополнительных полей конфигурации экземпляра блока
     * 
     * @return void
     */
    protected function specific_definition($mform) 
    {
        // ID quiz
        $id = optional_param('id',0, PARAM_INT);
        $cmid = optional_param('cmid',0, PARAM_INT);
        
        // Заголовок настроек
        $mform->addElement(
                'header', 
                'config_header', 
                get_string('config_header', 'block_quiz_teacher_feedback')
        );
        
        // режим отправки ответа на проверку (вопросы, оцениваемые вручную)
        $select = [
            block_quiz_teacher_feedback::REQUEST_MODE_IMMIDIATELY => get_string('config_request_mode_immidiately', 'block_quiz_teacher_feedback'),
            block_quiz_teacher_feedback::REQUEST_MODE_CHOOSE_DEFAULT_YES => get_string('config_request_mode_choose_default_yes', 'block_quiz_teacher_feedback'),
            block_quiz_teacher_feedback::REQUEST_MODE_CHOOSE_DEFAULT_NO=> get_string('config_request_mode_choose_default_no', 'block_quiz_teacher_feedback'),
        ];
        $mform->addElement(
                'select',
                'config_request_mode',
                get_string('config_request_mode', 'block_quiz_teacher_feedback'),
                $select,
                'chooseyes'
                );
        $mform->setType('config_request_mode', PARAM_INT);
        
        // перенос галка в форму отправки ответа
        $select = [
            0 => get_string('no', 'block_quiz_teacher_feedback'),
            1 => get_string('yes', 'block_quiz_teacher_feedback'),
        ];
        $mform->addElement(
                'select',
                'config_replace_checkbox',
                get_string('config_replace_checkbox', 'block_quiz_teacher_feedback'),
                $select,
                'chooseyes'
                );
        $mform->setType('config_replace_checkbox', PARAM_INT);
        
        // Проверяем, пришли ли верные данные и показывает вкладку контроля вопросов
        $quiz_instance = 0;
        if ( ! empty($id) )
        {
            $quiz_instance = $id;
        }
        elseif ( ! empty($cmid) )
        {
            $quiz_instance = $cmid;
        }
        // Выведем вкладку контроля вопросов
        if ( ! empty($quiz_instance) )
        {
            // Получим модуль курса
            $cm = get_coursemodule_from_id('quiz', $quiz_instance);
            // Получим все вопросы теста
            if ( ! empty($cm) && ! empty($cm->instance) )
            {
                // Получим объект quiz
                $quiz = quiz::create($cm->instance);
                // Загрузим вопросы
                $quiz->preload_questions();
                $quiz->load_questions();
                // Получим вопросы
                $questions = $quiz->get_questions();
                $count = count($questions);
                if ( $count > 0 )
                {// Если вопросы в тесте есть, отобразим селекты
                    $options = ['0' => get_string('feedback_form_control_off', 'block_quiz_teacher_feedback'), 
                                    '1' => get_string('feedback_form_control_on', 'block_quiz_teacher_feedback')];
                    // Заголовок настроек контроля вопросов
                    $mform->addElement(
                            'header',
                            'config_control_questions',
                            get_string('feedback_form_control_questions', 'block_quiz_teacher_feedback')
                            );
                    // Контроль прохождения теста пользователем
                    $select = [
                        0 => get_string('config_user_attempt_control_disable', 'block_quiz_teacher_feedback'),
                        1 => get_string('config_user_attempt_control_enable', 'block_quiz_teacher_feedback')
                    ];
                    $mform->addElement(
                            'select',
                            'config_user_attempt_control',
                            get_string('config_user_attempt_control_title', 'block_quiz_teacher_feedback'),
                            $select,
                            0
                            );
                    $mform->setType('config_user_attempt_control', PARAM_INT);
                    $mform->addHelpButton(
                            'config_user_attempt_control',
                            'config_user_attempt_control_title_help',
                            'block_quiz_teacher_feedback'
                            );
                    for ( $i = 1; $i <= $count; $i++)
                    {
                        $mform->addElement(
                                'select',
                                'config_question_control_id_' . $i,
                                get_string('config_question_slot', 'block_quiz_teacher_feedback') . $i,
                                $options,
                                0
                                );
                    }
                }
            }
        }
    }
}