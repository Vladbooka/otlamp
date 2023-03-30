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
 * Плагин записи на курс OTPAY. Основной класс плагина.
 *
 * @package    enrol
 * @subpackage otpay
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();
require_once ($CFG->dirroot . '/enrol/otpay/form.php');
require_once ($CFG->dirroot . '/enrol/otpay/classes/otserial.php');

class enrol_otpay_plugin extends enrol_plugin
{
    /**
     * Список провайдеров плагина подписки
     *
     * @var array|null
     */
    private $providers = null;

    private $currencycodes;

    private $editurl;

    private $pixicon;

    private $configcapability;

    private $unenrolcapability;

    private $managecapability;

    private $newinstanceurl;

    private $costsupports;

    private $couponsupports;

    private $couponsformprinted;

    private $minamount;

    /**
     * Установка базовых настроек плагина (могут переопределяться псевдосабплагинами)
     */
    function __construct()
    {
        $this->currencycodes = [
            'RUB'
        ];
        $this->newinstanceurl = "/enrol/otpay/edit.php";
        $this->editurl = "/enrol/otpay/edit.php";
        $this->pixicon = new pix_icon('otpay', get_string('pluginname', 'enrol_otpay'),
            'enrol_otpay');
        $this->configcapability = "enrol/otpay:config";
        $this->unenrolcapability = "enrol/otpay:unenrol";
        $this->managecapability = "enrol/otpay:manage";
        $this->costsupports = true;
        $this->couponsupports = true;
        //форму со скидочными купонами печатаем лишь один раз,
        //в связи с этим, при первом обращении, инициализируем флаг в значении false,
        //форма еще не выводилась
        $this->couponsformprinted = false;
        $this->minamount = 0;
    }

    /**
     * Поддержка цены псевдосабплагином
     *
     * @return boolean - поддерживает ли цену псевдосабплагин
     */
    public function is_cost_supports()
    {
        return $this->costsupports;
    }

    /**
     * Поддержка скидочных купонов псевдосабплагином
     *
     * @return boolean - поддерживает ли скидочные купоны псевдосабплагин
     */
    public function is_coupons_supports()
    {
        return $this->couponsupports;
    }

    /**
     * Получить список объектов псевдосабплагинов - провайдеров
     *
     * @return array - Массив инициализированных провайлеров
     */
    public function get_providers()
    {
        global $CFG;

        if ( $this->providers === null )
        {// Инициализация провайдеров

            $this->providers = [];

            // Получение списка псевдосаблагинов
            require($CFG->dirroot . '/enrol/otpay/db/subplugins.php');

            // Поиск местоположения провайдеров
            if( ! empty($subplugins['otpay']) )
            {// Путь найден

                // Валидация пути
                $providersdir = $CFG->dirroot.'/'.$subplugins['otpay'];
                if ( is_dir($providersdir) )
                {
                    // Поиск и инициализация провайдеров
                    foreach ( (array)scandir($providersdir) as $pluginname )
                    {
                        if ( $pluginname == '.' || $pluginname == '..' )
                        {
                            continue;
                        }

                        if ( is_dir($providersdir.'/'.$pluginname) )
                        {// Папка с псевдосабплаигином провайдера
                            $pluginpath = $providersdir.'/'.$pluginname.'/lib.php';
                            if ( file_exists($pluginpath) )
                            {// Класс провайдера найден
                                require_once($pluginpath);

                                $classname = 'otpay_'.$pluginname;
                                if ( class_exists($classname) )
                                {// Инициализация провайдера

                                    $provider = new $classname($this);
                                    if ( method_exists($provider, 'version') )
                                    {// Версия указана

                                        // Добавление провайдера
                                        $this->providers[$pluginname] = $provider;
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }
        return $this->providers;
    }

    /**
     * Получить данные подписки на курс
     *
     * @param int $instanceid - ID экземпляра подписки пользователя на курс
     *
     * @return stdClass|null - Экземпляр подписки
     */
    public function get_enrolment($instanceid)
    {
        global $DB;

        $enrolment = $DB->get_record('enrol_otpay', ['id' => (int)$instanceid]);
        if ( empty($enrolment) )
        {
            return null;
        }
        return $enrolment;
    }

    /**
     * Переход записи OTPAY в новый статус
     *
     * @param stdClass $record
     * @param string $newstatus
     *
     * @return bool
     */
    public function set_status($recordid, $newstatus)
    {
        global $DB;

        if ( empty($recordid) || empty($newstatus) )
        {
            return false;
        }

        $record = $DB->get_record('enrol_otpay', ['id' => $recordid]);
        // Проверка существования записи
        if ( empty($record) )
        {
            return false;
        }

        $providers = $this->get_providers();
        if ( empty($providers[$record->paymethod]) )
        {
            return false;
        }

        // Проверка, что на выбранный статус можно перейти
        $route_statuses = $providers[$record->paymethod]->get_statuses_route();
        if ( empty($route_statuses[$record->status]) ||  ! in_array($newstatus, $route_statuses[$record->status]) )
        {
            return false;
        }

        return $this->process_payment($record);
    }

    /**
     * Сгенерировать ошибку
     *
     * @param string  $errorcode - Идентификатор строки ошибки
     * @param int $enrolmentid - ID пользовательской заявки
     * @param null|stdClass $a - Данные для генерации строки ошибки
     * @param string $additionalinfo - Дополнительное описание ошибки
     *
     * @return void
     *
     * @throws moodle_exception
     */
    public function rise_error($errorcode = '', $enrolmentid = 0, $a = null, $additionalinfo = '')
    {
        // Нормализация
        $a = (object)$a;
        $enrolmentid = (int)$enrolmentid;

        // Генерация исключения
        $exception = new moodle_exception($errorcode, 'enrol_otpay', null, $a, $additionalinfo);

        // Логирование ошибки
        $logdata = [
            'error' => $exception->getMessage(),
            'description' => $exception->debuginfo,
            'enrolmentid' => $enrolmentid
        ];
        $this->otpay_log('error', $logdata);

        throw $exception;
    }














    /**
     * Переопределение базовой конфигурации псевдосабплагином
     *
     * @param object $instance
     */
    public function otpay_config($instance)
    {
        global $CFG;
        if ( $instance->id )
        {
            if ( $subplugin = $this->get_paymethod_with_existing_method($instance->customchar1,
                "otpay_config") )
            {
                $otpayconfig = $subplugin->otpay_config($instance);

                $this->currencycodes = $otpayconfig->currencycodes;
                $this->newinstanceurl = $otpayconfig->newinstanceurl;
                $this->editurl = $otpayconfig->editurl;
                $this->pixicon = $otpayconfig->pixicon;
                $this->configcapability = $otpayconfig->configcapability;
                $this->unenrolcapability = $otpayconfig->unenrolcapability;
                $this->managecapability = $otpayconfig->managecapability;
                $this->costsupports = $otpayconfig->costsupports;
                //купоны могут поддерживаться только если поддерживается цена, имеется поддержка купонов плагином и поддержка купонов инстансом
                $this->couponsupports = $this->costsupports && $otpayconfig->couponsupports && ((int)$instance->customint6 == 1);
                $this->minamount = $otpayconfig->minamount;
            }
        }
    }

    /**
     * Проверка настроек периода отображения способа записи в зависимости от предыдущих завершенных подписок
     *
     * @param object $instance
     * @return boolean
     */
    function is_displayenrol_period( $instance )
    {
        global $DB, $USER;

        //фиксируем текущее время
        $curtime = time();
        //период, который должен пройти с момента последней подписки до начала отображения
        $startdisplayperiod = $instance->customint2;
        //период, который должен пройти с момента последней подписки до окончания отображения
        $enddisplayperiod = $instance->customint3;
        //по умолчанию ставим конечную дату текущим временем, необходимо для старта подписки, если до этого подписок не было и настройки условия для старта не было
        $lastuetimeend = $curtime;

        if ( is_null($startdisplayperiod) and is_null($enddisplayperiod) )
        { //ограничения не настраивали, можно отображать без дополнительных проверок
            return true;
        }

        //получим способы записи в курсе, которые могли использоваться для записи
        $enrolselect = "courseid=:courseid";
        $enrolparams = [
            'courseid' => $instance->courseid
        ];
        if ( $instance->customint4 )
        { //требуется проверять прошедший отрезок времени только для подписок через otpay
            $enrolselect .= "AND enrol='otpay'";
        }
        $enrolids = $DB->get_fieldset_select('enrol', 'id', $enrolselect, $enrolparams);

        if ( ! empty($enrolids) )
        {
            //найдем последнюю завршенную автоматически по времени пользовательскую подписку среди найденных способов записи
            $ueselect = "status=0 AND userid=:userid AND enrolid IN (" . implode(",", $enrolids) .
                 ") AND timeend<:curtime ORDER BY timeend DESC LIMIT 1";
            $ueparams = [
                'userid' => $USER->id,
                'curtime' => $curtime
            ];

            if ( $lastue = $DB->get_record_select('user_enrolments', $ueselect, $ueparams, '*') )
            {
                if ( $lastue->timeend > 0 )
                {
                    $lastuetimeend = $lastue->timeend;
                }
            } else
            { //не было подписок ранее, а ограничения зависимые от них есть, значит, не отображаем способ записи
                if ( ! is_null($startdisplayperiod) )
                { //необходимо отобразить опдписку после предыдущей, а предыдущей не было - пичалька
                    return false;
                }
            }
        } else
        { //курс не имеет ни одного способа записи? а как мы сюда попали?
            return false;
        }

        //по умолчанию считаем, что ограничений нет - можно отображать
        $result = true;

        if ( ! is_null($startdisplayperiod) )
        {
            //если с момента завершения последней подписки прошло достаточное количество времени для старта отображения, то будет по прежнему true
            $result = $result && ($curtime >= ($lastuetimeend + $startdisplayperiod));
        }

        if ( ! is_null($enddisplayperiod) )
        {
            //если с момента завершения последней подписки не прошло количество времени отведенное для отображения, то будет по прежнему true
            $result = $result && ($curtime <= ($lastuetimeend + $enddisplayperiod));
        }

        return $result;
    }

    /**
     * Формирование страинцы записи на курс
     *
     * @param stdClass $instance - Экземпляр подписки на курс
     *
     * @return string HTML-код с формой подписки
     */
    public function enrol_page_hook(stdClass $instance)
    {
        global $DB, $USER, $PAGE;

        $html = '';

        // получение контекста курса
        $coursecontext = context_course::instance($instance->courseid);
        // является ли пользователь админом
        $isadmin = array_key_exists($USER->id, get_admins());
        // имеет ли пользователь активную подписку на курс (любую, а не только через этот инстанс, как проверялось ранее)
        $isenrolled = is_enrolled($coursecontext, $USER, '', true);
        // имеет ли право просматривать курс
        $canviewcourse = has_capability('moodle/course:view', $coursecontext);
        // если пользователь уже имеет какой-нибудь доступ к курсу, записи на курс ему не нужны
        // так было в витрине и все равно способ записи не был доступен никому
        // однако без этой проверки здесь, далее формировались формы записи: рендерились, но не выводились
        // из-за этого падали ошибки js, теперь отсекаем на более раннем этапе - не будут рендериться - не будет ошибок js
        if ($isadmin || $isenrolled || $canviewcourse) {
            return '';
        }

        // Получение экземпляров подписок на текущий курс
        $courseenrols = enrol_get_instances($instance->courseid, true);
        $courseenrolids = [];
        foreach($courseenrols as $courseenrol)
        {
            $courseenrolids[] = $courseenrol->id;
        }

        if( ! empty($courseenrolids) )
        {// В курсе есть активные экземпляры подписок

            // Поиск будующих подписок пользователя
            $futureueselect = "userid=:userid AND enrolid IN (".implode(',',$courseenrolids).") AND
                status=0 AND timestart>:timestart
                ORDER BY timestart";
            $futureueparams = [
                'userid' => $USER->id,
                'timestart' => time()
            ];
            if ( $DB->record_exists_select('user_enrolments', $futureueselect, $futureueparams) )
            {
                // Пользователь уже имеет подписку, которая активируется позже
                $futureue = $DB->get_record_select('user_enrolments', $futureueselect, $futureueparams,
                    '*', IGNORE_MULTIPLE);
                $futureenrollmentstartdate = userdate($futureue->timestart, '', $USER->timezone);
                $futureenrollmenttext = get_string('future_enrollment', 'enrol_otpay', $futureenrollmentstartdate);
                $futureenrollmenthtml = html_writer::div($futureenrollmenttext, 'futureenrollment');
                return $futureenrollmenthtml;
            }
        } else
        {// В курсе нет активных экземпляров подписок
            return $html;
        }

        // Обработка условий отображения подписки
        if ( ! $instance->customint5 && $instance->enrolstartdate != 0 && $instance->enrolstartdate > time() )
        {// Запрещено подписываться заранее, а настроенная дата начала подписки еще не наступила
            return $html;
        }
        if ( $instance->enrolenddate != 0 && $instance->enrolenddate < time() )
        {// Установленная дата окончания подписки уже прошла
            return $html;
        }
        if ( ! $this->is_displayenrol_period($instance) )
        {// Настроены ограничения отображения, действующие в текущий момент времени - не будем отображать этот способ записи
            return $html;
        }

        // Получение текущего экземпляра подписки
        $postinstanceid = optional_param('instanceid', null, PARAM_INT);
        if ( ! empty($postinstanceid) && $postinstanceid != $instance->id )
        {// Грязный хак, несколько форм одного класса не валидировались более одного раза, так что если был пост - показываем только отправленную форму
            return $html;
        }

        // Получение провайдера
        $plugin = enrol_get_plugin('otpay');
        $providers = $plugin->get_providers();
        if ( ! empty($providers[$instance->customchar1]) )
        {
            // Текущий провайдер
            $provider = $providers[$instance->customchar1];
            // Конфигурация текущего провайдера
            $this->otpay_config($instance);
            // Цена подписки на курс
            $cost = 0;
            // Итоговая сумма для оплаты
            $amount = 0;
            // Массив купонов
            $coupons = [];
            // Дополнительные данные
            $customdata = new stdClass();

            if ( $this->costsupports )
            {// Провайдер поддерживает установку стоимости

                // Цена подписки из конфигурации
                $cost = (float) $instance->cost;

                if ( $this->couponsupports )
                {// Провайдер поддерживает купоны
                    // Форма регистрации купонов
                    $couponform = new enrol_otpay_coupon_form($PAGE->__get('url')->__toString(),
                        [
                            'amount' => $cost,
                            'courseid' => $instance->courseid,
                            'minamount' => $this->minamount
                        ]
                    );
                    // Получение итоговой суммы с учетом скидки
                    $amount = $couponform->process();
                    $customdata->couponcodes = $couponform->get_coupons();
                } else
                {// Провайдер не поддерживает купоны
                    $amount = $cost;
                }
            }

            // Добавление информации об оплате
            $customdata->cost = format_float((float)$cost, 2, false);
            $customdata->amount = format_float($amount, 2, false);

            // Передача отображения формы провайдеру
            $providerhtml = $provider->enrol_page_hook($instance, $customdata);

            if ( isset($couponform) && $providerhtml != '' && !$this->couponsformprinted )
            {
                //подключение js для скрытия дополнительных кодов
                $PAGE->requires->strings_for_js([
                    'coupon_more_codes',
                    'coupon_hide_codes'
                ], 'enrol_otpay');
                $PAGE->requires->js('/enrol/otpay/couponscript.js');
                //вывод формы скидочных купонов
                $html .= $couponform->render();
                //помечаем форму скидочных купонов как распечатанную, чтобы повторно не выводить ее
                $this->couponsformprinted = true;
            }
            if ( $providerhtml != '' )
            {
                $html .= $providerhtml;
            }
        }

        return $html;
    }

    /**
     * Sets up navigation entries.
     *
     * @param navigation_node $instancesnode
     * @param stdClass $instance
     * @return void
     */
    public function add_course_navigation( $instancesnode, stdClass $instance )
    {
        if ( $instance->enrol !== 'otpay' )
        {
            throw new coding_exception('Invalid enrol instance type!');
        }
        $context = context_course::instance($instance->courseid);
        if ( has_capability($this->configcapability, $context) )
        {
            $managelink = new moodle_url($this->editurl,
                array(
                    'courseid' => $instance->courseid,
                    'id' => $instance->id
                ));
            $instancesnode->add($this->get_instance_name($instance), $managelink,
                navigation_node::TYPE_SETTING);
        }
    }

    /**
     * Returns optional enrolment information icons.
     *
     * This is used in course list for quick overview of enrolment options.
     *
     * We are not using single instance parameter because sometimes
     * we might want to prevent icon repetition when multiple instances
     * of one type exist. One instance may also produce several icons.
     *
     * @param array $instances
     *            all enrol instances of this type in one course
     * @return array of pix_icon
     */
    public function get_info_icons( array $instances )
    {
        $availableicons = [];
        foreach ( $instances as $instance )
        {

            if ( ! $instance->customint5 && $instance->enrolstartdate != 0 &&
                 $instance->enrolstartdate > time() )
            { //запрещено подписываться заранее, а настроенная дата начала подписки еще не наступила
                continue;
            }

            if ( $instance->enrolenddate != 0 && $instance->enrolenddate < time() )
            { //установленная дата окончания подписки уже прошла
                continue;
            }

            $this->otpay_config($instance);
            $availableicons[$instance->customchar1] = $this->pixicon;
        }
        return $availableicons;
    }

    /**
     * Lists all protected user roles.
     *
     * @return bool(true or false)
     */
    public function roles_protected()
    {
        // Users with role assign cap may tweak the roles later.
        return false;
    }

    /**
     * Defines if user can be unenrolled.
     *
     * @param stdClass $instance
     *            of the plugin
     * @return bool(true or false)
     */
    public function allow_unenrol( stdClass $instance )
    {
        // Users with unenrol cap may unenrol other users manually - requires enrol/stripe:unenrol.
        return true;
    }

    /**
     * Defines if user can be managed from admin.
     *
     * @param stdClass $instance
     *            of the plugin
     * @return bool(true or false)
     */
    public function allow_manage( stdClass $instance )
    {
        // Users with manage cap may tweak period and status - requires enrol/stripe:manage.
        return true;
    }

    /**
     * Defines if 'enrol me' link will be shown on course page.
     *
     * @param stdClass $instance
     *            of the plugin
     * @return bool(true or false)
     */
    public function show_enrolme_link( stdClass $instance )
    {
        return ($instance->status == ENROL_INSTANCE_ENABLED);
    }

    /**
     * Обработка "зависших" платежей (заблокированы на карте слушателя, но еще не списаны)
     */
    function process_stucked_payments()
    {
        global $DB;
        //время устаревания. платежи старше недельной давности не обрабатываем
        $timeaging = time() - 7 * 24 * 60 * 60;
        $stuckedpayments = $DB->get_records_select('enrol_otpay',
            'timemodified>:timeaging AND status="needdebit"',
            [
                'timeaging' => $timeaging
            ]);
        if ( ! empty($stuckedpayments) )
        {
            mtrace('Start to process stucked otpay-payments.');
            $countprocessedstuckedpayments = 0;
            foreach ( $stuckedpayments as $stuckedpayment )
            {
                if ( $this->complete_payment($stuckedpayment) )
                {
                    $countprocessedstuckedpayments ++;
                }
            }
            mtrace(
                'There are ' . $countprocessedstuckedpayments . ' of ' . count($stuckedpayments) .
                     ' stucked otpay-payments were processed successfully');
        } else
        {
            mtrace('There are no stucked otpay-payments to process.');
        }
    }

    /**
     * Обработка черновиков платежей (платежи созданы, но от банка не получен ответ о завершении оплаты)
     */
    function process_draft_payments()
    {
        global $DB;
        //время устаревания. платежи старше недельной давности не обрабатываем
        $timeaging = time() - 7 * 24 * 60 * 60;
        $draftpayments = $DB->get_records_select('enrol_otpay',
            'timemodified>:timeaging AND status="draft"',
            [
                'timeaging' => $timeaging
            ]);
        if ( ! empty($draftpayments) )
        {
            mtrace('Start to process draft otpay-payments.');
            $countprocesseddraftpayments = 0;
            foreach ( $draftpayments as $draftpayment )
            {
                if ( $this->process_payment_status($draftpayment) )
                {
                    $countprocesseddraftpayments ++;
                }
            }
            mtrace(
                'There are ' . $countprocesseddraftpayments . ' of ' . count($draftpayments) .
                ' draft otpay-payments were processed successfully');
        } else
        {
            mtrace('There are no draft otpay-payments to process.');
        }
    }

    /**
     * Execute synchronisation.
     *
     * @param progress_trace $trace
     * @return int exit code, 0 means ok
     */
    public function sync( progress_trace $trace )
    {
        $this->process_expirations($trace);
        return 0;
    }

    /**
     * Is it possible to delete enrol instance via standard UI?
     *
     * @param stdClass $instance
     * @return bool
     */
    public function can_delete_instance( $instance )
    {
        $context = context_course::instance($instance->courseid);
        return has_capability($this->configcapability, $context);
    }

    /**
     * Is it possible to hide/show enrol instance via standard UI?
     *
     * @param stdClass $instance
     * @return bool
     */
    public function can_hide_show_instance( $instance )
    {
        $context = context_course::instance($instance->courseid);
        return has_capability($this->configcapability, $context);
    }

    /**
     * Returns edit icons for the page with list of instances
     *
     * @param stdClass $instance
     * @return array
     */
    public function get_action_icons( stdClass $instance )
    {
        global $OUTPUT;

        if ( $instance->enrol !== 'otpay' )
        {
            throw new coding_exception('invalid enrol instance!');
        }
        $context = context_course::instance($instance->courseid);

        $icons = array();
        if ( has_capability($this->configcapability, $context) )
        {
            $editlink = new moodle_url($this->editurl,
                [
                    'courseid' => $instance->courseid,
                    'id' => $instance->id
                ]);
            $icons[] = $OUTPUT->action_icon($editlink,
                new pix_icon('t/edit', get_string('edit'), 'core',
                    [
                        'class' => 'iconsmall'
                    ]));
        }

        return $icons;
    }

    /**
     * Returns link to page which may be used to add new instance of enrolment plugin in course.
     *
     * @param int $courseid
     * @return moodle_url page url
     */
    public function get_newinstance_link( $courseid )
    {
        $context = context_course::instance($courseid, MUST_EXIST);

        if ( ! has_capability('moodle/course:enrolconfig', $context) or
             ! has_capability($this->configcapability, $context) )
        {
            return NULL;
        }

        // multiple instances supported - different cost for different roles
        return new moodle_url($this->newinstanceurl,
            array(
                'courseid' => $courseid
            ));
    }

    /**
     * Gets an array of the user enrolment actions
     *
     * @param course_enrolment_manager $manager
     * @param stdClass $ue
     *            A user enrolment object
     * @return array An array of user_enrolment_actions
     */
    public function get_user_enrolment_actions( course_enrolment_manager $manager, $ue )
    {
        $actions = array();
        $context = $manager->get_context();
        $instance = $ue->enrolmentinstance;
        $params = $manager->get_moodlepage()->url->params();
        $params['ue'] = $ue->id;
        if ( $this->allow_unenrol($instance) && has_capability($this->unenrolcapability, $context) )
        {
            $url = new moodle_url('/enrol/unenroluser.php', $params);
            $actions[] = new user_enrolment_action(new pix_icon('t/delete', ''),
                get_string('unenrol', 'enrol'), $url,
                array(
                    'class' => 'unenrollink',
                    'rel' => $ue->id
                ));
        }
        if ( $this->allow_manage($instance) && has_capability($this->managecapability, $context) )
        {
            $url = new moodle_url('/enrol/editenrolment.php', $params);
            $actions[] = new user_enrolment_action(new pix_icon('t/edit', ''), get_string('edit'),
                $url,
                array(
                    'class' => 'editenrollink',
                    'rel' => $ue->id
                ));
        }
        return $actions;
    }

    /**
     * Формирование панели администрирования
     *
     * @param int $instanceid
     * @param int $offset
     * @param int $per_page
     *
     * @return html_table | bool
     */
    public function get_admin_panel( $instanceid = null, $offset = 0, $per_page = 10)
    {
        global $DB;

        // Все сабплагины
        $subplugins = $this->get_providers();

        if ( ! empty($instanceid) )
        {
            $record_enrol = $DB->get_record('enrol', ['id' => $instanceid]);
            if ( empty($record_enrol) )
            {
                return false;
            }
            if ( ! isset($subplugins[$record_enrol->customchar1]) )
            {
                return false;
            }
            $enrolnments = $DB->get_records('enrol_otpay', ['instanceid' => $instanceid], 'id DESC', '*', $offset, $per_page);
        } else
        {
            $enrolnments = $DB->get_records('enrol_otpay', [], 'id DESC', '*', $offset, $per_page);
        }

        // Массив способов записи
        $enrols = [];

        // Формирование таблицы
        $table = new html_table();
        $table->align = ['left', 'left', 'left', 'left', 'left', 'left', 'left', 'center'];

        // Поля таблицы
        $table->head = [];
        $table->head[] = get_string('admin_panel_date', 'enrol_otpay');
        $table->head[] = get_string('admin_panel_fio', 'enrol_otpay');
        $table->head[] = get_string('admin_panel_course', 'enrol_otpay');
        $table->head[] = get_string('admin_panel_enroltype', 'enrol_otpay');
        $table->head[] = get_string('admin_panel_enrolname', 'enrol_otpay');
        $table->head[] = get_string('admin_panel_comment', 'enrol_otpay');
        $table->head[] = get_string('admin_panel_price', 'enrol_otpay');
        $table->head[] = get_string('admin_panel_status', 'enrol_otpay');

        // Добавление полей
        foreach ( $enrolnments as $enrolnment )
        {
            // Получение способа записи
            if ( empty($enrols[$enrolnment->instanceid]) )
            {
                $enrol_record = $DB->get_record('enrol', ['id' => $enrolnment->instanceid]);
                if ( empty($enrol_record) )
                {
                    continue;
                } else
                {
                    $enrols[$enrolnment->instanceid] = $enrol_record;
                }
            }

            // Получение провайдера
            $subplugin = $subplugins[$enrols[$enrolnment->instanceid]->customchar1];

            // Маршрут статусов
            $route = $subplugin->get_statuses_route();

            // Строка данных
            $row = new html_table_row();

            // Дата
            $row->cells[] = date('d-m-Y H:i:s', $enrolnment->createdate);

            // ФИО
            $user = $DB->get_record('user', ['id' => $enrolnment->userid]);
            if ( ! empty($user) )
            {
                $row->cells[] = html_writer::link(new moodle_url('/user/profile.php', ['id' => $user->id]), fullname($user));
            } else
            {
                $row->cells[] = '';
            }

            // Курс
            try
            {
                $coursename = html_writer::link(new moodle_url('/course/view.php', ['id' => $enrolnment->courseid]), get_course($enrolnment->courseid)->fullname);
            } catch( dml_exception $e )
            {
                $coursename = '';
            }
            $row->cells[] = $coursename;

            // Способ записи
            $row->cells[] = get_string('otpay_' . $enrolnment->paymethod, 'enrol_otpay');

            // Название способа записи
            $row->cells[] = $enrols[$enrolnment->instanceid]->name;

            // Комментарий
            $row->cells[] = $subplugin->get_comment($enrolnment);

            // Сумма
            $row->cells[] = get_string('otpay_currency_' . $enrolnment->currency, 'enrol_otpay', $enrolnment->amount);

            // Статус
            if ( ! empty($route[$enrolnment->status]) )
            {// У текущего статуса есть маршрут
                $options = [];
                foreach ( $route[$enrolnment->status] as $st )
                {
                    $options[$st] = get_string($st, 'enrol_otpay');
                }
                $row->cells[] = html_writer::select($options, '', '', [get_string($enrolnment->status, 'enrol_otpay')]);
            } else
            {
                $row->cells[] = get_string($enrolnment->status, 'enrol_otpay');
            }

            // Добавление аттрибутов для AJAX запроса
            $row->attributes = [
                'class' => 'admin_panel_row',
                'data-id' => $enrolnment->id
            ];

            // Добавим строку
            $table->data[] = $row;
        }

        return html_writer::table($table);
    }

    /**
     * Получение записей
     *
     * @param int $instanceid
     *
     * @return int | bool
     */
    public function get_enrolnments_count( $instanceid = null )
    {
        global $DB;
        if ( empty($instanceid) )
        {
            return $DB->count_records('enrol_otpay');
        } else
        {
            $record_enrol = $DB->get_record('enrol', ['id' => $instanceid]);
            if ( empty($record_enrol) )
            {
                return false;
            }
            return $DB->count_records('enrol_otpay', ['instanceid' => $instanceid]);
        }
    }

    /**
     * Restore user enrolment.
     *
     * @param restore_enrolments_structure_step $step
     * @param stdClass $data
     * @param stdClass $instance
     * @param int $userid
     * @param int $oldinstancestatus
     */
    public function restore_user_enrolment( restore_enrolments_structure_step $step, $data,
        $instance, $userid, $oldinstancestatus )
    {
        $this->enrol_user($instance, $userid, null, $data->timestart, $data->timeend, $data->status);
    }

    /**
     * Restore instance and map settings.
     *
     * @param restore_enrolments_structure_step $step
     * @param stdClass $data
     * @param stdClass $course
     * @param int $oldid
     */
    public function restore_instance( restore_enrolments_structure_step $step, stdClass $data,
        $course, $oldid )
    {
        global $DB;
        if ( $step->get_task()->get_target() == backup::TARGET_NEW_COURSE )
        {
            $merge = false;
        } else
        {
            $merge = array(
                'courseid' => $data->courseid,
                'enrol' => $this->get_name(),
                'roleid' => $data->roleid,
                'cost' => $data->cost,
                'currency' => $data->currency
            );
        }
        if ( $merge and $instances = $DB->get_records('enrol', $merge, 'id') )
        {
            $instance = reset($instances);
            $instanceid = $instance->id;
        } else
        {
            $instanceid = $this->add_instance($course, (array) $data);
        }
        $step->set_mapping('enrol', $oldid, $instanceid);
    }

    /**
     * Получить форму добавления купонов
     *
     * @return enrol_otpay_coupons_add_coupon_form
     */
    public function get_coupon_add_form()
    {
        global $CFG;
        require_once ($CFG->dirroot . '/enrol/otpay/form.php');

        $form = new enrol_otpay_coupons_add_coupon_form();

        return $form;
    }

    /**
     * Получить форму удаления купонов
     *
     * @return enrol_otpay_coupons_delete_coupon_form
     */
    public function get_coupon_delete_form( $id )
    {
        global $CFG;
        require_once ($CFG->dirroot . '/enrol/otpay/form.php');

        $form = new enrol_otpay_coupons_delete_coupon_form(
            '/enrol/otpay/coupons.php?layout=coupondelete',
            array(
                'id' => $id
            ));

        return $form;
    }

    /**
     * Получить форму добавления категорий
     *
     * @return enrol_otpay_coupons_add_category_form
     */
    public function get_category_add_form()
    {
        global $CFG;
        require_once ($CFG->dirroot . '/enrol/otpay/form.php');

        $form = new enrol_otpay_coupons_add_category_form(
            '/enrol/otpay/coupons.php?layout=categorylist');

        return $form;
    }

    /**
     * Получить форму удаления категории
     *
     * @return enrol_otpay_coupons_delete_category_form
     */
    public function get_category_delete_form( $id )
    {
        global $CFG;
        require_once ($CFG->dirroot . '/enrol/otpay/form.php');

        $form = new enrol_otpay_coupons_delete_category_form(
            '/enrol/otpay/coupons.php?layout=categorydelete',
            array(
                'id' => $id
            ));

        return $form;
    }

    /**
     * Сформировать и отобразить таблицу купонов
     *
     * @param int $offset
     *            - смещение
     * @param number $limit
     *            - лимит записей
     * @return NULL
     */
    public function display_coupons_list( $offset = 0, $limit = 0 )
    {
        global $DB;

        // Получим купоны
        $coupons = $DB->get_records('enrol_otpay_coupons', array(), 'id DESC', '*', $offset, $limit);
        // Получим категории купонов
        $couponcategories = $DB->get_records('enrol_otpay_coupon_cat');
        // Получим имена всех курсов
        $courses = $DB->get_records('course', array(), '' . 'id, shortname');

        // Формируем таблицу
        $table = new html_table();
        // Поля таблицы
        $table->head = array();
        $table->head[] = get_string('coupon_coupon_list_code', 'enrol_otpay');
        $table->head[] = get_string('coupon_coupon_list_category', 'enrol_otpay');
        $table->head[] = get_string('coupon_coupon_list_courseid', 'enrol_otpay');
        $table->head[] = get_string('coupon_coupon_list_type', 'enrol_otpay');
        $table->head[] = get_string('coupon_coupon_list_discounttype', 'enrol_otpay');
        $table->head[] = get_string('coupon_coupon_list_value', 'enrol_otpay');
        $table->head[] = get_string('coupon_coupon_list_createtime', 'enrol_otpay');
        $table->head[] = get_string('coupon_coupon_list_lifetime', 'enrol_otpay');
        $table->head[] = get_string('coupon_coupon_list_status', 'enrol_otpay');
        $table->head[] = get_string('coupon_system_actions', 'enrol_otpay');
        // Добавляем поля таблицы
        foreach ( $coupons as $coupon )
        {
            $row = array();
            // Код
            $url = new moodle_url('/enrol/otpay/coupons.php',
                array(
                    'id' => $coupon->id,
                    'layout' => 'couponview'
                ));
            $row[] = html_writer::link($url, $coupon->code);
            // Категория
            if ( isset($couponcategories[$coupon->catid]) )
            {
                $row[] = $couponcategories[$coupon->catid]->name;
            } else
            {
                if ( $coupon->catid == 0 )
                {
                    $row[] = get_string('without_category', 'enrol_otpay');
                } else
                {
                    $row[] = get_string('lost_category', 'enrol_otpay');
                }
            }
            // Курс
            if ( isset($courses[$coupon->courseid]) )
            {
                $url = new moodle_url('/course/view.php',
                    array(
                        'id' => $coupon->courseid
                    ));
                $row[] = html_writer::link($url, $courses[$coupon->courseid]->shortname);
            } else
            {
                $row[] = get_string('coupon_for_all_courses', 'enrol_otpay');
            }
            // Тип
            $row[] = get_string('coupon_type_' . $coupon->type, 'enrol_otpay');
            // Тип скидки
            $row[] = get_string('coupon_dtype_' . $coupon->discounttype, 'enrol_otpay');
            // Сумма скидки
            $row[] = $coupon->value;
            // Дата содания купона
            $date = usergetdate($coupon->createtime);
            $row[] = $date['mday'] . '.' . $date['mon'] . '.' . $date['year'] . ' ' . $date['hours'] .
                 ':' . $date['minutes'];
            // Продолжительность действия
            if ( $coupon->lifetime > 0 )
            {
                $date = usergetdate($coupon->createtime + $coupon->lifetime);
                $row[] = $date['mday'] . '.' . $date['mon'] . '.' . $date['year'] . ' ' .
                     $date['hours'] . ':' . $date['minutes'];
            } else
            {
                $row[] = get_string('coupon_coupon_list_lifetime_forever', 'enrol_otpay');
            }
            // Статус
            $row[] = get_string('coupon_coupon_list_status_' . $coupon->status, 'enrol_otpay');
            // Действия
            $url = new moodle_url('/enrol/otpay/coupons.php',
                array(
                    'id' => $coupon->id,
                    'layout' => 'coupondelete'
                ));
            $row[] = html_writer::link($url,
                get_string('coupon_system_actions_delete', 'enrol_otpay'));
            // Добавим строку
            $table->data[] = $row;
        }
        // Напечатаем таблицу
        echo html_writer::table($table);
    }

    /**
     * Сформировати и отобразить таблицу категорий купонов
     *
     * @param int $offset
     *            - смещение
     * @param number $limit
     *            - лимит записей
     * @return NULL
     */
    public function display_coupon_category_list( $offset = 0, $limit = 0 )
    {
        global $DB;

        // Получим категории купонов
        $couponcategories = $DB->get_records('enrol_otpay_coupon_cat');

        // Формируем таблицу
        $table = new html_table();
        // Поля таблицы
        $table->head = array();
        $table->head[] = get_string('coupon_category_list_name', 'enrol_otpay');
        $table->head[] = get_string('coupon_category_list_count', 'enrol_otpay');
        $table->head[] = get_string('coupon_category_list_status', 'enrol_otpay');
        $table->head[] = get_string('coupon_system_actions', 'enrol_otpay');
        // Добавляем поля таблицы
        foreach ( $couponcategories as $cat )
        {
            $row = array();
            // Имя
            $row[] = $cat->name;
            // Число активных купонов в категории
            $coupons = $DB->count_records('enrol_otpay_coupons',
                array(
                    'catid' => $cat->id,
                    'status' => 'active'
                ));
            $row[] = $coupons;
            // Статус
            $row[] = get_string('coupon_coupon_list_status_' . $cat->status, 'enrol_otpay');
            // Действия
            $url = new moodle_url('/enrol/otpay/coupons.php',
                array(
                    'id' => $cat->id,
                    'layout' => 'categorydelete'
                ));
            $row[] = html_writer::link($url,
                get_string('coupon_system_actions_delete', 'enrol_otpay'));
            // Добавим строку
            $table->data[] = $row;
        }
        // Напечатаем таблицу
        echo html_writer::table($table);
    }

    /**
     * Сформировати и отобразить таблицу категорий купонов
     *
     * @param int $offset
     *            - смещение
     * @param number $limit
     *            - лимит записей
     * @return NULL
     */
    public function display_coupon( $id )
    {
        global $DB;

        // Получим купон
        $coupon = $DB->get_records('enrol_otpay_coupons', array(
            'id' => $id
        ));
        // Получим категории купонов
        $couponuses = $DB->get_records('enrol_otpay_coupon_log',
            array(
                'couponid' => $id
            ));

        // Формируем таблицу
        $table = new html_table();
        $table->head = array();
        $table->head[] = get_string('coupon_coupon_view_course', 'enrol_otpay');
        $table->head[] = get_string('coupon_coupon_view_user', 'enrol_otpay');
        $table->head[] = get_string('coupon_coupon_view_time', 'enrol_otpay');
        // Добавляем поля таблицы
        foreach ( $couponuses as $item )
        {
            $user = $DB->get_record('user',
                array(
                    'id' => $item->userid
                ));
            $course = $DB->get_record('course',
                array(
                    'id' => $item->courseid
                ));

            $row = array();

            // Курс
            $url = new moodle_url('/course/view.php',
                array(
                    'id' => $course->id
                ));
            $row[] = html_writer::link($url, $course->shortname);
            // Пользователь
            $url = new moodle_url('/user/profile.php',
                array(
                    'id' => $user->id
                ));
            $row[] = html_writer::link($url, $user->firstname . ' ' . $user->lastname);
            // Время использования
            $date = usergetdate($item->date);
            $row[] = str_pad($date['mday'], 2, '0', STR_PAD_LEFT) . '.' .
                 str_pad($date['mon'], 2, '0', STR_PAD_LEFT) . '.' . $date['year'] . ' ' .
                 $date['hours'] . ':' . $date['minutes'];
            // Добавим строку
            $table->data[] = $row;
        }
        // Напечатаем таблицу
        echo html_writer::table($table);
    }

    /**
     * Напечатать вкладки для панели управления купонами
     *
     * @param $currenttab -
     *            текущая вкладка
     */
    function print_coupon_tab_menu( $currenttab )
    {
        global $OUTPUT;
        // Формируем табы
        $tabs = array();

        // Купоны
        $tabs[] = new tabobject('couponlist', '/enrol/otpay/coupons.php?layout=couponlist',
            get_string('coupon_system_tab_couponlist', 'enrol_otpay'),
            get_string('coupon_system_tab_couponlist', 'enrol_otpay'), false);
        // Категории
        $tabs[] = new tabobject('categorylist', '/enrol/otpay/coupons.php?layout=categorylist',
            get_string('coupon_system_tab_categorylist', 'enrol_otpay'),
            get_string('coupon_system_tab_categorylist', 'enrol_otpay'), false);
        // Распечатать табы
        print_tabs(array(
            $tabs
        ), $currenttab);
    }

    /**
     * Возвращает объект класса enrol_otpay_otserial
     */
    public function get_otpay_request_object( $upgrade = false )
    {
        return new enrol_otpay\otserial($upgrade);
    }

    /**
     * Сохранить запись в лог
     *
     * @param string $text
     * @param object $data
     */
    function otpay_log($text, $data = null )
    {
        global $DB;

        // Собрать лог в объект
        $insertlog = new stdClass();
        $insertlog->timestamp = time();
        $insertlog->textlog = $text;
        if ( ! empty($data) )
        {
            $insertlog->data = json_encode($data);
        } else
        {
            $insertlog->data = null;
        }
        $DB->insert_record('enrol_otpay_log', $insertlog);
    }

    /**
     * Проверка данных, вернувшихся от банка (верны ли данные, подпись банка)
     *
     * @param stdClass $enrolotpay - Экземпляр способа записи на курс
     * @param stdClass $data - Данные, возвращенные банком
     *
     * @return void
     */
    public function check_data($enrolotpay, $data)
    {
        if ( $paymethod = $this->get_paymethod_with_existing_method($enrolotpay->paymethod,
            'check_data') )
        {
            $checkdataresult = $paymethod->check_data($data, $enrolotpay);
            //             Результат должен быть в формате:
            //             [
            //                 'succeed'=>bool общий результат проверки данных и подписи (отсутствие ошибок проверки),
            //                 'errors'=> array массив с ошибками,
            //                 'needdebit'=>bool требуется ли подтверждение платежа (вторая стадия)
            //                 'waitdebit'=>bool true если не известен результат обработки платежа (бывает у яндекса, по крайней мере на старом api)
            //                 'paymentdata' => доп.сведения о платеже
            //             ];


            if ( $checkdataresult['succeed'] )
            {
                $context = context_course::instance($enrolotpay->courseid);
                $params = [
                    'courseid' => $enrolotpay->courseid,
                    'relateduserid' => $enrolotpay->userid,
                    'objectid' => $enrolotpay->id,
                    'contextid' => $context->id,
                    'other' => [
                        'waitdebit' => !empty($checkdataresult['waitdebit']),
                        'needdebit' => !empty($checkdataresult['needdebit']),
                        'additional_data' => $checkdataresult['paymentdata'] ?? []
                    ]
                ];
                $event = \enrol_otpay\event\response_obtained::create($params);
                $event->add_record_snapshot('enrol_otpay', $enrolotpay);
                $event->trigger();
                return $event->is_dispatched();
            } else
            {
                $checkdataresult['enrolotpay'] = $enrolotpay;
                $this->otpay_log("Check data failed", $checkdataresult);
                die();
            }
        }
    }

    /**
     * Отмена платежа
     *
     * @param stdClass $enrolotpay - Экземпляр способа записи на курс
     * @param boolean $needmerchantreject - требуется ли отменять платеж через мерчанта или просто сменить статус в базе
     *
     * @return void
     */
    public function reject_payment($enrolotpay, $needmerchantreject=false)
    {
        global $DB;
        if ( $needmerchantreject &&
            $paymethod = $this->get_paymethod_with_existing_method($enrolotpay->paymethod,
            'reject_payment') )
        {// требуется отмена платежа через мерчанта
            // обращаемся к сабплагину, который знает как отменять платеж через мерчанта
            $rejectresult = $paymethod->reject_payment($enrolotpay);
            $this->otpay_log('rejecting with merchant', [
                'enrolotpay' => $enrolotpay,
                'rejectresult' => $rejectresult
            ]);
        } else
        {//у плагина не реализован метод отмены платежа, считаем платеж отмененным
            $this->otpay_log('rejecting', [
                'enrolotpay' => $enrolotpay
            ]);
            $rejectresult = true;
        }




        if( !$needmerchantreject ||
            ($needmerchantreject && !empty($rejectresult)) )
        {
            $enrolotpay->timemodified = time();
            $enrolotpay->status = 'rejected';
            return $DB->update_record('enrol_otpay', $enrolotpay);
        } else
        {
            return false;
        }
    }

    /**
     * Обработка использованных купонов
     *
     * @param stdClass $enrolotpay
     *
     * @return void
     */
    function process_coupons( $enrolotpay )
    {
        global $DB;
        $enrolotpayoptions = unserialize($enrolotpay->options);
        if ( ! empty($enrolotpayoptions['couponcodes']) )
        { //использовались купоны - отметим их использование
            if(is_array($enrolotpayoptions['couponcodes']))
            {//обычные купоны
                $couponcodes = $enrolotpayoptions['couponcodes'];
            } else
            {//купон на вход
                $couponcodes = [$enrolotpayoptions['couponcodes']];
            }
            foreach ( $couponcodes as $code )
            {
                $coupon = $DB->get_record('enrol_otpay_coupons',
                    array(
                        'code' => $code
                    ));
                if ( ! empty($coupon) )
                {
                    if ( $coupon->type == 'single' )
                    { //купон - одноразовый, сменим статус
                        $coupon->status = 'used';
                        $DB->update_record('enrol_otpay_coupons', $coupon);
                    }

                    //  Запишем в базу лог использования купонов
                    $uses = new stdClass();
                    $uses->couponid = $coupon->id;
                    $uses->userid = $enrolotpay->userid;
                    $uses->courseid = $enrolotpay->courseid;
                    $uses->date = time();
                    $DB->insert_record('enrol_otpay_coupon_log', $uses);
                }
            }
        }
    }

    /**
     * Формирование даты начала и окончания подписки в зависимости от выбранных настроек способа подписки
     *
     * @param object $instance
     * @return stdClass с startdate и enddate
     */
    public function get_enrollment_period($instance)
    {
        //enrolstartdate может позволять выполнять подписку на курс до наступления этой даты (подписка будет неактивна - курс будет недоступен)
        //enrolenddate не позволяет выполнять подписку на курс после этой даты, в формировании периода подписки не принимает участие
        $curtime = time();
        $enrollmentperiod = new stdClass();

        if ( (int)$instance->enrolstartdate && (int)$instance->enrolstartdate > $curtime )
        {// Имеется дата начала подписки и она еще не наступила - подписываем с этой даты
            $enrollmentperiod->timestart = $instance->enrolstartdate;
        } else
        { //дата начала не установлена или она уже наступила - подписываем сразу
            $enrollmentperiod->timestart = $curtime;
        }

        if ( (int)$instance->enrolperiod )
        { //указан период подписки - указываем срок окончания
            $enrollmentperiod->timeend = $enrollmentperiod->timestart + $instance->enrolperiod;
        } else
        { //период подписки не указан - учись сколько хочешь
            $enrollmentperiod->timeend = 0;
        }

        return $enrollmentperiod;
    }

    /**
     * Первая стадия обработки платежа, выполняется подписка после проверки данных о платеже,
     * полученных от банка (может потребоваться вторая стадия для подтверждения списания заблокированной суммы)
     *
     * @param object $enrolotpay
     * @param object $event
     * @return boolean|object enrolotpay с обновленными данными после подписки
     */
    function process_payment($enrolotpay, $status='confirmed', $paymentdata=null)
    {
        global $DB, $CFG;

        //получение используемого способа записи
        $enrolparams = [
            'courseid' => $enrolotpay->courseid,
            'enrol' => 'otpay',
            'id' => $enrolotpay->instanceid
        ];
        $instance = $DB->get_record('enrol', $enrolparams, '*', MUST_EXIST);

        $enrollmentperiod = $this->get_enrollment_period($instance);

        // Подписание пользователя только после полного проведения плат
        $userenrolmentstatus = ($status == 'confirmed' ? null : 1);

        //@TODO: учитывать настройки (первичная подписка, продление, возобновление) для решения о восстановлении оценок?
        $recovergrades = true;
        if ( $CFG->disablegradehistory )
        {
            // если выключена история оценок, то нельзя подписывать с восстановлением курса
            // иначе приводит к варнингам
            $recovergrades = false;
        }

        //получение плагина способа записи на курс
        $plugin = enrol_get_plugin('otpay');
        //создание подписки пользвоателя
        $plugin->enrol_user($instance, $enrolotpay->userid, $instance->roleid, $enrollmentperiod->timestart,
                            $enrollmentperiod->timeend, $userenrolmentstatus, $recovergrades);
        //получение контекста курса
        $context = context_course::instance($instance->courseid);
        // Send notifications - уведомления о подписке контактам курса
        if (!is_enrolled($context, $enrolotpay->userid, '', false))
        {
            return false;
        }

        if ($instance->customint1 && is_null($userenrolmentstatus))
        { //уведомления о начале подписки требуются (настроено в инстансе)
            $this->send_managers_notifications($instance, $enrolotpay->userid);
        }

        $ueparams = [
            'enrolid' => $instance->id,
            'userid' => $enrolotpay->userid
        ];
        $ue = $DB->get_record('user_enrolments', $ueparams);
        if (empty($ue))
        {
            return false;
        }

        $enrolotpay->enrolmentid = $ue->id;
        $enrolotpay->enrolmentstartdate = $enrollmentperiod->timestart;
        $enrolotpay->enrolmentenddate = $enrollmentperiod->timeend;
        $enrolotpay->timemodified = time();
        $enrolotpay->status = $status;

        if (!empty($paymentdata['paymentid']))
        {// прилетел paymentid от банка, сохраним его
            $enrolotpay->externalpaymentid = $paymentdata['paymentid'];
        }

        try
        {
            $DB->update_record('enrol_otpay', $enrolotpay);
            if ($enrolotpay->status == 'confirmed')
            {
                $eventdata = [
                    'courseid' => $enrolotpay->courseid,
                    'relateduserid' => $enrolotpay->userid,
                    'objectid' => $enrolotpay->id,
                    'contextid' => $context->id,
                    'other' => [
                        'paymentid' => $enrolotpay->paymentid,
                        'enrolmentid' => $enrolotpay->enrolmentid,
                        'amount' => $enrolotpay->amount,
                        'currency' => $enrolotpay->currency,
                    ]
                ];
                $event = \enrol_otpay\event\payment_confirmed::create($eventdata);
                $event->trigger();
            }
        } catch ( ddl_exception $ex )
        {
            return false;
        }

        return $enrolotpay;
    }

    public function process_redirect($instance)
    {
        redirect($this->get_enrol_destination_url($instance));
    }

    /**
     * Добавление платежа в статусе черновик
     *
     * @param string $paymethod
     *            - способ оплаты (псевдосабплагин)
     * @param object $enrolotpay
     *            - сформированные данные или null для создания совсем дефолтной записи
     * @return int - идентификатор созданной записи
     */
    function add_draft_enrol_otpay( $paymethod, $enrolotpay = null )
    {
        global $DB;

        if ( empty($enrolotpay) or ! is_object($enrolotpay) )
        { //объект не был передан, создадим
            $enrolotpay = new stdClass();
        }
        //даже если следующие данные пришли с объектом, мы все равно установим насильно то, что должно быть по умолчанию при создании
        $enrolotpay->paymethod = $paymethod;
        $enrolotpay->status = 'draft';
        $enrolotpay->createdate = $enrolotpay->timemodified = time();

        $countmethodpayments = $DB->count_records_select('enrol_otpay',
            "paymethod=? AND createdate>?",
            [
                $paymethod,
                strtotime(date("Y-m-d 00:00:00"))
            ]);
        if ( empty($countmethodpayments) )
        {
            $countmethodpayments = 0;
        }

        $nextpaymentid = (int) $countmethodpayments + 1;

        try
        {
            $randompart = "";
            //доступно 16 символов
            //6 зарезервировано под дату слева
            //strlen((string)$nextpaymentid) зарезервировано под порядковый номер справа
            //1 зарезервирован под 0 перед порядковым номером для разделения
            //остальное надо заполнить рандомными числами от 1 до 9
            for ($i = 0; $i < (16 - 6 - 1 - strlen((string) $nextpaymentid)); $i ++)
            {
                $randompart .= (string) rand(1, 9);
            }
            $enrolotpay->paymentid = date("ymd") . $randompart . "0" . $nextpaymentid;

            //создание записи
            $enrolotpayid = $DB->insert_record('enrol_otpay', $enrolotpay);
            return $enrolotpayid;
        } catch ( dml_write_exception $e )
        { //если возникла ошибка при резервировании номера - попробуем номер на 1 больше
            return $this->add_draft_enrol_otpay($paymethod, $enrolotpay);
        }
    }

    /**
     * Вторая стадия обработки платежа.
     * Используется, если требуется подтверждение списания заблокированных у слушателя средств
     *
     * @param object $enrolotpay
     * @param object $event
     *
     * @return void
     */
    function complete_payment( $enrolotpay, $paymentdata = null )
    {
        global $DB;

        if ( $paymethod = $this->get_paymethod_with_existing_method($enrolotpay->paymethod,
            'complete_payment') )
        {
            $completepaymentresult = $paymethod->complete_payment($enrolotpay, $paymentdata);

            if ( $completepaymentresult['result'] )
            {// Получено подтверждение завершения платежа
                $this->otpay_log("Payment completed", [$enrolotpay, $completepaymentresult]);
                if( $enrolotpay->status !== "confirmed" )
                {// Платеж еще не подтвержден в СДО
                    //получение используемого способа записи
                    $instance = $DB->get_record('enrol', [
                        'courseid' => $enrolotpay->courseid,
                        'enrol' => 'otpay',
                        'id' => $enrolotpay->instanceid
                    ], '*', MUST_EXIST);
                    if (!empty($instance))
                    {
                        // Активация подписки
                        $this->update_user_enrol($instance, $enrolotpay->userid, ENROL_USER_ACTIVE);
                    }

                    // Обновление статуса платежа
                    $enrolotpay->status = 'confirmed';
                    $DB->update_record('enrol_otpay', $enrolotpay);

                    $context = context_course::instance($enrolotpay->courseid);
                    \enrol_otpay\event\payment_confirmed::create([
                        'courseid' => $enrolotpay->courseid,
                        'relateduserid' => $enrolotpay->userid,
                        'objectid' => $enrolotpay->id,
                        'contextid' => $context->id,
                        'other' => [
                            'paymentid' => $enrolotpay->paymentid,
                            'enrolmentid' => $enrolotpay->enrolmentid,
                            'amount' => $enrolotpay->amount,
                            'currency' => $enrolotpay->currency,
                        ]
                    ])->trigger();
                }
                return true;
            } else
            {
                $this->otpay_log("Payment not completed", $completepaymentresult);
                return false;
            }
        }
    }

    function process_payment_status( $enrolotpay )
    {
        if ( $paymethod = $this->get_paymethod_with_existing_method($enrolotpay->paymethod,
            'process_payment_status', true) )
        {
            return $paymethod->process_payment_status($enrolotpay);
        } else
        {
            // срок ожидания авторизации платежа
            $awperiod = get_config('enrol_otpay', 'sberbank_payment_authorization_waiting_period');
            if( time() > $enrolotpay->createdate + $awperiod )
            {// срок ожидания авторизации платежа превышен
                //необходимо отменить платеж в банке, затем сменить статус в базе
                return $this->reject_payment($enrolotpay, true);
            }
        }
    }

    /**
     * Проверка существования сабплагина и метода в нем
     *
     * @param string $paymethod
     * @param string $method
     * @param boolean $quiet
     * @return класс сабплагина или false, если нет файла, класса или метода в сабплагине
     */
    function get_paymethod_with_existing_method( $paymethod, $method, $quiet=false )
    {
        global $CFG;
        //подключим метод обработки входных данных
        $subpluginlib = $CFG->dirroot . "/enrol/otpay/plugins/" . $paymethod . "/lib.php";
        if ( file_exists($subpluginlib) )
        {
            require_once ($subpluginlib);
            $subpluginclass = 'otpay_' . $paymethod;
            if ( class_exists($subpluginclass) )
            {
                $subplugin = new $subpluginclass($this);
                if ( method_exists($subplugin, $method) )
                {
                    return $subplugin;
                } else
                {
                    if(!$quiet)
                    {
                        $this->otpay_log("Method not exist",
                            [
                                'subpluginmethod' => $method,
                                'subpluginclass' => $subpluginclass,
                                'subpluginlib' => $subpluginlib
                            ]);
                    }
                    return false;
                }
            } else
            {
                if(!$quiet)
                {
                    $this->otpay_log("Class not exist",
                        [
                            'subpluginclass' => $subpluginclass,
                            'subpluginlib' => $subpluginlib
                        ]);
                }
                return false;
            }
        } else
        {
            if(!$quiet)
            {
                $this->otpay_log("File not exist",
                    [
                        'subpluginlib' => $subpluginlib
                    ]);
            }
            return false;
        }
    }

    /**
     * Выполнение редиректа с отправкой данных методом post с помощью отправки формы javascript'ом
     *
     * @param string $url
     * @param array $data
     */
    function redirect_post( $url, array $data )
    {
        echo '<html xmlns="http://www.w3.org/1999/xhtml">
        <head>
            <script type="text/javascript">
                function closethisasap() {
                    document.forms["redirectpost"].submit();
                }
            </script>
        </head>
        <body onload="closethisasap();">
        <form name="redirectpost" method="post" action="' . $url . '">
            ';
        if ( ! is_null($data) )
        {
            foreach ( $data as $k => $v )
            {
                echo '<input type="hidden" name="' . $k . '" value="' . $v . '"> ';
            }
        }
        echo '</form>
        </body>
        </html>';
    }

    /**
     * Отправляет уведомления менеджерам курса о подписке нового студента на курс
     *
     * @param stdClass $instance
     *            - экземпляр плагина в курсе
     * @param int $userid
     *            - студент, записанный на курс
     */
    public function send_managers_notifications( stdClass $instance, $userid )
    {
        global $CFG, $DB;
        if ( ! $course = $DB->get_record('course',
            array(
                'id' => $instance->courseid
            )) )
        {
            debugging('course with id="' . $instance->courseid . '" not found', DEBUG_DEVELOPER);
            return false;
        }
        $coursecontext = context_course::instance($course->id);
        if ( ! $user = $DB->get_record("user", array(
            "id" => $userid
        )) )
        {
            debugging('user with id="' . $userid . '" not found', DEBUG_DEVELOPER);
            return false;
        }
        $managerroles = explode(',', $CFG->coursecontact);
        list ( $sort, $sortparams ) = users_order_by_sql('u');
        $managers = get_role_users($managerroles, $coursecontext, true,
            'ra.id AS raid, u.id, u.username, u.firstname, u.lastname, rn.name AS rolecoursealias,
             r.name AS rolename, r.sortorder, r.id AS roleid, r.shortname AS roleshortname',
            'r.sortorder ASC, ' . $sort, null, '', '', '', '', $sortparams);

        // PLAIN-версия письма
        $a = new stdClass();
        $a->course = format_string($course->fullname, true,
            array(
                'context' => $coursecontext
            ));
        $a->user = fullname($user);
        // HTML-версия письма
        $ahtml = new stdClass();
        $courseurl = new moodle_url("/course/view.php",
            array(
                'id' => $course->id
            ));
        $ahtml->course = html_writer::link($courseurl, $course->fullname);
        $userurl = new moodle_url('/user/view.php',
            array(
                'id' => $userid,
                'course' => $course->id
            ));
        $ahtml->user = html_writer::link($userurl, fullname($user));

        $eventdata = new \core\message\message();
        $eventdata->component = 'enrol_otpay';
        $eventdata->name = 'otpay_enrolment';
        $eventdata->userfrom = $user;
        $eventdata->subject = get_string("enrolmentnew", 'enrol_otpay');
        $eventdata->fullmessage = get_string('enrolmentnewuser', 'enrol_otpay', $a);
        $eventdata->fullmessageformat = FORMAT_HTML;
        $eventdata->fullmessagehtml = get_string('enrolmentnewuser', 'enrol_otpay', $ahtml);
        $eventdata->smallmessage = '';
        $eventdata->notification = 1;
        // Состояние отправки уведомлений
        $ok = true;
        foreach ( $managers as $teacher )
        {
            if ( $userto = $DB->get_record("user", array(
                "id" => $teacher->id
            )) )
            {
                $eventdata->userto = $userto;
                if ( message_send($eventdata) === false )
                {
                    debugging('message_send failed', DEBUG_DEVELOPER);
                    $ok = false;
                }
            } else
            {
                debugging('user with id="' . $teacher->id . '" not found', DEBUG_DEVELOPER);
                $ok = false;
            }
        }
        return $ok;
    }

    public function get_enrol_destination_url($enrolinstance)
    {
        // место настроенное для перенаправления (по умолчанию - витрина)
        $destination = $enrolinstance->customchar3 ?? 'localcrw';

        // перенаправление в витрину
        if ($destination == 'localcrw')
        {
            // убедимся, что плагин существует и вернем урл страницы описания курса
            $installlist = core_plugin_manager::instance()->get_installed_plugins('local');
            if (array_key_exists('crw', $installlist))
            {
                return new moodle_url('/local/crw/course.php', ['id' => $enrolinstance->courseid]);
            }
        }

        // перенаправление на wantsurl, если он есть
        if ($destination == 'wantsurl' && !empty($SESSION->wantsurl))
        {
            $url = $SESSION->wantsurl;
            unset($SESSION->wantsurl);
            return $url;
        }

        // если настроено перенаправление на стандартную страницу курса
        // или не сработали доп.условия для других способов, вернем урл стандартной страницы курса
        return new moodle_url('/course/view.php', ['id' => $enrolinstance->courseid]);
    }
}