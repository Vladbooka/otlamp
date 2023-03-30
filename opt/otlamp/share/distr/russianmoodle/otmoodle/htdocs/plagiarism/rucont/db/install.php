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
 * Плагин определения заимствований Руконтекст. Установка.
 *
 * @package    plagiarism
 * @subpackage rucont
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();
// Подключение библиотек
require_once($CFG->dirroot . '/plagiarism/rucont/classes/otserial.php');

/**
 * Действия при установки плагина
 * 
 * @return boolean - Результат установки
 */
function xmldb_plagiarism_rucont_install()
{
    global $OUTPUT;
    
    // Получение API OpenTechnology
    $otapi = new plagiarism_rucont\otserial();
    // Получить серийный ключ продукта
    $result = $otapi->issue_serial_and_get_data();
    if (isset($result['response']) && !empty($result['message']))
    {
        echo $OUTPUT->notification($result['message'], \core\output\notification::NOTIFY_SUCCESS);
        
    } else if(!isset($result['response']))
    {
        echo $OUTPUT->notification($result['message']??'Unknown error', \core\output\notification::NOTIFY_ERROR);
    }
    // В любом случае - успешная установка
    return true;
}