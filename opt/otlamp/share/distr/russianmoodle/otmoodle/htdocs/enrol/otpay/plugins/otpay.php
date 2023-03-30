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
 * Плагин записи на курс OTPAY. Базовый класс псевдосабплагинов - провайдеров платежей.
 *
 * @package    enrol
 * @subpackage otpay
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once ($CFG->libdir . '/form/duration.php');

use local_opentechnology\availability_condition\main;
use local_opentechnology\availability_condition\check_conditions_exception;
use enrol_otpay\plugins\no_credentials_exception;

abstract class otpay
{
    /**
     * Класс плагина
     *
     * @var enrol_otpay_plugin - Класс плагина подписки
     */
    protected $plugin = null;

    /**
     * Инициализация провайдера
     *
     * @param enrol_otpay $plugin - Плагин подписки на курс
     */
    public function __construct(enrol_otpay_plugin $plugin)
    {
        $this->plugin = $plugin;
    }

    /**
     * Версия провайдера
     *
     * @return int
     */
    public function version()
    {
        return 0;
    }

    /**
     * Получить имя провайдера
     *
     * @return string
     */
    public function get_name()
    {
        $classname = get_called_class();
        $classname = str_replace('otpay_', '', $classname);
        return $classname;
    }

    /**
     * Получить локализованное название провайдера
     *
     * @return string
     */
    public function get_localized_name()
    {
        return get_string(get_called_class(), 'enrol_otpay');
    }

    /**
     * Получить конфигурацию псевдосабплагина
     *
     * @return stdClass
     */
    function otpay_config()
    {
        $config = new stdClass();
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
    }

    abstract protected function get_enrol_page_hook_options($instance, $customdata);
    abstract protected function get_enrol_page_hook_form($instance, $customdata);

    /**
     * Хук страницы записи на курс
     *
     * @param stdClass $instance - Экземпляр способа записи на курс
     * @param stdClass $customdata - Дополнительные данные
     *
     * @return string - HTML-код блока записи на курс
     */
    final public function enrol_page_hook(stdClass $instance, $customdata) {

        global $USER, $PAGE, $CFG;

        // конфиг, хранимый в customtext3
        $config = json_decode($instance->customtext3, true);
        // настроены ли условия доступности способы записи
        $hasconditions = !empty($config['availability']['conditions']);
        // требуется ли скрывать способ записи, если он не доступен (иначе будут отображаться пояснения почему не доступен)
        $hideunavailable = !empty($config['availability']['hide_unavailable']);

        $options = $this->get_enrol_page_hook_options($instance, $customdata);

        // блок с заголовком подписки
        $instanceinfoheader = html_writer::start_div('otpay_instance_info_header');
        // иконка способа оплаты
        $renderer = $PAGE->get_renderer('core_enrol');
        $pixicon = fullclone($this->otpay_config()->pixicon);
        $pixicon->attributes['class'] = "otpay_instance_icon";
        $instanceinfoheader .= $renderer->render($pixicon);
        // название (описание) способа
        $instanceinfoheader .= html_writer::div($options->instancename, 'otpay_instance_name');
        $instanceinfoheader .= html_writer::end_div();


        // блок с датами подписки
        $instanceinfodates = html_writer::start_div('otpay_instance_info_dates');
        if(isset($options->enrolstartdate))
        {//начало обучения
            $enrolstartdatelabel = html_writer::div(
                get_string('otpay_enrolstartdate', 'enrol_otpay'), "otpay_instance_startdatelabel");
            if ($options->enrolstartdate == 0 || $options->enrolstartdate < time())
            { // подписываться можно всегда или уже наступил старт подписки
                $enrolstartdatestring = get_string('otpay_enrolstartdate_currenttime', 'enrol_otpay');
            } else
            {
                $enrolstartdatestring = userdate($options->enrolstartdate, '', $USER->timezone);
            }
            $enrolstartdate = html_writer::div($enrolstartdatestring, 'otpay_instance_startdate');

            $instanceinfodates .= html_writer::div($enrolstartdatelabel . $enrolstartdate);
        }

        if(!empty($options->enrolperiod))
        {//период обучения
            $enrolperiodlabel = html_writer::div(get_string('otpay_enrolperiod', 'enrol_otpay'),
                "otpay_instance_periodlabel");
            // период обучения
            $durationform = new MoodleQuickForm_duration();
            $enrolperiodarray = $durationform->seconds_to_unit($options->enrolperiod);
            $enrolperiodstring = $enrolperiodarray[0]." ".$durationform->get_units()[$enrolperiodarray[1]];
            $enrolperiod = html_writer::div($enrolperiodstring, 'otpay_instance_period');

            $instanceinfodates .= html_writer::div($enrolperiodlabel . $enrolperiod);
        }

        if(!empty($options->enrolenddate))
        {//окончание регистрации на курс
            $enrolenddatelabel = html_writer::span(get_string('otpay_enrolenddate', 'enrol_otpay'),
                "otpay_instance_enddatelabel");
            // дата окончания регистрации на курс
            $enrolenddatestring = userdate($options->enrolenddate, '', $USER->timezone);
            $enrolenddate = html_writer::div($enrolenddatestring, 'otpay_instance_enddate');

            $instanceinfodates .= html_writer::div($enrolenddatelabel . $enrolenddate);
        }
        $instanceinfodates .= html_writer::end_div();

        $instanceinfocost = "";
        if($this->otpay_config()->costsupports)
        {
            // блок с ценами подписки
            $instanceinfocost = html_writer::start_div('otpay_instance_info_cost');
            // валюта
            if(!empty($options->currency))
            {
                $currencycode = (string)$options->currency;
                // Нормализация валюты
                if ( ! isset($this->otpay_config()->currencycodes[(int)$currencycode]) )
                {// Числовой код валюты не найден
                    // Поиск валюты по буквенному индексу
                    $currencycode = array_search($currencycode, $this->otpay_config()->currencycodes);
                    if ( $currencycode == false )
                    {// Указанная в подписке валюта не найдена среди доступных
                        // Установка валюты по умолчанию
                        $currencycode = $this->otpay_config()->defaultcurrencycode;
                    }
                }
            } else
            {// Установка валюты по умолчанию
                $currencycode = $this->otpay_config()->defaultcurrencycode;
            }
            if(!empty($options->cost))
            {//стоимость
                $costlabel = html_writer::div(get_string('otpay_basecost','enrol_otpay'), "otpay_instance_costlabel");
                // стоимость
                $cost = $options->localisedcost;
                if((int)$options->cost == (float)$options->cost)
                {
                    $cost = (int)$options->cost;
                }
                $coststring = html_writer::div(
                    get_string('otpay_currency_'.$currencycode, 'enrol_otpay', html_writer::span($cost)),
                    'otpay_instance_cost'
                    );
                $instanceinfocost .= html_writer::div($costlabel . $coststring);
            }
            if(!empty($options->localisedamount) && !isguestuser())
            {//стоимость со скидкой
                // строка "стоимость со скидкой"
                $amountlabel = html_writer::div(get_string('otpay_amount', 'enrol_otpay'),
                    "otpay_instance_amountlabel");
                // стоимость со скидкой
                $amount = $options->localisedamount;
                if((int)$options->amount == (float)$options->amount)
                {
                    $amount = (int)$options->amount;
                }
                $amountstring = html_writer::div(
                    get_string('otpay_currency_'.$currencycode, 'enrol_otpay', html_writer::span($amount)),
                    'otpay_instance_amount'
                    );
                $instanceinfocost .= html_writer::div($amountlabel . $amountstring);
            }
            $instanceinfocost .= html_writer::end_div();
        }

        // описание экземпляра класса в произвольной форме
        $instanceinfodescription = html_writer::div(
            format_text($options->instancedescription),
            'otpay_instance_description'
            );

        // блок информации
        $instanceinfo = $instanceinfoheader . $instanceinfodates . $instanceinfocost . $instanceinfodescription;
        $instanceinfo = html_writer::div($instanceinfo, 'otpay_instance_info');

        $instanceform = html_writer::start_div('otpay_instance_form');
        if (!isloggedin() || isguestuser()) {
            // пользователь не авторизован (либо авторизован под гостем)
            // а для записи на курс нужен авторизованный негость
            // подменяем форму записи на курс - на кнопку для авторизации/регистрации

            if (empty($config['display_unauthorized'])) {
                // для данного способа записи настроен запрет отображения неавторизованным пользователям
                return '';
            }

            if ($hasconditions && $hideunavailable) {
                // способ записи на курс имеет условия доступа
                // а еще в нем настроено, что блок записи должен быть скрыт, если не доступен
                // мы врядли можем корректно проверить доступность для неатворизованного пользвоателя
                // у него и профиля-то нет
                // поэтому в таких случаях, считаем, что способ всегда полностью недоступен и скрыт
                return '';
            }

            $description = '';
            if (isguestuser()) {
                $description = html_writer::div(get_string('guest_should_login', 'enrol_otpay'));
            }
            $loginurl = new moodle_url('/login/');
            $logintext = get_string('otpay_login', 'enrol_otpay');
            $pageurl = $PAGE->url;
            $pageurl->param('otpayinstanceid', $instance->id);
            $signuptext = '';
            if (!empty($CFG->registerauth)) {
                $signuptext = get_string('unauthorized_can_signup', 'enrol_otpay');
            }
            $loginattrs = [
                'class' => 'btn btn-primary ot-login-button',
                'data-custom-pageurl' => $pageurl->out(false),
                'data-loginform-alert' => get_string('unauthorized_should_login', 'enrol_otpay', $signuptext),
                'data-loginform-alert-type' => 'warning',
            ];
            $loginlink = html_writer::link($loginurl, $logintext, $loginattrs);
            $form = html_writer::div($description . $loginlink, 'enrol_otpay_payment_form');
        } else {
            // пользователь авторизован и не гость

            try {

                if ($hasconditions) {
                    $coursecontext = context_course::instance($instance->courseid);
                    $ac = new main($coursecontext->id);
                    $ac->check_conditions($config['availability']['conditions']);
                }
                // условия доступа выполнились (или их не было настроено)
                // получение основной формы записи на курс
                try {
                    $form = $this->get_enrol_page_hook_form($instance, $customdata);
                } catch(no_credentials_exception $ex) {
                    // форма не доступна, такое бывает при отсутствии реквизитов подключения
                    // нет основных настроек - вообще не стоит показывать этот способ записи (он не настроен)
                    return '';
                }

            } catch (check_conditions_exception $ex) {
                // условия доступа к записи на курс не выполнены
                if ($hideunavailable) {
                    // настроено полностью не отображать блок записи на курс при невыполнении условий
                    return '';
                } else {
                    // отображение пояснений, почему способ записи не доступен
                    $a = new \stdClass();
                    $a->explanations = $ex->getMessage();
                    $a->name = $instance->name;
                    if (empty($a->name)) {
                        $a->name = get_string('otpay_' . $instance->customchar1, 'enrol_otpay');
                    }
                    $explanations = get_string('availability_conditions_explanations', 'enrol_otpay', $a);
                    $form = html_writer::div($explanations, 'unavailable_explanations');
                    $options->class[] = 'paymethod_unavailable';
                }
            }
        }
        $instanceform .= html_writer::div($form);
        $instanceform .= html_writer::end_div();

        $otpayinstanceid = optional_param('otpayinstanceid', NULL, PARAM_INT);
        if (!is_null($otpayinstanceid) && $otpayinstanceid == $instance->id) {
            $PAGE->requires->js_call_amd('enrol_otpay/formsender', 'init', [$otpayinstanceid]);
        }

        $class = implode(' ',$options->class);
        return html_writer::div($instanceinfo . $instanceform, 'otpay_instance '.$class, ['data-id' => $instance->id]);
    }

    /**
     * Формирование HTML-кода для отображения формы записи на курс
     *
     * @param object $options
     *
     * @return string html-код с формой подписки
     */
    public function get_enrolment_block($instance, $options, $form='')
    {
    }

    /**
     * Формирование html-кода с изображениями платежных систем
     *
     * @param string $paysystemsfield - Список платежных систем
     * @return string - html код с изображениями платежных систем
     */
    protected function render_paysystems($paysystems)
    {
        // Имеющиеся в плагине платежные системы
        $availablepaysystems = $this->get_paysystems();

        $html = '';
        $paysystems = (array)explode(',', $paysystems);
        foreach ( $paysystems as $paysystem )
        {
            $paysystem = trim($paysystem);
            $paysystem = ($paysystem == 'yad' ? 'yoomoney' : $paysystem);
            if ( isset($availablepaysystems[$paysystem]) )
            {// Выбранная платежная система поддерживается плагином
                $paysystem = $availablepaysystems[$paysystem];
                $html .= html_writer::img(new moodle_url(
                    $paysystem->picpath),
                    $paysystem->name
                );
            }
        }

        return $html;
    }

    /**
     * Получение доступных для выбора платежных систем
     *
     * @return array массив с объектами
     *         ['code'] - код платежной системы (имя файла, часть языковой строки)
     *         ['name'] - наименование платежной системы из языковой строки
     *         ['picpath'] - путь к изображению относительно dirroot
     */
    public function get_paysystems()
    {
        global $CFG;
        $paysystems = [];
        // путь к папке с изображениями относительно dirroot
        $paysystemspath = '/enrol/otpay/pix/paysystems';
        // полный путь к папке с изображениями
        $paysystemsfullpath = $CFG->dirroot . $paysystemspath;
        if (is_dir($paysystemsfullpath))
        {
            foreach ((array) scandir($paysystemsfullpath) as $paysyspath)
            {
                if ($paysyspath == '.' || $paysyspath == '..')
                {
                    continue;
                }
                if (file_exists($paysystemsfullpath . '/' . $paysyspath))
                {
                    $pathparts = pathinfo($paysyspath);
                    $paysys = new stdClass();
                    $paysys->code = $pathparts['filename'];
                    $paysys->name = get_string('otpay_paysystem_' . $pathparts['filename'],
                        'enrol_otpay');
                    $paysys->picpath = $paysystemspath . '/' . $paysyspath;
                    $paysystems[$paysys->code] = $paysys;
                }
            }
        }
        return $paysystems;
    }

    /**
     * Массив маршрутов статусов
     *
     * @return array
     */
    function get_statuses_route()
    {
        return [];
    }

    /**
     * Получение комментария
     *
     * @param stdClass $enrolnment
     *
     * @return string
     */
    function get_comment($enrolnment)
    {
        return '';
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
    }

    /**
     * Получение стандартного описания платежа
     *
     * @param object $enrolment - запись из таблицы enrol_otpay
     * @param object $user - запись из таблицы user; если null, то будет произведена попытка получить запись используя $enrolment->userid
     * @namespace array $extradata - массив дополнительных данных, которые надо передать в языковую строку.
*                                 в ключе должна быть строка, в значении то, что может конвертироваться в строку
*                                 ожидаемые ключи: vat, couponcodes
*                                 если их нет, в строку передаются свойства с пустой строкой
     */
    public function get_payment_description($enrolment, $user=null, array $extradata=[], int $maxlength=null)
    {
        global $DB;

        // Описание заявки
        $descriptiondata = new stdClass();
        $systemcontext = context_system::instance();
        $context = $systemcontext;
        // Данные пользователя
        $user = $DB->get_record('user', ['id' => $enrolment->userid]);
        if ( $user )
        {
            $context = context_user::instance($user->id);
            $descriptiondata->user_fullname = fullname($user);
        } else
        {
            $descriptiondata->user_fullname = '';
        }
        // Данные курса
        $course = get_course($enrolment->courseid);
        if ( $course )
        {
            if ( $context->id == $systemcontext->id )
            {
                $context = context_course::instance($course->id);
            }
            $descriptiondata->course_fullname = format_string($course->fullname, true, ['context' => $context]);
            $descriptiondata->course_shortname = format_string($course->shortname, true, ['context' => $context]);
        } else
        {
            $descriptiondata->course_fullname = '';
            $descriptiondata->course_shortname = '';
        }
        $descriptiondata->paymentid = $enrolment->paymentid;
        $descriptiondata->enrolmentid = $enrolment->id;

        // добавление информации об НДС
        $descriptiondata->vat = $this->get_description_extravar($extradata, 'vat');

        // добавление информации о купонах
        $couponcodes = $this->get_description_extravar($extradata, 'couponcodes');
        $descriptiondata->couponcodes = '';
        if (!empty($couponcodes))
        {
            // купоны дополнительно прогоняем через строку, потому что без пояснения не ясно что это
            // а с пояснением и при отсутствии купонов, будет замусоривать описание
            $descriptiondata->couponcodes = get_string('user_enrolment_description_couponcodes', 'enrol_otpay', $couponcodes);
        }

        $descriptionstring = get_string('user_enrolment_description', 'enrol_otpay', $descriptiondata);

        if (!is_null($maxlength))
        {
            $length = mb_strlen((string)$descriptionstring, 'utf-8');
            if ($length > $maxlength) {
                $descriptionstring = mb_substr((string)$descriptionstring, 0, $maxlength);
            }
        }

        return $descriptionstring;
    }

    /**
     * Получение значения переменной из доп.данных, используемого для формирования строки описания платежа
     * @param array $extradata - массив доп.данных, обычно формируется при создании платежа
     * @param string $key - ключ (aka название доп.переменной), поиск которого будет произведен в массиве доп.данных
     * @return string - строка найденная в доп.данных или пустая строка, если не будет найдено или найденное значение окажется не строкой
     */
    protected function get_description_extravar(array $extradata, $key)
    {
        if (array_key_exists($key, $extradata))
        {
            $value = $extradata[$key];
            if ((is_object($value) && method_exists($value, '__toString')) ||
                (!is_object($value) && !is_array($value) && settype($value, 'string') !== false))
            {
                return (string)$value;
            }
        }

        return '';
    }

    /**
     * Получить ставку НДС, настроенную для плагина
     * @param object $enrolinstance - запись из таблицы enrol
     */
    function get_vat($enrolinstance=null)
    {
        // Ставка НДС
        if (!is_null($enrolinstance) && !empty($enrolinstance->customchar2))
        {
            return json_decode($enrolinstance->customchar2);
        } else
        {
            return get_config('enrol_otpay', 'yandex_tax');
        }
    }

    /**
     * Получение значения языковой строки, описывающей выбранное значение НДС
     *
     * @param string $vatvalue - значение НДС
     * @throws coding_exception при отсутствии запрошенного значения НДС
     * @return string
     */
    function get_vat_string($vatvalue)
    {
        $vatoptions = $this->get_vat_options();
        if (!array_key_exists($vatvalue, $vatoptions))
        {
            throw new coding_exception('Unknown vat value');
        }
        return $vatoptions[$vatvalue];
    }

    /**
     * Получнение возможных значений НДС
     * @return string[] - в ключах код НДС, в значениях - языковая строка, описывающая выбранное значение НДС
     */
    function get_vat_options() {
        return [
            1 => get_string('settings_tax_first', 'enrol_otpay'),
            2 => get_string('settings_tax_second', 'enrol_otpay'),
            3 => get_string('settings_tax_third', 'enrol_otpay'),
            4 => get_string('settings_tax_fourth', 'enrol_otpay'),
            5 => get_string('settings_tax_fifth', 'enrol_otpay'),
            6 => get_string('settings_tax_sixth', 'enrol_otpay')
        ];
    }
}
