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
 * Блок привязки соцсетей. Настройки.
 *
 * @package    block
 * @subpackage linksocial
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// Получить включенные плагины авторизации
$plugins = get_enabled_auth_plugins();

// Заголовок - Основные настройки
$settings->add(new admin_setting_heading(
        'setings_header_base',
        get_string('setings_header_base', 'block_linksocial'),
        get_string('setings_header_base_desc', 'block_linksocial')
));

    // Включить напоминание о возможности привязки
    $settings->add(new admin_setting_configcheckbox(
            'block_linksocial/enable_notice',
            get_string('enable_notice', 'block_linksocial'),
            get_string('enable_notice_desc', 'block_linksocial'),
            false
    ));

// Заголовок - Белый список плагинов
$settings->add(new admin_setting_heading(
            'headerconfig',
            get_string('headerconfig', 'block_linksocial'),
            get_string('descconfig', 'block_linksocial')
));

    foreach ($plugins as $plugin) {
        $pluginname = get_string('pluginname', 'auth_' . $plugin);
        $settings->add(new admin_setting_configcheckbox(
                'block_linksocial/' . $plugin,
                get_string('enable', 'block_linksocial') . $plugin,
                get_string('enablingdescribe', 'block_linksocial') . "'$pluginname'",
                true
        ));
    }

// Добавим страницу статистики
$ADMIN->add(
        'accounts',
        new admin_externalpage(
                'reporttpcompletion',
                get_string('page_statistics', 'block_linksocial'),
                "$CFG->wwwroot/blocks/linksocial/statistics.php"
        )
);