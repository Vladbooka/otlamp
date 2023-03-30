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
 * Плагин формата курсов OpenTechnology. Страница AJAX установки параметров разделов для пользователя.
 *
 * @package    format
 * @subpackage opentechnology
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(dirname(__FILE__) . '/../../../config.php');

// Проверка доступа
if ( ! confirm_sesskey() ) 
{
    print_error('invalidsesskey');
}

// Получить имя настройки для обновления
$name = required_param('pref', PARAM_RAW);
if ( ! isset($USER->ajax_updatable_user_prefs[$name]) ) 
{
    print_error('notallowedtoupdateprefremotely');
}

// Получение значения настройки
$value = required_param('value', PARAM_ALPHANUM);

if ($value) 
{
    if ( ! set_user_preference($name, $value) ) 
    {
        print_error('errorsettinguserpref');
    }
    echo 'OK';
} else {
    header('HTTP/1.1 406 Not Acceptable');
    echo 'Not Acceptable';
}
