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
 * Тип вопроса Случайный вопрос с учетом правил. Группа вопросов, 
 * которые еще не были показаны пользователю.
 *
 * Группа формирует набор из вопросов, которые подходят по условию группы. 
 * Все вопросы в группе получают повышенную вероятность попадания в тест. 
 * Вероятность зависит от веса, настраимаевого при добавлении случайного вопроса.
 * 
 * @package    qtype
 * @subpackage otrandom
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace qtype_otrandom\groups\lessused;

defined('MOODLE_INTERNAL') || die();

use qtype_otrandom\groups\base;

class lessused extends base
{
    /**
     * Получить имя плагина группы
     *
     * @return string
     */
    public static function get_plugin_name()
    {
        return 'lessused';
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
            return true;
        }
        
        // Среднее количество показа вопросов из категории
        $aerageqa = $this->get_average_question_attempts($questionattempts);
        
        if ( count($questionattempts[(int)$questionid]) < $aerageqa )
        {// Вопрос редко попадался пользователю
            return true;
        }
        
        return false;
    }
}