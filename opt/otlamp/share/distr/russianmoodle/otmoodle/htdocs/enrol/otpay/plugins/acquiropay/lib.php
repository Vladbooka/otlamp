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
 * Основной класс псевдосабплагина AcquiroPay.
 *
 * @package enrol
 * @subpackage otpay
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die();
require_once ($CFG->dirroot . "/enrol/otpay/plugins/otpay.php");

class otpay_acquiropay extends otpay
{
    /**
     * Версия провайдера
     *
     * @return int
     */
    public function version()
    {
        return 2016061000;
    }

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
            643 => 'RUB'
        ];
        // Валюта по умолчанию
        $config->defaultcurrencycode = 643;
        $config->newinstanceurl = "/enrol/otpay/edit.php";
        $config->editurl = "/enrol/otpay/edit.php";
        $config->pixicon = new pix_icon('acquiropay', get_string('otpay_acquiropay', 'enrol_otpay'),
            'enrol_otpay');
        $config->configcapability = "enrol/otpay:config";
        $config->unenrolcapability = "enrol/otpay:unenrol";
        $config->managecapability = "enrol/otpay:manage";
        $config->couponsupports = true;
        $config->costsupports = true;
        $config->minamount = 1;

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
        $mform->addElement('header', 'header', get_string('otpay_acquiropay', 'enrol_otpay'));

        // Стоимость
        $mform->addElement('text', 'cost', get_string('acquiropay_form_field_cost', 'enrol_otpay'),
            [
                'size' => 4
            ]);
        $mform->setType('cost', PARAM_RAW);

        $mform->addElement('checkbox', 'couponsupports', get_string('acquiropay_form_field_couponsupports', 'enrol_otpay'));
        $mform->setDefault('couponsupports', true);

        // Валюта
        $currencies = $this->otpay_config()->currencycodes;
        foreach ( $currencies as &$currence )
        {
            $currence = get_string($currence, 'core_currencies');
        }
        $mform->addElement('select', 'currency',
            get_string('acquiropay_form_field_currency', 'enrol_otpay'), $currencies);

        // Ставка НДС курса
        $visiblename = get_string('settings_tax', 'enrol_otpay');
        $options = [
            'none' => get_string('settings_tax_first', 'enrol_otpay'),
            'vat0' => get_string('settings_tax_second', 'enrol_otpay'),
            'vat10' => get_string('settings_tax_third', 'enrol_otpay'),
            'vat18' => get_string('settings_tax_fourth', 'enrol_otpay'),
            'vat110' => get_string('settings_tax_fifth', 'enrol_otpay'),
            'vat118' => get_string('settings_tax_sixth', 'enrol_otpay')
        ];
        $mform->addElement('select', 'cash', $visiblename, $options);
        if ( ! empty($form->get_instance()->customchar2) )
        {
            $mform->setDefault('cash', json_decode($form->get_instance()->customchar2));
        } else
        {
            // Ставка НДС
            $mform->setDefault('cash', get_config('enrol_otpay', 'acquiropay_tax'));
        }
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
            $errors['cost'] = get_string('acquiropay_error_form_validation_cost', 'enrol_otpay');
        }
        $config = $this->otpay_config();
        if ( $data['cost'] < $config->minamount )
        {
            $errors['cost'] = get_string('acquiropay_error_form_validation_costminamount',
                'enrol_otpay', $config->minamount);
        }

        // Валидация валюты
        $currencies = $this->otpay_config()->currencycodes;
        if ( empty($data['currency']) || empty($currencies[$data['currency']]) )
        {// Указанная валюта не найдена среди доступных
            $errors['currency'] = get_string('error_provider_acquiropay_form_edit_enrol_validation_currency', 'enrol_otpay');
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

            // НДС
            $instance->customchar2 = json_encode($formdata->cash);
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
            $submitlabel = get_string('acquiropay_free_enrol_field_submit', 'enrol_otpay');
        } else
        {
            $available_paysystems = (string)get_config('enrol_otpay', 'acquiropay_available_paysystems');
            $paysysform = $this->render_paysystems($available_paysystems);
            if (!empty($paysysform)) {
                // Отображение изображений указанных пользователем платежных систем
                $mform->addElement('static','paysystems','',$paysysform);
            }
            $submitlabel = get_string('acquiropay_payform_field_submit', 'enrol_otpay');
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
                    ])
            );
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
        $enrolotpayid = $plugin->add_draft_enrol_otpay('acquiropay', $defaultenrolotpay);

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
            // Объект для формирования кастомного поля
            $input = new stdClass();
            $input->amount = $amount;
            $input->currency = $instance->currency;
            $input->userid = $enrolotpay->userid;
            $input->instanceid = $instance->id;
            $input->enrolperiod = $instance->enrolperiod;
            $input->enrolstartdate = $instance->enrolstartdate;
            $input->enrolenddate = $instance->enrolenddate;
            $input->couponcodes = $couponcodes;
            $cf = $this->make_json_base64_string($input);

            // Токен
            $token = md5($creds->merchantid . $creds->productid . $amount . $cf . $creds->secret);

            // Формирование наименования продукта
            $course = $DB->get_record('course',
                [
                    'id' => $instance->courseid
                ]
                );
            $context = context_course::instance($course->id);
            $coursefullname = format_string($course->fullname, true,
                [
                    'context' => $context
                ]);

            // Назначение платежа (подписка на курс)
            $productname = get_string('acquiropay_payform_field_productname', 'enrol_otpay',
                $coursefullname);

            $postdata = [
                'product_id' => $creds->productid,
                'product_name' => $productname,
                'token' => $token,
                'amount' => $amount,
                'cf' => $cf,
                'cb_url' => new moodle_url('/enrol/otpay/ipn.php',
                    [
                        'enrolotpayid' => $enrolotpayid
                    ]),
                'ok_url' => new moodle_url('/enrol/otpay/return.php',
                    [
                        'id' => $COURSE->id,
                        'ok' => '1',
                        'ko' => '0',
                        'enrolotpayid' => $enrolotpayid
                    ]),
                'ko_url' => new moodle_url('/enrol/otpay/return.php',
                    [
                        'id' => $COURSE->id,
                        'ok' => '0',
                        'ko' => '1',
                        'enrolotpayid' => $enrolotpayid
                    ])
            ];

            // Передача email пользователя в acquiropay (для автоподстановки в поле email формы оплаты)
            $canemailtransfer = (bool)get_config('enrol_otpay', 'emailtransfer');
            if( $canemailtransfer )
            {
                $postdata['email'] = $USER->email;
            }

            // Проверим, включена ли интеграция с онлайн кассой
            $integration_status = get_config('enrol_otpay', 'acquiropay_kassa');
            if ( $integration_status )
            {// Интеграция включена, формирование данных для чека

            // Ставка НДС
            if ( ! empty($instance->customchar2) )
            {
                $tax = json_decode($instance->customchar2);
            } else
            {
                $tax = get_config('enrol_otpay', 'acquiropay_tax');
            }

            // Название товара
            $new_name = 'course' . $course->id;

            // Сбор данных онлайн-кассы
            $check_info = [
                'items' => [
                    [
                        'name' => $new_name,
                        'price' => format_float($amount, 2, false, false),
                        'sum' => format_float($amount, 2, false, false),
                        'quantity' => format_float(1, 1, false, false),
                        'tax' => $tax
                    ]
                ]
            ];

            // Добавление поля
            $postdata['receipt'] = htmlentities(json_encode($check_info));
            }

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
        $options->class = ['otpay_acquiropay'];
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
     *
     * {@inheritDoc}
     *
     * @see otpay::check_data()
     */
    function check_data( $data, $enrolotpay )
    {
        global $DB;
        //сюда будем собирать ошибки
        $checkdataresult = [];

        if ( ! $creds = $this->get_credentials() )
        {
            $checkdataresult[] = "Error: couldn't get credentials";
        }
        if ( empty($data->cf2) )
        {
            $data->cf2 = '';
        }
        if ( empty($data->cf3) )
        {
            $data->cf3 = '';
        }

        // Формируем подпись, которая будет сравниваться с подписью от аквиры
        $md5 = md5(
            $creds->merchantid . $data->payment_id . strtoupper($data->status) . $data->cf .
                 $data->cf2 . $data->cf3 . $creds->secret);

        if ( $md5 != $data->sign )
        {
            $checkdataresult[] = "Invalid hash";
            $verifysignresult = "Invalid hash";
        } else
        {
            $verifysignresult = "signature correct";
        }
        // Распарсим пришедшие данные
        $cf = $this->return_base64_json($data->cf);
        if ( ! is_object($cf) )
        { // Должен быть объект
            $checkdataresult[] = "error parsing cf";
        }
        foreach ( $cf as $key => $value )
        {
            $data->$key = $value;
        }
        $data->timeupdated = time();

        // Проверяем данные, пришедшие от аквиры
        if ( ! $instance = $DB->get_record("enrol",
            array(
                "id" => $data->instanceid,
                "status" => 0
            )) )
        {
            $checkdataresult[] = "Not a valid instance id";
        }
        $data->courseid = $instance->courseid;

        // Получим (проверим) записи пользователя и курса
        if ( ! $user = $DB->get_record("user", array(
            "id" => $data->userid
        )) )
        {
            $checkdataresult[] = "Not a valid user id";
        }
        if ( ! $course = $DB->get_record("course",
            array(
                "id" => $data->courseid
            )) )
        {
            $checkdataresult[] = "Not a valid course id";
        }
        if ( ! $context = context_course::instance($course->id) )
        {
            $checkdataresult[] = "Not a valid context id";
        }
        if ( isset($data->status) )
        {
            //оплата подтверждена
            if ( strcmp($data->status, "OK") != 0 || strcmp($data->status, "KO") == 0 )
            {
                $checkdataresult[] = "Transaction status is failed!";
            }
        } else
        {
            $checkdataresult[] = "Transaction status is failed!";
        }

        return [
            'succeed' => empty($checkdataresult),
            'errors' => $checkdataresult,
            'needdebit' => false,
            'waitdebit' => false,
            'paymentdata' => ['paymentid' => $data->payment_id]
        ];
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

        if ( get_config('enrol_otpay', 'acquiropay_mode') == 0 )
        { //используется тестовый режим, при этом пользователь является администратором
            // AND is_siteadmin($USER->id)
            //@TODO:добавить право подписки в тестовом режиме и проверять его, а не админские права
            include ($CFG->dirroot . "/enrol/otpay/plugins/acquiropay/test/settings.php");
            //тестовый идентификатор мерчанта
            $creds->merchantid = $merchantid;
            //тестовый идентификатор продукта
            $creds->productid = $productid;
            //тестовое секретное слово
            $creds->secret = $secret;
            //тестовый URL для авторизации суммы в банке
            $creds->url = $url;
        } else
            if ( get_config('enrol_otpay', 'acquiropay_mode') == 1 )
            {
                //идентификатор мерчанта
                $creds->merchantid = get_config('enrol_otpay', 'acquiropay_merchantid');
                //идентификатор продукта
                $creds->productid = get_config('enrol_otpay', 'acquiropay_productid');
                //секретное слово
                $creds->secret = get_config('enrol_otpay', 'acquiropay_secret');
                //URL для авторизации суммы в банке
                $creds->url = get_config('enrol_otpay', 'acquiropay_url');
            } else
            { //вероятно простой пользователь может получить доступ к тестовым платежам, этого не нужно допускать
                //или проблема с настройками, что маловероятно
                return false;
            }

        if ( empty($creds->merchantid) or empty($creds->productid) or empty($creds->secret) or
             empty($creds->url) )
        {
            return false;
        }
        return $creds;
    }

    /**
     * Создать строку состояния (для отправки OAuth-сервису и получения её обратно
     * на login/index.php)
     *
     * @param mixed $input
     *            - объект или массив с данными
     * @return string
     */
    function make_json_base64_string( $input )
    {
        $json = json_encode($input);
        return $this->base64_urlencode($json);
    }

    /**
     * Получить из параметры строки состояния
     * (для отправки OAuth-сервису и получения обратно)
     *
     * @param string $statestring
     *            - объект или массив с данными
     * @return object
     */
    function return_base64_json( $statestring )
    {
        $json = $this->base64_urldecode($statestring);
        return json_decode($json);
    }

    /**
     * Закодировать строку в base64, заменяя символы для безопасной передачи через url
     *
     * @param string $inputstr
     * @return string
     */
    function base64_urlencode( $inputstr )
    {
        return strtr(base64_encode($inputstr), '+/=', '-_,');
    }

    /**
     * Декодировать из безопасного кодирования строки в base64
     *
     * @param string $inputstr
     * @return string
     */
    function base64_urldecode( $inputstr )
    {
        return base64_decode(strtr($inputstr, '-_,', '+/='));
    }
}