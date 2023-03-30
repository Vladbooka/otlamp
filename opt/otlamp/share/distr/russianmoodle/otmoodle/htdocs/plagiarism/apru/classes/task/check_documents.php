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
 * Задача массовой постановки Документов в на проверку в систему Антиплагиат
 *
 * @package    plagiarism
 * @subpackage apru
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace plagiarism_apru\task;
use moodle_exception;
use plagiarism_apru\settings_form;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot.'/plagiarism/apru/lib.php');

class check_documents extends \core\task\scheduled_task 
{
    /**
     * Получить имя события
     *
     * @return string
     */
    public function get_name() 
    {
        return get_string('task_check_documents_title', 'plagiarism_apru');
    }

    /**
     * Исполнение задачи
     */
    public function execute() 
    {
        global $DB;

        // Получение настройки количества документов, отправляемых в Антиплагиат на проверку за 1 раз
        $configsettings = settings_form::get_settings();
        if( empty($configsettings['docs_for_check']) )
        {
            $docsforcheck = 25;
        } else 
        {
            $docsforcheck = $configsettings['docs_for_check'];
        }

        $params = ['uploaded', 'notchecked'];
        $select = ' statuscode = ? OR statuscode = ? AND ';
        $select .= $DB->sql_isnotempty('plagiarism_apru_files', 'externalid', TRUE, TRUE);
        $tocheck = $DB->get_records_select('plagiarism_apru_files', $select, $params, 'lastmodified ASC', '*', 0, $docsforcheck);
        if ( ! empty($tocheck) )
        {// Постановка документов на проверку
            
            // Получение плагина
            $plugin = new \plagiarism_plugin_apru();
            foreach ( $tocheck as $documentid => $document )
            {
                // Попытка постановки файла на проверку
                try 
                {
                    $plugin->check_file($documentid);
                } catch ( moodle_exception $e )
                {
                    // @TODO - Логирование ошибок
                    continue;
                }
            }
        }
    }
}
