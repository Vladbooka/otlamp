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
 * Плагин записи на курс OTPAY. Точка входа для получения ответа банка.
*
* @package    enrol
* @subpackage otpay
* @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
*/

// Подключение библиотек
require(__DIR__.'/../../../../../config.php');

$PAGE->set_context(null);

require_once($CFG->dirroot . '/enrol/otpay/lib.php');

// Инициализация плагина
$plugin = enrol_get_plugin('otpay');
$plugin->otpay_log('begin', $plugin);

// Валидация входных данных
if ( isset($_REQUEST['orderNumber']) )
{// Получен идентификатор платежа
    // Получение записи о платеже
    $enrolotpay = $DB->get_record('enrol_otpay', [
        'paymentid' => $_REQUEST['orderNumber']
    ]);
}

if( empty($enrolotpay) )
{// Запись о платеже не получена

    // Фиксация ошибки в логе
    $logdata = [
        'remote_addr' => $_SERVER['REMOTE_ADDR'],
        'request' => $_REQUEST
    ];
    $plugin->otpay_log('orderNumber not presented or invalid', $logdata);


    // Вывод ошибки для банка

    $invoiceid = '';
    if ( isset($_REQUEST['invoiceId']) )
    {
        $invoiceid = $_REQUEST['invoiceId'];
    }

    $date = new \DateTime();

    $errorresponse = '<?xml version="1.0" encoding="UTF-8"?><checkOrderResponse performedDatetime="' .
        $date->format("Y-m-d") . "T" . $date->format("H:i:s") . ".000" . $date->format("P") . '" code="1" ' .
        'invoiceId="' . $invoiceid . '" shopId="' .get_config('enrol_otpay', 'yandex_shopid') . '"/>';
    header("HTTP/1.0 200");
    header("Content-Type: application/xml");
    echo $errorresponse;
    exit;
}

// Формирование объекта с входными данными, полученными от банка
$data = new stdClass();
foreach ( $_REQUEST as $key => $value )
{
    $data->$key = $value;
}

// Запуск дальнейшей обработки платежа
try
{
    $plugin->otpay_log('check_data starting', [$enrolotpay,$data]);
    $plugin->check_data($enrolotpay, $data);
} catch ( Exception $ex )
{
    $plugin->otpay_log("exception", $ex->getTraceAsString());
}
