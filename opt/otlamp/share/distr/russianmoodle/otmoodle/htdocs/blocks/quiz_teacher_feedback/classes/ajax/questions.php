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

namespace block_quiz_teacher_feedback\ajax;

defined('MOODLE_INTERNAL') || die();

// Подключение дополнительных библиотек
require_once($CFG->libdir . "/externallib.php");
require_once($CFG->dirroot . '/blocks/quiz_teacher_feedback/locallib.php');
require_once($CFG->dirroot . '/mod/quiz/locallib.php');
require_once($CFG->libdir.'/blocklib.php');

use context_system;
use external_api;
use external_function_parameters;
use external_value;
use question_engine;

class questions extends external_api
{
    
    /**
     * Returns description of method parameters
     * @return external_function_parameters
     */
    public static function get_questions_list_parameters()
    {
        // ID набора вопросов
        $data = new external_value(
            PARAM_RAW_TRIMMED,
            'data',
            VALUE_REQUIRED
        );
        
        $params = [
            'data' => $data
        ];
        
        return new external_function_parameters($params);
    }
    
    /**
     * Проверяет, изменились ли данные (AJAX) и возвращает результат изменений
     * 
     * @param string $data - закодированные в json данные, содержащие instance, quizid, students_info, groupid
     * 
     * @return NULL|string - результат изменений
     */
    public static function get_questions_list($data)
    {
        global $PAGE;
        
        // Лимит выполнения скрипта
        @set_time_limit(40);
        \core\session\manager::write_close();
        
        $PAGE->set_context(context_system::instance());
        
        $result = null;
        // Отформатируем данные
        $formated_data = json_decode($data);
        // ID - блока
        $instance = $formated_data->instance;
        // ID теста
        $quizid = $formated_data->quizid;
        
        // Инстанс блока
        $block = block_quiz_teacher_feedback_get_block($instance);
        
        if ( empty($block) && empty($quizid) )
        {
            $result['error'] = get_string('eror_ajax_invalid_param', 'block_quiz_teacher_feedback');
        }
        else
        {
            // Время слипа
            $sleeptime = $block->get_long_poll_sleep_time();
            $maxcycles = $block->get_long_poll_max_cycles();
            
            //Запускаем цикл проверки данных
            while ( ! $result = $block->is_changed($quizid, $formated_data) )
            {
                if($maxcycles <= 0)
                {
                    return null;
                }
                $maxcycles--;
                sleep($sleeptime);
            }
            
        }
        return json_encode($result);
    }
    
    /**
     * @return \external_value
     */
    public static function get_questions_list_returns()
    {
        return new external_value(PARAM_RAW, 'JSON-encoded feedback-data');
    }
}