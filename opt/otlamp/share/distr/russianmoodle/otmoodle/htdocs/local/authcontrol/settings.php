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
 * Панель управления доступом в СДО
 * 
 * Страница настроек плагина
 * 
 * @package    local_authcontrol
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

if ($hassiteconfig) 
{// Имеются права на конфигурирование плагина
    
    // Добавим категорию настроек
    $ADMIN->add(
        'localplugins', 
        new admin_category('localauthcontrol', get_string('pluginname', 'local_authcontrol'))
    );
    
    // Объявляем страницу настроек плагина
    $settings = new admin_settingpage(
        'authcontrol_settings', 
        get_string('settings_general', 'local_authcontrol')
    );
    
    if ( $ADMIN->fulltree ) 
    {// Требуется подгрузка страницы настроек
        
        // Включить плагин
        $choices = [
            0 => get_string('no'),
            1 => get_string('yes')
        ];
        $settings->add(new admin_setting_configselect(
            'local_authcontrol/authcontrol_select',
            get_string('settings_form_control_enable', 'local_authcontrol'),
            get_string('settings_form_control_enable_desc', 'local_authcontrol'),
            0,
            $choices)
        );
        $settings->add(new admin_setting_configselect(
            'local_authcontrol/authcontrol_select_session',
            get_string('settings_form_control_enable_session', 'local_authcontrol'),
            get_string('settings_form_control_enable_session_desc', 'local_authcontrol'),
            0,
            $choices)
        );
    }
    // Добавим страницу основных настроек в меню администратора
    $ADMIN->add('localauthcontrol', $settings);
}

// У плагина нет стандартной страницы настроек, вернем NULL
$settings = null;
