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
 * Тип вопроса Случайный вопрос с учетом правил. Группа вопросов с последним неправильным ответом.
 *
 * Группа формирует набор из вопросов, которые подходят по условию группы. 
 * Все вопросы в группе получают повышенную вероятность попадания в тест. 
 * Вероятность зависит от веса, настраимаевого при добавлении случайного вопроса.
 * 
 * @package    qtype
 * @subpackage otrandom
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace qtype_otrandom\groups\lastfailed;

defined('MOODLE_INTERNAL') || die();

use qtype_otrandom\groups\base;
use question_engine;

class lastfailed extends base
{
    /**
     * Получить имя плагина группы
     *
     * @return string
     */
    public static function get_plugin_name()
    {
        return 'lastfailed';
    }
    
    /**
     * Определение валидности вопроса
     * 
     * @param int $questionid - ID вопроса
     * @param array $questionattempts - Попытки прохождения вопросов
     * 
     * @return bool
     */
    public function question_is_valid($questionid, $questionattempts) 
    {
        if ( empty($questionattempts[(int)$questionid]) )
        {// Попытки прохождения вопроса не найдены
            return false;
        }
        
        // Получение последней попытки прохождения вопроса
        $qarecord = array_shift($questionattempts[(int)$questionid]);
        
        
        // Получение набора, в котором участвовала указанная попытка
        $quba = question_engine::load_questions_usage_by_activity($qarecord->questionusageid);
            
        // Инициализация попытки
        $qa = $quba->get_question_attempt($qarecord->slot);
            
        // Получить состояние попытки
        $state = $qa->get_state();

        // Проверка состояния попытки
        // Состояние вопроса mangrwrong почему-то не считается, как неправильно отвеченное
        if ( $state->is_graded() && ( $state->is_incorrect() || (string)$state === 'mangrwrong') )
        {// Ответ на вопрос - неправильный
            return true;
        }

        return false;
    }
}