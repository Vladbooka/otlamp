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
    $name = 'crw_courses_list_tiles/settingspage_title';
    $title = get_string('settingspage_title','crw_courses_list_tiles');
    $description = get_string('settingspage_title_desc','crw_courses_list_tiles');
    $setting = new admin_setting_heading($name, $title, $description);
    $settings->add($setting);

    // Скрытие заголовка
    $choices = [
        0 => get_string('no', 'local_crw'),
        1 => get_string('yes', 'local_crw')
    ];
    $name = 'crw_courses_list_tiles/hide_course_block_title';
    $title = get_string('settings_hide_course_block_title_title','crw_courses_list_tiles');
    $description = get_string('settings_hide_course_block_title_desc','crw_courses_list_tiles');
    $default = 0;
    $setting = new admin_setting_configselect($name, $title, $description, $default, $choices);
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
            'crw_courses_list_tiles/course_in_line',
            get_string('settings_course_in_line','crw_courses_list_tiles'),
            get_string('settings_course_in_line_desc','crw_courses_list_tiles'),
            4,
            $choices
        )
    );
    
    // Отображать категорию у курса
    $choices = array(
        0 => get_string('no', 'local_crw'),
        1 => get_string('yes', 'local_crw'),
        2 => get_string('settings_courses_showcategory_courseconfig', 'crw_courses_list_tiles')
    );
    $settings->add(new admin_setting_configselect(
            'crw_courses_list_tiles/settings_courses_showcategory',
            get_string('settings_courses_showcategory','crw_courses_list_tiles'),
            get_string('settings_courses_showcategory_desc','crw_courses_list_tiles'),
            1,
            $choices
    )
    );

    //где отображать доп.инфо по курсу
    $choices = array(
        0 => get_string('settings_courseinfo_place_on_top_of_image','crw_courses_list_tiles'),
        1 => get_string('settings_courseinfo_place_below_image','crw_courses_list_tiles')
    );
    $settings->add(
        new admin_setting_configselect('crw_courses_list_tiles/courseinfo_place',
            get_string('settings_courseinfo_place', 'crw_courses_list_tiles'),
            get_string('settings_courseinfo_place_desc', 'crw_courses_list_tiles'), 0, $choices));
    
    //Что является ссылкой
    $choices = array(
        0 => get_string('settings_link_holder_tile','crw_courses_list_tiles'),
        1 => get_string('settings_link_holder_button','crw_courses_list_tiles'),
        2 => get_string('settings_link_holder_pic_and_button','crw_courses_list_tiles'),
        3 => get_string('settings_link_holder_pic_coursename','crw_courses_list_tiles')
    );
    $settings->add(
        new admin_setting_configselect('crw_courses_list_tiles/link_holder',
            get_string('settings_link_holder', 'crw_courses_list_tiles'),
            get_string('settings_link_holder_desc', 'crw_courses_list_tiles'), 0, $choices));
    
    //Отображать с рамкой
    $settings->add(
        new admin_setting_configcheckbox('crw_courses_list_tiles/passe_partout',
            get_string('settings_passe_partout', 'crw_courses_list_tiles'),
            get_string('settings_passe_partout_desc', 'crw_courses_list_tiles'), 0));
    
}
