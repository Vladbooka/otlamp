<?php
use enrol_otpay\plugins\no_credentials_exception;

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
 * Способ записи на курс OTPay.
 * Основной класс псевдосабплагина KazKom.
 *
 * @package enrol
 * @subpackage otpay
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die();
require_once ($CFG->dirroot . "/enrol/otpay/plugins/otpay.php");

class otpay_kazkom extends otpay
{

    /**
     *
     * {@inheritDoc}
     *
     * @see otpay::otpay_config()
     */
    function otpay_config()
    {
        $config = new stdClass();
        // Доступные валюты по ISO 4217
        $config->currencycodes = [
            398 => 'KZT'
            //840 => 'USD'
        ];

        // Валюта по умолчанию
        $config->defaultcurrencycode = 398;

        $config->newinstanceurl = "/enrol/otpay/edit.php";
        $config->editurl = "/enrol/otpay/edit.php";
        $config->pixicon = new pix_icon('kazkom', get_string('otpay_kazkom', 'enrol_otpay'),
            'enrol_otpay');
        $config->configcapability = "enrol/otpay:config";
        $config->unenrolcapability = "enrol/otpay:unenrol";
        $config->managecapability = "enrol/otpay:manage";
        $config->couponsupports = true;
        $config->costsupports = true;
        $config->minamount = 5;

        return $config;
    }

    /**
     * Дополнение формы сохранения способа записи курс
     *
     * @param enrol_otpay_edit_enrol_form $form - Объект формы
     * @param array $customdata
     *
     * @return void
     */
    public function form_edit_enrol_definition(enrol_otpay_edit_enrol_form &$form, $customdata)
    {
        parent::form_edit_enrol_definition($form, $customdata);

        $mform = $form->get_mform();
        $plugin = $form->get_plugin();

        // Заголовок раздела формы
        $mform->addElement('header', 'header', get_string('otpay_kazkom', 'enrol_otpay'));

        // Стоимость
        $mform->addElement('text', 'cost', get_string('kazkom_form_field_cost', 'enrol_otpay'),
            [
                'size' => 4
            ]);
        $mform->setType('cost', PARAM_RAW);

        // Валюта
        $currencies = $this->otpay_config()->currencycodes;
        foreach ( $currencies as &$currence )
        {
            $currence = get_string($currence, 'core_currencies');
        }
        $mform->addElement('select', 'currency',
            get_string('kazkom_form_field_currency', 'enrol_otpay'), $currencies);

        //Поддержка скидочных купонов
        $mform->addElement('checkbox', 'couponsupports', get_string('kazkom_form_field_couponsupports', 'enrol_otpay'));
        $mform->setDefault('couponsupports', true);

    }

    /**
     * Дополнительная валидация формы сохранения способа записи курс
     *
     * @param enrol_otpay_edit_enrol_form $form - Объект формы
     * @param array $errors - Массив ошибок исходной формы
     * @param array $data - Массив с переданными данными формы
     * @param unknown $files - Массив с переданными файлами формы
     *
     * @return void
     */
    public function form_edit_enrol_validation(enrol_otpay_edit_enrol_form &$form, &$errors, $data, $files)
    {
        parent::form_edit_enrol_validation($form, $errors, $data, $files);

        // Валидация стоимости
        $cost = str_replace(get_string('decsep', 'langconfig'), '.', $data['cost']);
        if ( ! is_numeric($cost) )
        {
            $errors['cost'] = get_string('kazkom_error_form_validation_cost', 'enrol_otpay');
        }
        $config = $this->otpay_config();
        if ( $data['cost'] < $config->minamount )
        {
            $errors['cost'] = get_string('kazkom_error_form_validation_costminamount',
                'enrol_otpay', $config->minamount);
        }

        // Валидация валюты
        $currencies = $this->otpay_config()->currencycodes;
        if ( empty($data['currency']) || empty($currencies[$data['currency']]) )
        {// Указанная валюта не найдена среди доступных
            $errors['currency'] = get_string('error_provider_kazkom_form_edit_enrol_validation_currency', 'enrol_otpay');
        }
    }

    /**
     * Предварительная обработка формы сохранения способа записи курс
     *
     * @param enrol_otpay_edit_enrol_form $form - Объект формы
     * @param stdClass $formdata - Объект с данными формы
     *
     * @return void
     */
    public function form_edit_enrol_preprocess(enrol_otpay_edit_enrol_form &$form, &$formdata)
    {
        parent::form_edit_enrol_preprocess($form, $formdata);
    }

    /**
     * Постобработка формы сохранения способа записи курс
     *
     * @param enrol_otpay_edit_enrol_form $form - Объект формы
     * @param stdClass $instance - Объект экземпляра подписки
     * @param stdClass $formdata - Объект с данными формы
     *
     * @return void
     */
    public function form_edit_enrol_postprocess(enrol_otpay_edit_enrol_form &$form, &$instance, &$formdata)
    {
        parent::form_edit_enrol_postprocess($form, $instance, $formdata);

        // Установка стоимости
        $config = $this->otpay_config();
        if ( $config->costsupports && isset($instance->id) )
        {// Поддержка стоимости
            $instance->cost = unformat_float($formdata->cost);
            $instance->currency = $formdata->currency;

            if( isset($formdata->couponsupports) )
            {
                $instance->customint6 = 1;
            } else
            {
                $instance->customint6 = 0;
            }
        }
    }

    /**
     * Дополнение формы записи пользователя на курс
     *
     * @param enrol_otpay_edit_enrol_form $form - Объект формы
     * @param array $customdata
     *
     * @return void
     */
    public function form_add_user_enrolment_definition(enrol_otpay_add_user_enrolment_form &$form, $customdata)
    {
        global $PAGE;
        parent::form_add_user_enrolment_definition($form, $customdata);

        $mform = $form->get_mform();

        list($instance, $couponcodes) = $customdata;

        $mform->addElement('hidden', 'couponcodes', implode(',', $couponcodes));
        $mform->setType('couponcodes', PARAM_RAW);

        $config = $this->otpay_config();

        // Обработка купонов
        $couponform = new enrol_otpay_coupon_form($PAGE->__get('url')->__toString(),
            [
                'amount' => $instance->cost,
                'courseid' => $instance->courseid,
                'minamount' => $config->minamount
            ]);
        // Подсчет итоговой суммы с учетом купонов
        $amount = $couponform->get_amount($couponcodes);

        if( (int)$amount <= 0 )
        {// Купон покрывает стоимость курса
            $submitlabel = get_string('kazkom_free_enrol_field_submit', 'enrol_otpay');
        } else
        {
            $available_paysystems = (string)get_config('enrol_otpay', 'kazkom_available_paysystems');
            $paysysform = $this->render_paysystems($available_paysystems);
            if( ! empty($paysysform) )
            {// Отображение изображений указанных пользователем платежных систем
                $mform->addElement('static','paysystems','',$paysysform);
            }
            $submitlabel = get_string('kazkom_payform_field_submit', 'enrol_otpay');
        }
        $submitname = 'submitbutton_otpay_'.$instance->id;
        $mform->addElement('submit', $submitname, $submitlabel);
        $mform->closeHeaderBefore($submitname);
    }

    /**
     * Дополнительная валидация формы записи пользователя на курс
     *
     * @param enrol_otpay_edit_enrol_form $form - Объект формы
     * @param array $errors - Массив ошибок исходной формы
     * @param array $data - Массив с переданными данными формы
     * @param unknown $files - Массив с переданными файлами формы
     *
     * @return void
     */
    public function form_add_user_enrolment_validation(enrol_otpay_add_user_enrolment_form &$form, &$errors, $data, $files)
    {
        parent::form_add_user_enrolment_validation($form, $errors, $data, $files);
    }

    /**
     * Обработка формы записи пользователя на курс
     *
     * @param enrol_otpay_edit_enrol_form $form - Объект формы
     * @param stdClass $instance - Объект экземпляра подписки
     * @param stdClass $formdata - Объект с данными формы
     *
     * @return void
     */
    public function form_add_user_enrolment_process(enrol_otpay_add_user_enrolment_form &$form, &$instance, &$formdata)
    {
        global $DB, $CFG, $PAGE, $COURSE, $USER;

        // Базовый обработчик
        parent::form_add_user_enrolment_process($form, $instance, $files);

        $plugin = $form->get_plugin();

        if ( ! $creds = $this->get_credentials() )
        {// Настройки для провайдера не указаны
            redirect(
                new moodle_url('/enrol/otpay/return.php',
                    [
                        'id' => $COURSE->id,
                        'ok' => '0',
                        'ko' => '1'
                    ]));
        }

        // Получение экземпляра
        $instanceid = $formdata->instanceid;
        $instance = $DB->get_record('enrol',
            [
                'id' => $instanceid
            ], '*', MUST_EXIST
        );

        // Конфигурация провайдера
        $config = $this->otpay_config();

        // Обработка купонов
        if ( ! $couponcodes = explode(',', $formdata->couponcodes) )
        {
            $couponcodes = "";
        }
        $couponform = new enrol_otpay_coupon_form($PAGE->__get('url')->__toString(),
            [
                'amount' => $instance->cost,
                'courseid' => $instance->courseid,
                'minamount' => $config->minamount
            ]);
        // Подсчет итоговой суммы с учетом купонов
        $amount = $couponform->get_amount($couponcodes);

        // Формирование данных платежа
        $defaultenrolotpay = new stdClass();
        $defaultenrolotpay->instanceid = $instanceid;
        $defaultenrolotpay->courseid = $instance->courseid;
        $defaultenrolotpay->userid = $USER->id;
        $defaultenrolotpay->amount = (int)$amount < $config->minamount ? $config->minamount : $amount;
        $defaultenrolotpay->currency = $instance->currency;
        $defaultenrolotpay->options = serialize(
            [
                'couponcodes' => $couponcodes
            ]
        );

        // Создание записи
        $enrolotpayid = $plugin->add_draft_enrol_otpay('kazkom', $defaultenrolotpay);

        // Получение записи
        $enrolotpay = $DB->get_record('enrol_otpay',
            [
                'id' => $enrolotpayid
            ]);

        if( (int)$amount <= 0 )
        {// Скидка по купону покрывает стоимость курса - подписываем пользователя на курс
            // Обработка скидочных купонов
            $plugin->process_coupons($enrolotpay);
            // Подписка на курс
            $plugin->process_payment($enrolotpay);
            // Редирект на страницу назначения
            $plugin->process_redirect($instance);
        } else
        {
            $amount = (int)$amount < $config->minamount ? $config->minamount : $amount;
            //сформируем строку, которую будем подписывать
            $xml = '<merchant cert_id="%certificate%" name="%merchant_name%"><order order_id="%order_id%" amount="%amount%" currency="%currency%"><department merchant_id="%merchant_id%" amount="%amount%" RL="%rl%"/></order></merchant>';

            $description = '';
            $course = get_course($instance->courseid);
            if ( $course )
            {
                $description = '['.$course->id.'] '.$course->shortname;
                // Транслитерация
                $transliterator = Transliterator::create('Cyrillic-Latin');
                $description = $transliterator->transliterate($description);
            }

            //формирование массива данных для вставки в xml-текст
            $data = [
                'certificate' => $creds->merchantcertificateid,
                'merchant_name' => $creds->merchantname,
                'merchant_id' => $creds->merchantid,
                'order_id' => $enrolotpay->paymentid,
                'currency' => $instance->currency,
                'amount' => $amount,
                'rl' => $description
            ];
            //заполним значениями
            $merchant = $this->fill_xml($xml, $data);
            //сформируем подпись
            $merchant_sign = $this->make_sign($merchant, $creds->privateuserkey,
                $creds->privateuserkeypassword);
            if ( empty($sign['errors']) )
            {
                $merchant_sign = $merchant_sign['sign'];
            }

            //соберем окончательный документ с подписью
            $paymentxml = "<document>" . $merchant . "<merchant_sign type=\"RSA\">" . $merchant_sign .
            "</merchant_sign></document>";
            //Данные для отправки на сервер банка

            $postdata = [
                'Signed_Order_B64' => base64_encode($paymentxml),
                'email' => $USER->email,
                'BackLink' => new moodle_url('/enrol/otpay/return.php',
                    [
                        'id' => $COURSE->id,
                        'ok' => '1',
                        'ko' => '0',
                        'enrolotpayid' => $enrolotpayid
                    ]),
                'FailureBackLink' => new moodle_url('/enrol/otpay/return.php',
                    [
                        'id' => $COURSE->id,
                        'ok' => '0',
                        'ko' => '1',
                        'enrolotpayid' => $enrolotpayid
                    ]),
                'PostLink' => new moodle_url('/enrol/otpay/ipn.php',
                    [
                        'enrolotpayid' => $enrolotpayid
                    ])
            ];
            ob_clean();

            //отправляем пост-запрос через страницу с формой, отправляемую javascriptом
            $plugin->redirect_post($creds->url, $postdata);
            exit();
        }
    }

    /**
     * Опции записи на курс
     *
     * @param stdClass $instance - Экземпляр подписки на курс
     * @param stdClass $customdata - Данные о заявке на оплату пользователя
     *
     * @return string рендер формы
     */
    protected function get_enrol_page_hook_options($instance, $customdata) {

        $options = new stdClass();
        $options->localisedcost = format_float($customdata->cost, 2, true);
        $options->cost = $customdata->cost;
        $options->localisedamount = format_float($customdata->amount, 2, true);
        $options->amount = $customdata->amount;
        $options->currency = $instance->currency;
        $options->instancename = $instance->name;
        $options->instancedescription = $instance->customtext2 ?? '';
        $options->enrolstartdate = $instance->enrolstartdate;
        $options->enrolperiod = $instance->enrolperiod;
        $options->enrolenddate = $instance->enrolenddate;
        $options->class = ['otpay_kazkom'];
        return $options;

    }

    /**
     * Форма записи на курс
     *
     * @param stdClass $instance - Экземпляр подписки на курс
     * @param stdClass $customdata - Данные о заявке на оплату пользователя
     *
     * @return string рендер формы
     */
    protected function get_enrol_page_hook_form($instance, $customdata) {
        global $PAGE;

        // способ записи не настроен, не отображаем его вовсе
        if (!$this->get_credentials()) {
            throw new no_credentials_exception('No valid credentials');
        }

        $form = '';
        if (isloggedin() && !isguestuser()) {

            $couponcodes = [];
            if( ! empty($instance->customint6) && (int)$instance->customint6 == 1 ) {
                $couponcodes = $customdata->couponcodes;
            }

            $adduserenrolmentform = new enrol_otpay_add_user_enrolment_form($PAGE->url, [$instance, $couponcodes]);
            $adduserenrolmentform->process();
            $form = $adduserenrolmentform->render();

        }

        return $form;
    }

    /**
     * Подстановка данных в размеченную строку
     *
     * @param sting $xml
     *            - строка, в которую необходимо вставить подстановки
     * @param array $data
     *            - ассоциативный массив с данными для подстановок
     * @return string - результат
     */
    private function fill_xml( $xml, $data )
    {
        global $CFG;

        foreach ( $data as $k => $v )
        {
            $xml = preg_replace("/\%$k\%/", $v, $xml);
        }

        return $xml;
    }

    /**
     * Получение настроек псевдосабплагина
     *
     * @return stdClass
     */
    private function get_credentials()
    {
        global $USER, $CFG;

        $creds = new stdClass();

        if ( get_config('enrol_otpay', 'kazkom_mode') == 0 )
        { //используется тестовый режим, при этом пользователь является администратором
            // AND is_siteadmin($USER->id)
            //@TODO:добавить право подписки в тестовом режиме и проверять его, а не админские права
            include ($CFG->dirroot . "/enrol/otpay/plugins/kazkom/test/settings.php");
            //Серийный номер тестового сертификата
            $creds->merchantcertificateid = $merchantcertificateid;
            //Имя магазина(сайта) для тестового сертификата
            $creds->merchantname = $merchantname;
            //ID продавца в платежной системе для тестового аккаунта
            $creds->merchantid = $merchantid;
            //тестовый приватный ключ пользователя
            $creds->privateuserkey = $privateuserkey;
            //пароль тестового приватного ключа пользователя
            $creds->privateuserkeypassword = $privateuserkeypassword;
            //тестовый публичный ключ банка
            $creds->publicbankkey = $publicbankkey;
            //тестовый URL для авторизации суммы в банке
            $creds->url = $url;
            //тестовый URL для подтверждения-анулирования авторизации
            $creds->urlcontrol = $urlcontrol;
        } else
            if ( get_config('enrol_otpay', 'kazkom_mode') == 1 )
            {
                //Серийный номер сертификата
                $creds->merchantcertificateid = get_config('enrol_otpay',
                    'kazkom_merchant_certificate_id');
                //имя магазина(сайта)
                $creds->merchantname = get_config('enrol_otpay', 'kazkom_merchant_name');
                //ID продавца в платежной системе
                $creds->merchantid = get_config('enrol_otpay', 'kazkom_merchant_id');
                //приватный ключ пользователя
                $creds->privateuserkey = get_config('enrol_otpay', 'kazkom_privateuserkey');
                //пароль приватного ключа пользователя
                $creds->privateuserkeypassword = get_config('enrol_otpay',
                    'kazkom_privateuserkeypassword');
                //публичный ключ банка
                $creds->publicbankkey = get_config('enrol_otpay', 'kazkom_publicbankkey');
                //URL для авторизации суммы в банке
                $creds->url = get_config('enrol_otpay', 'kazkom_url');
                //URL для подтверждения-анулирования авторизации
                $creds->urlcontrol = get_config('enrol_otpay', 'kazkom_urlcontrol');
            } else
            { //вероятно простой пользователь может получить доступ к тестовым платежам, этого не нужно допускать
                //или проблема с настройками, что маловероятно
                return false;
            }

        if ( empty($creds->merchantcertificateid) or empty($creds->merchantname) or
             empty($creds->merchantid) or empty($creds->privateuserkey) or
             empty($creds->privateuserkeypassword) or empty($creds->publicbankkey) or
             empty($creds->url) or empty($creds->urlcontrol) )
        {
            return false;
        }
        return $creds;
    }



    /**
     *
     * {@inheritDoc}
     *
     * @see otpay::check_data()
     */
    function check_data( $data, $enrolotpay )
    {
        //@TODO: проверять дату во избежание обработки повторных orderid
        global $DB;
        //сюда будем собирать ошибки
        $checkdataresult = [];

        if ( ! $creds = $this->get_credentials() )
        {
            $checkdataresult[] = "Error: couldn't get credentials";
        }

        $sxml = new SimpleXMLElement($data->response);

        //проверим совпадает ли merchantcertificateid в ответе от банка с нашими данными
        if ( (string) $creds->merchantcertificateid !=
             $sxml->bank->customer->merchant['cert_id']->__toString() )
        {
            $checkdataresult[] = "merchantcertificateid doesn't match";
        }
        //проверим совпадает ли merchantname в ответе от банка с нашими данными
        if ( (string) $creds->merchantname != $sxml->bank->customer->merchant['name']->__toString() )
        {
            $checkdataresult[] = "merchantname doesn't match";
        }
        //проверим совпадает ли merchantid в ответе от банка с нашими данными
        if ( (string) $creds->merchantid !=
             $sxml->bank->customer->merchant->order->department['merchant_id']->__toString() or
             (string) $creds->merchantid !=
             $sxml->bank->results->payment['merchant_id']->__toString() )
        {
            $checkdataresult[] = "merchantid doesn't match";
        }
        //проверим успешно ли проведен платеж
        if ( $sxml->bank->results->payment['response_code']->__toString() != "00" )
        {
            $checkdataresult[] = "response code is not success";
        }
        //проверим совпадает ли сумма платежа в ответе от банка с нашими данными
        if ( (float) $enrolotpay->amount != (float) $sxml->bank->customer->merchant->order['amount'] )
        {
            $checkdataresult[] = "amount doesn't match";
        }
        //проверим совпадает ли валюта в ответе от банка с нашими данными
        if ( (int) $enrolotpay->currency !=
             (int) $sxml->bank->customer->merchant->order['currency'] )
        {
            $checkdataresult[] = "currency doesn't match";
        }
        //проверим совпадает ли валюта в ответе от банка с нашими данными
        if ( (string) $enrolotpay->paymentid !=
             $sxml->bank->customer->merchant->order['order_id']->__toString() )
        {
            $checkdataresult[] = "paymentid doesn't match";
        }

        $paymentdata = [
            'payment_reference' => $sxml->bank->results->payment['reference']->__toString(),
            'payment_approval_code' => $sxml->bank->results->payment['approval_code']->__toString()
        ];

        $responsebank = $sxml->bank->asXML();
        $responsebanksign = $sxml->bank_sign->__toString();

        $verifysignresult = $this->check_sign($creds->publicbankkey, $responsebank,
            $responsebanksign);
        if ( $verifysignresult != "signature correct" )
        {
            $checkdataresult[] = [
                'publicbankkey' => $creds->publicbankkey,
                'responsebank' => $responsebank,
                'responsebanksign' => $responsebanksign
            ];
        }

        if ( empty($checkdataresult) )
        {
            //в этом способе оплаты требуется, чтобы был напечатан 0, чтобы банк был уверен, что сайт получил его данные
            echo "0";
        }

        return [
            'succeed' => empty($checkdataresult),
            'errors' => $checkdataresult,
            'needdebit' => true,
            'waitdebit' => false,
            'paymentdata' => $paymentdata
        ];
    }

    /**
     * Завершение оплаты.
     * Списание заблокированных на карте клиента средств
     *
     * @param object $enrolotpay
     *            - объект otpay-платежа
     * @param array $additionaldata
     *            - дополнительная информация, пришедшая от банка, требуемая для совершения операции
     *
     * @return array - массив с результатами выполнения операции
     */
    function complete_payment( $enrolotpay, $additionaldata = null )
    {
        global $DB;
        $verifysignresult = 'unknown';
        $enrolotpayoptions = [];
        if ( ! empty($enrolotpay->options) )
        {
            //получим опциональные данные
            $enrolotpayoptions = unserialize($enrolotpay->options);
        }

        if ( ! empty($additionaldata) )
        { //пришли данные, которые надо записать в БД
            $enrolotpayoptions['payment_reference'] = $additionaldata['payment_reference'];
            $enrolotpayoptions['payment_approval_code'] = $additionaldata['payment_approval_code'];
            $enrolotpay->options = serialize($enrolotpayoptions);
            $enrolotpay->timemodified = time();
            $DB->update_record('enrol_otpay', $enrolotpay);
        }

        if ( $creds = $this->get_credentials() )
        {
            //сюда будем собирать ошибки
            $checkdataresult = [];
            if ( isset($enrolotpayoptions['payment_reference']) and
                 ! empty($enrolotpayoptions['payment_reference']) and
                 isset($enrolotpayoptions['payment_approval_code']) and
                 ! empty($enrolotpayoptions['payment_approval_code']) )
            {
                //сформируем строку, которую будем подписывать
                $xml = '<merchant id="%merchant_id%"><command type="complete"/><payment reference="%reference%" approval_code="%approval_code%" orderid="%orderid%" amount="%amount%" currency_code="%currency_code%"/></merchant>';
                $data = [
                    'merchant_id' => $creds->merchantid,
                    'reference' => $enrolotpayoptions['payment_reference'],
                    'approval_code' => $enrolotpayoptions['payment_approval_code'],
                    'orderid' => $enrolotpay->paymentid,
                    'amount' => $enrolotpay->amount,
                    'currency_code' => $enrolotpay->currency
                ];
                //заполним значениями
                $merchant = $this->fill_xml($xml, $data);
                //сформируем подпись
                $merchant_sign = $this->make_sign($merchant, $creds->privateuserkey,
                    $creds->privateuserkeypassword);
                if ( empty($sign['errors']) )
                {
                    $merchant_sign = $merchant_sign['sign'];
                } else
                {
                    $checkdataresult[] = implode('; ', $sign['errors']);
                }
                //соберем окончательный документ с подписью
                $completepaymentxml = "<document>" . $merchant .
                     "<merchant_sign type=\"RSA\" cert_id=\"" . $creds->merchantcertificateid . "\">" .
                     $merchant_sign . "</merchant_sign></document>";

                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, $creds->urlcontrol . "?" .
                     urlencode($completepaymentxml));
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
                curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
                curl_setopt($ch, CURLOPT_HEADER, false);
                curl_setopt($ch, CURLOPT_TIMEOUT, 3);
                //curl_setopt($ch, CURLOPT_POSTFIELDS, "payment_id=free&datetime=".$time."&status=OK&cf=".$data->data."&cf2=&cf3=&sign=".$sign);
                $result = curl_exec($ch);
                curl_close($ch);

                if ( $result )
                {

                    $sxml = new SimpleXMLElement(preg_replace('/\r\n|\r|\n/', "", html_entity_decode($result)));

                    //проверим успешно ли проведен платеж
                    if ( ( isset($sxml->bank->response['code']) && $sxml->bank->response['code']->__toString() != "00" ) ||
                        ! isset($sxml->bank->response['code']) )
                    {
                        $checkdataresult[] = "response code is not success";
                    }
                    //проверим совпадает ли merchantid в ответе от банка с нашими данными
                    if ( ( isset($sxml->bank->merchant['id']) && (string) $creds->merchantid != $sxml->bank->merchant['id']->__toString() ) ||
                        ! isset($sxml->bank->merchant['id']) )
                    {
                        $checkdataresult[] = "merchantid doesn't match";
                    }
                    //проверим совпадает ли payment_reference в ответе от банка с нашими данными
                    if ( ( isset($sxml->bank->merchant->payment['reference']) && (string) $enrolotpayoptions['payment_reference'] !=
                         $sxml->bank->merchant->payment['reference']->__toString() ) ||
                        ! isset($sxml->bank->merchant->payment['reference']) )
                    {
                        $checkdataresult[] = "payment_reference doesn't match";
                    }
                    //проверим совпадает ли payment_approval_code в ответе от банка с нашими данными
                    if ( ( isset($sxml->bank->merchant->payment['approval_code']) && (string) $enrolotpayoptions['payment_approval_code'] !=
                         $sxml->bank->merchant->payment['approval_code']->__toString() ) ||
                        ! isset($sxml->bank->merchant->payment['approval_code']) )
                    {
                        $checkdataresult[] = "payment_approval_code doesn't match";
                    }
                    //проверим совпадает ли order_id в ответе от банка с нашими данными
                    if ( ( isset($sxml->bank->merchant->payment['orderid']) && (int) $enrolotpay->paymentid !=
                         (int) $sxml->bank->merchant->payment['orderid'] ) ||
                        ! isset($sxml->bank->merchant->payment['orderid']) )
                    {
                        $checkdataresult[] = "paymentid doesn't match";
                    }
                    //проверим совпадает ли сумма платежа в ответе от банка с нашими данными
                    if ( ( isset($sxml->bank->merchant->payment['amount']) && (float) $enrolotpay->amount !=
                         (float) $sxml->bank->merchant->payment['amount'] ) ||
                        ! isset($sxml->bank->merchant->payment['amount']) )
                    {
                        $checkdataresult[] = "amount doesn't match";
                    }
                    //проверим совпадает ли валюта в ответе от банка с нашими данными
                    if ( ( isset($sxml->bank->merchant->payment['currency_code']) && (int)$enrolotpay->currency !=
                         (int) $sxml->bank->merchant->payment['currency_code'] ) ||
                        ! isset($sxml->bank->merchant->payment['currency_code']) )
                    {
                        $checkdataresult[] = "currency doesn't match";
                    }

                    $responsebank = $sxml->bank->asXML();
                    $responsebanksign = $sxml->bank_sign->__toString();

                    $verifysignresult = $this->check_sign($creds->publicbankkey, $responsebank,
                        $responsebanksign);
                    if ( $verifysignresult == "signature correct" )
                    {
                        if ( empty($checkdataresult) )
                        {
                            // Подтверждение платежа идет в методе process_payment вместо с подпиской пользователя в курс
                            // $enrolotpay->status = "confirmed";
                            $enrolotpay->timemodified = time();
                            $DB->update_record('enrol_otpay', $enrolotpay);
                        }
                    } else
                    {
                        $checkdataresult[] = [
                            'response' => $result,
                            'publicbankkey' => $creds->publicbankkey,
                            'responsebank' => $responsebank,
                            'responsebanksign' => $responsebanksign
                        ];
                    }
                } else
                {
                    $checkdataresult[] = "Failed curl exec";
                }
            } else
            {
                $checkdataresult[] = "payment_approval_code or payment_reference are lost";
            }
        } else
        {
            $checkdataresult[] = "Error: couldn't get credentials";
        }

        return [
            'result' => empty($checkdataresult),
            'checkdata' => $checkdataresult,
            'verifysignresult' => $verifysignresult
        ];
    }

    /**
     * Выполнение подписи по требованиям Казкоммерцбанка
     *
     * @param string $data
     *            - строка, которую необходимо подписать
     * @param string $privateuserkey
     *            - приватный ключ для подписи
     * @param string $privateuserkeypassword
     *            - пароль для приватного ключа
     *
     * @return array - результат выполнения подписи
     */
    function make_sign( $data, $privateuserkey, $privateuserkeypassword )
    {
        $result = [
            'errors' => [],
            'sign' => ""
        ];
        //очищение стека ошибок openssl
        while ( $msg = openssl_error_string() )
        {}
        //получение ключа
        $prvkey = openssl_get_privatekey(preg_replace('/\r\n|\r|\n/', "\r\n", $privateuserkey),
            $privateuserkeypassword);
        //обработка ошибок
        while ( $error = openssl_error_string() )
        {
            $result['errors'][] = $this->parse_errors($error);
        }
        if ( ! empty($result['errors']) )
        {
            return $result;
        }

        if ( is_resource($prvkey) )
        {
            if ( openssl_sign($data, $sign, $prvkey) )
            { //данные подписаны
                $result['sign'] = base64_encode(strrev($sign));
                return $result;
            } else
            { //данные не подписаны - обработка ошибок
                while ( $error = openssl_error_string() )
                {
                    $result['errors'][] = $this->parse_errors($error);
                }
                return $result;
            }
        } else
        {
            $result['errors'][] = "Private key is not resourse";
            return $result;
        }
    }

    /**
     * Проверка подписиданных, пришедших от банка
     *
     * @param string $publicbankkey
     *            - публичный ключ банка
     * @param string $response
     *            - строка с ответом
     * @param string $responsesign
     *            - подпись строки с ответом
     * @return string - результат проверки подписи
     */
    function check_sign( $publicbankkey, $response, $responsesign )
    {
        //очищение стека ошибок openssl
        while ( $msg = openssl_error_string() )
        {}

        $errors = [];
        //получение ключа
        $pubkey = openssl_get_publickey(preg_replace('/\r\n|\r|\n/', "\r\n", $publicbankkey));
        //обработка ошибок
        while ( $error = openssl_error_string() )
        {
            $errors[] = $this->parse_errors($error);
        }
        if ( ! empty($errors) )
        {
            return implode('; ', $errors);
        }

        if ( is_resource($pubkey) )
        {
            //проверка подписи на соответствие исходной строки
            $verifyresult = openssl_verify(html_entity_decode($response), strrev(base64_decode($responsesign)), $pubkey);
            //обработка ошибок
            $errors = [];
            while ( $error = openssl_error_string() )
            {
                $errors[] = $this->parse_errors($error);
            }
            if ( ! empty($errors) )
            {
                return implode('; ', $errors);
            }

            switch ( $verifyresult )
            {
                case 1:
                    return "signature correct";
                    break;
                case 0:
                    return "signature incorrect";
                    break;
                default:
                    return "verify error";
            }
        } else
        {
            return "Public key is not resourse";
        }
    }

    /**
     * Обработка кодов возможных ошибок
     *
     * @param string $error
     *            - строка с ошибкой
     *
     * @return string - если в строке с ошибкой найден известный код - вернется понятное объяснение ошибки. Иначе - исходная ошибка
     */
    function parse_errors( $error )
    {
        if ( strpos($error, "0906D06C") )
        {
            return "Error reading Certificate. Verify Cert type.";
        }
        ;
        if ( strpos($error, "06065064") )
        {
            return "Bad decrypt. Verify your Cert password or Cert type.";
        }
        ;
        if ( strpos($error, "0906A068") )
        {
            return "Bad password read. Maybe empty password.";
        }
        ;
        return $error;
    }

    /**
     *
     * {@inheritDoc}
     *
     * @see otpay::version()
     */
    function version()
    {
        return 2016060900;
    }
}