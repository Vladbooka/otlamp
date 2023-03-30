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
 * Блок таблиы курсов c AJAX - отображением описания. Настройки.
 * 
 * @package    local
 * @subpackage crw
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

if ($ADMIN->fulltree)
{
    // Заголовок - Настройки блока курсов
    $name = 'crw_courses_list_ajax/settingspage_title';
    $title = get_string('settingspage_title', 'crw_courses_list_ajax');
    $description = get_string('settingspage_title_desc', 'crw_courses_list_ajax');
    $setting = new admin_setting_heading($name, $title, $description);
    $settings->add($setting);
    
    // Скрытие общего заголовка для плагина
    $choices = [
        0 => get_string('no', 'local_crw'),
        1 => get_string('yes', 'local_crw')
    ];
    $name = 'crw_courses_list_ajax/hide_course_block_title';
    $title = get_string('settings_hide_course_block_title_title','crw_courses_list_ajax');
    $description = get_string('settings_hide_course_block_title_desc','crw_courses_list_ajax');
    $default = 0;
    $setting = new admin_setting_configselect($name, $title, $description, $default, $choices);
    $settings->add($setting);

    // Включение/выключение отображения через модальное окно с дополнительными данными
    $name = 'crw_courses_list_ajax/enable_ajax';
    $title = get_string('settings_enable_ajax_title', 'crw_courses_list_ajax');
    $description = get_string('settings_enable_ajax_desc', 'crw_courses_list_ajax');
    $defaultsetting = '0';
    $setting = new admin_setting_configcheckbox($name, $title, $description, $defaultsetting);
    $settings->add($setting);
    
    // Включение/выключение использования ссылки на курс вместо ссылки на страницу описания курса в витрине
    $name = 'crw_courses_list_ajax/use_course_link';
    $title = get_string('settings_use_course_link_title', 'crw_courses_list_ajax');
    $description = get_string('settings_use_course_link_desc', 'crw_courses_list_ajax');
    $defaultsetting = '0';
    $setting = new admin_setting_configcheckbox($name, $title, $description, $defaultsetting);
    $settings->add($setting);
    
    // Включение/выключение группировки курсов по категориям
    $name = 'crw_courses_list_ajax/group_by_category';
    $title = get_string('settings_group_by_category_title', 'crw_courses_list_ajax');
    $description = get_string('settings_group_by_category_desc', 'crw_courses_list_ajax');
    $defaultsetting = '0';
    $setting = new admin_setting_configcheckbox($name, $title, $description, $defaultsetting);
    $settings->add($setting);
    
    // Включение/выключение отображения заголовка таблицы с курсами
    $name = 'crw_courses_list_ajax/display_table_header';
    $title = get_string('settings_display_table_header_title', 'crw_courses_list_ajax');
    $description = get_string('settings_display_table_header_desc', 'crw_courses_list_ajax');
    $defaultsetting = '0';
    $setting = new admin_setting_configcheckbox($name, $title, $description, $defaultsetting);
    $settings->add($setting);
    
    // Файл иконки для курсов
    $name = 'crw_courses_list_ajax/iconfile';
    $title = get_string('settings_iconfile', 'crw_courses_list_ajax');
    $description = get_string('settings_iconfile_desc', 'crw_courses_list_ajax');
    $setting = new admin_setting_configstoredfile($name, $title, $description, 'icon');
    $setting->set_updatedcallback('theme_reset_all_caches');
    $settings->add($setting);
    
    // Файл иконки для кнопки "вверх"
    $name = 'crw_courses_list_ajax/scrolltotop_iconfile';
    $title = get_string('settings_scrolltotop_iconfile', 'crw_courses_list_ajax');
    $description = get_string('settings_scrolltotop_iconfile_desc', 'crw_courses_list_ajax');
    $setting = new admin_setting_configstoredfile($name, $title, $description, 'scrolltotopicon');
    $setting->set_updatedcallback('theme_reset_all_caches');
    $settings->add($setting);
    
    // Файл иконки для кнопки "Показать больше курсов"
    $name = 'crw_courses_list_ajax/more_courses_iconfile';
    $title = get_string('settings_more_courses_iconfile', 'crw_courses_list_ajax');
    $description = get_string('settings_more_courses_iconfile_desc', 'crw_courses_list_ajax');
    $setting = new admin_setting_configstoredfile($name, $title, $description, 'morecoursesicon');
    $setting->set_updatedcallback('theme_reset_all_caches');
    $settings->add($setting);
    
    // Настройка скрывать курсы под кат, если количество превышает указанное
    $name = 'crw_courses_list_ajax/hide_more_than';
    $title = get_string('settings_hide_more_than', 'crw_courses_list_ajax');
    $description = get_string('settings_hide_more_than_desc', 'crw_courses_list_ajax');
    $setting = new admin_setting_configtext($name, $title, $description, 0, PARAM_INT);
    $settings->add($setting);
    
    // Файл иконки для кнопки "Свернуть список курсов"
    $name = 'crw_courses_list_ajax/less_courses_iconfile';
    $title = get_string('settings_less_courses_iconfile', 'crw_courses_list_ajax');
    $description = get_string('settings_less_courses_iconfile_desc', 'crw_courses_list_ajax');
    $setting = new admin_setting_configstoredfile($name, $title, $description, 'lesscoursesicon');
    $setting->set_updatedcallback('theme_reset_all_caches');
    $settings->add($setting);
    
    // Включение/выключение отображения краткого названия курса
    $name = 'crw_courses_list_ajax/display_course_shortname';
    $title = get_string('settings_display_course_shortname_title', 'crw_courses_list_ajax');
    $description = get_string('settings_display_course_shortname_desc', 'crw_courses_list_ajax');
    $defaultsetting = '0';
    $setting = new admin_setting_configcheckbox($name, $title, $description, $defaultsetting);
    $settings->add($setting);
    
    // Включение/выключение отображения сложности курса
    $name = 'crw_courses_list_ajax/display_course_difficult';
    $title = get_string('settings_display_course_difficult_title', 'crw_courses_list_ajax');
    $description = get_string('settings_display_course_difficult_desc', 'crw_courses_list_ajax');
    $defaultsetting = '0';
    $setting = new admin_setting_configcheckbox($name, $title, $description, $defaultsetting);
    $settings->add($setting);
    
    // Включение/выключение отображения кнопок записи на курс напротив каждого курса
    $name = 'crw_courses_list_ajax/enable_enrol_button';
    $title = get_string('settings_enable_enrol_button_title', 'crw_courses_list_ajax');
    $description = get_string('settings_enable_enrol_button_desc', 'crw_courses_list_ajax');
    $defaultsetting = '0';
    $setting = new admin_setting_configcheckbox($name, $title, $description, $defaultsetting);
    $settings->add($setting);
    

    // Отображение 
    $choices = [
        0 => get_string('settings_tools_display_mode_link','crw_courses_list_ajax'),
        1 => get_string('settings_tools_display_mode_button','crw_courses_list_ajax'),
    ];
    $name = 'crw_courses_list_ajax/tools_display_mode';
    $title = get_string('settings_tools_display_mode','crw_courses_list_ajax');
    $description = get_string('settings_tools_display_mode_desc','crw_courses_list_ajax');
    $setting = new admin_setting_configselect($name, $title, $description, 0, $choices);
    $settings->add($setting);
}