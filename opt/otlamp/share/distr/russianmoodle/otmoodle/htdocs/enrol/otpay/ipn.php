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
require('../../config.php');

require_once($CFG->dirroot . '/enrol/otpay/lib.php');

// Инициализация плагина
$plugin = enrol_get_plugin('otpay');

// Валидация входных данных
if ( isset($_GET['enrolotpayid']) )
{// ID подписки указан
    $enrolotpayid = (int)$_GET['enrolotpayid'];
} else
{// ID подписки не указан
    $logdata = [
        'remote_addr' => $_SERVER['REMOTE_ADDR'],
        'post' => $_POST,
        'get' => $_GET
    ];
    $plugin->otpay_log('Error ipn. Enrolotpayid not set', $logdata);
    die();
}

// Валидация формата входных данных
if ( empty($_POST) | $_GET != [
    'enrolotpayid' => $enrolotpayid
] | empty($enrolotpayid) | (! $enrolotpay = $DB->get_record('enrol_otpay', 
    [
        'id' => $enrolotpayid
    ])) )
{
    
    $logdata = [
        'remote_addr' => $_SERVER['REMOTE_ADDR'],
        'post' => $_POST,
        'get' => $_GET,
        'enrolotpay' => $enrolotpay
    ];
    $plugin->otpay_log('Error ipn. Invalid request format', $logdata);
    die();
} else
{// Валидация прошла успешно
    $logdata = [
        'remote_addr' => $_SERVER['REMOTE_ADDR'],
        'post' => $_POST,
        'get' => $_GET,
        'enrolotpay' => $enrolotpay
    ];
    $plugin->otpay_log('Request', $logdata);
}

// Формирование объекта с входными данными от банка
$data = new stdClass();
foreach ( $_POST as $key => $value )
{
    $data->$key = $value;
}

try
{
    $plugin->check_data($enrolotpay, $data);
} catch ( Exception $ex )
{
    $plugin->otpay_log("exception", $ex->getTraceAsString());
}
