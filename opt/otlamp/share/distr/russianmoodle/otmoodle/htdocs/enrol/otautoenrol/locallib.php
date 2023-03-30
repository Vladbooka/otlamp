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
 * Дополнительная библиотека плагинов
 *
 * @package    enrol
 * @subpackage otautoenrol
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Получить кастомные поля
 * @param string $shortname краткое имя кастомного поля
 */
function otautoenrol_get_customfields($shortname = '')
{
    global $DB;
    /**
     * @todo Сделать настройку по выбору списка типов кастомных полей
     */
    $fieldtypes = "'checkbox','datetime','menu','text','textarea'";
    $where = ' WHERE f.datatype IN (' . $fieldtypes . ') ';
    $params = [];
    if( ! empty($shortname) )
    {
        $where = 'AND f.shortname=? ';
        $params = [$shortname];
    }
    return $DB->get_records_sql("SELECT f.*
        FROM {user_info_field} f
        JOIN {user_info_category} c ON f.categoryid=c.id
        $where
        ORDER BY c.sortorder ASC, f.sortorder ASC", $params);
}

/**
 * Получить список стандартных полей доступных для использования
 * @return array
 */
function otautoenrol_get_userfields()
{
    return [
        'auth',
        'department',
        'institution',
        'lang',
        'email',
        'username',
        'firstname',
        'lastname',
        'idnumber',
        'icq',
        'skype',
        'yahoo',
        'aim',
        'msn',
        'phone1',
        'phone2',
        'address',
        'city',
        'country',
        'timezone',
        'lastip',
        'lastnamephonetic',
        'firstnamephonetic',
        'middlename',
        'alternatename',
    ];
}