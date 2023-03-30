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
 * Плагин записи на курс OTPAY. Класс работы с API yandex.
 *
 * @package    enrol
 * @subpackage otpay
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace enrol_otpay\plugins\yandex;

use enrol_otpay_plugin;
use otpay_yandex;
use moodle_url;
use moodle_exception;

class connector
{
    /**
     * URL для отправки платежной формы
     *
     * @var string
     */
    private $requesturl = null;
    /**
     * Идентификатор магазина, который используется для работы с API
     *
     * @var string
     */
    private $shopid = null;
    /**
     * Пароль магазина, который используется для работы с API
     *
     * @var string
     */
    private $shoppassword = null;
    
    /**
     * Идентификатор витрины магазина, который используется для работы с API
     *
     * @var string
     */
    private $scid = null;
    
    /**
     * Плагин подписки
     *
     * @var enrol_otpay_plugin
     */
    private $enrolplugin = null;
    
    /**
     * Плагин провайдера
     *
     * @var otpay_yandex
     */
    private $provider = null;
    
    /**
     * Инициализация соединения
     */
    public function __construct(enrol_otpay_plugin $enrolplugin, otpay_yandex $provider)
    {
        $this->enrolplugin = $enrolplugin;
        $this->provider = $provider;
        
        // Инициализация соединения
        $this->init_connection();
    }
    
    /**
     * Зарегистрировать заказ в Сбербанке
     *
     * @param int $enrolmentid - ID заявки на подписку в курсе
     *
     * @return array - Массив с полями [orderid, formurl]
     *
     * @throws \moodle_exception - Исключение с описанием ошибки
     */
    public function send_payment_form($enrolmentid)
    {
        global $DB;
        
        $enrolment = $this->enrolplugin->get_enrolment($enrolmentid);
        if ( empty($enrolment) )
        {// Подписка пользователя не найдена
            $this->enrolplugin->rise_error(
                0, 'error_provider_yandex_action_register_enrolment_not_found');
        }
        
        // Получение стоимости c переводом а копейки/центы
        $amount = $enrolment->amount;

        // Экземпляр записи
        $instance = $DB->get_record('enrol', ['id' => $enrolment->instanceid], '*', MUST_EXIST);
        
        // Формирование URL успешной оплаты
        $successurl = new moodle_url('/enrol/otpay/return.php',
            [
                'id' => $enrolment->courseid,
                'ok' => '1',
                'ko' => '0',
                'enrolotpayid' => $enrolmentid
            ]
        );
        
        // Формирование URL НЕуспешной оплаты
        $failurl = new moodle_url('/enrol/otpay/return.php',
            [
                'id' => $enrolment->courseid,
                'ok' => '0',
                'ko' => '1',
                'enrolotpayid' => $enrolmentid
            ]
        );

        // Данные пользователя
        $user = $DB->get_record('user', ['id' => $enrolment->userid]);
        
        $postdata = [
            'shopid' => $this->shopid,
            'scid' => $this->scid,
            'sum' => $amount,
            'customerNumber' => $enrolment->userid,
            'orderNumber' => $enrolment->paymentid,
            'shopSuccessURL' => $successurl->out(false),
            'shopFailURL' => $failurl->out(false),
            'cps_email' => $user->email
        ];
        
        // Проверим, включена ли интеграция с онлайн кассой
        $integration_status = get_config('enrol_otpay', 'yandex_kassa');
        if ( $integration_status )
        {// Интеграция включена, сформироуем данный для чека
            
            // Сбор данных онлайн-кассы
            $check_info = [
                'customerContact' => $user->email,
                // Система налогооблажения
                'taxSystem' => get_config('enrol_otpay', 'yandex_taxsystem'),
                // В данном случае товар один - курс
                'items' => [
                    [
                        // Количество
                        'quantity' => 1,
                        'price' => [
                            'amount' => format_float($amount, 2, false, false),
                        ],
                        // Ставка НДС
                        'tax' => $this->provider->get_vat($instance),
                        // Название товара
                        'text' => 'course' . $enrolment->courseid
                ]]
            ];
            
            // Добавление поля
            $postdata['ym_merchant_receipt'] = htmlentities(json_encode($check_info));
        }
        
        ob_clean();
        //отправляем пост-запрос через страницу с формой, отправляемую javascriptом
        $this->enrolplugin->redirect_post($this->requesturl, $postdata);
        exit();
    }
    

    /**
     * CheckOrder request processing. We suppose there are no item with price less
     * than 100 rubles in the shop.
     * @param  array $request payment parameters
     * @return string         prepared XML response
     */
    public function check_order($requestdata)
    {
        $response = null;
    
        $checkdataresult = [];
    
        $checkmd5 = $this->check_md5($requestdata);
    
        if ( ! $checkmd5 )
        {
            // MD5 checking fails, respond with "1" error code
            $response = $this->build_response('checkOrder', $requestdata->invoiceId, 1);
            $checkdataresult[] = [
                'requestmd5' => $requestdata->md5
            ];
        } else
        {
            $response = $this->build_response('checkOrder', $requestdata->invoiceId, 0);
        }
    
        $this->send_response($response);
    
        return [
            'succeed' => empty($checkdataresult),
            'errors' => $checkdataresult,
            'waitdebit' => true,
            'needdebit' => false,
            'paymentdata' => []
        ];
    }
    
    /**
     * PaymentAviso request processing.
     * @param  array $request payment parameters
     * @param object $enrolotpay
     *            - объект otpay-платежа
     *
     * @return string prepared response in XML format
     */
    public function payment_aviso( $requestdata, $enrolotpay )
    {
        $response = null;

        $checkdataresult = [];
        
        $checkmd5 = $this->check_md5($requestdata);
        
        if ( ! $checkmd5 )
        {
            $verifysignresult = 'signature incorrect';
            // MD5 checking fails, respond with "1" error code
            $response = $this->build_response('paymentAviso', $requestdata->invoiceId, 1);
            $checkdataresult[] = [
                'requestmd5' => $requestdata->md5
            ];
        } else
        {
            $verifysignresult = 'signature correct';
            $response = $this->build_response('paymentAviso', $requestdata->invoiceId, 0);
        }
        
        // Умножение на 100 для корректного сравнения
        $otpay_amount = $enrolotpay->amount * 100;
        $paid_amount = $requestdata->orderSumAmount * 100;
        
        // Проверка суммы оплаты
        if ( $otpay_amount != $paid_amount )
        {
            $checkdataresult[] = [
                'need_amount' => $otpay_amount,
                'paid_amount' => $paid_amount
            ];
        }
        
        $this->enrolplugin->otpay_log('send_response', $response);
        $this->send_response($response);

        return [
            'result' => empty($checkdataresult),
            'checkdata' => $checkdataresult,
            'verifysignresult' => $verifysignresult
        ];
    }
    
    /**
     * Checking the MD5 sign.
     * @param  array $request payment parameters
     * @return bool true if MD5 hash is correct
     */
    private function check_md5($requestdata)
    {
        $str = $requestdata->action . ";" .
            $requestdata->orderSumAmount . ";" . $requestdata->orderSumCurrencyPaycash . ";" .
            $requestdata->orderSumBankPaycash . ";" . $requestdata->shopId . ";" .
            $requestdata->invoiceId . ";" . trim($requestdata->customerNumber) . ";" . $this->shoppassword;
        $this->enrolplugin->otpay_log('String to md5', $str);
        $md5 = md5($str);

        if (strtoupper($md5) != strtoupper($requestdata->md5) )
        {
            $this->enrolplugin->otpay_log('verify failed', "Wait for md5:" . $md5 . ", recieved md5: " . $requestdata->md5);
            return false;
        }
        return true;
    }

    /**
     * Building XML response.
     * @param  string $functionName  "checkOrder" or "paymentAviso" string
     * @param  string $invoiceId     transaction number
     * @param  string $result_code   result code
     * @param  string $message       error message. May be null.
     * @return string                prepared XML response
     */
    private function build_response($functionName, $invoiceId, $result_code, $message = null)
    {
        try
        {
            $date = new \DateTime();
            $response = '<?xml version="1.0" encoding="UTF-8"?><' . $functionName . 'Response performedDatetime="' .
            $date->format("Y-m-d") . "T" . $date->format("H:i:s") . ".000" . $date->format("P") . '" code="' . $result_code . '" ' .
            ($message != null ? 'message="' . $message . '"' : "") . ' invoiceId="' . $invoiceId . '" shopId="' . $this->shopid . '"/>';
            return $response;
        } catch (\Exception $e)
        {
            $this->enrolplugin->otpay_log('buildResponseException', $e);
        }
        return null;
    }
    
    public function send_response($responseBody)
    {
        $this->enrolplugin->otpay_log("Response", $responseBody);
        header("HTTP/1.0 200");
        header("Content-Type: application/xml");
        echo $responseBody;
        //exit;
    }
    
    /**
     * Инициализация соединения с API
     *
     * @param array $options - Дополнительные опции инициализации
     *
     * @return void
     *
     * @throws \moodle_exception - Исключение с описанием ошибки
     */
    protected function init_connection($options = [])
    {
        // Добавление данных для авторизации в api
        $this->requesturl = get_config('enrol_otpay', 'yandex_requesturl');
        $this->shopid = get_config('enrol_otpay', 'yandex_shopid');
        $this->shoppassword = get_config('enrol_otpay', 'yandex_shoppassword');
        $this->scid = get_config('enrol_otpay', 'yandex_scid');
        
        if ( empty($this->shopid) || empty($this->shoppassword) || empty($this->scid) || empty($this->requesturl) )
        {// Настройки не указаны
            $this->enrolplugin->rise_error(
                'error_provider_yandex_init_connection_errorcode_settings_invalid'
            );
        }
    }
}