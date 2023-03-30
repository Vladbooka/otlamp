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
 * Модуль Логика курса. Настройки плагина.
 *
 * @package    mod_otcourselogic
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// Подключение библиотек
require_once($CFG->dirroot . '/mod/otcourselogic/lib.php');
require_once($CFG->dirroot . '/mod/otcourselogic/classes/otserial.php');

defined('MOODLE_INTERNAL') || die();

if ($hassiteconfig)
{// Имеются прова на настройку плагина
    // Добавление категории настроек
    $ADMIN->add(
        'modsettings',
        new admin_category(
            'modotcourselogic',
            get_string('pluginname', 'mod_otcourselogic')
        )
    );
    
    
    // Создание объекта OTAPI
    $otapi = new \mod_otcourselogic\otserial();
    // Добавление страницы управления тарифом
    $otapi->settings_page_add($ADMIN, 'modotcourselogic', [
        'settings_page_name' => 'mod_otcourselogic_tarif',
        'plugin_string_identifiers' => [
            'otserial_settingspage_visiblename' => 'settings_tarif',
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
        
    // Создание страницы настроек плагина - Общие настройки
    $settings = new admin_settingpage(
        'mod_otcourselogic_general',
        get_string('settings_general', 'mod_otcourselogic')
    );
    
    // Регистрация страницы настроек
    $ADMIN->add('modotcourselogic', $settings);
}

// У плагина нет стандартной страницы настроек
$settings = null;