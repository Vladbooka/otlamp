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
    $name = 'crw_courses_list_squares/settingspage_title';
    $title = get_string('settingspage_title','crw_courses_list_squares');
    $description = get_string('settingspage_title_desc','crw_courses_list_squares');
    $setting = new admin_setting_heading($name, $title, $description);
    $settings->add($setting);

    $choices = [
        0 => get_string('no', 'local_crw'),
        1 => get_string('yes', 'local_crw')
    ];
    
    // Скрытие заголовка
    $name = 'crw_courses_list_squares/hide_course_block_title';
    $title = get_string('settings_hide_course_block_title_title','crw_courses_list_squares');
    $description = get_string('settings_hide_course_block_title_desc','crw_courses_list_squares');
    $default = 0;
    $setting = new admin_setting_configselect($name, $title, $description, $default, $choices);
    $settings->add($setting);
    
    // Скрытие блока с тегами курса
    $name = 'crw_courses_list_squares/hide_course_tags';
    $title = get_string('settings_hide_course_tags_title','crw_courses_list_squares');
    $description = get_string('settings_hide_course_tags_desc','crw_courses_list_squares');
    $default = 0;
    $setting = new admin_setting_configselect($name, $title, $description, $default, $choices);
    $settings->add($setting);
    
    // Скрытие категории курса
    $name = 'crw_courses_list_squares/hide_course_category';
    $title = get_string('settings_hide_course_category_title','crw_courses_list_squares');
    $description = get_string('settings_hide_course_category_desc','crw_courses_list_squares');
    $default = 0;
    $setting = new admin_setting_configselect($name, $title, $description, $default, $choices);
    $settings->add($setting);
    
    // Скрытие цены курса
    $name = 'crw_courses_list_squares/hide_course_price';
    $title = get_string('settings_hide_course_price_title','crw_courses_list_squares');
    $description = get_string('settings_hide_course_price_desc','crw_courses_list_squares');
    $default = 0;
    $setting = new admin_setting_configselect($name, $title, $description, $default, $choices);
    $settings->add($setting);
    
    // Скрытие кнопки "Пройти курс"
    $name = 'crw_courses_list_squares/hide_course_pass_button';
    $title = get_string('settings_hide_course_pass_button_title','crw_courses_list_squares');
    $description = get_string('settings_hide_course_pass_button_desc','crw_courses_list_squares');
    $default = 0;
    $setting = new admin_setting_configselect($name, $title, $description, $default, $choices);
    $settings->add($setting);
    
    $choices = [
        'light' => get_string('light_theme', 'crw_courses_list_squares'),
        'dark' => get_string('dark_theme', 'crw_courses_list_squares')
    ];
    
    // Цветовая схема
    $name = 'crw_courses_list_squares/course_theme';
    $title = get_string('settings_course_theme_title','crw_courses_list_squares');
    $description = get_string('settings_course_theme_desc','crw_courses_list_squares');
    $default = 'light';
    $setting = new admin_setting_configselect($name, $title, $description, $default, $choices);
    $settings->add($setting);
    
    $choices = [
        0 => get_string('no', 'local_crw'),
        1 => get_string('yes', 'local_crw')
    ];
    
    // Растягивать по высоте плашку или нет
    $name = 'crw_courses_list_squares/coursebox_stretch';
    $title = get_string('settings_coursebox_stretch_title','crw_courses_list_squares');
    $description = get_string('settings_coursebox_stretch_desc','crw_courses_list_squares');
    $default = 0;
    $setting = new admin_setting_configselect($name, $title, $description, $default, $choices);
    $settings->add($setting);
    
    // Отображать ли фоновую картинку в плашке
    $name = 'crw_courses_list_squares/coursebox_background';
    $title = get_string('settings_coursebox_background_title','crw_courses_list_squares');
    $description = get_string('settings_coursebox_background_desc','crw_courses_list_squares');
    $default = 0;
    $setting = new admin_setting_configselect($name, $title, $description, $default, $choices);
    $settings->add($setting);
}
