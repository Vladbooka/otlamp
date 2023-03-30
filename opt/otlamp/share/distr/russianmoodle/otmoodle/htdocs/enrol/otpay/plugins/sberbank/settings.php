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
 * Плагин записи на курс OTPAY. Настройки псевдосабплагина Sberbank.
 *
 * @package    enrol
 * @subpackage otpay
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

if ( $ADMIN->fulltree )
{
    // Адрес сервера для запросов
    $name = 'enrol_otpay/sberbank_requesturl';
    $visiblename = get_string('sberbank_settings_requesturl', 'enrol_otpay');
    $description = get_string('sberbank_settings_requesturl_desc', 'enrol_otpay');
    $setting = new admin_setting_configtext($name, $visiblename, $description, '', PARAM_RAW_TRIMMED);
    $settings->add($setting);
    
    // Логин
    $name = 'enrol_otpay/sberbank_login';
    $visiblename = get_string('sberbank_settings_login', 'enrol_otpay');
    $description = get_string('sberbank_settings_login_desc', 'enrol_otpay');
    $setting = new admin_setting_configtext($name, $visiblename, $description, '', PARAM_RAW_TRIMMED);
    $settings->add($setting);
    
    // Пароль
    $name = 'enrol_otpay/sberbank_password';
    $visiblename = get_string('sberbank_settings_password', 'enrol_otpay');
    $description = get_string('sberbank_settings_password_desc', 'enrol_otpay');
    $setting = new admin_setting_configtext($name, $visiblename, $description, '', PARAM_RAW_TRIMMED);
    $settings->add($setting);
    
    // Период ожидания подтверждения платежа
    $name = 'enrol_otpay/sberbank_payment_authorization_waiting_period';
    $visiblename = get_string('sberbank_settings_payment_authorization_waiting_period', 'enrol_otpay');
    $description = get_string('sberbank_settings_payment_authorization_waiting_period_desc', 'enrol_otpay');
    $setting = new admin_setting_configduration($name, $visiblename, $description, 3 * DAYSECS);
    $settings->add($setting);
    
    // Список доступных текущему провайдеру способов оплаты
    $name = 'enrol_otpay/sberbank_available_paysystems';
    $visiblename = get_string('sberbank_settings_available_paysystems', 'enrol_otpay');
    $description = get_string('sberbank_settings_available_paysystems_desc', 'enrol_otpay');
    $choises = [];
    $paysystems = $provider->get_paysystems();
    foreach ( $paysystems as $paysystem )
    {
        $choises[$paysystem->code] = $paysystem->name;
    }
    $setting = new admin_setting_configmulticheckbox($name, $visiblename, $description, [], $choises);
    $settings->add($setting);
}