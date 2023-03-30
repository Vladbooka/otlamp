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
 * This file adds the settings pages to the navigation menu
 *
 * @package   mod_event3kl
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

// Подключение библиотек
require_once($CFG->dirroot . '/mod/event3kl/lib.php');
require_once($CFG->dirroot . '/mod/event3kl/classes/otserial.php');

if ($hassiteconfig) {

    // Добавление категории настроек
    $ADMIN->add('modsettings', new admin_category('mod_event3kl', get_string('pluginname', 'mod_event3kl')));

    // ОСНОВНАЯ СТРАНИЦА НАСТРОЕК ПЛАГИНА
    $settingspage = new admin_settingpage('modsettingevent3kl', get_string('settings', 'mod_event3kl'));
    if ($ADMIN->fulltree) {

        // Время жизни сессии (по истечению которого сессия будет принудительно завершаться)
        $name = 'mod_event3kl/session_lifetime';
        $visiblename = get_string('settings_session_lifetime_label', 'mod_event3kl');
        $description = get_string('settings_session_lifetime_desc', 'mod_event3kl');
        $setting = new admin_setting_configduration($name, $visiblename, $description, 86400);
        $settingspage->add($setting);

    }
    $ADMIN->add('mod_event3kl', $settingspage);

    // Страница управления провайдерами
    $manageprovidersurl = new moodle_url('/mod/event3kl/manage_providers.php');
    $manageprovidersname = get_string('manage_providers', 'mod_event3kl');
    $manageproviders = new admin_externalpage('event3kl_manage_providers', $manageprovidersname, $manageprovidersurl);
    $ADMIN->add('mod_event3kl', $manageproviders);

    // Создание объекта OTAPI
    $otapi = new \mod_event3kl\otserial();
    // Добавление страницы управления тарифом
    $otapi->settings_page_add($ADMIN, 'mod_event3kl');
}
$settings = NULL;