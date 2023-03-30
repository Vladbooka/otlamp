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
 * Блок списка курсов в виде отдельных блоков. Настройки.
 * 
 * @package    local
 * @subpackage crw
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

if ($ADMIN->fulltree) 
{
// Заголовок - Настройки блока курсов
    $name = 'crw_courses_list_sections/settingspage_title';
    $title = get_string('settingspage_title','crw_courses_list_sections');
    $description = get_string('settingspage_title_desc','crw_courses_list_sections');
    $setting = new admin_setting_heading($name, $title, $description);
    $settings->add($setting);

    // Скрытие заголовка
    $choices = [
        0 => get_string('no', 'local_crw'),
        1 => get_string('yes', 'local_crw')
    ];
    $name = 'crw_courses_list_sections/hide_course_block_title';
    $title = get_string('settings_hide_course_block_title_title','crw_courses_list_sections');
    $description = get_string('settings_hide_course_block_title_desc','crw_courses_list_sections');
    $default = 0;
    $setting = new admin_setting_configselect($name, $title, $description, $default, $choices);
    $settings->add($setting);

    // Отображать кнопку Подробнее
    $choices = array(
        0 => get_string('no', 'local_crw'),
        1 => get_string('yes', 'local_crw')
    );
    $settings->add(new admin_setting_configselect(
            'crw_courses_list_sections/settings_display_more',
            get_string('settings_display_more','crw_courses_list_sections'),
            get_string('settings_display_more_desc','crw_courses_list_sections'),
            1,
            $choices
        )
    );
}