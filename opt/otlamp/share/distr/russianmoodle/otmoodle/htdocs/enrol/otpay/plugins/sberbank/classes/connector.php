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
 * Плагин записи на курс OTPAY. Класс работы с API Сбербанка.
 *
 * @package    enrol
 * @subpackage otpay
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace enrol_otpay\plugins\sberbank;

use enrol_otpay_plugin;
use otpay_sberbank;
use moodle_url;
use stdClass;
use context_system;
use context_course;
use context_user;
use moodle_exception;

class connector
{
    /**
     * Логин, который используется для работы с API
     *
     * @var string
     */
    private $login = null;

    /**
     * Пароль, который используется для работы с API
     *
     * @var string
     */
    private $password = null;

    /**
     * Плагин подписки
     *
     * @var enrol_otpay_plugin
     */
    private $enrolplugin = null;

    /**
     * Плагин провайдера
     *
     * @var otpay_sberbank
     */
    private $provider = null;

    /**
     * Инициализация соединения
     */
    public function __construct(enrol_otpay_plugin $enrolplugin, otpay_sberbank $provider)
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
    public function create_order($enrolmentid)
    {
        global $DB;

        $enrolment = $this->enrolplugin->get_enrolment($enrolmentid);
        if ( empty($enrolment) )
        {// Подписка пользователя не найдена
            $this->enrolplugin->rise_error(
                0, 'error_provider_sberbank_action_register_enrolment_not_found');
        }

        // Конфигурация провайдера
        $providerconfig = $this->provider->otpay_config();

        // Получение валюты
        $currency = (string)$enrolment->currency;
        // Нормализация валюты
        if ( ! isset($providerconfig->currencycodes[(int)$currency]) )
        {// Числовой код валюты не найден

            // Поиск валюты по буквенному индексу
            $code = array_search($currency, $providerconfig->currencycodes);
            if ( $code )
            {// Валюта найдена по буквенному индексу
                $currency = $code;
            } else
            {// Указанная в подписке валюта не найдена среди доступных
                // Установка валюты по умолчанию
                $currency = $providerconfig->defaultcurrencycode;
            }
        }

        // Получение стоимости c переводом а копейки/центы
        $amount = $enrolment->amount * 100;

        // Формирование URL успешной оплаты
        $successurl = new moodle_url('/enrol/otpay/return.php',
            [
                'id' => $enrolment->courseid,
                'ok' => '1',
                'ko' => '0',
                'enrolotpayid' => $enrolmentid
            ]
        );

        $user = $DB->get_record('user', ['id' => $enrolment->userid]);


        $extradata = [];

        $enrolotpayoptions = unserialize($enrolment->options);
        $couponcodes = (array)($enrolotpayoptions['couponcodes'] ?? []);
        $extradata['couponcodes'] = implode(', ', $couponcodes);
        try {
            $vatval = $enrolotpayoptions['vat_value'] ?? null;
            $extradata['vat'] = $this->provider->get_vat_string($vatval);
        } catch(\Exception $ex) {}

        $description = $this->provider->get_payment_description($enrolment, null, $extradata);


        // Установка языка
        $language = current_language();

        // Установка ID пользователя
        $userid = 0;
        if ( ! empty($user->id) )
        {
            $userid = $user->id;
        }

        // Инициализация запроса
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->requesturl.'/payment/rest/register.do');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_POSTFIELDS,
            [
                'userName' => $this->login,
                'password' => $this->password,
                'orderNumber' => $enrolment->paymentid,
                'amount' => $amount,
                'currency' => $currency,
                'returnUrl' => $successurl->out(false),
                'description' => $description,
                'language' => $language,
                'clientId' => $userid
            ]
        );
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_TIMEOUT, 60);
        // Исполнение запроса
        $result = curl_exec($ch);
        curl_close($ch);

        // Обработка результатов запроса
        if ( empty($result) )
        {
            $this->enrolplugin->rise_error(
                'error_provider_sberbank_action_register_connection_failed', $enrolment->id);
        }
        // Декодирование данных
        $result = json_decode($result);

        // Проверка на ошибки в ответе от банка
        if ( isset($result->errorCode) )
        {// Получен код ошибки от банка

            // Генерация данных об ошибке
            $errorcode = 'error_provider_sberbank_action_register_response_errorcode_undefined';
            $errorstring = 'errorCode'.$result->errorCode;
            if ( isset($result->errorMessage) )
            {
                $errorstring = $result->errorMessage;
            }
            $errordata = new stdClass();
            switch ($result->errorCode)
            {
                case '1' :
                    // Заказ с таким $enrolment->paymentid уже имеется в системе
                    $errorcode = 'error_provider_sberbank_action_register_response_errorcode_order_already_exist';
                    break;
                case '3' :
                    // Неизвестная валюта
                    $errorcode = 'error_provider_sberbank_action_register_response_errorcode_invalid_currency';
                    $errordata->currency = $currency;
                    break;
                case '4' :
                    // Отсутствует обязательный параметр запроса на регистрацию
                    $errorcode = 'error_provider_sberbank_action_register_response_errorcode_required_param_not_found';
                    break;
                case '5' :
                    // Ошибка в параметре запроса на регистрацию
                    $errorcode = 'error_provider_sberbank_action_register_response_errorcode_invalid_required_param';
                    break;
                case '7' :
                    // Системная ошибка API
                    $errorcode = 'error_provider_sberbank_action_register_response_errorcode_sberbank_api_systemerror';
                    break;
            }
            $this->enrolplugin->rise_error(
                $errorcode, $enrolment->id, $errordata, $errorstring);
        }
        if ( ! isset($result->orderId) || ! isset($result->formUrl) )
        {// Внутренняя ошибка API
             $this->enrolplugin->rise_error(
                 'error_provider_sberbank_action_register_response_errorcode_no_response', $enrolment->id);
        }

        // Генерация данных ответа
        return ['orderid' => $result->orderId, 'formurl' => $result->formUrl];
    }

    /**
     * Получить данные о заказе
     *
     * @param int $enrolmentid - ID заявки на подписку в курсе
     *
     * @return array - Массив с полями [ordernumber, orderstatus, amount, currency]
     *
     * @throws \moodle_exception - Исключение с описанием ошибки
     */
    public function get_order_info($enrolmentid)
    {
        global $DB;

        $enrolment = $this->enrolplugin->get_enrolment($enrolmentid);
        if ( empty($enrolment) )
        {// Подписка пользователя не найдена
            $this->enrolplugin->rise_error(
                0, 'error_provider_sberbank_action_getorderstatus_enrolment_not_found');
        }

        // Конфигурация провайдера
        $providerconfig = $this->provider->otpay_config();

        // Установка языка
        $language = current_language();

        // Инициализация запроса
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->requesturl.'/payment/rest/getOrderStatus.do');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_POSTFIELDS, [
                'userName' => $this->login,
                'password' => $this->password,
                'orderId' => $enrolment->externalpaymentid,
                'language' => $language
            ]
        );
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_TIMEOUT, 60);
        // Исполнение запроса
        $result = curl_exec($ch);
        curl_close($ch);

        // Обработка результатов запроса
        if ( empty($result) )
        {
            $this->enrolplugin->rise_error(
                'error_provider_sberbank_action_getorderstatus_connection_failed', $enrolment->id);
        }

        // Декодирование данных
        $result = json_decode($result);

        // Проверка на ошибки в ответе от банка
        if ( isset($result->errorCode) )
        {// Получен код ошибки от банка
            // Генерация данных об ошибке

            $errorstring = 'errorCode'.$result->errorCode;
            if ( isset($result->errorMessage) )
            {
                $errorstring = $result->errorMessage;
            }
            $errordata = new stdClass();
            switch ($result->errorCode)
            {
                case '2' :
                    // Заказ отклонен по причине ошибки в реквизитах платежа
                    $errorcode = 'error_provider_sberbank_action_getorderstatus_response_errorcode_invalid_payment_details';
                    break;
                case '5' :
                    // Ошибка значения параметра запроса
                    $errorcode = 'error_provider_sberbank_action_getorderstatus_response_errorcode_invalid_required_param';
                    break;
                case '6' :
                    // Незарегистрированный OrderId
                    $errorcode = 'error_provider_sberbank_action_getorderstatus_response_errorcode_invalid_orderid';
                    break;
                default:
                    // Неизвестная ошибка
                    $errorcode = 'error_provider_sberbank_action_getorderstatus_response_errorcode_undefined';
                    break;
            }
            $this->enrolplugin->rise_error(
                $errorcode, $enrolment->id, $errordata, $errorstring);
        }

        if ( ! isset($result->OrderStatus) )
        {// Заказ не был найден на стороне банка
             $this->enrolplugin->rise_error(
                 'error_provider_sberbank_action_getorderstatus_response_errorcode_order_not_found', $enrolment->id);
        }

        if ( ! isset($result->OrderNumber) || ! isset($result->Amount) || ! isset($result->currency) )
        {// Внутренняя ошибка API
             $this->enrolplugin->rise_error(
                 'error_provider_sberbank_action_getorderstatus_response_errorcode_no_response', $enrolment->id);
        }

        return [
            'ordernumber' => $result->OrderNumber,
            'orderstatus' => $result->OrderStatus,
            'amount' => $result->Amount,
            'currency' => $result->currency
        ];
    }


    /**
     * Отмена оплаты заказа
     *
     * @param int $enrolmentid - ID заявки на подписку в курсе
     *
     * @return bool - Результат отмены заказа
     *
     * @throws \moodle_exception - Исключение с описанием ошибки
     */
    public function order_reverse($enrolmentid)
    {
        global $DB;

        $enrolment = $this->enrolplugin->get_enrolment($enrolmentid);
        if ( empty($enrolment) )
        {// Подписка пользователя не найдена
            $this->enrolplugin->rise_error(
                0, 'error_provider_sberbank_action_reverse_enrolment_not_found');
        }

        // Конфигурация провайдера
        $providerconfig = $this->provider->otpay_config();

        // Установка языка
        $language = current_language();

        // Инициализация запроса
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->requesturl.'/payment/rest/reverse.do');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_POSTFIELDS, [
            'userName' => $this->login,
            'password' => $this->password,
            'orderId' => $enrolment->externalpaymentid
        ]);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_TIMEOUT, 60);
        // Исполнение запроса
        $result = curl_exec($ch);
        curl_close($ch);

        // Обработка результатов запроса
        if ( empty($result) )
        {
            $this->enrolplugin->rise_error(
                'error_provider_sberbank_action_reverse_connection_failed', $enrolment->id);
        }

        // Декодирование данных
        $result = json_decode($result);

        // Проверка на ошибки в ответе от банка
        if ( !empty($result->errorCode) )
        {// Получен код ошибки от банка
            // Генерация данных об ошибке
            $errorstring = 'errorCode'.$result->errorCode;
            if ( isset($result->errorMessage) )
            {
                $errorstring = $result->errorMessage;
            }
            $errordata = new stdClass();
            switch ($result->errorCode)
            {
                case '5' :
                    // Ошибка значения параметра запроса
                    $errorcode = 'error_provider_sberbank_action_reverse_response_errorcode_invalid_required_param';
                    break;
                case '6' :
                    // Незарегистрированный OrderId
                    $errorcode = 'error_provider_sberbank_action_reverse_response_errorcode_invalid_orderid';
                    break;
                case '7' :
                    // Системная ошибка
                    $errorcode = 'error_provider_sberbank_action_reverse_response_errorcode_system_error';
                    break;
                default:
                    // Неизвестная ошибка
                    $errorcode = 'error_provider_sberbank_action_reverse_response_errorcode_undefined';
                    break;
            }
            $this->enrolplugin->rise_error(
                $errorcode, $enrolment->id, $errordata, $errorstring);
        }
        if ( ! isset($result->errorCode) ||
             ( isset($result->errorCode) && (int)$result->errorCode == 0) )
        {// Оплата отменена
            return true;
        }
        return false;
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
        $this->requesturl = get_config('enrol_otpay', 'sberbank_requesturl');
        $this->login = get_config('enrol_otpay', 'sberbank_login');
        $this->password = get_config('enrol_otpay', 'sberbank_password');

        if ( empty($this->login) || empty($this->password) || empty($this->requesturl) )
        {// Настройки не указаны
            $this->enrolplugin->rise_error(
                'error_provider_sberbank_init_connection_errorcode_settings_invalid'
            );
        }
    }
}