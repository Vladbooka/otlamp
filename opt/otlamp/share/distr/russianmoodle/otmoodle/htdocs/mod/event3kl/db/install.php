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
 * event3kl plugin installation.
 *
 * @package    mod
 * @subpackage event3kl
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();
require_once($CFG->dirroot . '/mod/event3kl/classes/otserial.php');

function xmldb_event3kl_install()
{
    global $DB, $OUTPUT;
    // Делаем плагин видимым
    $DB->set_field('modules', 'visible', 1, ['name'=>'event3kl']);

    $otapi = new \mod_event3kl\otserial();

    $result = $otapi->issue_serial_and_get_data();
    if (isset($result['response'])) {
        // всё прошло хорошо
        if (!empty($result['message']))
        {
            echo $OUTPUT->notification($result['message'], \core\output\notification::NOTIFY_SUCCESS);
        }

        // делаем всё, что нужно делать после успешной регистрации
        ////////////////////////////////////////
        // Проверяем, можем ли активировать

        // Передан ли ключ?
        $opt = $result['response']->options;
        
        // Проверки...

        // Все проверки пройдены, включаем плагин
        $OUTPUT->notification(get_string('api_response_ok', 'mod_event3kl'), 'notifysuccess');
    } else {// что-то незаладилось
        echo $OUTPUT->notification($result['message']??'Unknown error', \core\output\notification::NOTIFY_ERROR);
    }
    return true;
}