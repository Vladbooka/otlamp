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
 * Способ записи на курс OTPay.
 * Настройки.
 *
 * @package enrol
 * @subpackage otpay
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die();

define('OTPAY_KAZKOM_TEST_MODE', 0);
define('OTPAY_KAZKOM_WORK_MODE', 1);

if ( $ADMIN->fulltree )
{
    $component = 'enrol_otpay';
    $settingsprefix = "kazkom_";
    
    //тестовый/рабочий режим
    $settingname = 'mode';
    $options = [
        OTPAY_KAZKOM_TEST_MODE => get_string($settingsprefix . 'settings_value_testmode', 
            $component),
        OTPAY_KAZKOM_WORK_MODE => get_string($settingsprefix . 'settings_value_workmode', 
            $component)
    ];
    $name = $component . '/' . $settingsprefix . $settingname;
    $visiblename = get_string($settingsprefix . 'settings_' . $settingname, $component);
    $description = get_string($settingsprefix . 'settings_' . $settingname . '_desc', $component);
    $setting = new admin_setting_configselect($name, $visiblename, $description, 
        OTPAY_KAZKOM_WORK_MODE, $options);
    $settings->add($setting);
    
    //URL для авторизации суммы в банке
    $settingname = 'url';
    $name = $component . '/' . $settingsprefix . $settingname;
    $visiblename = get_string($settingsprefix . 'settings_' . $settingname, $component);
    $description = get_string($settingsprefix . 'settings_' . $settingname . '_desc', $component);
    $setting = new admin_setting_configtext($name, $visiblename, $description, 
        "https://epay.kkb.kz/jsp/process/logon.jsp");
    $settings->add($setting);
    
    //URL для подтверждения-анулирования авторизации
    $settingname = 'urlcontrol';
    $name = $component . '/' . $settingsprefix . $settingname;
    $visiblename = get_string($settingsprefix . 'settings_' . $settingname, $component);
    $description = get_string($settingsprefix . 'settings_' . $settingname . '_desc', $component);
    $setting = new admin_setting_configtext($name, $visiblename, $description, 
        "https://epay.kkb.kz/jsp/remote/control.jsp");
    $settings->add($setting);
    
    //Серийный номер сертификата
    $settingname = 'merchant_certificate_id';
    $name = $component . '/' . $settingsprefix . $settingname;
    $visiblename = get_string($settingsprefix . 'settings_' . $settingname, $component);
    $description = get_string($settingsprefix . 'settings_' . $settingname . '_desc', $component);
    $setting = new admin_setting_configtext($name, $visiblename, $description, "");
    $settings->add($setting);
    
    //имя магазина(сайта)
    $settingname = 'merchant_name';
    $name = $component . '/' . $settingsprefix . $settingname;
    $visiblename = get_string($settingsprefix . 'settings_' . $settingname, $component);
    $description = get_string($settingsprefix . 'settings_' . $settingname . '_desc', $component);
    $setting = new admin_setting_configtext($name, $visiblename, $description, "");
    $settings->add($setting);
    
    //ID продавца в платежной системе
    $settingname = 'merchant_id';
    $name = $component . '/' . $settingsprefix . $settingname;
    $visiblename = get_string($settingsprefix . 'settings_' . $settingname, $component);
    $description = get_string($settingsprefix . 'settings_' . $settingname . '_desc', $component);
    $setting = new admin_setting_configtext($name, $visiblename, $description, "");
    $settings->add($setting);
    
    //приватный ключ пользователя
    $settingname = 'privateuserkey';
    $name = $component . '/' . $settingsprefix . $settingname;
    $visiblename = get_string($settingsprefix . 'settings_' . $settingname, $component);
    $description = get_string($settingsprefix . 'settings_' . $settingname . '_desc', $component);
    $defaultsetting = "";
    $setting = new admin_setting_configtextarea($name, $visiblename, $description, $defaultsetting);
    $settings->add($setting);
    
    //пароль от приватного ключа пользователя
    $settingname = 'privateuserkeypassword';
    $name = $component . '/' . $settingsprefix . $settingname;
    $visiblename = get_string($settingsprefix . 'settings_' . $settingname, $component);
    $description = get_string($settingsprefix . 'settings_' . $settingname . '_desc', $component);
    $setting = new admin_setting_configpasswordunmask($name, $visiblename, $description, "");
    $settings->add($setting);
    
    //публичный ключ банка
    $settingname = 'publicbankkey';
    $name = $component . '/' . $settingsprefix . $settingname;
    $visiblename = get_string($settingsprefix . 'settings_' . $settingname, $component);
    $description = get_string($settingsprefix . 'settings_' . $settingname . '_desc', $component);
    $defaultsetting = "";
    $setting = new admin_setting_configtextarea($name, $visiblename, $description, $defaultsetting);
    $settings->add($setting);
    
    // Список доступных текущему провайдеру способов оплаты
    $settingname = 'available_paysystems';
    $name = $component . '/' . $settingsprefix . $settingname;
    $visiblename = get_string($settingsprefix . 'settings_' . $settingname, $component);
    $description = get_string($settingsprefix . 'settings_' . $settingname . '_desc', $component);
    $choises = [];
    $paysystems = $provider->get_paysystems();
    foreach ( $paysystems as $paysystem )
    {
        $choises[$paysystem->code] = $paysystem->name;
    }
    $setting = new admin_setting_configmulticheckbox($name, $visiblename, $description, [], $choises);
    $settings->add($setting);
}