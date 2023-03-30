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
 * Плагин записи на курс OTPAY. Настройки псевдосабплагина yandex.
 *
 * @package    enrol
 * @subpackage otpay
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

if ( $ADMIN->fulltree )
{
    // Способ подключения
    $name = 'enrol_otpay/yandex_connection';
    $visiblename = get_string('yandex_settings_connection', 'enrol_otpay');
    $description = get_string('yandex_settings_connection_desc', 'enrol_otpay');
    $choices = [
        'api' => get_string('yandex_settings_connection_api', 'enrol_otpay'),
        'http' => get_string('yandex_settings_connection_http', 'enrol_otpay'),
    ];
    $setting = new admin_setting_configselect($name, $visiblename, $description, 'api', $choices);
    $settings->add($setting);
    
    // Адрес сервера для запросов
    $name = 'enrol_otpay/yandex_requesturl';
    $visiblename = get_string('yandex_settings_requesturl', 'enrol_otpay');
    $description = get_string('yandex_settings_requesturl_desc', 'enrol_otpay');
    $setting = new admin_setting_configtext($name, $visiblename, $description, '', PARAM_RAW_TRIMMED);
    $settings->add($setting);
    
    // Идентификатор магазина
    $name = 'enrol_otpay/yandex_shopid';
    $visiblename = get_string('yandex_settings_shopid', 'enrol_otpay');
    $description = get_string('yandex_settings_shopid_desc', 'enrol_otpay');
    $setting = new admin_setting_configtext($name, $visiblename, $description, '', PARAM_RAW_TRIMMED);
    $settings->add($setting);
    
    // Пароль магазина
    $name = 'enrol_otpay/yandex_shoppassword';
    $visiblename = get_string('yandex_settings_shoppassword', 'enrol_otpay');
    $description = get_string('yandex_settings_shoppassword_desc', 'enrol_otpay');
    $setting = new admin_setting_configtext($name, $visiblename, $description, '', PARAM_RAW_TRIMMED);
    $settings->add($setting);
    
    // Идентификатор витрины магазина
    $name = 'enrol_otpay/yandex_scid';
    $visiblename = get_string('yandex_settings_scid', 'enrol_otpay');
    $description = get_string('yandex_settings_scid_desc', 'enrol_otpay');
    $setting = new admin_setting_configtext($name, $visiblename, $description, '', PARAM_RAW_TRIMMED);
    $settings->add($setting);
    
//     // Период ожидания подтверждения платежа
//     $name = 'enrol_otpay/yandex_payment_authorization_waiting_period';
//     $visiblename = get_string('yandex_settings_payment_authorization_waiting_period', 'enrol_otpay');
//     $description = get_string('yandex_settings_payment_authorization_waiting_period_desc', 'enrol_otpay');
//     $setting = new admin_setting_configduration($name, $visiblename, $description, 3 * DAYSECS);
//     $settings->add($setting);
    
    // Включение отправки данных чека в кассу (Интеграция с кассой должна быть настроена в магазине яндекса)
    $name = 'enrol_otpay/yandex_kassa';
    $visiblename = get_string('settings_kassa', 'enrol_otpay');
    $description = get_string('settings_kassa_desc', 'enrol_otpay');
    $options = [0 => get_string('no'), 1 => get_string('yes')];
    $setting = new admin_setting_configselect($name, $visiblename, $description, 0, $options);
    $settings->add($setting);
    
    // Система налогооблажения
    $name = 'enrol_otpay/yandex_taxsystem';
    $visiblename = get_string('yandex_settings_taxsystem', 'enrol_otpay');
    $description = get_string('yandex_settings_taxsystem_desc', 'enrol_otpay');
    $options = [
        1 => get_string('yandex_settings_taxsystem_first', 'enrol_otpay'),
        2 => get_string('yandex_settings_taxsystem_second', 'enrol_otpay'),
        3 => get_string('yandex_settings_taxsystem_third', 'enrol_otpay'),
        4 => get_string('yandex_settings_taxsystem_fourth', 'enrol_otpay'),
        5 => get_string('yandex_settings_taxsystem_fifth', 'enrol_otpay'),
        6 => get_string('yandex_settings_taxsystem_sixth', 'enrol_otpay')
    ];
    $setting = new admin_setting_configselect($name, $visiblename, $description, 1, $options);
    $settings->add($setting);
    
    // Ставка НДС товаров
    $name = 'enrol_otpay/yandex_tax';
    $visiblename = get_string('settings_tax', 'enrol_otpay');
    $description = get_string('settings_tax_desc', 'enrol_otpay');
    $options = [
        1 => get_string('settings_tax_first', 'enrol_otpay'),
        2 => get_string('settings_tax_second', 'enrol_otpay'),
        3 => get_string('settings_tax_third', 'enrol_otpay'),
        4 => get_string('settings_tax_fourth', 'enrol_otpay'),
        5 => get_string('settings_tax_fifth', 'enrol_otpay'),
        6 => get_string('settings_tax_sixth', 'enrol_otpay')
    ];
    $setting = new admin_setting_configselect($name, $visiblename, $description, 1, $options);
    $settings->add($setting);
    
    // Список доступных текущему провайдеру способов оплаты
    $name = 'enrol_otpay/yandex_available_paysystems';
    $visiblename = get_string('yandex_settings_available_paysystems', 'enrol_otpay');
    $description = get_string('yandex_settings_available_paysystems_desc', 'enrol_otpay');
    $choises = [];
    $paysystems = $provider->get_paysystems();
    foreach ( $paysystems as $paysystem )
    {
        $choises[$paysystem->code] = $paysystem->name;
    }
    $setting = new admin_setting_configmulticheckbox($name, $visiblename, $description, [], $choises);
    $settings->add($setting);
}