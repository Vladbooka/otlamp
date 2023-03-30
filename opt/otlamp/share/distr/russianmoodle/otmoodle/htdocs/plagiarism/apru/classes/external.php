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
 * История обучения. Веб-сервисы
 *
 * @package    local_learninghistory
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


namespace plagiarism_apru;

defined('MOODLE_INTERNAL') || die();

require_once("$CFG->libdir/externallib.php");

use external_api;
use external_function_parameters;
use external_value;
use moodle_exception;
use context_course;
use context_module;
use html_writer;
use stdClass;

class external extends external_api
{
    /**
     * Returns description of method parameters
     * @return external_function_parameters
     */
    public static function add_to_index_parameters()
    {
        $id = new external_value(
            PARAM_INT,
            'Id-value for document in antiplagiat database',
            VALUE_REQUIRED
        );
        
        $params = [
            'id' => $id
        ];

        return new external_function_parameters($params);
    }
    
    /**
     * Добавление лога обновления времени изучения курса
     * @param int $userid
     * @param int $courseid
     * @param bool $addlog
     * @return boolean
     */
    public static function add_to_index($id)
    {
        global $DB;
        $result = false;
        $params = self::validate_parameters(self::add_to_index_parameters(), [
            'id' => $id
        ]);
        // Получение идентификатора модуля курса документа
        $document = $DB->get_record('plagiarism_apru_files', ['id' => $id]);
        if( empty($document) )
        {
            return json_encode(['result' => false, 'capability' => false]);
        }
        // Получим контекст модуля
        $context = context_module::instance($document->cm);
        // Проверка права на противоположное действие
        $candisable = has_capability('plagiarism/apru:disableindexstatus', $context);
        if( ! has_capability('plagiarism/apru:enableindexstatus', $context) )
        {// Нет прав на совершение запрошенного действия
            return json_encode(['result' => false, 'capability' => $candisable]);
        }
        // Открытие соединения с сервисом Антиплагиат
        $connection = new connection();
        if ( ! $connection->is_alive())
        {// Соединение отсутствует
            return json_encode(['result' => false, 'capability' => $candisable]);
        }
        // Смена статуса
        $externalid = $document->externalid;
        if ( ! $externalid )
        {// Документ не передан в сервис Антиплагиат
            return json_encode(['result' => false, 'capability' => $candisable]);
        }
        if ( ! $connection->set_indexed_status($externalid, true) )
        {// Ошибка во время смены статуса
            return json_encode(['result' => false, 'capability' => $candisable]);
        }
        // Обновление данных документа
        $update = new stdClass();
        $update->id = $document->id;
        $additional = $document->additional;
        if ( ! empty($additional) )
        {
            $additional = unserialize($additional);
        } else
        {
            $additional = [];
        }
        $additional['index_status'] = 1;
        $update->additional = serialize($additional);
        $DB->update_record('plagiarism_apru_files', $update);
        return json_encode(['result' => true, 'capability' => $candisable]);
    }

    /**
     * Returns description of method result value
     * @return external_description
     */
    public static function add_to_index_returns()
    {
        return new external_value(PARAM_RAW, 'HTML code of button "remove_from_index"');
    }
    
    /**
     * Returns description of method parameters
     * @return external_function_parameters
     */
    public static function remove_from_index_parameters()
    {
        $id = new external_value(
            PARAM_INT,
            'Id-value for document in antiplagiat database',
            VALUE_REQUIRED
        );
        
        $params = [
            'id' => $id
        ];

        return new external_function_parameters($params);
    }
    
    /**
     * Добавление лога обновления времени изучения курса
     * @param int $userid
     * @param int $courseid
     * @param bool $addlog
     * @return boolean
     */
    public static function remove_from_index($id)
    {
        global $DB;
        $result = false;
        $params = self::validate_parameters(self::remove_from_index_parameters(), [
            'id' => $id
        ]);
        // Получение идентификатора модуля курса документа
        $document = $DB->get_record('plagiarism_apru_files', ['id' => $id]);
        if( empty($document) )
        {
            return json_encode(['result' => false, 'capability' => false]);
        }
        // Получим контекст модуля
        $context = context_module::instance($document->cm);
        // Проверка права на противоположное действие
        $canenable = has_capability('plagiarism/apru:enableindexstatus', $context);
        if( ! has_capability('plagiarism/apru:disableindexstatus', $context) )
        {// Нет прав на совершение запрошенного действия
            return json_encode(['result' => false, 'capability' => $canenable]);
        }
        // Открытие соединения с сервисом Антиплагиат
        $connection = new connection();
        if ( ! $connection->is_alive())
        {// Соединение отсутствует
            return json_encode(['result' => false, 'capability' => $canenable]);
        }
        // Смена статуса
        $externalid = $document->externalid;
        if ( ! $externalid )
        {// Документ не передан в сервис Антиплагиат
            return json_encode(['result' => false, 'capability' => $canenable]);
        }
        if ( ! $connection->set_indexed_status($externalid, false) )
        {// Ошибка во время смены статуса
            return json_encode(['result' => false, 'capability' => $canenable]);
        }
        // Обновление данных документа
        $update = new stdClass();
        $update->id = $document->id;
        $additional = $document->additional;
        if ( ! empty($additional) )
        {
            $additional = unserialize($additional);
        } else
        {
            $additional = [];
        }
        $additional['index_status'] = 0;
        $update->additional = serialize($additional);
        $DB->update_record('plagiarism_apru_files', $update);
        return json_encode(['result' => true, 'capability' => $canenable]);
    }

    /**
     * Returns description of method result value
     * @return external_description
     */
    public static function remove_from_index_returns()
    {
        return new external_value(PARAM_RAW, 'HTML code of button "add_to_index"');
    }
}