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
 * @package auth_otoauth
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;



$category = new admin_category('auth_otoauth', get_string('pluginname', 'auth_otoauth'));
// Добавим категорию настроек
$ADMIN->add('authsettings', $category);

// Объявляем страницу настроек плагина
$settings = new admin_settingpage(
    'authsettingotoauth',
    get_string('settings_page_general', 'auth_otoauth'),
    'auth/otoauth:managecustomproviders'
);

if ($ADMIN->fulltree)
{
    // Получение объекта плагина
    $authplugin = get_auth_plugin('otoauth');
    
    $yesno = [
        0 => get_string('no'),
        1 => get_string('yes')
    ];
    
    // ОБЩИЕ НАСТРОЙКИ ДЛЯ ПЛАГИНА
    // Заголовок блока настроек
    $settings->add(new admin_setting_heading(
        'auth_otoauth/pluginname',
        new lang_string('settings_main_header', 'auth_otoauth'),
        ''
    ));
    
    // Использование единой точки входа для сервисов после авторизации (otoauth/redirect.php)
    $name = 'auth_otoauth/usenewredirect';
    $visiblename = get_string('settings_usenewredirect_label', 'auth_otoauth');
    $description = get_string('settings_usenewredirect', 'auth_otoauth');
    $settings->add(new admin_setting_configselect(
        $name,
        $visiblename,
        $description,
        0,
        $yesno
    ));
    
    // Требуется подтверждения почты
    $name = 'auth_otoauth/requireconfirm';
    $visiblename = get_string('settings_requireconfirm_label', 'auth_otoauth');
    $description = get_string('settings_requireconfirm', 'auth_otoauth');
    $settings->add(new admin_setting_configselect(
        $name,
        $visiblename,
        $description,
        0,
        $yesno
    ));
    
    // Глобальный запрет регистрации новых аккаунтов через сервисы
    $name = 'auth_otoauth/allowregister';
    $visiblename = get_string('settings_allowregister_label', 'auth_otoauth');
    $description = get_string('settings_allowregister', 'auth_otoauth');
    $settings->add(new admin_setting_configselect(
        $name,
        $visiblename,
        $description,
        0,
        $yesno
    ));
    
    // Префикс логина для новых пользователей
    $name = 'auth_otoauth/googleuserprefix';
    $visiblename = get_string('settings_googleuserprefix_label', 'auth_otoauth');
    $description = get_string('settings_googleuserprefix', 'auth_otoauth');
    $settings->add(new admin_setting_configtext(
        $name,
        $visiblename,
        $description,
        'social_user_',
        PARAM_TEXT
    ));
    
    // Ключ IPinfoDB
    $name = 'auth_otoauth/googleipinfodbkey';
    $visiblename = get_string('settings_googleipinfodbkey_label', 'auth_otoauth');
    $description = get_string('settings_googleipinfodbkey', 'auth_otoauth');
    $settings->add(new admin_setting_configtext(
        $name,
        $visiblename,
        $description,
        '',
        PARAM_TEXT
    ));
    
    
    
    // Получение настроек плагина
    $pluginconfig = $authplugin->get_config();
    $name = 'auth_otoauth/salt';
    $visiblename = get_string('settings_salt', 'auth_otoauth');
    $description = get_string('settings_salt_desc', 'auth_otoauth');
    $settings->add(new admin_setting_configtext(
        $name,
        $visiblename,
        $description,
        $pluginconfig->salt ?? uniqid(mt_rand(), true),
        PARAM_TEXT
        )
    );
    
    // Настройка режима обновления данных из соцсети
    $name = 'auth_otoauth/updatelocal';
    $visiblename = get_string('settings_updatelocal', 'auth_otoauth');
    $description = get_string('settings_updatelocal_desc', 'auth_otoauth');
    $settings->add(new admin_setting_configmultiselect(
        $name,
        $visiblename,
        $description,
        [$authplugin->get_updatelocal()],
        [
            'oncreate' => get_string('updatelocal_oncreate', 'auth_otoauth'),
            'onlogin' => get_string('updatelocal_onlogin', 'auth_otoauth'),
            'onlink' => get_string('updatelocal_onlink', 'auth_otoauth'),
        ]
    ));
    
    // АКТИВАЦИЯ НОВЫХ АККАУНТОВ АДМИНИСТРАТОРОМ


    $defaultsubjects = [
        'admin_message_suspended' => 'Пользователь ожидает подтверждения регистрации'
    ];
    
    $defaultmessagesshort = [
        'admin_message_suspended' => 'Здравствуйте, пользователь {userfullname} зарегистрировался и ожидает подтверждения учетной записи.'
    ];
    
    $defaultmessagesfull = [
        'admin_message_suspended' => 'Здравствуйте!<br /><br />Пользователь {userfullname} зарегистрировался и ожидает подтверждения учетной записи.<br />
                                      Чтобы подтвердить учетную запись требуется перейти на <a href="{userprofileediturl}">карточку пользователя</a> и деактивировать параметр "Учетная запись заблокирована"'
    ];
    
    // заголовок блока подтверждения пользователей
    $settings->add(
        new admin_setting_heading('auth_otoauth/suspended', get_string('settings_suspended_header', 'auth_otoauth'), ''));
    
    // Требуется активация новых аккаунтов администратором
    $name = 'auth_otoauth/admin_suspended';
    $visiblename = get_string('settings_suspended_label', 'auth_otoauth');
    $description = get_string('settings_suspended', 'auth_otoauth');
    $settings->add(new admin_setting_configselect(
        $name,
        $visiblename,
        $description,
        0,
        $yesno
        ));
    // Требуется отправка сообщений администратору
    $name = 'auth_otoauth/admin_message_suspended';
    $visiblename = get_string('settings_admin_message_suspended_label', 'auth_otoauth');
    $description = get_string('settings_admin_message_suspended', 'auth_otoauth');
    $settings->add(new admin_setting_configselect(
        $name,
        $visiblename,
        $description,
        0,
        $yesno
        ));
    
    $settings->add(
        new admin_setting_configtext('auth_otoauth/admin_message_suspended_subject',
            get_string('message_subject', 'auth_otoauth'), '', get_string('default_registration_confirmation_message_subject', 'auth_otoauth'), PARAM_RAW));
    $settings->add(
        new admin_setting_confightmleditor('auth_otoauth/admin_message_suspended_full',
            get_string('message_full', 'auth_otoauth'), '', get_string('default_registration_confirmation_message_full', 'auth_otoauth'), PARAM_RAW));
    $settings->add(
        new admin_setting_confightmleditor('auth_otoauth/admin_message_suspended_short',
            get_string('message_short', 'auth_otoauth'), '', get_string('default_registration_confirmation_message_short', 'auth_otoauth'), PARAM_RAW));
    
    
    
    
    // Display locking / mapping of profile fields.
    display_auth_lock_options($settings, $authplugin->authtype, $authplugin->userfields,
        get_string('auth_fieldlocks_help', 'auth'), false, false);

}

// Добавим страницу основных нестроек в меню администратора
$ADMIN->add('auth_otoauth', $settings);





// Объявляем страницу настроек плагина
$settings = new admin_externalpage(
    'auth_otoauth_custom_providers',
    get_string('custom_providers', 'auth_otoauth'),
    new moodle_url('/auth/otoauth/provider.php'),
    'auth/otoauth:managecustomproviders'
);

// Добавим страницу основных нестроек в меню администратора
$ADMIN->add('auth_otoauth', $settings);





// Получение списка сервисов автоиризации
$providers = otoauth_provider_list();

$category = new admin_category('auth_otoauth_providers', get_string('settings_category_providers', 'auth_otoauth'));
// Добавим категорию настроек
$ADMIN->add('auth_otoauth', $category);


// Настройки провайдеров
foreach( $providers as $providername )
{
    // Объявляем страницу настроек плагина
    $settings = new admin_settingpage('auth_otoauth_provider_'.$providername, get_string('provider_'.$providername, 'auth_otoauth'));
    
    if ($ADMIN->fulltree)
    {
        $classname = '\auth_otoauth\providers\\' . $providername;
        if (class_exists($classname)) {
            $provider = new $classname;
            $provider->add_main_settings($settings);
            $provider->add_custom_settings($settings);
        }
    }
    // Добавим страницу основных нестроек в меню администратора
    $ADMIN->add('auth_otoauth_providers', $settings);
}


// У плагина нет стандартной страницы настроек, вернем NULL
$settings = NULL;