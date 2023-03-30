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

require_once($CFG->dirroot.'/enrol/otpay/plugins/yandex/classes/sdk/autoload.php');

use YandexCheckout\Model\NotificationEventType;
use YandexCheckout\Model\Notification\NotificationFactory;
use core\notification;

$source = file_get_contents('php://input');
$requestBody = json_decode($source, true);

$plugin = enrol_get_plugin('otpay');

$plugin->otpay_log('Yandex notification init', $requestBody);

if (is_array($requestBody))
{
    try {
        $notificationfactory = new NotificationFactory();
        $notification = $notificationfactory->factory($requestBody);
    } catch (Exception $ex) {
        $plugin->otpay_log("Yandex notification error while processing requestBody payment", $ex->getTraceAsString());
        exit;
    }
    
    $paymentevents = [
        NotificationEventType::PAYMENT_SUCCEEDED,
        NotificationEventType::PAYMENT_CANCELED,
        NotificationEventType::PAYMENT_WAITING_FOR_CAPTURE
    ];
    
    if (in_array($notification->getEvent(), $paymentevents))
    {
        // Для указанных уведомлений объектом является платеж
        
        /** @var YandexCheckout\Request\Payments\PaymentResponse $paymentresponse - объект платежа */
        $paymentresponse = $notification->getObject();
        
        $enrolotpayid = $paymentresponse->getMetadata()->offsetGet('enrolotpayid');
        if (is_null($enrolotpayid))
        {
            $plugin->otpay_log('Yandex notification error. Enrolotpayid not found', json_decode(json_encode($paymentresponse),true));
            exit;
        }
        
        $enrolotpay = $DB->get_record('enrol_otpay', ['id' => $enrolotpayid]);
        if (empty($enrolotpay))
        {
            $plugin->otpay_log('Yandex notification error. Enrolotpay record is empty', ['id' => $enrolotpayid]);
            exit;
        }
        
        if ($enrolotpay->externalpaymentid == $paymentresponse->getId())
        {
            // Нашли запись в enrol_otpay соответствующую ответу от банка
            // запустим обработку статуса платежа по данной записи
            $plugin->process_payment_status($enrolotpay);
        }
        
    }
}

?>