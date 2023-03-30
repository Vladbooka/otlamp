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
    // Инициализация плагина
    $plugin = enrol_get_plugin('otpay');
    
    // Получение списка провайдеров
    $providers = $plugin->get_providers();
    
    $fields =  [
        'recipient' => PARAM_RAW_TRIMMED,
        'kpp' => PARAM_RAW_TRIMMED,
        'inn' => PARAM_RAW_TRIMMED,
        'oktmo' => PARAM_RAW_TRIMMED,
        'raccount' => PARAM_RAW_TRIMMED,
        'rinn' => PARAM_RAW_TRIMMED,
        'bik' => PARAM_RAW_TRIMMED,
        'kaccount' => PARAM_RAW_TRIMMED,
        'kbk' => PARAM_RAW_TRIMMED
    ];
    
    foreach ( $fields as $setting => $type )
    {
        $config_name = 'enrol_otpay/accountgenerate_' . $setting;
        $visiblename = get_string($setting, 'enrol_otpay');
        $setting = new admin_setting_configtext($config_name, $visiblename, '', '', $type);
        $settings->add($setting);
    }
    
    $select = [
        'id' => get_string('settings_account_number_id', 'enrol_otpay'),
        'hash' => get_string('settings_account_number_hash', 'enrol_otpay')
    ];
    $config_name = 'enrol_otpay/accountgenerate_account_number';
    $visiblename = get_string('settings_account_number', 'enrol_otpay');
    $setting = new admin_setting_configselect($config_name, $visiblename, '', 'id', $select);
    $settings->add($setting);
}
