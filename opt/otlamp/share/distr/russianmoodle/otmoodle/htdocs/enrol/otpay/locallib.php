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
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle. If not, see <http://www.gnu.org/licenses/>.

/**
 * Плагин записи на курс OTPAY. Дополнительная библиотека плагина.
 *
 * @package    enrol
 * @subpackage otpay
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Подключает доплнительные языковые строки, необходимые для работы кастомных сценариев
 * @param string $lang The langauge to use when processing the string
 * @param array $string указатель на основной массив языковых строк
 */
function enrol_otpay_include_custom_scenario_language_strings($lang, & $string) {
    global $CFG;
    if (empty($lang)) {
        return;
    }
    // Все кастомные языковые строки кладем в директорию сценария, по аналогии со стандартными кастомными строками
    $basedir = $CFG->dirroot . '/enrol/otpay/plugins/accountgenerate/scenarios';
    // Поиск сценариев
    foreach ((array)scandir($basedir) as $scenarioname) {
        if ($scenarioname == '.' || $scenarioname == '..') {
            continue;
        }
        if (is_dir($basedir . '/' . $scenarioname) ) {
            // Папка сценария
            // Директория с подключаемыми языковыми строками
            $customlangdir = $basedir . '/' . $scenarioname . '/lang';
            if (is_dir($customlangdir)) {
                // Директория найдена
                if (is_dir($customlangdir . '/' . $lang)) {
                    // Дополнительный языковой пакет имеется
                    // Файл пакета
                    $langpack = $customlangdir . '/' . $lang . '/accountgenerate_scenario_' . $scenarioname . '.php';
                    if (file_exists($langpack)) {
                        // Файл пакета найден
                        include($langpack);
                    }
                }
            }
        }
    }
}