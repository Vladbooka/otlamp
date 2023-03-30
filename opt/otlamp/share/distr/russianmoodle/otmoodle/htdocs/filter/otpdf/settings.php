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
 * Настройки фильтра otpdf.
 *
 * @package    filter_otpdf
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

if ($ADMIN->fulltree) {
    // Заголовок
    $item = new admin_setting_heading('filter_otpdf/settings_header',
        new lang_string('settings_header', 'filter_otpdf'),
        new lang_string('settings_header_help', 'filter_otpdf'));
    $settings->add($item);
    
    // Вариант отображения результата обработки фильтром
    $choices = [
        0 => get_string('display_option__viewer','filter_otpdf'),
        1 => get_string('display_option__link','filter_otpdf')
    ];
    $item = new admin_setting_configselect('filter_otpdf/display_option',
        new lang_string('display_option', 'filter_otpdf'),
        new lang_string('display_option_help', 'filter_otpdf'), 
        0,
        $choices);
    $settings->add($item);
    
    // Отображать возомжность открыть другой файл
    $item = new admin_setting_configcheckbox('filter_otpdf/display_open_tool',
        new lang_string('display_open_tool', 'filter_otpdf'),
        new lang_string('display_open_tool_help', 'filter_otpdf'), 0);
    $settings->add($item);
    
    // Отображать возомжность скачивания
    $item = new admin_setting_configcheckbox('filter_otpdf/display_download_tool',
        new lang_string('display_download_tool', 'filter_otpdf'),
        new lang_string('display_download_tool_help', 'filter_otpdf'), 0);
    $settings->add($item);
    
    // Отображать возможность печати
    $item = new admin_setting_configcheckbox('filter_otpdf/display_print_tool',
        new lang_string('display_print_tool', 'filter_otpdf'),
        new lang_string('display_print_tool_help', 'filter_otpdf'), 0);
    $settings->add($item);
    
}
