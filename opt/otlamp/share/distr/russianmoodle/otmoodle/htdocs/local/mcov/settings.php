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
 * Настраиваемые поля. Настройки плагина
 *
 * @package    local_mcov
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

use \local_mcov\helper;

if ($hassiteconfig)
{
    // Имеются права на конфигурирование плагинов

    // Страница настроек плагина
    $settings = new admin_settingpage('mcov_settings', get_string('settings_general', 'local_mcov'));

    if ( $ADMIN->fulltree )
    {
        // Требуется подгрузка страницы настроек

        // Заголовок страницы настроек
        extract(helper::get_setting_default_args('title_general'));
        $setting = new admin_setting_heading($name, $displayname, $description);
        $settings->add($setting);
        
        // Конфиг формы с полями для глобальных групп
        extract(helper::get_setting_default_args('cohort_yaml'));
        $setting = new admin_setting_configtextarea($name, $displayname, $description, '');
        $settings->add($setting);
        
        // Конфиг формы с полями для групп (локальных, в курсе)
        extract(helper::get_setting_default_args('group_yaml'));
        $setting = new admin_setting_configtextarea($name, $displayname, $description, '');
        $settings->add($setting);

    }

    // Добавление категории настроек
    $ADMIN->add('localplugins', $settings);
}

$settings = null;
