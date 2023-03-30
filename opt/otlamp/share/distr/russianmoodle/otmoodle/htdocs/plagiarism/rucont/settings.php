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
 * Плагин определения заимствований Руконтекст. Настройки.
 *
 * @package    plagiarism
 * @subpackage rucont
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// Подключение библиотек
require_once(dirname(dirname(__FILE__)) . '/../config.php');
require_once($CFG->libdir.'/adminlib.php');
require_once($CFG->dirroot.'/plagiarism/rucont/lib.php');
require_once($CFG->dirroot . '/plagiarism/rucont/classes/otserial.php');

use core\notification;
use local_opentechnology\otserial_base_exception;
use plagiarism_rucont\settings_form;

// Установка расширенной страницы настроек
admin_externalpage_setup('plagiarismrucont');

// Получение GET параметров
$page = optional_param('page', 'tarif', PARAM_ALPHA);
$action = optional_param('action', '', PARAM_ALPHANUMEXT);

// Cсылка на настройки плагина
$link_base = new moodle_url('/plagiarism/rucont/settings.php');

// Массив ошибок
$errors = [];

// Создание объекта OTAPI
$otapi = new plagiarism_rucont\otserial();
$otapioptions = [
    'plugin_string_identifiers' => [
        'otserial_settingspage_otserial' => 'otserial',
        'otserial_settingspage_issue_otserial' => 'get_otserial',
        'otserial_settingspage_otservice' => 'otservice',
        'otserial_error_otserial_check_fail' => 'otserial_check_fail',
        'otserial_notification_otserial_check_ok' => 'otserial_check_ok',
        'otserial_error_tariff_wrong' => 'otserial_tariff_wrong',
        'otserial_notification_otservice_active' => 'otservice_active',
        'otserial_error_otservice_expired' => 'otservice_expired',
        'otserial_notification_otservice_unlimited' => 'otservice_unlimited',
    ]
];


// Объявление плагина
$plagiarismpluginrucont = new plagiarism_plugin_rucont();

// Поддерживаемые модули
$supported_mods = ['assign'];

// Настройки плагина
$pluginconfig = ['enable' => 0];

// Получение уведомлений
if ( isset($_SESSION["notice"]) )
{
    $notice = $_SESSION["notice"];
    $notice["type"] = (empty($_SESSION["notice"]["type"])) ? "general" : $_SESSION["notice"]["type"];
    unset($_SESSION["notice"]);
} else {
    $notice = NULL;
}

$configfield = $DB->get_record('config_plugins', [
    'name' => 'enable',
    'plugin' => 'plagiarism_rucont'
]);
if ( $configfield )
{
    $pluginconfig['enable'] = $configfield->value;
}

foreach ( $supported_mods as $mod )
{
    $tmp_pluginconfig = settings_form::get_config_settings('mod_'.$mod);
    $pluginconfig = array_merge($pluginconfig, $tmp_pluginconfig);
}
$plugindefaults = settings_form::get_settings();

// Форма настроек
$url = new \moodle_url('/plagiarism/rucont/settings.php', ['page' => $page]);
$customdata = new \stdClass();
$customdata->page = $page;
$customdata->pluginconfig = $pluginconfig;
$customdata->plugindefaults = $plugindefaults;
$form = new settings_form($url, $customdata);

// Обработчики действий
switch ( $action )
{
        // Очистка
        case 'deletefile':
            
            $id = optional_param('id', 0, PARAM_INT);
            $DB->delete_records('plagiarism_rucont_files', ['id' => $id]);

            redirect(new \moodle_url('/plagiarism/rucont/settings.php', ['page' => 'errors']));
            break;
            
        // обработка действий для otserial
        case 'issue_otserial':
            if (method_exists($otapi, 'settings_page_action_issue_otserial'))
            {
                try {
                    // обработка запрошенного действия
                    $otapi->settings_page_action_issue_otserial($otapioptions);
                } catch(otserial_base_exception $ex)
                {
                    notification::error($ex->getMessage());
                }
                // перенаправление обратно на страницу управления тарифами
                redirect($link_base);
            }
            break;
       default :
           break;
}

// Обработчик
$form->process();

// Шапка
echo $OUTPUT->header();

// HTML код
$html = '';

// Формирование вкладок
$form->draw_settings_tab_menu($page, $notice);

// Отображение ошибок
if ( count($errors) > 0 )
{
    $return .= html_writer::div($OUTPUT->notification(implode('<br>\n', $errors)));
}

// Отображение в зависимости от вкладки
switch ($page)
{
    // Тарифный план
    case 'tarif':
        
        $html = '';
        
        if ($otapi->plugin_has_configured_otapi_data())
        {
            // отображение серийника в отформатированном виде
            $settingname = $otapi->get_string('otserial_settingspage_otserial', null, $otapioptions);
            $settingvalue = $otapi->prepare_info_otserial($otapioptions);
            $html .= html_writer::div($settingname);
            $html .= html_writer::div($settingvalue);
            
            // сообщение, что серийник действителен (или нет)
            list($settingvalue, $otserialdata) = $otapi->prepare_info_otserial_check($otapioptions);
            $html .= $settingvalue;
            
            if (!is_null($otserialdata))
            {
                // тариф, указанный в серийнике
                $settingvalue = $otapi->prepare_info_otservice($otserialdata, $otapioptions);
                $html .= html_writer::div($settingvalue);
                
                $otserialdata->tariff == $otserialdata->tariff ?? 'free';
                if ($otserialdata->tariff == 'free')
                {
                    $html .= $OUTPUT->notification(get_string('demo_settings', 'plagiarism_rucont', @$otserialdata->message));
                    
                } else
                {
                    // срок действия тарифа
                    $html .= $otapi->prepare_info_otserial_otservice_expiry_time($otserialdata, $otapioptions);
                }
            }
            
        } else
        {
            // название настройки для отображения
            $settingname = $otapi->get_string('otserial_settingspage_otserial', null, $otapioptions);
            // Адрес получения серийного кода для плагина
            $url = new \moodle_url($link_base, ['action' => 'issue_otserial']);
            // Ссылка на получение серийного кода для плагина
            $link = html_writer::link($url, $otapi->get_string('otserial_settingspage_issue_otserial', null, $otapioptions));
            
            $html .= html_writer::div($settingname);
            $html .= $link;
        }
        echo $html;
        
        break;
    case 'configuration':
    case 'defaults':
        // Получение данных о тарифе
        $response = $otapi->issue_serial_and_get_data();
        if ( isset($response['response']) && $response['response']->status === 'ok' && $response['response']->tariff === 'rucont' )
        {// Тариф верный
            $form->display();
        } else
        {// Тариф не доступен
            echo $OUTPUT->notification(get_string('demo_settings', 'plagiarism_rucont'));
        }
        break;
}

echo $OUTPUT->footer();
