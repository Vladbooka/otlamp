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
 * Модуль Логика курса. Процесс установки плагина.
 *
 * @package    mod_otcourselogic
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

// Подключение библитек
require_once($CFG->dirroot . '/mod/otcourselogic/lib.php');
require_once($CFG->dirroot . '/mod/otcourselogic/classes/otserial.php');

/**
 * Установка плагина в систему
 */
function xmldb_otcourselogic_install()
{
    global $DB, $OUTPUT;
    
    // Инициализация шлюза к OTAPI
    $otapi = new mod_otcourselogic\otserial();

    // Получение серийного ключа для экземпляра плагина
    $result = $otapi->issue_serial_and_get_data();
    
    if (isset($result['response']) && !empty($result['message']))
    {
        echo $OUTPUT->notification($result['message'], \core\output\notification::NOTIFY_SUCCESS);
        
    } else if(!isset($result['response']))
    {
        echo $OUTPUT->notification($result['message']??'Unknown error', \core\output\notification::NOTIFY_ERROR);
    }
    return true;
}