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
 * Блок Информация, настройки
 *
 * @package    block_myinfo
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

require_once($CFG->dirroot . '/blocks/myinfo/locallib.php');

if ($ADMIN->fulltree) 
{
    // Отображение заголовка блока
    $settings->add(
        new admin_setting_configcheckbox(
            'block_myinfo/display_header',
            get_string('config_display_header', 'block_myinfo'),
            get_string('config_display_header_desc', 'block_myinfo'),
            1
        )
    );

    // Настройка отображаемых блоком полей
    $customfields = get_customfields_list();
    $userfields = get_userfields_list();
    $settings->add(
        new admin_setting_configmultiselect(
            'block_myinfo/displayfields',
            get_string('config_displayfields', 'block_myinfo'),
            get_string('config_displayfields_desc', 'block_myinfo'),
            [],
            array_merge($userfields, $customfields)
        )
    );
}
