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
 * Плагин определения заимствований Антиплагиат.
* Задача массового обновления данных документов в таблице plagiarism_apru_files
*
* @package    plagiarism
* @subpackage apru
* @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
*/

namespace plagiarism_apru\task;

use plagiarism_apru\connection;
use stdClass;
use moodle_exception;
use plagiarism_apru\settings_form;
use html_writer;

defined('MOODLE_INTERNAL') || die();

class update_documents extends \core\task\scheduled_task
{
    /**
     * Объект для связи с сервером Антиплагиата
     * @var connection
     */
    protected $connection;

    /**
     * Получить имя события
     * @return string
     */
    public function get_name()
    {
        return get_string('task_update_documents_title', 'plagiarism_apru');
    }

    /**
     * Исполнение задачи
     */
    public function execute()
    {
        try
        {// Установка соединения с сервером Антиплагиата
            $this->connection = new connection();
        } catch (moodle_exception $ex)
        {// Ошибка соединения с сервером Антиплагиата
            /**
             * @todo Логирование ошибок
             */
            mtrace($ex->debuginfo);
        }
        
        if ( ! empty($this->connection) )
        {// Соединение с сервером Антиплагиата установлено
            $this->update_data($this->connection);
        }
    }
    
    /**
     * Синхронизация данных
     * @param connection $connection объект для связи с сервером Антиплагиата
     */
    protected function update_data($connection)
    {
        $this->update_reporturls($connection);
    }
    
    /**
     * Синхронизация ссылок на отчет о проверке документа
     * @param connection $connection объект для связи с сервером Антиплагиата
     */
    protected function update_reporturls($connection)
    {
        global $DB;
        
        // Получение настройки количества документов, отправляемых на синхронизацию за 1 раз
        $configsettings = settings_form::get_settings();
        if( empty($configsettings['docs_for_update']) )
        {
            $docsforupdate = 25;
        } else
        {
            $docsforupdate = $configsettings['docs_for_update'];
        }
        
        //Выберем только те записи, где указан externalid и reporturl
        $select = 'externalid IS NOT NULL AND reporturl IS NOT NULL';
        $fields = 'id, externalid, reporturl';
        $limitnum = $docsforupdate;
        $sort = 'lastmodified ASC';
        $docs = $DB->get_records_select('plagiarism_apru_files', $select, [], $sort, $fields, 0, $limitnum);
        
        if( ! empty($docs) )
        {
            foreach($docs as $doc)
            {
                try
                {// Получим данные по документу
                    $result[$doc->externalid] = $connection->get_check_status($doc->externalid);
                } catch(moodle_exception $ex)
                {
                    /**
                     * @todo Логирование ошибок
                     */
                    mtrace($ex->debuginfo);
                }
                if( ! empty($result[$doc->externalid]->reporturl) && $result[$doc->externalid]->reporturl != $doc->reporturl )
                {// Синхронизируем только в том случае, если были изменения
                    $doc->reporturl = $result[$doc->externalid]->reporturl;
                    $doc->lastmodified = time();
                    $DB->update_record('plagiarism_apru_files', $doc);
                    // Выводим информацию о синхронизации
                    $a = new stdClass();
                    $a->id = $doc->externalid;
                    $a->reporturl = html_writer::link($result[$doc->externalid]->reporturl, $result[$doc->externalid]->reporturl);
                    mtrace(get_string('apru_update_reporturl', 'plagiarism_apru', $a));
                }
            }
        }
    }
}
