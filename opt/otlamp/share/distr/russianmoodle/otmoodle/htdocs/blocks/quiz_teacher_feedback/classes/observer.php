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
 * Блок комментарий преподавателя. Обозреватель событий.
 * 
 * @package    block
 * @subpackage block_quiz_teacher_feedback
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace block_quiz_teacher_feedback;

require_once($CFG->dirroot.'/mod/quiz/locallib.php');
require_once($CFG->dirroot . '/blocks/quiz_teacher_feedback/locallib.php');

use context;
use quiz_attempt;
use context_module;

defined('MOODLE_INTERNAL') || die();

/**
 * Обработчик событий для block_quiz_teacher_feedback
 */
class observer 
{
    /**
     * Перехват события оценки попытки пользователя
     *
     * @param mod_quiz\event\attempt_submitted $event - Объект события
     * 
     * @return void
     */
    public static function attempt_submitted(\mod_quiz\event\attempt_submitted $event) 
    {
        global $CFG, $DB;
        
        // Получение данных события
        $data = $event->get_data();
        // Получение попытки прохождения теста
        $attemptobj = quiz_attempt::create($data['objectid']);
        // Получение слотов поптыки прохождения теста
        $slots = $attemptobj->get_slots();
        
        foreach ($slots as $slot)
        {
            // Получение попытки прохождения вопроса в слоте
            $question_attempt = $attemptobj->get_question_attempt($slot);
            
            // Поддержка ручного оценивания попытки прохождения вопроса
            $ismanualgraded = $question_attempt->get_question()->qtype->is_manual_graded();
            if ( $ismanualgraded )
            {   
                // Получение комментария преподавателя по указанной попытке
                $feedback = block_quiz_teacher_feedback_get_feedback($question_attempt->get_database_id());
                if ( $feedback )
                {
                    // Добавление оценки и комментария по вопросу
                    $question_attempt->manual_grade($feedback->feedback, $feedback->grade, $feedback->feedbackformat);
                }
            }
            
            // Генерация события ручного оценивания вопроса
            $params = [
                'objectid' => $attemptobj->get_question_attempt($slot)->get_question()->id,
                'courseid' => $attemptobj->get_courseid(),
                'context' => context_module::instance($attemptobj->get_cmid()),
                'other' => [
                    'quizid' => $attemptobj->get_quizid(),
                    'attemptid' => $attemptobj->get_attemptid(),
                    'slot' => $slot
                ]
            ];
            $event = \mod_quiz\event\question_manually_graded::create($params);
            $event->trigger();
        }
        
        // Инициализация сохранения состояния
        $attemptobj->process_auto_save(time());        

        $transaction = $DB->start_delegated_transaction();
        
        // Обновление оценок
        quiz_update_all_attempt_sumgrades($attemptobj->get_quiz());

        $transaction->allow_commit();
    }
}
