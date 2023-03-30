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
 * Плагин записи на курс OTPAY. Основной класс псевдосабплагина Sberbank.
 *
 * @package    enrol
 * @subpackage otpay
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once ($CFG->dirroot . "/enrol/otpay/plugins/otpay.php");

class otpay_sberbank extends otpay
{
    protected $connector = null;

    /**
     * Получить версию псевдосабплагина
     *
     * @return int
     */
    public function version()
    {
        return 2016120100;
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
        $config->pixicon = new pix_icon('sberbank', get_string('otpay_sberbank', 'enrol_otpay'),
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
        $mform->addElement('header', 'header', get_string('otpay_sberbank', 'enrol_otpay'));

        // Стоимость
        $mform->addElement('text', 'cost', get_string('sberbank_form_field_cost', 'enrol_otpay'),
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
            get_string('sberbank_form_field_currency', 'enrol_otpay'),
            $currencies);

        // Поддержка скидочных купонов
        $mform->addElement('checkbox', 'couponsupports', get_string('sberbank_form_field_couponsupports', 'enrol_otpay'));
        $mform->setDefault('couponsupports', true);


        // Ставка НДС курса
        $visiblename = get_string('settings_tax', 'enrol_otpay');
        $mform->addElement('select', 'cash', $visiblename, $this->get_vat_options());
        if (!empty($form->get_instance()->customchar2))
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
            $errors['cost'] = get_string('sberbank_error_form_validation_cost', 'enrol_otpay');
        }
        $config = $this->otpay_config();
        if ( $data['cost'] < $config->minamount )
        {
            $errors['cost'] = get_string('sberbank_error_form_validation_costminamount',
                'enrol_otpay', $config->minamount);
        }

        // Валидация валюты
        $currencies = $this->otpay_config()->currencycodes;
        if ( empty($data['currency']) || empty($currencies[$data['currency']]) )
        {// Указанная валюта не найдена среди доступных
            $errors['currency'] = get_string('error_provider_sberbank_form_edit_enrol_validation_currency', 'enrol_otpay');
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
            $available_paysystems = (string)get_config('enrol_otpay', 'sberbank_available_paysystems');
            $paysysform = $this->render_paysystems($available_paysystems);
            if( ! empty($paysysform) )
            {// Отображение изображений указанных пользователем платежных систем
                $mform->addElement('static','paysystems','',$paysysform);
            }

            $submitlabel = get_string('sberbank_payform_field_submit', 'enrol_otpay');
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
        $defaultenrolotpay->options = serialize([
            'couponcodes' => $couponcodes,
            'vat_value' => $this->get_vat($instance)
        ]);

        // Формирование черонвика заявки на оплату
        $enrolotpayid = $plugin->add_draft_enrol_otpay('sberbank', $defaultenrolotpay);
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

            try {
                // Регистрация заказа в Сбербанке
                $orderdata = $connector->create_order($enrolotpayid);

                // Привязка внешнего идентификатора к заказу
                $enrolment = new stdClass();
                $enrolment->id = $enrolotpayid;
                $enrolment->externalpaymentid = $orderdata['orderid'];
                $DB->update_record('enrol_otpay', $enrolment);

                // Редирект пользователя на форму оплаты
                redirect($orderdata['formurl']);
            } catch ( moodle_exception $e )
            {
                // Отображение ошибки
                debugging($e->getMessage().$e->debuginfo, DEBUG_DEVELOPER);
                \core\notification::add(
                    get_string('error_provider_sberbank_action_register', 'enrol_otpay'),
                    \core\notification::ERROR
                    );
            } catch ( dml_exception $e )
            {
                // Отображение ошибки
                debugging($e->getMessage().$e->debuginfo, DEBUG_DEVELOPER);

                \core\notification::add(
                    get_string('error_provider_sberbank_action_register', 'enrol_otpay'),
                    \core\notification::ERROR
                    );
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
        $options->class = ['otpay_sberbank'];
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
        $plugin = enrol_get_plugin('otpay');
        // Получение контроллера API
        $connector = $this->get_connector();

        try {
            // Запрос состояния платежа в Сбербанке
            return $connector->order_reverse($enrolotpay->id);
        } catch ( moodle_exception $e )
        {
            // Отображение ошибки
            debugging($e->getMessage().$e->debuginfo, DEBUG_DEVELOPER);
            \core\notification::add(
                get_string('error_provider_sberbank_action_reverse', 'enrol_otpay'),
                \core\notification::ERROR
                );
        } catch ( dml_exception $e )
        {
            // Отображение ошибки
            debugging($e->getMessage().$e->debuginfo, DEBUG_DEVELOPER);

            \core\notification::add(
                get_string('error_provider_sberbank_action_reverse', 'enrol_otpay'),
                \core\notification::ERROR
                );
        }
    }

    function process_payment_status($enrolotpay)
    {
        $plugin = enrol_get_plugin('otpay');
        // Получение контроллера API
        $connector = $this->get_connector();

        //заказ необходимо отклонить
        $toreject = false;
        //заказ необходимо подтвердить
        $toconfirm = false;

        try {
            // Запрос состояния платежа в Сбербанке
            $orderinfo = $connector->get_order_info($enrolotpay->id);
        } catch ( moodle_exception $e )
        {
            if( (int)$e->errorcode == 2 )
            {// платеж отклонен по причине ошибки в реквизитах
                // запланируем отмену заказа
                $toreject = true;
            }
            // Отображение ошибки
            debugging($e->getMessage().$e->debuginfo, DEBUG_DEVELOPER);
            \core\notification::add(
                get_string('error_provider_sberbank_action_getorderstatus', 'enrol_otpay'),
                \core\notification::ERROR
                );
        } catch ( dml_exception $e )
        {
            // Отображение ошибки
            debugging($e->getMessage().$e->debuginfo, DEBUG_DEVELOPER);

            \core\notification::add(
                get_string('error_provider_sberbank_action_getorderstatus', 'enrol_otpay'),
                \core\notification::ERROR
                );
        }

        if( !empty($orderinfo['orderstatus']) )
        {
            if( (int)$orderinfo['orderstatus'] == 2 )
            {// Проведена полная авторизация суммы платежа
                // запланируем подтверждение заказа и подписку в курс
                $toconfirm = true;
            }
            if( in_array($orderinfo['orderstatus'],[3, 4, 6]) )
            {//платеж в конечном статусе, по которому уже не будет произведена авторизация
                // запланируем отмену заказа
                $toreject = true;
            }
        }

        // срок ожидания авторизации платежа
        $awperiod = get_config('enrol_otpay', 'sberbank_payment_authorization_waiting_period');

        if( $toconfirm )
        {
            $this->plugin->otpay_log('trying to confirm', serialize($orderinfo));
            // запуск проверки данных платежа и как следствие подписки в курс
            $triggerdispatched = $this->plugin->check_data($enrolotpay, $orderinfo);
            return $triggerdispatched;
        } else if( $toreject )
        {
            $this->plugin->otpay_log('trying to reject by merchantstatus', serialize($orderinfo));
            //платеж уже отклонен в банке по какой-то из причин - просто меняем ему статус в базе
            return $this->plugin->reject_payment($enrolotpay, false);
        } else if( time() > $enrolotpay->createdate + $awperiod )
        {// срок ожидания авторизации платежа превышен
            $this->plugin->otpay_log('trying to reject by waiting period', serialize($orderinfo));
            //необходимо отменить платеж в банке, затем сменить статус в базе
            return $this->plugin->reject_payment($enrolotpay, true);
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
        $checkdataresult = [];
        // Получение валюты
        $currency = (string)$enrolotpay->currency;
        // Нормализация валюты
        if ( ! isset($this->otpay_config()->currencycodes[(int)$currency]) )
        {// Числовой код валюты не найден
            // Поиск валюты по буквенному индексу
            $code = array_search($currency, $this->otpay_config()->currencycodes);
            if ( $code )
            {// Валюта найдена по буквенному индексу
                $currency = $code;
            } else
            {// Указанная в подписке валюта не найдена среди доступных
                // Установка валюты по умолчанию
                $currency = $this->otpay_config()->defaultcurrencycode;
            }
        }

        // Получение стоимости c переводом на копейки/центы
        $amount = $enrolotpay->amount * 100;


        //проверим совпадает ли сумма платежа в ответе от банка с нашими данными
        if ( !isset($data['amount']) || $amount != $data['amount'] )
        {
            $checkdataresult[] = "amount doesn't match";
        }
        //проверим совпадает ли валюта в ответе от банка с нашими данными
        if ( !isset($data['currency']) || $currency != $data['currency'] )
        {
            $checkdataresult[] = "currency doesn't match";
        }
        //проверим совпадает номер платежа в ответе от банка с нашими данными
        if ( !isset($data['ordernumber']) || $enrolotpay->paymentid != $data['ordernumber'] )
        {
            $checkdataresult[] = "paymentid doesn't match";
        }
        //проверим соответствует ли статус проведенному платежу
        if ( !isset($data['orderstatus']) || $data['orderstatus'] != 2 )
        {
            $checkdataresult[] = "payment is not completed";
        }

        return [
            'succeed' => empty($checkdataresult),
            'errors' => $checkdataresult,
            'needdebit' => false,
            'waitdebit' => false,
            'paymentdata' => []
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

        if ( get_config('enrol_otpay', 'sberbank_mode') == 0 )
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
            // Подключение класса коннектора
            require_once($CFG->dirroot.'/enrol/otpay/plugins/sberbank/classes/connector.php');

            $this->connector = new \enrol_otpay\plugins\sberbank\connector($this->plugin, $this);
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