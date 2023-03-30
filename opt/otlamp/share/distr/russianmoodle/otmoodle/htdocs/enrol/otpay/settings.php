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
 * Плагин записи на курс OTPAY. Глобальные настройки плагина.
 *
 * @package    enrol
 * @subpackage otpay
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once ($CFG->dirroot . '/enrol/otpay/lib.php');
require_once ($CFG->dirroot . '/enrol/otpay/classes/otserial.php');

if ($ADMIN->fulltree)
{
    
    // Создание объекта OTAPI
    $otapi = new \enrol_otpay\otserial();
    // Добавление страницы управления тарифом
    $response = $otapi->settings_page_fill($settings, [
        'plugin_string_identifiers' => [
            'otserial_settingspage_otserial' => 'otserial',
            'otserial_settingspage_issue_otserial' => 'get_otserial',
            'otserial_settingspage_otservice' => 'otservice',
            'otserial_exception_already_has_serial' => 'already_has_otserial',
            'otserial_error_get_otserial_fail' => 'get_otserial_fail',
            'otserial_error_otserial_check_fail' => 'otserial_check_fail',
            'otserial_error_tariff_wrong' => 'otserial_tariff_wrong',
            'otserial_error_otservice_expired' => 'otservice_expired',
            'otserial_notification_otserial_check_ok' => 'otserial_check_ok',
            'otserial_notification_otservice_active' => 'otservice_active',
            'otserial_notification_otservice_unlimited' => 'otservice_unlimited',
        ],
    ]);
    
    if (!is_null($response))
    {
        $name = 'enrol_otpay/general';
        $visiblename = get_string('settings_general', 'enrol_otpay');
        $description = get_string('settings_general_desc', 'enrol_otpay');
        $setting = new admin_setting_heading(
            'enrol_otpay/general',
            $visiblename,
            $description
        );
        $settings->add($setting);
        
        // Ссылка на панель купонов
        $url_coupons = new moodle_url('/enrol/otpay/coupons.php');
        $settings->add(new admin_setting_heading('enrol_otpay/url_coupons', '', html_writer::link($url_coupons->out(false), get_string('url_coupon_panel', 'enrol_otpay'), ['class' => 'button'])));
        
        //                         // Ссылка на панель администрирования
        //                         $url_admin_panel = new moodle_url('/enrol/otpay/apanel.php');
        //                         $settings->add(new admin_setting_heading('enrol_otpay/url_admin', '', html_writer::link($url_admin_panel->out(false), get_string('url_admin_panel', 'enrol_otpay'))));
        
        
        // Роль по умолчанию
        $options = get_default_enrol_roles(context_system::instance());
        $student = get_archetype_roles('student');
        $student = reset($student);
        $name = 'enrol_otpay/roleid';
        $visiblename = get_string('settings_roleid', 'enrol_otpay');
        $description = get_string('settings_roleid_desc', 'enrol_otpay');
        $setting = new admin_setting_configselect(
            $name,
            $visiblename,
            $description,
            $student->id,
            $options
        );
        $settings->add($setting);
        
        // Состояние экземпляра подписки по умолчанию
        $options = [
            ENROL_INSTANCE_ENABLED => get_string('yes'),
            ENROL_INSTANCE_DISABLED => get_string('no')
        ];
        $name = 'enrol_otpay/status';
        $visiblename = get_string('settings_status', 'enrol_otpay');
        $description = get_string('settings_status_desc', 'enrol_otpay');
        $setting = new admin_setting_configselect(
            $name,
            $visiblename,
            $description,
            ENROL_INSTANCE_ENABLED,
            $options
            );
        $settings->add($setting);
        
        // Время для уведомлений о завершающихся подписках
        $options = [];
        for ($i = 0; $i < 24; $i ++)
        {
            $options[$i] = $i;
        }
        $name = 'enrol_otpay/expirynotifyhour';
        $visiblename = get_string('expirynotifyhour', 'core_enrol');
        $description = '';
        $settings->add(
            new admin_setting_configselect(
                $name,
                $visiblename,
                $description,
                6,
                $options
            )
        );
        
        // Действие при истечении времени подписки
        $options = [
            ENROL_EXT_REMOVED_KEEP => get_string('extremovedkeep', 'enrol'),
            ENROL_EXT_REMOVED_SUSPENDNOROLES => get_string( 'extremovedsuspendnoroles', 'enrol'),
            ENROL_EXT_REMOVED_UNENROL => get_string('extremovedunenrol', 'enrol')
        ];
        $name = 'enrol_otpay/expiredaction';
        $visiblename = get_string('settings_expiredaction', 'enrol_otpay');
        $description = get_string('settings_expiredaction_desc', 'enrol_otpay');
        $settings->add(
            new admin_setting_configselect(
                $name,
                $visiblename,
                $description,
                ENROL_EXT_REMOVED_SUSPENDNOROLES,
                $options
            )
        );
        
        $name = 'enrol_otpay/emailtransfer';
        $visiblename = get_string('settings_emailtransfer', 'enrol_otpay');
        $description = get_string('settings_emailtransfer_desc', 'enrol_otpay');
        $settings->add(
            new admin_setting_configcheckbox(
                $name,
                $visiblename,
                $description,
                0
            )
        );
        
        // Инициализация плагина
        $plugin = enrol_get_plugin('otpay');
        // Получение списка провайдеров
        $providers = $plugin->get_providers();
        
        // Добавление глобальных настроек псевдосабплагинов - провайдеров
        foreach ( $providers as $providername => $provider )
        {
            $subpluginsettings = "{$CFG->dirroot}/enrol/otpay/plugins/{$providername}/settings.php";
            if ( file_exists($subpluginsettings) )
            {
                // Заголовок провайдера
                $name = "enrol_otpay/{$providername}_general";
                $visiblename = get_string("{$providername}_settings_general", 'enrol_otpay');
                $description = get_string("{$providername}_settings_general_desc", 'enrol_otpay');
                $setting = new admin_setting_heading($name, $visiblename, $description);
                $settings->add($setting);
                
                include_once($subpluginsettings);
            }
        }
    }
}