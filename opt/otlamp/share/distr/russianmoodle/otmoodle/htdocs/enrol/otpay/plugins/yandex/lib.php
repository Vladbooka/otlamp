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
 * Плагин записи на курс OTPAY. Основной класс псевдосабплагина yandex.
 *
 * @package    enrol
 * @subpackage otpay
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once ($CFG->dirroot . "/enrol/otpay/plugins/otpay.php");

class otpay_yandex extends otpay
{
    protected $connector = null;

    /**
     * Получить версию псевдосабплагина
     *
     * @return int
     */
    public function version()
    {
        return 2017071300;
    }

    /**
     * Получить конфигурацию псевдосабплагина
     *
     * @return stdClass
     */
    public function otpay_config()
    {
        $config = new stdClass();

        // Доступные валюты по ISO 4217
        $config->currencycodes = [
            643 => 'RUB'
            //840 => 'USD'
        ];
        // Валюта по умолчанию
        $config->defaultcurrencycode = 643;

        $config->newinstanceurl = '/enrol/otpay/edit.php';
        $config->editurl = '/enrol/otpay/edit.php';
        $config->pixicon = new pix_icon('yookassa-pay', get_string('otpay_yandex', 'enrol_otpay'),
            'enrol_otpay');
        $config->configcapability = 'enrol/otpay:config';
        $config->unenrolcapability = 'enrol/otpay:unenrol';
        $config->managecapability = 'enrol/otpay:manage';
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
        $mform->addElement('header', 'header', get_string('otpay_yandex', 'enrol_otpay'));

        // Стоимость
        $mform->addElement('text', 'cost', get_string('yandex_form_field_cost', 'enrol_otpay'),
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
            get_string('yandex_form_field_currency', 'enrol_otpay'),
            $currencies);

        // Поддержка скидочных купонов
        $mform->addElement('checkbox', 'couponsupports', get_string('yandex_form_field_couponsupports', 'enrol_otpay'));
        $mform->setDefault('couponsupports', true);

        // Ставка НДС курса
        $visiblename = get_string('settings_tax', 'enrol_otpay');
        $mform->addElement('select', 'cash', $visiblename, $this->get_vat_options());
        if ( ! empty($form->get_instance()->customchar2) )
        {
            $mform->setDefault('cash', json_decode($form->get_instance()->customchar2));
        } else
        {
            // Ставка НДС
            $mform->setDefault('cash', get_config('enrol_otpay', 'yandex_tax'));
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
            $errors['cost'] = get_string('error_provider_yandex_form_edit_enrol_validation_cost', 'enrol_otpay');
        }
        $config = $this->otpay_config();
        if ( $data['cost'] < $config->minamount )
        {
            $errors['cost'] = get_string('error_provider_yandex_form_edit_enrol_validation_costminamount',
                'enrol_otpay', $config->minamount);
        }

        // Валидация валюты
        $currencies = $this->otpay_config()->currencycodes;
        if ( empty($data['currency']) || empty($currencies[$data['currency']]) )
        {// Указанная валюта не найдена среди доступных
            $errors['currency'] = get_string('error_provider_yandex_form_edit_enrol_validation_currency', 'enrol_otpay');
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
            $submitlabel = get_string('sberbank_free_enrol_field_submit', 'enrol_otpay');
        } else
        {
            $available_paysystems = (string)get_config('enrol_otpay', 'yandex_available_paysystems');
            $paysysform = $this->render_paysystems($available_paysystems);
            if( ! empty($paysysform) )
            {// Отображение изображений указанных пользователем платежных систем
                $mform->addElement('static','paysystems','',$paysysform);
            }
            $submitlabel = get_string('yandex_payform_field_submit', 'enrol_otpay');
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
        global $DB, $COURSE, $USER, $PAGE;

        // Базовый обработчик
        parent::form_add_user_enrolment_process($form, $instance, $files);

        $plugin = $form->get_plugin();

        if ( ! $creds = $this->get_credentials() )
        {// Настройки для провайдера не указаны
            redirect(
                new moodle_url('/enrol/otpay/return.php',[
                    'id' => $COURSE->id,
                    'ok' => '0',
                    'ko' => '1'
                ])
            );
        }

        // Получение экземпляра
        $instanceid = $formdata->instanceid;
        $instance = $DB->get_record('enrol', ['id' => $instanceid], '*', MUST_EXIST);

        // Конфигурация провайдера
        $config = $this->otpay_config();

        // Обработка купонов
        if ( ! $couponcodes = explode(',', $formdata->couponcodes) )
        {
            $couponcodes = "";
        }
        $couponform = new enrol_otpay_coupon_form($PAGE->__get('url')->__toString(), [
            'amount' => $instance->cost,
            'courseid' => $instance->courseid,
            'minamount' => $config->minamount
        ]);

        // Подсчет итоговой суммы с учетом купонов
        $amount = $couponform->get_amount($couponcodes);

        // Значение НДС
        $vatval = $this->get_vat($instance);

        // Формирование данных платежа
        $defaultenrolotpay = new stdClass();
        $defaultenrolotpay->instanceid = $instanceid;
        $defaultenrolotpay->courseid = $instance->courseid;
        $defaultenrolotpay->userid = $USER->id;
        $defaultenrolotpay->amount = (int)$amount < $config->minamount ? $config->minamount : $amount;
        $defaultenrolotpay->currency = $instance->currency;
        $defaultenrolotpay->options = serialize([
            'couponcodes' => $couponcodes,
            'vat_value' => $vatval
        ]);

        // Формирование черонвика заявки на оплату
        $enrolotpayid = $plugin->add_draft_enrol_otpay('yandex', $defaultenrolotpay);
        $enrolotpay = $DB->get_record('enrol_otpay',['id' => $enrolotpayid]);

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
            // Получение контроллера API
            $connector = $this->get_connector();
            if ($connector instanceof YandexCheckout\Client)
            {// работа с актуальным в 2020 sdk
                // Данные пользователя
                $user = $DB->get_record('user', ['id' => $enrolotpay->userid]);
                $amountstring = format_float($enrolotpay->amount, 2, false, false);
                $currencystring = $this->otpay_config()->currencycodes[(int)$enrolotpay->currency];
                $returnurl = new moodle_url('/enrol/otpay/return.php', [
                    // яндекс перенесет пользователя на эту страницу как в случае успеха, так и неудачи,
                    // но в любом случае запустится запрос текущего состояния платежа и его обработка (process_payment_status)
                    // в случае отмены - отменится, поэтому ko = 0, ok = 1
                    'ko' => 0,
                    'ok' => 1,
                    'enrolotpayid' => $enrolotpayid,
                    'id' => $enrolotpay->courseid,
                ]);

                // формирование описания платежа
                $extradata = [];
                $extradata['couponcodes'] = implode(', ', (array)$couponcodes);
                try {
                    $extradata['vat'] = $this->get_vat_string($vatval);
                } catch(\Exception $ex) {}
                $maxlength = YandexCheckout\Model\Payment::MAX_LENGTH_DESCRIPTION;
                $description = $this->get_payment_description($enrolotpay, $user, $extradata, $maxlength);


                // автоматический прием поступившего платежа
                // если false - будут работать как двухстадийные платежи
                $capture = true;
                $paymentata = [
                    'amount' => [
                        'value' => $amountstring,
                        'currency' => $currencystring
                    ],
                    'confirmation' => [
                        'type' => 'redirect',
                        'return_url' => $returnurl->out(false),
                    ],
                    'capture' => $capture,
                    'description' => $description,
                    'metadata' => ['enrolotpayid' => $enrolotpayid]
                ];

                // Проверка активности интеграции с онлайн кассой
                if (get_config('enrol_otpay', 'yandex_kassa'))
                {
                    // Название товара
                    $items = [
                        [
                            'description' => 'course'.$enrolotpay->courseid,
                            'quantity' => 1,
                            'amount' => [
                                'value' => $amountstring,
                                'currency' => $currencystring
                            ],
                            'vat_code' => $this->get_vat($instance),
                        ]
                    ];

                    // Данные для чека в онлайн-кассе
                    $paymentata['receipt'] = [
                        'email' => $user->email,
                        'customer' => ['email' => $user->email],
                        'tax_system_code' => get_config('enrol_otpay', 'yandex_taxsystem'),
                        'items' => $items
                    ];
                }

                try {
                    $payment = $connector->createPayment($paymentata);
                } catch(Exception $ex)
                {
                    $debuginfo = 'An exception was thrown during the creation of the payment; '.PHP_EOL.
                        'Exception code: '.$ex->getCode().'; '.PHP_EOL.
                        'Exception message: '.$ex->getMessage().'; '.PHP_EOL.
                        'Exception trace: '.$ex->getTraceAsString().'; '.PHP_EOL;
                    $link = $this->plugin->get_enrol_destination_url($instance);
                    print_error('error_provider_yandex_action_create_payment', 'enrol_otpay', $link, null, $debuginfo);
                }

                // Привязка внешнего идентификатора к заказу
                $enrolotpay->externalpaymentid = $payment->getId();
                $DB->update_record('enrol_otpay', $enrolotpay);

                $confirmationUrl = $payment->getConfirmation()->getConfirmationUrl();
                redirect($confirmationUrl);

            } else
            {// работа с устаревшим способом интеграции (HTTP-протокол)
                try {
                    // Переадресация пользователя в Яндекс кассу с помощью заполненной за него платежной формы
                    $orderdata = $connector->send_payment_form($enrolotpayid);
                } catch ( moodle_exception $e )
                {
                    // Отображение ошибки
                    debugging($e->getMessage().$e->debuginfo, DEBUG_DEVELOPER);
                    \core\notification::add(
                        get_string('error_provider_yandex_action_register', 'enrol_otpay'),
                        \core\notification::ERROR
                        );
                } catch ( dml_exception $e )
                {
                    // Отображение ошибки
                    debugging($e->getMessage().$e->debuginfo, DEBUG_DEVELOPER);

                    \core\notification::add(
                        get_string('error_provider_yandex_action_register', 'enrol_otpay'),
                        \core\notification::ERROR
                        );
                }
            }
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
        $options->class = ['otpay_yandex'];
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

    function reject_payment($enrolotpay)
    {
        // Получение контроллера API
        $connector = $this->get_connector();

        if ($connector instanceof YandexCheckout\Client)
        {// работа с актуальным в 2020 sdk

            $payment = $connector->getPaymentInfo($enrolotpay->externalpaymentid);


            // сюда, согласно нашей логике, мы можем попадать в случае, если истек срок ожидания платежа
            // проверяется по крону во время обработки платежей в статусе draft (черновик)
            // но вот коллапс, отменить платеж у яндекса, согласно документации, можно только если он в статусе waiting_for_capture
            // то есть средства захолдированы у пользователя и нам остается их только списать...
            // но тогда зачем нам отменять платеж? можно же забрать деньги и подписать в курс...
            // задал вопрос в яндекс можно ли отменять в статусе pending, пока ответа не получил

            // в любом случае двухстадийные платежи пока не реализованы
            // это заготовка на случай, если придется реализовать двухстадийные платежи
            // делается это путем передачи параметра capture=false при создании платежа
            // сейчас реализованы простые платежи и этот код исполняться не должен


            $idempotenceKey = uniqid('', true);
            $cancelresponse = $connector->cancelPayment($payment->getId(), $idempotenceKey);
            return ($cancelresponse->getStatus() == YandexCheckout\Model\PaymentStatus::CANCELED);
        }

        return false;
    }

    function process_payment_status($enrolotpay)
    {
        // Получение контроллера API
        $connector = $this->get_connector();

        if ($connector instanceof YandexCheckout\Client)
        {// работа с актуальным в 2020 sdk

            try {

                // Получим платеж от Яндекс Кассы, заодно убедившись в подлинности данных (если попали сюда через notification.php)
                $payment = $connector->getPaymentInfo($enrolotpay->externalpaymentid);
                $paymentdata = json_decode(json_encode($payment), true);
                $paymentstatus = $payment->getStatus();

                switch($paymentstatus)
                {
                    // Успешно оплачен и подтвержден магазином
                    case YandexCheckout\Model\PaymentStatus::SUCCEEDED:
                        // во что бы то ни стало, надо попытаться подписать пользователя в курс
                        $this->plugin->check_data($enrolotpay, $paymentdata);
                        break;

                    // Неуспех оплаты или отменен магазином (cancel)
                    case YandexCheckout\Model\PaymentStatus::CANCELED:
                        // яндекс уже знает, что платеж отменен - отменим и у себя
                        $this->plugin->reject_payment($enrolotpay);
                        break;

                    // Успешно оплачен покупателем, ожидает подтверждения магазином (capture или aviso)
                    case YandexCheckout\Model\PaymentStatus::WAITING_FOR_CAPTURE:
                        // если статус draft, значит мы еще не получали ответ от банка и у нас не зарегистрировано событие
                        // для такого случая запустим check_data, чтобы пройти полный путь, включающий генерацию события и создание подписки
                        // в другом случае, запустим процесс завершения платежа (2 стадия), спишем деньги и активируем подписку
                        $method = ($enrolotpay->status == "draft" ? 'check_data' : 'complete_payment');
                        $this->plugin->$method($enrolotpay, $paymentdata);
                        break;

                    // Ожидает оплаты покупателем
                    case YandexCheckout\Model\PaymentStatus::PENDING:
                    default:
                        // период ожидания платежа
                        $awperiod = get_config('enrol_otpay', 'yandex_payment_authorization_waiting_period');
                        if (time() > $enrolotpay->createdate + $awperiod)
                        {// платеж устарел, мы устали ждать, когда пользователь совершит оплату
                            // отменим платеж у себя и в банке
                            $this->plugin->reject_payment($enrolotpay, true);
                        }
                        break;
                }
            } catch ( Exception $ex ) {
                $this->plugin->otpay_log("Yandex notification error while processing enrol_otpay ".$enrolotpay->id." payment ".($paymentstatus ?? ''), [
                    $ex->getMessage(),
                    $ex->getCode(),
                    $ex->getTraceAsString()
                ]);
                if (in_array($ex->getCode(), [0, 404]))
                {
                    // несуществующий платеж с пустым идентификтаором (0)
                    // или платеж не существует в яндекс.кассе (404)
                    $this->plugin->reject_payment($enrolotpay);
                }
            }
        }
    }

    /**
     * Проверка данных, пришедших в ответе от банка
     *
     * @param object $data
     *            - данные вернувшиеся от банка
     * @param object $enrolotpay
     *            - объект otpay-платежа
     *
     * @return array - массив с результатом проверки
     */
    function check_data( $data, $enrolotpay )
    {
        // Получение контроллера API
        $connector = $this->get_connector();

        if ($connector instanceof YandexCheckout\Client)
        {// работа с актуальным в 2020 sdk
            // по новому sdk мы сюда попадаем только после того как сами сделали запрос а получение платежа в яндекс.кассу
            // и не сильно сомневаемся в подлинности данных, не требуется проверять какую-либо подпись (её нет)

            $errors = [];
            // платеж ожидает, когда мы спишем деньги
            $needdebit = ($data['status'] == YandexCheckout\Model\PaymentStatus::WAITING_FOR_CAPTURE);
            // платеж еще обрабатывается и мы не можем быть уверены, что он прошел
            $waitdebit = ($data['status'] == YandexCheckout\Model\PaymentStatus::PENDING);
            // Неуспех оплаты или отменен магазином (cancel)
            $canceled = ($data['status'] == YandexCheckout\Model\PaymentStatus::CANCELED);

            // успешна ли проверка данных - считаем, что да, если не было отказа;
            // для любого из перечисленных случаев создаем подписку (для $waitdebit и $needdebit неактивную)
            $succeed = ($needdebit || $waitdebit || ($data['status'] == YandexCheckout\Model\PaymentStatus::SUCCEEDED));

            if ($canceled)
            {// платеж не прошёл

                // в банке платеж уже отменен, отменим у себя
                $this->plugin->reject_payment($enrolotpay);

                // нет смысла создавать подписку
                // поэтому передаем ошибку обработки данных
                $succeed = false;
                $errors[] = 'payment was canceled';
            }

            return [
                'succeed' => $succeed,
                'errors' => $errors,
                'waitdebit' => $waitdebit,
                'needdebit' => $needdebit,
                'paymentdata' => $data
            ];
        } else
        {// работа с устаревшим способом интеграции (HTTP-протокол)
            return $connector->check_order($data);
        }
    }


    /**
     * Проверка данных, пришедших в ответе от банка
     *
     * @param object $enrolotpay - объект otpay-платежа
     *
     * @return array - массив с результатом проверки
     */
    function complete_payment( $enrolotpay, $paymentdata = null )
    {
        // Получение контроллера API
        $connector = $this->get_connector();

        if ($connector instanceof YandexCheckout\Client)
        {// работа с актуальным в 2020 sdk

            // это заготовка на случай, если придется реализовать двухстадийные платежи
            // делается это путем передачи параметра capture=false при создании платежа
            // сейчас реализованы простые платежи и до этого кода доходить не должно

            $payment = $connector->getPaymentInfo($enrolotpay->externalpaymentid);

            $captresponse = $connector->capturePayment(['amount' => $payment->amount], $payment->id, uniqid('', true));
            $result = ($captresponse->getStatus() == YandexCheckout\Model\PaymentStatus::SUCCEEDED);

            return ['result' => $result];
        } else
        {
            return $connector->payment_aviso( $paymentdata, $enrolotpay );
        }
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

        $workmode = get_config('enrol_otpay', 'yandex_mode');
        if ( empty($workmode) )
        {// Включен тестовый режим работы
            // @TODO:добавить право подписки в тестовом режиме

        }

        return $creds;
    }

    /**
     * Получение коннектора для работы с API Сбербанка
     */
    private function get_connector()
    {
        global $CFG;

        if ( empty($this->connector) )
        {// Требуется инициализация
            switch(get_config('enrol_otpay', 'yandex_connection'))
            {
                case 'http':
                    // Устаревший способ интеграции (HTTP-протокол)
                    // Подключение класса коннектора
                    require_once($CFG->dirroot.'/enrol/otpay/plugins/yandex/classes/connector.php');
                    $this->connector = new \enrol_otpay\plugins\yandex\connector($this->plugin, $this);
                    break;
                case 'api':
                default:
                    // Актуальный способ интеграции, именуемый "Умный платеж"
                    require_once($CFG->dirroot.'/enrol/otpay/plugins/yandex/classes/sdk/autoload.php');
                    $this->connector = new YandexCheckout\Client();
                    $this->connector->setAuth(
                        get_config('enrol_otpay', 'yandex_shopid'),
                        get_config('enrol_otpay', 'yandex_shoppassword')
                    );
                    break;
            }
        }

        return $this->connector;
    }

    /**
     * Получение комментария
     *
     * @param stdClass $enrolotpay - объект otpay-платежа, запись из таблицы enrol_otpay
     *
     * @return string
     */
    public function get_comment($enrolotpay)
    {
        // Сбор комментария
        $comment = '';

        if (empty($enrolotpay->options))
        {
            return $comment;
        }

        $options = unserialize($enrolotpay->options);
        if (empty($options))
        {
            return $comment;
        }

        $couponcodesarr = (array)($options['couponcodes'] ?? []);
        $couponcodes = implode(', ', $couponcodesarr);
        if (!empty($couponcodes))
        {
            $comment .= html_writer::div(get_string('apanel_couponcodes', 'enrol_otpay', $couponcodes)).PHP_EOL;
        }

        try {
            $vatval = $options['vat_value'] ?? null;
            $vatstr = $this->get_vat_string($vatval);
            $comment .= html_writer::div(get_string('apanel_vat', 'enrol_otpay', $vatstr)).PHP_EOL;
        } catch(\Exception $ex) {}

        return $comment;
    }
}