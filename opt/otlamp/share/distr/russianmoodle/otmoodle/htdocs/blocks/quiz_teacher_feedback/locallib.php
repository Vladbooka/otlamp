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
 * Блок комментарий преподавателя. Библиотека дополнительных функцй блока.
 * 
 * @package    block
 * @subpackage block_quiz_teacher_feedback
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Получить комментарий преподавателя
 * 
 * Получение комментария преподавателя для указанной попытки прохождения вопроса
 * 
 * @param int $qaid - ID попытки прохождения вопроса
 * 
 * @return false|stdClass - Объект комментария или false
 */
function block_quiz_teacher_feedback_get_feedback($qaid)
{
    global $DB;
    
    // Получить комментарий
    return $DB->get_record('block_quiz_teacher_feedback', ['qaid' => (int)$qaid]);
}

/**
 * Сохранить комментарий преподавателя
 *
 * @param int $qaid - ID попытки прохождения вопроса
 * @param string $feedback - Комментарий по попытке
 * @param float|string|null $grade - Оценка по вопросу
 * @param string $feedbackformat - Формат комментария
 * @param bool - Флаг завершения ответа
 *
 * @return stdClass|false - Сохраненный комментарий или false в случае неуспешного сохранения
 */
function block_quiz_teacher_feedback_save_feedback(stdClass $record)
{
    global $DB;
    
    // Нормализация переданных данных
    $record->qaid = (int)$record->qaid;
    // Проверка на наличие обязательных данных
    if ( ! $record->qaid )
    {// Попытка прохождения вопроса не указана
        return false;
    }
    
    // Получение текущего комментария для попытки
    $currentfeedback = block_quiz_teacher_feedback_get_feedback($record->qaid);
    if ( $currentfeedback )
    {
        $record->id = $currentfeedback->id;
        if ( property_exists($record, 'qaid') && $record->qaid != $currentfeedback->qaid )
        {
            return false;
        }
    }
    
    if ( ! empty($record->feedback) )
    {
        $record->feedback = trim((string)$record->feedback);
        if ( empty($record->feedbackformat) )
        {
            $record->feedbackformat = FORMAT_HTML;
        }
    } elseif ( $currentfeedback )
    {
        $record->feedback = $currentfeedback->feedback;
        $record->feedbackformat = $currentfeedback->feedbackformat;
    } else
    {
        $record->feedback = '';
        $record->feedbackformat = FORMAT_HTML;
    }
    if ( property_exists($record, 'grade') )
    {
        if ( !strlen($record->grade) )
        {
            $record->grade = null;
        } else 
        {
            $record->grade = str_replace(',', '.', (string)$record->grade);
            $record->grade = (float)$record->grade;
        }
    } elseif ( $currentfeedback )
    {
        $record->grade = $currentfeedback->grade;
    }
    
    if ( property_exists($record, 'completed') )
    {
        $record->completed = (bool)$record->completed;
    } elseif ( $currentfeedback )
    {
        $record->completed = $currentfeedback->completed;
    }
    
    if ( property_exists($record, 'needsgrading') )
    {
        $record->needsgrading = (bool)$record->needsgrading;
    } else 
    {
        $record->needsgrading = 0;
    }
    
    // Подготовка базовых данных для сохранения попытки
    if ( ! empty($record->id) )
    {
        // Обновление комментария
        $result = $DB->update_record('block_quiz_teacher_feedback', $record);
        if ( ! $result )
        {// Сохранение прошло не успешно
            return false;
        }
    } else
    {// Комментарий не найден
        $id = $DB->insert_record('block_quiz_teacher_feedback', $record);
        if ( ! $id )
        {// Сохранение прошло не успешно
            return false;
        }
        $record->id = $id;
    }
    
    return $record;
}

/**
 * Получить инстанс блока
 * 
 * @param int $instanceid - ID инстанса блока
 * 
 * @return stdClass|false - Объект блока или false в случае неудачи
 */
function block_quiz_teacher_feedback_get_block($instanceid = 0)
{
    global $DB;
    
    if ( ! is_numeric($instanceid) || empty($instanceid) )
    {
        return false;
    }
    try 
    {
        $instance_block = $DB->get_record('block_instances', ['id' => $instanceid]); 
    } 
    catch ( Exception $e )
    {// Вернем false в случае ошибки
        return false;
    }
    if ( ! empty( $instance_block) )
    {
        return block_instance('quiz_teacher_feedback', $instance_block);
    }
}

