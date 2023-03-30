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
 * Блок комментарий преподавателя. Класс блока.
 * 
 * @package    block
 * @subpackage block_quiz_teacher_feedback
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

define('AJAX_SCRIPT', true);

require('../../config.php');

// Подключение дополнительных библиотек
require_once($CFG->dirroot . '/blocks/quiz_teacher_feedback/locallib.php');
require_once($CFG->dirroot . '/mod/quiz/locallib.php');
require_once($CFG->libdir.'/blocklib.php');

// ПАРАМЕТРЫ ЗАПРОСА
// ID набора вопросов
$qubaid = required_param('quba', PARAM_INT);
// Номер слота в наборе
$slot = required_param('slot', PARAM_INT);
// Токен доступа
$token = required_param('token', PARAM_RAW_TRIMMED);
// ID - блока
$instance = required_param('instance', PARAM_INT);
// Требуется авторизация в системе
require_login();

$data = [];
// Получим конфиг блока
$block = block_quiz_teacher_feedback_get_block($instance);
// Получение набора вопросов
$quba = question_engine::load_questions_usage_by_activity($qubaid);
if ( empty($quba) && empty($block) )
{
    $data['error'] = get_string('eror_ajax_invalid_param', 'block_quiz_teacher_feedback');
} 
// Получение попытки прохождения вопроса пользователем
$qa = $quba->get_question_attempt($slot);

if ( empty($qa) )
{
    $data['error'] = get_string('eror_ajax_invalid_param', 'block_quiz_teacher_feedback');
} elseif ( $block->is_slot_should_be_graded($qa)  )
{
    // Получение ID попытки прохождения вопроса
    $qaid = (int)$qa->get_database_id();
    // Сравнение подписей запроса
    $currenttoken = md5(sesskey().$qaid.$qubaid.$slot);
    if ( $token !== $currenttoken )
    {// Подписи не совпадают
        $data['error'] = get_string('eror_ajax_invalid_token', 'block_quiz_teacher_feedback');
    } else 
    {
        // Получение комментария по вопросу
        $feedback = block_quiz_teacher_feedback_get_feedback($qaid);
        $data['feedback'] = '';
        $data['grade'] = '';
        $data['completedstring'] = get_string('feedback_info_current_notcompleted', 'block_quiz_teacher_feedback');
        if ( ! empty($feedback->feedback) )
        {// Добавление комментария по вопросу
            $data['feedback'] = $feedback->feedback;
        }
        if ( isset($feedback->grade) && $feedback->grade !== null )
        {// Добавление оценки по вопросу
            $data['grade'] = format_float($feedback->grade, 2, true, true);
        }
        if ( ! empty($feedback->completed) )
        {// Добавление строки о завершении вопроса
            $data['completedstring'] = get_string('feedback_info_current_completed', 'block_quiz_teacher_feedback');
        }
        // Проверим, что все элементы под контролем оценены
        $questions = $quba->get_slots();
        if ( ! empty($questions) )
        {
            // Проверим статус, что преподаватель подтвердил все вопросы студента
            $questions_config = $block->get_formated_config_questions();
            $completeall = 'complete';
            if ( empty($questions_config) )
            {
                $completeall = 'incomplete';
            }
            else
            {
                foreach ($questions as $question)
                {
                    $qa = $quba->get_question_attempt($question);
                    $slot = $qa->get_slot();
                    $feedback = block_quiz_teacher_feedback_get_feedback($qa->get_database_id());
                    if ( isset($questions_config[$slot])
                         && ! empty($questions_config[$slot])
                         && empty($feedback->completed)
                         && ! empty($qa->get_question()->length))
                    {
                        $completeall = 'incomplete';
                        break;
                    }
                }
                $data['completeall'] = $completeall;
            }
        }
    }
}
echo json_encode($data);
die;