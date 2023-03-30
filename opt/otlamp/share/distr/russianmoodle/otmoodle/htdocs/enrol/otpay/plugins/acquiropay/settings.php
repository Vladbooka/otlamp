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

define('OTPAY_ACQUIROPAY_TEST_MODE', 0);
define('OTPAY_ACQUIROPAY_WORK_MODE', 1);

if ( $ADMIN->fulltree )
{
    $component = 'enrol_otpay';
    $settingsprefix = "acquiropay_";
    
    //тестовый/рабочий режим
    $settingname = 'mode';
    $options = [
        OTPAY_ACQUIROPAY_TEST_MODE => get_string($settingsprefix . 'settings_value_testmode', 
            $component),
        OTPAY_ACQUIROPAY_WORK_MODE => get_string($settingsprefix . 'settings_value_workmode', 
            $component)
    ];
    $name = $component . '/' . $settingsprefix . $settingname;
    $visiblename = get_string($settingsprefix . 'settings_' . $settingname, $component);
    $description = get_string($settingsprefix . 'settings_' . $settingname . '_desc', $component);
    $setting = new admin_setting_configselect($name, $visiblename, $description, 
        OTPAY_ACQUIROPAY_WORK_MODE, $options);
    $settings->add($setting);
    
    //URL для взаимодействия с банком
    $settingname = 'url';
    $name = $component . '/' . $settingsprefix . $settingname;
    $visiblename = get_string($settingsprefix . 'settings_' . $settingname, $component);
    $description = get_string($settingsprefix . 'settings_' . $settingname . '_desc', $component);
    $setting = new admin_setting_configtext($name, $visiblename, $description, 
        "https://secure.acquiropay.com/");
    $settings->add($setting);
    
    $settingname = 'merchantid';
    $name = $component . '/' . $settingsprefix . $settingname;
    $visiblename = get_string($settingsprefix . 'settings_' . $settingname, $component);
    $description = get_string($settingsprefix . 'settings_' . $settingname . '_desc', $component);
    $setting = new admin_setting_configpasswordunmask($name, $visiblename, $description, 0);
    $settings->add($setting);
    
    $settingname = 'productid';
    $name = $component . '/' . $settingsprefix . $settingname;
    $visiblename = get_string($settingsprefix . 'settings_' . $settingname, $component);
    $description = get_string($settingsprefix . 'settings_' . $settingname . '_desc', $component);
    $setting = new admin_setting_configpasswordunmask($name, $visiblename, $description, 0);
    $settings->add($setting);
    
    $settingname = 'secret';
    $name = $component . '/' . $settingsprefix . $settingname;
    $visiblename = get_string($settingsprefix . 'settings_' . $settingname, $component);
    $description = get_string($settingsprefix . 'settings_' . $settingname . '_desc', $component);
    $setting = new admin_setting_configpasswordunmask($name, $visiblename, $description, '');
    $settings->add($setting);
    
    // Включение отправки данных чека в кассу
    $name = 'enrol_otpay/acquiropay_kassa';
    $visiblename = get_string('settings_kassa', 'enrol_otpay');
    $description = get_string('settings_kassa_desc', 'enrol_otpay');
    $options = [0 => get_string('no'), 1 => get_string('yes')];
    $setting = new admin_setting_configselect($name, $visiblename, $description, 0, $options);
    $settings->add($setting);
    
    // Ставка НДС товаров
    $name = 'enrol_otpay/acquiropay_tax';
    $visiblename = get_string('settings_tax', 'enrol_otpay');
    $description = get_string('settings_tax_desc', 'enrol_otpay');
    $options = [
        'none' => get_string('settings_tax_first', 'enrol_otpay'),
        'vat0' => get_string('settings_tax_second', 'enrol_otpay'),
        'vat10' => get_string('settings_tax_third', 'enrol_otpay'),
        'vat18' => get_string('settings_tax_fourth', 'enrol_otpay'),
        'vat110' => get_string('settings_tax_fifth', 'enrol_otpay'),
        'vat118' => get_string('settings_tax_sixth', 'enrol_otpay')
    ];
    $setting = new admin_setting_configselect($name, $visiblename, $description, 1, $options);
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