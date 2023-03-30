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
 * Admin settings and defaults.
*
 * @package    auth_dof
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

// Добавление категории настроек
$ADMIN->add('authsettings',
    new admin_category('auth_dof_settings', get_string('pluginname', 'auth_dof'))
    );

// СТРАНИЦА НАСТРОЕК УПРАВЛЕНИЯ ПОЛЬЗОВАТЕЛЬСКИМИ ПОЛЯМИ
$settings = new admin_externalpage(
    'registration_fields_settings',
    get_string('registration_fields_settings', 'auth_dof'),
    new moodle_url('/auth/dof/registration_fields_settings.php'),
    'moodle/site:config'
    );
$ADMIN->add('auth_dof_settings',  $settings);

// СТРАНИЦА НАСТРОЕК УПРАВЛЕНИЯ ВНЕШНИМИ ИСТОЧНИКАМИ
$settings = new admin_externalpage(
    'external_sources_settings',
    get_string('external_sources_settings', 'auth_dof'),
    new moodle_url('/auth/dof/external_sources_settings.php'),
    'moodle/site:config'
    );
$ADMIN->add('auth_dof_settings',  $settings);

// ОСНОВНАЯ СТРАНИЦА НАСТРОЕК ПЛАГИНА
$settings = new admin_settingpage(
    'authsettingdof',
    get_string('settings_page_general', 'auth_dof'),
    'moodle/site:config'
    );
if ($ADMIN->fulltree) {
    // Класс для особой валидации настроек типа select
    require_once($CFG->dirroot.'/auth/dof/classes/extended_config_settings_validation.php');
    // Получение объекта плагина
    $authplugin = get_auth_plugin('dof');
    // Получение настроек плагина
    $config = $authplugin->get_config();

    // Определение элементов для выпадающего списка Да/Нет
    $yesno = [
        0 => get_string('no'),
        1 => get_string('yes')
    ];

    /*******  НАСТРОЙКИ АВТОРИЗАЦИИ *******/

    // ДВУХЭТАПНАЯ АВТОРИЗАЦИЯ
    // Заголовок блока авторизации
    $settings->add(new admin_setting_heading(
        'settings_dual_auth', get_string('settings_dual_auth', 'auth_dof'), ''
            ));

    // чекбокс тригер
    $name = 'auth_dof/dualauth';
    $visiblename = get_string('settings_enable_dual_auth_label', 'auth_dof');
    $description = get_string('settings_enable_dual_auth', 'auth_dof');
    $settings->add(new admin_setting_configcheckbox(
        $name,
        $visiblename,
        $description,
        0,
        1,
        0
        ));

    // срок жизни ключа
    $name = 'auth_dof/codelivetime';
    $visiblename = get_string('settings_code_live_time_label', 'auth_dof');
    $description = get_string('settings_code_live_time', 'auth_dof');
    $settings->add(new admin_setting_configduration(
        $name,
        $visiblename,
        $description,
        600,
        60
        ));

    // Количество разрешенных попыток для ввода кода авторизации
    $name = 'auth_dof/allowedentryattempts';
    $visiblename = get_string('settings_number_of_allowed_code_entry_attempts_label', 'auth_dof');
    $description = get_string('settings_number_of_allowed_code_entry_attempts_desc', 'auth_dof');
    $settings->add(new admin_setting_configselect(
        $name,
        $visiblename,
        $description,
        3,
        array_combine(range(1,10,1),range(1,10,1))
        ));

    /*******  НАСТРОЙКИ РЕГИСТРАЦИИ *******/
    $settings->add(new admin_setting_heading(
        'registration_settings',
        get_string('registration_settings', 'auth_dof'),
        get_string('settings_dof_registrationtype', 'auth_dof')
        ));
    // Выбор способа уведомления
    $sendmethods = $authplugin->get_available_send_methods();
    $name = 'auth_dof/sendmethod';
    $visiblename = get_string('settings_sendmethod_label', 'auth_dof');
    $description = get_string('settings_sendmethod', 'auth_dof');
    $settings->add(new auth_dof_sendmethod(
        $name,
        $visiblename,
        $description,
        ['email'],
        $sendmethods
    ));

    $departments = $authplugin->get_available_dof_departments();
    if ( ! empty($config->dof_departmentid) && ! array_key_exists($config->dof_departmentid, $departments) )
    {// Текущее подразделение не найдено в списке
        $departments[$config->dof_departmentid] = get_string('dof_departments_not_found', 'auth_dof');
    }

    $name = 'auth_dof/dof_departmentid';
    $visiblename = get_string('settings_dof_departmentid_label', 'auth_dof');
    $description = get_string('settings_dof_departmentid', 'auth_dof');
    $settings->add(new admin_setting_configselect(
        $name,
        $visiblename,
        $description,
        0,
        $departments
        ));
    // Активация подтверждения учетной записи
    $name = 'auth_dof/confirmation';
    $visiblename = get_string('settings_confirmation_label', 'auth_dof');
    $description = get_string('settings_confirmation', 'auth_dof');
    $settings->add(new admin_setting_configselect($name, $visiblename, $description, 0, $yesno));

    // Мгновенная авторизация после регистрации
    $confirmation = get_config('auth_dof', 'confirmation');
    if (!empty($confirmation)) {
        $name = 'auth_dof/auth_after_reg';
        $visiblename = get_string('settings_auth_after_reg_label', 'auth_dof');
        $description = get_string('settings_auth_after_reg_desc', 'auth_dof');
        $settings->add(new admin_setting_configselect($name, $visiblename, $description, 0, $yesno));
    }

    // ОГРАНИЧЕНИЕ ПОПЫТОК ПОИСКА ВО ВНЕШНЕМ ИСТОЧНИКЕ ПРИ РЕГИСТРАЦИИ ПО ПРЕДВАРИТЕЛЬНЫМ СПИСКАМ
    // Заголовок блока ограничения попыток
    $settings->add(new admin_setting_heading(
        'limiting_registration_attempts', '',
        html_writer::tag('h5', get_string('limiting_registration_attempts', 'auth_dof'))
        ));

    // чекбокс тригер
    $name = 'auth_dof/limiting_registration_attempts';
    $visiblename = get_string('settings_enable_limiting_registration_attempts_label', 'auth_dof');
    $description = get_string('settings_enable_limiting_registration_attempts_desc', 'auth_dof');
    $settings->add(new admin_setting_configcheckbox(
        $name,
        $visiblename,
        $description,
        0,
        1,
        0
        ));

    // Время до сброса попыток
    $name = 'auth_dof/plist_reg_retry_time';
    $visiblename = get_string('settings_plist_reg_retry_time_label', 'auth_dof');
    $description = get_string('settings_plist_reg_retry_time_desc', 'auth_dof');
    $settings->add(new admin_setting_configduration(
        $name,
        $visiblename,
        $description,
        600,
        60
        ));

    // Количество попыток до таймера ожидания
    $name = 'auth_dof/plist_reg_attempts';
    $visiblename = get_string('settings_plist_reg_attempts_label', 'auth_dof');
    $description = get_string('settings_plist_reg_attempts_desc', 'auth_dof');
    $settings->add(new admin_setting_configselect(
        $name,
        $visiblename,
        $description,
        3,
        array_combine(range(1,10,1),range(1,10,1))
        ));

    // ДОПОЛНИТЕЛЬНЫЕ НАСТРОЙКИ ПО ПОЛЯМ ПОЛЬЗОВАТЕЛЯ
    $settings->add(new admin_setting_heading(
        'additional_fields_settings',
        get_string('additional_fields_settings', 'auth_dof'),
        ''
        ));

    // Капча
    $name = 'auth_dof/recaptcha';
    $visiblename = get_string('settings_recaptcha_label', 'auth_dof');
    $description = get_string('settings_recaptcha', 'auth_dof');
    $settings->add(new admin_setting_configselect(
        $name,
        $visiblename,
        $description,
        0,
        $yesno
        ));
    // Поле для повтора пароля
    $name = 'auth_dof/passwordrepeat';
    $visiblename = get_string('settings_passwordrepeat_label', 'auth_dof');
    $description = get_string('settings_passwordrepeat', 'auth_dof');
    $settings->add(new admin_setting_configselect(
        $name,
        $visiblename,
        $description,
        0,
        $yesno
        ));

    // Display locking / mapping of profile fields.
    display_auth_lock_options($settings, $authplugin->authtype, $authplugin->userfields,
        get_string('auth_fieldlocks_help', 'auth'), false, false);

}
// Добавим страницу основных нестроек в меню
$ADMIN->add('auth_dof_settings', $settings);

// У плагина нет стандартной страницы настроек, вернем NULL
$settings = NULL;
