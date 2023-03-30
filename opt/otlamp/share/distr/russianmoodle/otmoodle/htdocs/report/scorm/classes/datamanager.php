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
 * Отчет по результатам SCORM. Обработчик данных отчета.
 *
 * @package    report
 * @subpackage scorm
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace report_scorm;

defined('MOODLE_INTERNAL') || die();

use stdClass;
use dml_exception;

class datamanager 
{
    /**
     * Получение опций отчета для указанного модуля курса
     * 
     * @param int $cmid - ID модуля курса SCORM
     * 
     * @return array
     */
    public function get_cm_options($cmid)
    {
        global $DB;
        
        // Получение опций работы
        $options = (array)$DB->get_records(
            'report_scorm_cmoptions', ['cmid' => (int)$cmid]);
        
        return $options;
    }
    
    /**
     * Получение опции отчета для указанного модуля курса
     *
     * @param int $cmid - ID модуля курса SCORM
     * @param string $name - Название опции
     *
     * @return string|null - Значение опции или null, если опция не найдена
     */
    public function get_cm_option($cmid, $name)
    {
        global $DB;
    
        // Нормализация данных
        $cmid = (int)$cmid;
        $name = (string)$name;
    
        // Валидация данных
        if ( $cmid < 1 )
        {// Молуль курса не указан
            return null;
        }
        if ( empty($name) )
        {// Имя опции не указано
            return null;
        }
    
        // Получение ранее сохраненной опции
        $option = $DB->get_record(
            'report_scorm_cmoptions', ['cmid' => $cmid, 'name' => $name], '*', IGNORE_MULTIPLE);
        if ( $option )
        {// Опция найдена
            return $option->value;
        }
        return null;
    }
    
    /**
     * Сохранение опции отчета для указанного модуля курса
     * 
     * @param int $cmid - ID модуля курса SCORM
     * @param string $name - Название опции
     * @param string $value - Значение опции
     * 
     * @return stdClass|null - Сохраненная опция или null, если сохранение не удалось
     */
    public function set_cm_option($cmid, $name, $value)
    {
        global $DB;
        
        // Нормализация данных
        $cmid = (int)$cmid;
        $name = (string)$name;
        $value = (string)$value;
        
        // Валидация данных
        if ( $cmid < 1 )
        {// Молуль курса не указан
            return null;
        }
        if ( empty($name) )
        {// Имя опции не указано
            return null;
        }
        
        // Получение ранее сохраненной опции
        $option = $DB->get_record(
            'report_scorm_cmoptions', ['cmid' => $cmid, 'name' => $name], '*', IGNORE_MULTIPLE);
        if ( $option )
        {// Обновление 
            $update = new stdClass();
            $update->id = $option->id;
            $update->value = $value;
            
            // Попытка сохранения данных
            try 
            {
                // Обновление записи
                $DB->update_record('report_scorm_cmoptions', $update);
                // Получение обновленной записи для результата работы
                $option = $DB->get_record(
                    'report_scorm_cmoptions', ['id' => $update->id]);
                // Результат сохранения
                if ( $option )
                {
                    return $option;
                }
            } catch ( dml_exception $e )
            {// Ошибка во время сохранения опции
                return null;
            }
        } else
        {// Создание опции
            $insert = new stdClass();
            $insert->cmid = $cmid;
            $insert->name = $name;
            $insert->value = $value;
            
            // Попытка сохранения данных
            try 
            {
                // Добавление записи
                $id = $DB->insert_record('report_scorm_cmoptions', $insert);
                // Получение добавленной записи для результата работы
                $option = $DB->get_record(
                    'report_scorm_cmoptions', ['id' => $id]);
                // Результат сохранения
                if ( $option )
                {
                    return $option;
                }
            } catch ( dml_exception $e )
            {// Ошибка во время сохранения опции
                return null;
            }
        }
        
        return null;
    }
    
    /**
     * Удаление опции отчета для указанного модуля курса
     *
     * @param int $cmid - ID модуля курса SCORM
     * @param string $name - Название опции
     * 
     * @return bool - Результат удаления опции
     */
    public function remove_cm_option($cmid, $name)
    {
        global $DB;
    
        // Нормализация
        $cmid = (int)$cmid;
        $name = (string)$name;

        // Попытка удаления опции
        try 
        {
            $DB->delete_records(
                'report_scorm_cmoptions', ['cmid' => $cmid, 'name' => $name]);
            return true;
        } catch ( dml_exception $e )
        {// Ошибка во время удаления опции
            return false;
        }
    }
    
    
}