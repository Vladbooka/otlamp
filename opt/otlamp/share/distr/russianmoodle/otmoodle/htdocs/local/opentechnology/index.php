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
 * Get opentechnology serial and opentechnology status from api.opentechnology.ru
 *
 * @package    local_opentechnology
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use core\notification;
use local_opentechnology\otserial_base_exception;
use \local_opentechnology\output\renderer;
use local_opentechnology\output\techinfo;

require_once(__DIR__ . '/../../config.php');
require_once($CFG->dirroot . '/local/opentechnology/classes/otserial.php');
require_once($CFG->dirroot . '/local/opentechnology/locallib.php');
require_once($CFG->libdir.'/adminlib.php');

admin_externalpage_setup('local_opentechnology');

$otapihtml = '';

// Создание объекта OTAPI
$otapi = new local_opentechnology\otserial();
$otapioptions = [
    'settings_page_name' => 'local_opentechnology',
    'plugin_string_identifiers' => [
        'otserial_settingspage_visiblename' => 'pluginname',
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
];

$otapihtml = $otapi->prepare_extenalpage_html('local_opentechnology', $otapioptions);

// Формирование данных информационного блока otapi
$otapitable = local_opentechnology_get_otapi_table();
$otapitable['table']['info'] = $otapihtml;

// Сбор технической и статистической информации об инсталляции
//$about = local_opentechnology_get_about();
// Получение данных инфомационных блоков страницы техподдержки
$tables = local_opentechnology_get_info_tables();

// Добавление информационного блока otapi
array_unshift($tables, $otapitable);

//рендеринг
$renderer = $PAGE->get_renderer('local_opentechnology');
$techinfo = new techinfo($tables);
$html = $renderer->render($techinfo);

// //////////////////////////////////////
// Вывод

echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('pageheader', 'local_opentechnology'));

echo $html;

echo $OUTPUT->footer();
