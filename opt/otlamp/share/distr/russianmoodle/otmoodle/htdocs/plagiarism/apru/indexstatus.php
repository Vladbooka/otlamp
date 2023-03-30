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
 * Плагин определения заимствований Антиплагиат. Страница смены статуса у документа.
 *
 * @package    plagiarism
 * @subpackage apru
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require('../../config.php');
require_once('lib.php');

use plagiarism_apru\connection;

// Получение действия
$action = required_param('do', PARAM_ALPHA);

// Получение url возврата
$url = required_param('returnurl', PARAM_RAW);
$url = new moodle_url($url);

if ( ! confirm_sesskey() )
{// Ошибка проверки сессии
    redirect($url);
}

switch ( $action )
{
    case 'change' :
        // Получение идентификатора
        $id = required_param('docid', PARAM_INT);
        $status = required_param('status', PARAM_INT);
        
        // Получение идентификатора модуля курса документа
        $document = $DB->get_record('plagiarism_apru_files', ['id' => $id]);
        if ( empty($document) )
        {// Документ не найден
            redirect($url, get_string('error_document_not_found', 'plagiarism_apru'));
        }
        
        // Проверка прав доступа
        $context = context_module::instance((int)$document->cm);
        if ( empty($status) )
        {// Изьятие из индекса
            if ( ! has_capability('plagiarism/apru:disableindexstatus', $context) )
            {
                redirect($url, get_string('error_access_disableindexstatus_denied', 'plagiarism_apru'));
            }
        } else
        {// Добавление в индекс
            if ( ! has_capability('plagiarism/apru:enableindexstatus', $context) )
            {
                redirect($url, get_string('error_access_enableindexstatus_denied', 'plagiarism_apru'));
            }
        }
        
        // Открытие соединения с сервисом Антиплагиат
        $connection = new connection();
        if ( ! $connection->is_alive())
        {// Соединение отсутствует
            redirect($url, get_string('noconnection', 'plagiarism_apru'));
        }
        
        // Смена статуса
        $externalid = $document->externalid;
        if ( ! $externalid )
        {// Документ не передан в сервис Антиплагиат
            redirect($url, get_string('error_document_externalid_not_set', 'plagiarism_apru'));
        }
        if ( $connection->set_indexed_status($externalid, (bool)$status) )
        {// Смена статуса прошла успешно
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
            $additional['index_status'] = (int)$status;
            $update->additional = serialize($additional);
            $DB->update_record('plagiarism_apru_files', $update);
            redirect($url);
        } else 
        {// Ошибка во время смены статуса
            redirect($url, get_string('error_document_index_status_not_changed', 'plagiarism_apru'));
        }
        break;
}
die;