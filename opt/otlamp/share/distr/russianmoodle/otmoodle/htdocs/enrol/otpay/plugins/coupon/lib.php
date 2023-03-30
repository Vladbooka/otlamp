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
 * Плагин записи на курс OTPay. Провайдер оплаты "Купон".
 *
 * Провайдер записывает пользователя при указании специального купона
 *
 * @package    enrol
 * @subpackage otpay
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once ($CFG->dirroot.'/enrol/otpay/plugins/otpay.php');

class otpay_coupon extends otpay
{
    /**
     * Версия провайдера
     *
     * @return int
     */
    function version()
    {
        return 2016060900;
    }

    /**
     * Конфигурация провайдера
     *
     * @return stdClass
     */
    function otpay_config()
    {
        $config = new stdClass();
        $config->currencycodes = [];
        $config->newinstanceurl = "/enrol/otpay/edit.php";
        $config->editurl = "/enrol/otpay/edit.php";
        $config->pixicon = new pix_icon('coupon', get_string('otpay_coupon', 'enrol_otpay'),
            'enrol_otpay');
        $config->configcapability = "enrol/otpay:config";
        $config->unenrolcapability = "enrol/otpay:unenrol";
        $config->managecapability = "enrol/otpay:manage";
        $config->couponsupports = false;
        $config->costsupports = false;
        $config->minamount = 0;

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

        // Заголовок раздела формы
        $mform->addElement('header', 'header', get_string('otpay_coupon', 'enrol_otpay'));

        // Настройки не указаны
        $mform->addElement('static', 'message', '',
            get_string('coupon_form_field_nofields', 'enrol_otpay'));
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
        parent::form_add_user_enrolment_definition($form, $customdata);

        $mform = $form->get_mform();

        list($instance) = $customdata;

        // поле для ввода купона
        $mform->addElement('text', 'couponcode', get_string('coupon_payform_field_enter_code','enrol_otpay'));
        $mform->setType('couponcode', PARAM_ALPHANUM);
        // обязательность для ввода
        $requiredmessage = get_string('coupon_error_form_validation_emptycouponcode', 'enrol_otpay');
        $mform->addRule('couponcode', $requiredmessage, 'required', null, 'client');
        // добавление плейсхолдера
        $placeholdertext = get_string('coupon_payform_field_placeholder_coupon_code','enrol_otpay');
        $mform->updateElementAttr(['couponcode'], ['placeholder' => $placeholdertext]);

        $submitlabel = get_string('coupon_payform_field_submit', 'enrol_otpay');
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

        global $DB;

        // Валидация купона
        if ( empty($data['couponcode']) )
        {
            $errors['couponcode'] = get_string('coupon_error_form_validation_emptycouponcode', 'enrol_otpay');
        } else
        {
            $instanceid = $data['instanceid'];
            $instance = $DB->get_record('enrol',
                [
                    'id' => $instanceid
                ], '*', MUST_EXIST);

            $couponsselect = "code=:code
                AND status='active'
                AND discounttype='freeaccess'
                AND (courseid=0 OR courseid=:courseid)
                AND (lifetime=0 OR createtime+lifetime>:curtime)";
            $couponsparams = [
                'code' => $data['couponcode'],
                'courseid' => $instance->courseid,
                'curtime' => time()
            ];
            if ( ! $DB->record_exists_select('enrol_otpay_coupons', $couponsselect, $couponsparams) )
            { //нет подходящего действующего купона
                $errors['couponcode'] = get_string('coupon_error_form_validation_badcouponcode',
                    'enrol_otpay');
            }
        }
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
        global $DB, $USER;

        // Базовый обработчик
        parent::form_add_user_enrolment_process($form, $instance, $files);

        $plugin = $form->get_plugin();

        // Получение экземпляра
        $instanceid = $formdata->instanceid;
        $instance = $DB->get_record('enrol',
            [
                'id' => $instanceid
            ], '*', MUST_EXIST
        );
        // подпишем пользователя
        $data = self::enrol_draft_and_subscription(
            $instanceid, $instance->courseid, $USER->id, $formdata->couponcode, $plugin);
        // перенаправим на курс
        self::enrol_redirect($data, $instance->courseid);
    }

    /**
     * Редирект на станицу
     *
     * @param mixed[] $data ответ от ipn.php, enrolotpayid
     * @param int $courseid
     */
    public static function enrol_redirect ($data, $courseid)
    {
        global $COURSE;

        if ( !empty($data[0]) && (int)$data[0] == 1 )
        {
            redirect(
                new moodle_url('/enrol/otpay/return.php',
                    [
                        'id' => $courseid,
                        'ok' => '1',
                        'ko' => '0',
                        'enrolotpayid' => $data[1]
                    ]));
        } else
        {
            redirect(
                new moodle_url('/enrol/otpay/return.php',
                    [
                        'id' => $courseid,
                        'ok' => '0',
                        'ko' => '1',
                        'enrolotpayid' => $data[1]
                    ])
                );
        }
    }

    /**
     * Подписка на курс
     *
     * @param int $instanceid
     * @param int $courseid
     * @param int $userid
     * @param string $couponcode
     * @param object $plugin
     * @return mixed[] ответ от ipn.php, enrolotpayid
     */
    public static function enrol_draft_and_subscription (
        $instanceid, $courseid, $userid, $couponcode, $plugin)
    {
        // Формирование данных платежа
        $defaultenrolotpay = new stdClass();
        $defaultenrolotpay->instanceid = $instanceid;
        $defaultenrolotpay->courseid = $courseid;
        $defaultenrolotpay->userid = $userid;
        $defaultenrolotpay->options = serialize(
            [
                'couponcodes' => $couponcode
            ]
            );

        // Создание записи
        $enrolotpayid = $plugin->add_draft_enrol_otpay('coupon', $defaultenrolotpay);

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL,
            new moodle_url('/enrol/otpay/ipn.php',
                [
                    'enrolotpayid' => $enrolotpayid
                ]));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
        curl_setopt($ch, CURLOPT_POSTFIELDS,
            http_build_query(
                [
                    'couponcode' => $couponcode
                ]));
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_TIMEOUT, 3);

        $result = curl_exec($ch);
        curl_close($ch);
        return [$result, $enrolotpayid];
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
        $options->instancename = $instance->name;
        $options->instancedescription = $instance->customtext2 ?? '';
        $options->enrolstartdate = $instance->enrolstartdate;
        $options->enrolperiod = $instance->enrolperiod;
        $options->enrolenddate = $instance->enrolenddate;
        $options->class = ['otpay_coupon'];
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

        $form = '';
        if (isloggedin() && !isguestuser()) {
            $adduserenrolmentform = new enrol_otpay_add_user_enrolment_form($PAGE->url, [$instance]);
            $adduserenrolmentform->process();
            $form = $adduserenrolmentform->render();
        }

        return $form;
    }

    /**
     * {@inheritDoc}
     * @see otpay::check_data()
     */
    function check_data( $data, $enrolotpay )
    {
        global $DB;
        //сюда будем собирать ошибки
        $checkdataresult = [];

        if ( ! empty($data->couponcode) )
        { //код ввели - надо проверить актуальность кода
            $couponsselect = "code=:code
                AND status='active'
                AND discounttype='freeaccess'
                AND (courseid=0 OR courseid=:courseid)
                AND (lifetime=0 OR createtime+lifetime>:curtime)";
            $couponsparams = [
                'code' => $data->couponcode,
                'courseid' => $enrolotpay->courseid,
                'curtime' => time()
            ];
            if ( ! $DB->record_exists_select('enrol_otpay_coupons', $couponsselect, $couponsparams) )
            { //нет подходящего действующего купона
                $checkdataresult[] = "no active coupon with this couponcode";
            }
        } else
        {
            $checkdataresult[] = "empty couponcode";
        }
        if ( empty($checkdataresult) )
        { //в этом способе оплаты печатаем 1, чтобы понять, что запрос выполнен успешно
            echo "1";
        } else
        {
            echo "0";
        }

        return [
            'succeed' => empty($checkdataresult),
            'errors' => $checkdataresult,
            'needdebit' => false,
            'waitdebit' => false,
            'paymentdata' => [],
        ];
    }
}