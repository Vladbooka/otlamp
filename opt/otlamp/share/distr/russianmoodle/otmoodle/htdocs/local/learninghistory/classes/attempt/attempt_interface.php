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
 * Интерфейс класса работы с попытками прохождения модулей курса
 * 
 * @package    local_learninghistory
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_learninghistory\attempt;

interface attempt_interface
{
    /**
     * Получить номер текущей попытки прохождения модуля курса
     */
    public function get_current_attemptnumber();
    
    /**
     * Получить номер последней попытки прохождения модуля курса
     */
    public function get_last_attemptnumber();
    
    /**
     * Получить возможный номер первой попытки прохождения модуля курса
     */
    public function get_possible_first_attemptnumber();
    
    /**
     * Получить номер попытки, во время которой произошло логирование
     * @param int $logtimecreated время создания лога
     * @return int|bool номер попытки или false, если лог добавлен между попытками
     */
    public function get_attempt_linked_log($logtimecreated);
}