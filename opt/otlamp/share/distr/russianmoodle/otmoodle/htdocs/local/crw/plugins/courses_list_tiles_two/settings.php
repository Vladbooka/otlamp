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
 * Блок списка курсов в виде плиток. Настройки.
 * 
 * @package    local
 * @subpackage crw
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

if ($ADMIN->fulltree) 
{
// Заголовок - Настройки блока курсов
    $name = 'crw_courses_list_tiles_two/settingspage_title';
    $title = get_string('settingspage_title','crw_courses_list_tiles_two');
    $description = get_string('settingspage_title_desc','crw_courses_list_tiles_two');
    $setting = new admin_setting_heading($name, $title, $description);
    $settings->add($setting);

    // Число плиток курсов в одной строке
    $choices = [
            1 => 'xxx-large',
            2 => 'xx-large',
            3 => 'x-large',
            4 => 'large',
            5 => 'medium',
            6 => 'small',
            7 => 'x-small',
            8 => 'xx-small',
    ];
    $settings->add(new admin_setting_configselect(
            'crw_courses_list_tiles_two/course_in_line',
            get_string('settings_course_in_line','crw_courses_list_tiles_two'),
            get_string('settings_course_in_line_desc','crw_courses_list_tiles_two'),
            4,
            $choices
        )
    );
    
    // Отображать дату у курса
    $choices = array(
        0 => get_string('no', 'local_crw'),
        1 => get_string('yes', 'local_crw'),
        2 => get_string('settings_courses_showdate_courseconfig', 'crw_courses_list_tiles_two')
    );
    $settings->add(new admin_setting_configselect(
            'crw_courses_list_tiles_two/settings_courses_showdate',
            get_string('settings_courses_showdate','crw_courses_list_tiles_two'),
            get_string('settings_courses_showdate_desc','crw_courses_list_tiles_two'),
            1,
            $choices
        )
    );
}
