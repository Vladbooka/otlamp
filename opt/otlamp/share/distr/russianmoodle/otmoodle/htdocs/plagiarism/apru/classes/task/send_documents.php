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
 * Задача массовой загрузки Документов в систему Антиплагиат
 *
 * @package    plagiarism
 * @subpackage apru
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace plagiarism_apru\task;

use moodle_exception;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot.'/plagiarism/apru/lib.php');

class send_documents extends \core\task\scheduled_task 
{
    /**
     * Получить имя события
     *
     * @return string
     */
    public function get_name() 
    {
        return get_string('task_send_documents_title', 'plagiarism_apru');
    }

    /**
     * Исполнение задачи
     */
    public function execute() 
    {
        global $DB;

        // Получение последних 25 требующих загрузки документов
        $params = ['externalid' => NULL, 'statuscode' => 'not_upload', 'submissiontype' => 'file'];
        $notuploaded = $DB->get_records('plagiarism_apru_files', $params, 'lastmodified ASC', '*', 0, 25);
        if ( ! empty($notuploaded) )
        {// Загрузка документов в систему антиплагиат
            
            // Получение плагина
            $plugin = new \plagiarism_plugin_apru();
            foreach ( $notuploaded as $documentid => $document )
            {
                // Попытка загрузки файла в сервис Антиплагиат
                try 
                {
                    if ($externalid = $plugin->upload_file($documentid)) {
                        mtrace(get_string('upload_successful', 'plagiarism_apru', $externalid));
                    } else {
                        mtrace(get_string('upload_failed', 'plagiarism_apru', $documentid));
                    }
                } catch ( moodle_exception $e )
                {// Ошибка отправки документа в сервис
                    // @TODO - Логирование ошибок
                    continue;
                }
            }
        }
    }
}
