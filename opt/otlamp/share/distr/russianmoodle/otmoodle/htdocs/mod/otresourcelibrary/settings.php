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
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle. If not, see <http://www.gnu.org/licenses/>.

/**
 * Модуль Библиотека ресурсов. Настройки плагина.
 *
 * @package    mod_otcourselogic
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

// Подключение библиотек
require_once($CFG->dirroot . '/mod/otresourcelibrary/lib.php');
require_once($CFG->dirroot . '/mod/otresourcelibrary/classes/otserial.php');

if ($hassiteconfig)
{// Имеются прова на настройку плагина
    
    // Добавление категории настроек
    $ADMIN->add('modsettings', new admin_category('mod_otresourcelibrary_', get_string('pluginname', 'mod_otresourcelibrary')));
    
    
    
    
    // Создание объекта OTAPI
    $otapi = new \mod_otresourcelibrary\otserial();
    // Добавление страницы управления тарифом
    $otapi->settings_page_add($ADMIN, 'mod_otresourcelibrary_', [
        'settings_page_name' => 'mod_otresourcelibrary_otserial',
        'plugin_string_identifiers' => [
            'otserial_settingspage_visiblename' => 'settings_otserial',
            'otserial_settingspage_otserial' => 'otserial',
            'otserial_settingspage_issue_otserial' => 'get_otserial',
            'otserial_settingspage_otservice' => 'otservice',
            'otserial_exception_already_has_serial' => 'already_has_serial',
            'otserial_error_get_otserial_fail' => 'get_otserial_fail',
            'otserial_error_otserial_check_fail' => 'otserial_check_fail',
            'otserial_error_tariff_wrong' => 'otserial_tariff_wrong',
            'otserial_error_otservice_expired' => 'otservice_expired',
            'otserial_notification_otserial_check_ok' => 'otserial_check_ok',
            'otserial_notification_otservice_active' => 'otservice_active',
            'otserial_notification_otservice_unlimited' => 'otservice_unlimited',
        ],
    ]);
    
    
    
    
    $ADMIN->add(
        'mod_otresourcelibrary_',
        new admin_externalpage(
            'tool_otrl_manage_sources',
            get_string('manage_source', 'otresourcelibrary'),
            new moodle_url('/mod/otresourcelibrary/manage_sources.php'),
            'moodle/site:config',
            true
        )
    );
    
    
    
    
    // Создание страницы настроек плагина - Управление источниками
    $settings = new admin_settingpage(
        'mod_otresourcelibrary_sources',
        get_string('settings_sources', 'mod_otresourcelibrary')
    );
    
    // Ссылка на управление источниками библиотеки
    $urlmanagesources = new moodle_url('/mod/otresourcelibrary/manage_sources.php');
    $settings->add(new admin_setting_heading(
        "mod_otresourcelibrary/manage_sources_link",
        '',
        html_writer::link($urlmanagesources, get_string('manage_source', 'otresourcelibrary'))
    ));
    
    // Регистрация страницы настроек
    $ADMIN->add('mod_otresourcelibrary_', $settings);
}

// У плагина нет стандартной страницы настроек
$settings = null;