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
 * Activetime report. Settings.
 *
 * @package    report_activetime
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

/**
 *
 * Получить список настраиваемых полей пользователей
 *
 * @return array
 */
function report_activetime_get_customfields_list()
{
    global $DB;

    $customfields = [];
    if ($profilefields = $DB->get_records('user_info_field', null, 'sortorder ASC') )
    {
        foreach ($profilefields as $profilefield)
        {
            $customfields['profile_field_'.$profilefield->shortname] = get_string('custom_field','report_activetime',$profilefield->name);
        }
    }
    return $customfields;
}

/**
 * Получить список стандартных обрабатываемых полей пользователей
 *
 * @return array
 */
function report_activetime_get_userfields_list($addfields=[], $withcustomlangs = true)
{
    $userfields = [];
    $profilefields = [
        'username',
        'email',
        'firstname',
        'lastname',
        'idnumber',
        'institution',
        'department',
        'phone1',
        'phone2',
        'city',
        'url',
        'icq',
        'skype',
        'aim',
        'yahoo',
        'msn',
        'country'
    ];
    if( $withcustomlangs )
    {
        foreach(array_merge($profilefields, $addfields) as $k => $v)
        {
            if( is_number($k) )
            {
                $userfields[$v] = get_user_field_name($v);
            } else
            {
                $userfields[$k] = $v;
            }
        }
        return $userfields;
    } else 
    {
        return array_merge($profilefields, $addfields);
    }
}