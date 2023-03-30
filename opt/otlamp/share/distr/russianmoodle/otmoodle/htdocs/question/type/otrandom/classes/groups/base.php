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
 * Тип вопроса Случайный вопрос с учетом правил. Базовый класс группы вопросов.
 *
 * Группа формирует набор из вопросов, которые подходят по условию группы. 
 * Все вопросы в группе получают повышенную вероятность попадания в тест. 
 * Вероятность зависит от веса, настраимаевого при добавлении случайного вопроса.
 * 
 * @package    qtype
 * @subpackage otrandom
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace qtype_otrandom\groups;

defined('MOODLE_INTERNAL') || die();

class base
{
    /**
     * Получить имя типа вопроса
     *
     * @return string
     */
    final public static function get_qtype_plugin_name()
    {
        return 'qtype_otrandom';
    }
    
    /**
     * Получить локализованное имя группы
     *
     * @return string
     */
    public static function get_local_name()
    {
        $class = get_called_class();
        return get_string(
            'group_'.$class::get_plugin_name().'_name', 
            self::get_qtype_plugin_name()
        );
    }
    
    /**
     * Получить локализованное описание группы
     *
     * @return string
     */
    public static function get_local_description()
    {
        $class = get_called_class();
        return get_string(
            'group_'.$class::get_plugin_name().'_description', 
            self::get_qtype_plugin_name()
        );
    }
    
    /**
     * Получить имя плагина группы
     *
     * @return string
     */
    public static function get_plugin_name()
    {
        // Уведомление для разработчиков
        debugging('You should include get_plugin_name method', DEBUG_DEVELOPER);
        
        return 'base';
    }
    
    /**
     * Получить вес по умолчанию для текущей группы
     *
     * @return int
     */
    public function get_default_weight()
    {
        return 1;
    }
    
    /**
     * Получить минимальный поддерживаемый вес для текущей группы
     *
     * @return int
     */
    public function get_min_weight()
    {
        return 0;
    }
    
    /**
     * Получить максимальный поддерживаемый вес для текущей группы
     *
     * @return int
     */
    public function get_max_weight()
    {
        return 10;
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
        return false;
    }
    
    /**
     * Получение среднего значения числа попаданий вопроса в тест
     * 
     * @param array $questionattempts - Попытки прохождения вопросов
     * 
     * @return int
     */
    protected function get_average_question_attempts($questionattempts)
    {
        // Общее количество попыток прохождения вопросов
        $qacount = count($questionattempts, COUNT_RECURSIVE) - count($questionattempts);
        
        // Вычисление среднего числа выпаданий вопроса в попытке пользователя
        if ( $qacount == 0 )
        {// Попыток нет
            $averageqa = 1;
        } else
        {
            // Среднее значение с округлением до большего целого
            $averageqa = ceil($qacount / count($questionattempts));
        }
        return $averageqa;
    }
}