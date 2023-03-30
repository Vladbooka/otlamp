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
 * Плагин записи на курс OTPAY. Классы форм.
 *
 * @package    enrol
 * @subpackage otpay
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once ($CFG->libdir . '/formslib.php');
require_once ($CFG->dirroot . "/enrol/otpay/lib.php");

/**
 * Форма добавления/редактирования способа записи на курс
 *
 * @package enrol
 * @subpackage otpay
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class enrol_otpay_edit_enrol_form extends moodleform
{
    /**
     * Плагин подписки на курс
     *
     * @var null|enrol_otpay
     */
    protected $plugin = null;

    /**
     * Список инициализированных провайдеров оплаты
     *
     * @var array
     */
    protected $providers = [];

    /**
     * Текущий курс
     *
     * @var stdClass|null
     */
    protected $course = null;

    /**
     * Текущий контекст
     *
     * @var null|context_course
     */
    protected $context = null;

    /**
     * Экземпляр текущей подписки на курс
     *
     * @var stdClass|null
     */
    protected $instance = null;

    /**
     * Получение текущей записи
     *
     * @return stdClass
     */
    public function get_instance()
    {
        return $this->instance;
    }

    /**
     * Получить контроллер формы
     *
     * @return MoodleQuickForm
     */
    public function get_mform()
    {
        return $this->_form;
    }

    /**
     * Получить контроллер формы
     *
     * @return MoodleQuickForm
     */
    public function get_plugin()
    {
        return $this->plugin;
    }


    /**
     * Структура конфига, хранимого в customtext3 с дефолтными значениями
     *
     * @return array
     */
    private function get_default_config() {
        return [
            'display_unauthorized' => true,
            'availability' => [
                'conditions' => [],
                'hide_unavailable' => false
            ]
        ];
    }

    /**
     * Объявление формы сохранения экземпляра подписки на курс на основе провайдера
     *
     * Описание дополнительных полей:
     * customchar1 - paymethod, provider, providername, способ оплаты, код псевдосабплагина (acquiropay, kazkom и т.д.)
     * customchar2 - в accountgenerate код сценария, в acquiropay, sberbank, yandex - информация об НДС
     * customchar3 - На какую страницу перенаправлять пользователя после оплаты
     * customchar6 - в accountgenerate, похоже, ошибочно, используется в качестве проверки, имеется ли поддержка купонов
     * customint1 - посылать ли уведомление о новой подписке
     * customint2 - время, прошедшее после последней подписки, определяющее начальную дату для отображения способа записи
     * customint3 - время, прошедшее после последней подписки, определяющее конечную дату для отображения способа записи
     * customint4 - учитывать ли для ограничения отображения подписки только otpay-подписки
     * customint5 - разрешать ли пользователю подписываться раньше даты начала подписки (курс все равно будет не доступен)
     * customint6 - применять ли скидочные купоны для данного инстанса
     * customtext1 - по коду использования не нашёл, но вижу в базе значения, описывающие поддерживаемые карты оплаты
     * customtext2 - Краткое описание экземпляра способа записи для пользователя
     * customtext3 - json-encoded конфиг под ограничения доступа и прочее (свободные поля стремительно кончаются - экономим)
     *
     * @return void
     */
    public function definition()
    {
        global $PAGE;

        $mform = $this->_form;

        // Базовая инициализация
        $this->plugin = enrol_get_plugin('otpay');
        $this->providers = $this->plugin->get_providers();
        $this->course = $this->_customdata->course;
        $this->context = $this->_customdata->context;
        $this->instance = $this->_customdata->instance;

        if ( ! empty($this->instance->id) )
        {// Экземпляр определен

            $providername = $this->instance->customchar1;
            if ( isset($this->providers[$providername]) )
            {// Провайдер указан
                $provider = $this->providers[$providername];
                // Добавление индивидуальных настроек провайдера
                $provider->form_edit_enrol_definition($this, $this->_customdata);
            }
        }

        // Заголовок - Базовая конфигурация экземпляра
        $mform->addElement('header', 'header', get_string('pluginname', 'enrol_otpay'));

        // Скрытые поля
        $mform->addElement('hidden', 'id');
        $mform->setType('id', PARAM_INT);
        $mform->addElement('hidden', 'courseid');
        $mform->setType('courseid', PARAM_INT);

        // Уведомление об изменении используемой подписки
        if ( enrol_accessing_via_instance($this->instance) )
        {
            $mform->addElement('static', 'selfwarn',
                get_string('instanceeditselfwarning', 'core_enrol'),
                get_string('instanceeditselfwarningtext', 'core_enrol'));
        }

        if ( ! $this->instance->id )
        {// Экземпляр еще не создан

            // Выбор провайдера
            $options = [];
            foreach ( $this->providers as $provider )
            {
                $options[$provider->get_name()] = $provider->get_localized_name();
            }
            $mform->addElement('select', 'customchar1', get_string('otpay_method', 'enrol_otpay'),
                $options);
        }

        // Название экземпляра
        $mform->addElement('text', 'name', get_string('custominstancename', 'enrol'));
        $mform->setType('name', PARAM_TEXT);

        // Краткое описание для пользователя
        $descriptionlabel = get_string('description_for_user', 'enrol_otpay');
        $descriptionattributes = ['rows'=>10, 'cols'=>45, 'width'=>0,'height'=>0];
        $mform->addElement('editor', 'description_for_user', $descriptionlabel, $descriptionattributes);
        $mform->setType('description_for_user', PARAM_RAW);

        // Отображать неавторизованным пользователям
        $elementname = 'display_unauthorized';
        $elementdisplayname = get_string($elementname, 'enrol_otpay');
        $mform->addElement('advcheckbox', $elementname, $elementdisplayname);
        $mform->addHelpButton($elementname, $elementname, 'enrol_otpay');
//         $mform->setDefault($elementname, 1);

        // Ограничения доступа
        $mform->addElement('textarea', 'availability_conditions', get_string('availability_conditions', 'enrol_otpay'));
        $mform->setDefault('availability_conditions', '[]');
        $fullmodule = 'local_opentechnology/availability_condition';
        $PAGE->requires->js_call_amd($fullmodule, 'init', ['id_availability_conditions', $this->context->id]);
        $PAGE->requires->css(new moodle_url('/local/opentechnology/availability_condition.css'));

        // Скрывать недоступный способ записи? иначе будут отображаться объяснения почему не доступен
        $elementname = 'availability_hide_unavailable';
        $elementdisplayname = get_string($elementname, 'enrol_otpay');
        $mform->addElement('advcheckbox', $elementname, $elementdisplayname);
        $mform->addHelpButton($elementname, $elementname, 'enrol_otpay');

        // Статус
        $options = [
            ENROL_INSTANCE_ENABLED => get_string('yes'),
            ENROL_INSTANCE_DISABLED => get_string('no')
        ];
        $mform->addElement('select', 'status', get_string('form_field_status', 'enrol_otpay'), $options);
        $mform->setDefault('status', $this->plugin->get_config('status'));

        // Роль, назначаемая при подписке
        if ($this->instance->id) {
            $roles = get_default_enrol_roles($this->context, $this->instance->roleid);
        } else {
            $roles = get_default_enrol_roles($this->context, $this->plugin->get_config('roleid'));
        }
        $mform->addElement('select', 'roleid', get_string('form_field_roleid', 'enrol_otpay'), $roles);
        $mform->setDefault('roleid', $this->plugin->get_config('roleid'));

        // На какую страницу перенаправлять пользователя после оплаты
        $returnurls = [
            'course' => get_string('return_url_course', 'enrol_otpay'),
        ];
        $installlist = core_plugin_manager::instance()->get_installed_plugins('local');
        if ( array_key_exists('crw', $installlist) )
        {
            $returnurls['localcrw'] = get_string('return_url_localcrw', 'enrol_otpay');
        }
        $returnurls['wantsurl'] = get_string('return_url_wantsurl', 'enrol_otpay');
        $mform->addElement('select', 'customchar3', get_string('form_return_url', 'enrol_otpay'), $returnurls);
        $mform->setDefault('customchar3', 'course');

        // Дата начала подписки
        $mform->addElement('date_time_selector', 'enrolstartdate',
            get_string('form_field_enrolstartdate', 'enrol_otpay'),
            [
                'optional' => true
            ]);
        $mform->setDefault('enrolstartdate', 0);
        $mform->addHelpButton('enrolstartdate', 'form_field_enrolstartdate', 'enrol_otpay');

        //разрешать пользователю подписываться раньше даты начала подписки (курс все равно будет не доступен до этой даты)
        $mform->addElement('advcheckbox', 'customint5',
            get_string('form_field_allowearlyenrol', 'enrol_otpay'));
        $mform->addHelpButton('customint5', 'form_field_allowearlyenrol', 'enrol_otpay');

        //продолжительность зачисления
        $mform->addElement('duration', 'enrolperiod',
            get_string('form_field_enrolperiod', 'enrol_otpay'),
            array(
                'optional' => true,
                'defaultunit' => 86400
            ));
        $mform->setDefault('enrolperiod', $this->plugin->get_config('enrolperiod'));
        $mform->addHelpButton('enrolperiod', 'form_field_enrolperiod', 'enrol_otpay');

        //дата окончания подписки
        $mform->addElement('date_time_selector', 'enrolenddate',
            get_string('form_field_enrolenddate', 'enrol_otpay'),
            array(
                'optional' => true
            ));
        $mform->setDefault('enrolenddate', 0);
        $mform->addHelpButton('enrolenddate', 'form_field_enrolenddate', 'enrol_otpay');

        //рассылать ли уведомления учителям о подписке
        $mform->addElement('advcheckbox', 'customint1',
            get_string('form_field_enrolmentnotify', 'enrol_otpay'));
        $mform->addHelpButton('customint1', 'form_field_enrolmentnotify', 'enrol_otpay');

        //рассылать уведомления учителям
        $mform->addElement('advcheckbox', 'expirynotify',
            get_string('form_field_expirynotify', 'enrol_otpay'));
        $mform->addHelpButton('expirynotify', 'form_field_expirynotify', 'enrol_otpay');

        //рассылать уведомления всем (учителям и студентам)
        $mform->addElement('advcheckbox', 'notifyall',
            get_string('form_field_notifyall', 'enrol_otpay'));
        $mform->addHelpButton('notifyall', 'form_field_notifyall', 'enrol_otpay');
        $mform->disabledIf('notifyall', 'expirynotify', 'eq', 0);

        $mform->addElement('duration', 'expirythreshold',
            get_string('expirythreshold', 'core_enrol'),
            array(
                'optional' => false,
                'defaultunit' => 86400
            ));
        $mform->addHelpButton('expirythreshold', 'expirythreshold', 'core_enrol');
        $mform->disabledIf('expirythreshold', 'expirynotify', 'eq', 0);

        //отображать способ записи только если с момента последней подписки прошло
        $mform->addElement('advcheckbox', 'customint2_en',
            get_string('form_field_use_displayenrol_startperiod', 'enrol_otpay'));
        $mform->addHelpButton('customint2_en', 'form_field_use_displayenrol_startperiod',
            'enrol_otpay');
        $mform->setAdvanced('customint2_en');

        $mform->addElement('duration', 'customint2',
            get_string('form_field_displayenrol_startperiod', 'enrol_otpay'),
            array(
                'optional' => false,
                'defaultunit' => 86400
            ));
        $mform->addHelpButton('customint2', 'form_field_displayenrol_startperiod', 'enrol_otpay');
        $mform->setAdvanced('customint2');

        $mform->disabledIf('customint2', 'customint2_en', 'eq', 0);

        //скрывать способ записи если с момента последней подписки прошло
        $mform->addElement('advcheckbox', 'customint3_en',
            get_string('form_field_use_displayenrol_endperiod', 'enrol_otpay'));
        $mform->addHelpButton('customint3_en', 'form_field_use_displayenrol_endperiod',
            'enrol_otpay');
        $mform->setAdvanced('customint3_en');

        $mform->addElement('duration', 'customint3',
            get_string('form_field_displayenrol_endperiod', 'enrol_otpay'),
            array(
                'optional' => false,
                'defaultunit' => 86400
            ));
        $mform->addHelpButton('customint3', 'form_field_displayenrol_endperiod', 'enrol_otpay');
        $mform->setAdvanced('customint3');

        $mform->disabledIf('customint3', 'customint3_en', 'eq', 0);

        //для ограничения отображения использовать только плагины OTPay
        $mform->addElement('advcheckbox', 'customint4',
            get_string('form_field_displayenrol_otpayonly', 'enrol_otpay'));
        $mform->addHelpButton('customint4', 'form_field_displayenrol_otpayonly', 'enrol_otpay');
        $mform->setAdvanced('customint4');

        $this->add_action_buttons(true, null);

        // Инициализация флагов блокировки полей
        $this->instance->customint2_en = 0;
        $this->instance->customint3_en = 0;
        $this->instance->couponsupports = 0;
        if ( isset($this->instance->customint2) and ! is_null($this->instance->customint2) )
        {
            $this->instance->customint2_en = 1;
        }
        if ( isset($this->instance->customint3) and ! is_null($this->instance->customint3) )
        {
            $this->instance->customint3_en = 1;
        }
        if ( !empty($this->instance->customint6) )
        {
            $this->instance->couponsupports = 1;
        }

        // Заполнение формы данными
        $this->set_data($this->instance);
    }

    /**
     * Load in existing data as form defaults. Usually new entry defaults are stored directly in
     * form definition (new entry form); this function is used to load in data where values
     * already exist and data is being edited (edit entry form).
     *
     * @param mixed $defaultvalues object or array of default values
     */
    function set_data($defaultvalues) {
        if (is_object($defaultvalues)) {
            $defaultvalues = (array)$defaultvalues;
        }

        $defaultvalues['description_for_user']['format'] = FORMAT_HTML;
        $defaultvalues['description_for_user']['text'] = $defaultvalues['customtext2'] ?? '';

        // формирование объекта конфига. По умолчанию - с дефолтными значениями
        $config = $this->get_default_config();
        // если есть сохраненные данные - пробуем получить из них конфиг
        if (array_key_exists('customtext3', $defaultvalues)) {
            $decodedcustomtext3 = json_decode($defaultvalues['customtext3'], true);
            if (!is_null($decodedcustomtext3)) {
                $config = $decodedcustomtext3;
            }
        }
        // Условия доступа
        $defaultvalues['availability_conditions'] = json_encode($config['availability']['conditions'] ?? []);
        $defaultvalues['availability_hide_unavailable'] = $config['availability']['hide_unavailable'] ?? false;
        $defaultvalues['display_unauthorized'] = $config['display_unauthorized'] ?? true;

        parent::set_data($defaultvalues);
    }

    /**
     * Валидация формы
     *
     * @param array $data - Данные формы
     * @param stdClass $files - Файлы формы
     *
     * @return $error - Массив с ошибками валидации
     */
    public function validation($data, $files)
    {
        global $DB;

        $errors = parent::validation($data, $files);

        // Валидация даты
        if ( ! empty($data['enrolenddate']) and $data['enrolenddate'] < $data['enrolstartdate'] )
        {
            $errors['enrolenddate'] = get_string('error_form_validation_enrolenddate',
                'enrol_otpay');
        }

        if ( $data['expirynotify'] > 0 and $data['expirythreshold'] < 86400 )
        {
            $errors['expirythreshold'] = get_string('errorthresholdlow', 'core_enrol');
        }

        if ( ! empty($this->instance->id) )
        {// Экземпляр определен

            $providername = $this->instance->customchar1;
            if ( isset($this->providers[$providername]) )
            {// Провайдер указан

                // Дополнительная валидация провайдера
                $this->providers[$providername]->form_edit_enrol_validation($this, $errors, $data, $files);
            }
        }

        return $errors;
    }

    /**
     * Обработка формы
     *
     * @return void
     */
    function process()
    {
        global $DB;

        // URL возврата
        $backurl = new moodle_url('/enrol/instances.php', ['id' => $this->course->id]);

        if ( $this->is_submitted() && confirm_sesskey() &&
             $this->is_validated() && $formdata = $this->get_data() )
        {// Данные получены


            // конфиг под условия доступа и прочее. свободные поля стремительно кончаются - экономим,
            // начинаем собирать все конфиги в одно поле и храним в json
            $config = $this->get_default_config();
            // Условия доступа
            if (property_exists($formdata, 'availability_conditions')) {
                $config['availability']['conditions'] = json_decode($formdata->availability_conditions);
            }
            // Прятать недоступный способ записи? (иначе будет отображаться объяснение почему не доступен)
            $config['availability']['hide_unavailable'] = !empty($formdata->availability_hide_unavailable);
            // Отображать неавторизованным
            $config['display_unauthorized'] = !empty($formdata->display_unauthorized);


            if ( ! empty($this->instance->id) )
            {// Процесс обновления экземпляра

                // Предварительная обработка провайдером
                $providername = $this->instance->customchar1;
                if ( isset($this->providers[$providername]) )
                {// Провайдер указан
                    $this->providers[$providername]->form_edit_enrol_preprocess($this, $formdata);
                }

                $this->instance->customchar3 = $formdata->customchar3;

                //@TODO вынести часть обработки формы в сабплагины
                $reset = ($this->instance->status != $formdata->status);

                //уведомления о новых подписках
                $this->instance->customint1 = $formdata->customint1;

                if ( $formdata->customint2_en )
                {//отображать способ записи только если с момента последней подписки прошло
                    $this->instance->customint2 = (int) $formdata->customint2;
                } else
                {//такое ограничение не настроено
                    $this->instance->customint2 = null;
                }

                if ( $formdata->customint3_en )
                { //скрывать способ записи если с момента последней подписки прошло
                    $this->instance->customint3 = (int) $formdata->customint3;
                } else
                { //такое ограничение не настроено
                    $this->instance->customint3 = null;
                }
                //для ограничения отображения ориентироваться только на otpay-подписки
                $this->instance->customint4 = $formdata->customint4;
                //разрешить подписываться ранее даты начала подписки (курс все равно будет не доступен)
                $this->instance->customint5 = $formdata->customint5;

                // Краткое описания экземпляра способа записи для пользователя
                if (property_exists($formdata, 'description_for_user'))
                {
                    // количество полей в enrol ограничено и занимать поле под хранение формата может
                    // оказаться роковой ошибкой, пробуем обойтись без этого
                    $text = $formdata->description_for_user['text'] ?? '';
                    $this->instance->customtext2 = format_text($text, FORMAT_HTML);
                }

                // конфиг под условия доступа и прочее
                $this->instance->customtext3 = json_encode($config);

                $this->instance->status = $formdata->status;
                $this->instance->name = $formdata->name;
                $this->instance->roleid = $formdata->roleid;
                $this->instance->enrolstartdate = $formdata->enrolstartdate;
                $this->instance->enrolperiod = $formdata->enrolperiod;
                $this->instance->enrolenddate = $formdata->enrolenddate;
                $this->instance->expirynotify = $formdata->expirynotify;
                $this->instance->notifyall = $formdata->expirynotify == 0 ? 0 : $formdata->notifyall;
                $this->instance->expirythreshold = $formdata->expirynotify == 0 ? 0 : $formdata->expirythreshold;
                $this->instance->timemodified = time();

                // Постобработка провайдером
                $providername = $this->instance->customchar1;
                if ( isset($this->providers[$providername]) )
                {// Провайдер указан
                    $this->providers[$providername]->form_edit_enrol_postprocess($this, $this->instance, $formdata);
                }

                $DB->update_record('enrol', $this->instance);

                if ( $reset )
                {
                    $this->context->mark_dirty();
                }
                redirect($backurl);

            } else
            {// Процесс создания экземпляра
                $instance = new stdClass();
                $instance->customint1 = $formdata->customint1;
                $instance->customint4 = $formdata->customint4;
                $instance->customint5 = $formdata->customint5;
                $instance->customchar1 = $formdata->customchar1;
                $instance->customchar3 = $formdata->customchar3;
                $instance->status = $formdata->status;
                $instance->name = $formdata->name;
                $instance->roleid = $formdata->roleid;
                $instance->enrolstartdate = $formdata->enrolstartdate;
                $instance->enrolperiod = $formdata->enrolperiod;
                $instance->enrolenddate = $formdata->enrolenddate;
                $instance->expirynotify = $formdata->expirynotify;
                $instance->notifyall = $formdata->expirynotify == 0 ? 0 : $formdata->notifyall;
                $instance->expirythreshold = $formdata->expirynotify == 0 ? 86400 : $formdata->expirythreshold;
                $instance->customint2 = null;
                $instance->customint3 = null;
                if ( $formdata->customint2_en )
                {//отображать способ записи только если с момента последней подписки прошло
                    $instance->customint2 = (int)$formdata->customint2;
                }
                if ( $formdata->customint3_en )
                {//скрывать способ записи если с момента последней подписки прошло
                    $instance->customint3 = (int)$formdata->customint3;
                }

                // Краткое описания экземпляра способа записи для пользователя
                if (property_exists($formdata, 'description_for_user'))
                {
                    // количество полей в enrol ограничено и занимать поле под хранение формата может
                    // оказаться роковой ошибкой, пробуем обойтись без этого
                    $text = $formdata->description_for_user['text'] ?? '';
                    $instance->customtext2 = format_text($text, FORMAT_HTML);
                }

                // конфиг под условия доступа и прочее
                $instance->customtext3 = json_encode($config);

                // Постобработка провайдером
                $providername = $formdata->customchar1;
                if ( isset($this->providers[$providername]) )
                {// Провайдер указан
                    $this->providers[$providername]->form_edit_enrol_postprocess($this, $instance, $formdata);
                }

                // Добавление экземпляра
                if ( $instanceid = $this->plugin->add_instance($this->course, (array)$instance) )
                {
                    $editurl = new moodle_url('/enrol/otpay/edit.php',
                        [
                            'courseid' => $this->course->id,
                            'id' => $instanceid
                        ]);
                    redirect($editurl);
                } else
                {
                    redirect($backurl);
                }
            }
        } else
        {
            if ( $this->is_cancelled() )
            {
                redirect($backurl);
            }
        }
    }
}

/**
 * Форма подписки пользователя на курс
 *
 * @package enrol
 * @subpackage otpay
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class enrol_otpay_add_user_enrolment_form extends moodleform
{
    /**
     * Экземпляр подписки
     *
     * @var stdClass
     */
    protected $instance = null;

    /**
     * Плагин подписки на курс
     *
     * @var null|otpay
     */
    protected $plugin = null;

    /**
     * Счетчик
     *
     * @var int
     */
    protected static $counter = 0;

    /**
     * Получить контроллер формы
     *
     * @return MoodleQuickForm
     */
    public function get_mform()
    {
        return $this->_form;
    }

    /**
     * Получить контроллер формы
     *
     * @return MoodleQuickForm
     */
    public function get_plugin()
    {
        return $this->plugin;
    }

    /**
     * Получение текущей записи
     *
     * @return stdClass
     */
    public function get_instance()
    {
        return $this->instance;
    }

    /**
     * Объявление формы
     *
     * @return void
     */
    public function definition()
    {
        global $CFG;

        // Базовая инициализация
        $mform = $this->_form;
        $this->instance = $this->_customdata[0];
        $this->plugin = enrol_get_plugin('otpay');
        $providers = $this->plugin->get_providers();

        // Установка параметров формы
        $mformattrs = $mform->_attributes;
        $mformattrs['class'] = $mformattrs['class'] . ' enrol_otpay_payment_form';
        $mform->setAttributes($mformattrs);

        if ( $this->instance->id )
        {// Экземпляр указан
            $mform->addElement('hidden', 'instanceid', $this->instance->id);
            $mform->setType('instanceid', PARAM_INT);

            if ( ! empty($this->instance->customchar1) && isset($providers[$this->instance->customchar1]) )
            {// Провайдер указан
                // Передача управления провайдеру
                $providers[$this->instance->customchar1]->form_add_user_enrolment_definition($this, $this->_customdata);
            }
        }
    }

    /**
     * Валидация формы
     *
     * @param array $data - Массив с переданными данными формы
     * @param unknown $files - Массив с переданными файлами формы
     *
     * @return $error error list
     */
    public function validation($data, $files)
    {
        global $DB, $CFG;

        $errors = parent::validation($data, $files);

        if ( $this->instance->id == $data['instanceid'] )
        {
            $providers = $this->plugin->get_providers();

            if ( ! empty($this->instance->customchar1) && isset($providers[$this->instance->customchar1]) )
            {// Провайдер указан
                // Передача управления провайдеру
                $providers[$this->instance->customchar1]->
                    form_add_user_enrolment_validation($this, $errors, $data, $files);
            }
        }

        return $errors;
    }

    /**
     * Обработка формы
     *
     * @return void
     */
    public function process()
    {
        global $CFG;

        if ( $this->is_submitted() && confirm_sesskey() &&
             $this->is_validated() && $formdata = $this->get_data() )
        {// Данные получены
            $providers = $this->plugin->get_providers();
            if ( ! empty($this->instance->customchar1) && isset($providers[$this->instance->customchar1]) )
            {// Провайдер указан
                // Передача управления провайдеру
                $providers[$this->instance->customchar1]->
                     form_add_user_enrolment_process($this, $this->instance, $formdata);
            }
        }
    }

    /**
     * Добавление кнопки модального окна со вкладками
     *
     * @param string $text
     * @param array $tabs
     *
     * @return void
     */
    public function add_modal_button($text = 'empty', $tabs = [])
    {
        // Валидация
        if ( empty($text) || ! is_array($tabs) ||empty($tabs) )
        {
            return false;
        }

        // Формирование кнопки с модальным окном
        $mform = $this->_form;

        $formattrs = $mform->getAttributes();
        if( empty($formattrs['class']))
        {
            $formattrs['class'] = "unresponsive";
        } else
        {
            $formattrs['class'] .= " unresponsive";
        }
        $mform->setAttributes($formattrs);

        $mform->addElement('html', html_writer::start_div('otpay_modal'));
        $mform->addElement('html', html_writer::div($text, 'felement otpay_modal button btn btn-primary'));
        $mform->addElement('html', html_writer::start_div('otpay_modal_content user-loginas-panel otpay_modal_hide', [
            'data-tabs-count' => count($tabs)
        ]));

        // Хидер модального окна
        $modal_header = '';
        $modal_header .=  html_writer::tag('h2', get_string('accountgenerate_form', 'enrol_otpay'), ['class' => 'header_info']);
        $modal_header .= html_writer::div('', 'close');

        $mform->addElement('html', html_writer::div($modal_header, 'ulp-header ulp-content header'));

        // Тело модального окна
        $counter = self::$counter;
        $formscount = count($tabs);
        foreach ( $tabs as $tab )
        {
            if ( array_key_exists('header', $tab) )
            {
                // заголовок отображается только если вкладок 2 и более
                if ( $formscount < 2 )
                {
                    $mform->addElement('html', html_writer::label($tab['header'], 'otpay_tab_id_' . ++self::$counter, true, ['class' => 'otpay_tab', 'style' => 'display: none;']));
                } else
                {
                    $mform->addElement('html', html_writer::label($tab['header'], 'otpay_tab_id_' . ++self::$counter, true, ['class' => 'otpay_tab']));
                }
            }
        }
        self::$counter = $counter;

        $first = false;
        foreach ( $tabs as $tab )
        {
            if ( ! empty($tab['elements']) )
            {
                $mform->addElement('html', html_writer::tag('input', '', [
                    'type' => 'radio',
                    'name' => 'tabs',
                    'id' => 'otpay_tab_id_' . ++self::$counter
                ]));

                $mform->addElement('html', html_writer::start_div('otpay_modal_tab_content'));
                foreach ( $tab['elements'] as $elem )
                {
                    $mform->addElement($elem);
                }
                if ( ! $first )
                {
                    $mform->addElement('html', html_writer::end_div());
                    $mform->addElement('html', html_writer::end_div());
                    $first = true;
                } else
                {
                    $mform->addElement('html', html_writer::end_div());
                }
            }
        }

        $mform->addElement('html', html_writer::end_div());
    }
}

/**
 * Форма использования купонов
 *
 * @package enrol
 * @subpackage otpay
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class enrol_otpay_coupon_form extends moodleform
{
    // Сумма к оплате
    private $amount;
    // ID курса
    private $courseid;
    // Массив купонов
    private $coupons;
    //минимальная сумма оплаты
    private $minamount;

    /**
     * Объявление формы
     */
    function definition()
    {
        global $CFG, $DB, $COURSE;

        $mform = $this->_form;
        $this->amount = $this->_customdata['amount'];
        $this->courseid = $this->_customdata['courseid'];
        $this->minamount = $this->_customdata['minamount'];
        $this->coupons = array();

        $mformattrs = $mform->_attributes;
        $mformattrs['class'] = $mformattrs['class'] . ' coupon_system';
        $mform->setAttributes($mformattrs);

        // Шапка
        $mform->addElement('static', 'desciption', "", get_string('coupon_codes', 'enrol_otpay'));
        // Код1
        $mform->addElement('text', 'coupon_code1', get_string('coupon_code', 'enrol_otpay'));
        $mform->setType('coupon_code1', PARAM_TEXT);
        $mform->setDefault('coupon_code1', '');

        //скрыть/показать остальные купоны
        $mform->addElement('static', 'more_codes', "",
            html_writer::div(
                get_string('coupon_more_codes', 'enrol_otpay'),
                'showhidecodes',
                [
                    'id' => 'showhidecodes'
                ]
            )
        );

        // Код2
        $mform->addElement('text', 'coupon_code2', get_string('coupon_code', 'enrol_otpay'));
        $mform->setType('coupon_code2', PARAM_TEXT);
        $mform->setDefault('coupon_code2', '');
        // Код3
        $mform->addElement('text', 'coupon_code3', get_string('coupon_code', 'enrol_otpay'));
        $mform->setType('coupon_code3', PARAM_TEXT);
        $mform->setDefault('coupon_code3', '');
        // Код4
        $mform->addElement('text', 'coupon_code4', get_string('coupon_code', 'enrol_otpay'));
        $mform->setType('coupon_code4', PARAM_TEXT);
        $mform->setDefault('coupon_code4', '');
        // Код5
        $mform->addElement('text', 'coupon_code5', get_string('coupon_code', 'enrol_otpay'));
        $mform->setType('coupon_code5', PARAM_TEXT);
        $mform->setDefault('coupon_code5', '');

        $mform->updateElementAttr([
            'coupon_code1',
            'coupon_code2',
            'coupon_code3',
            'coupon_code4',
            'coupon_code5'
        ], [
            'placeholder' => get_string('coupon_payform_field_placeholder_coupon_code','enrol_otpay')
        ]);

        $this->add_action_buttons(false, get_string('coupon_codes_insert', 'enrol_otpay'));
        // Применение проверки ко всем элементам
    }

    /**
     * Проверка данных формы
     *
     * @param array $data
     *            - данные, пришедшие из формы
     *
     * @return array - массив ошибок, или пустой массив, если ошибок нет
     */
    public function validation( $data, $files )
    {
        $mform = & $this->_form;
        $errors = array();
        // убираем лишние пробелы со всех полей формы
        $mform->applyFilter('__ALL__', 'trim');
        // Возвращаем ошибки, если они есть
        return $errors;
    }

    /**
     * Обработчик формы
     */
    function process()
    {
        if ( $this->is_submitted() && $this->is_validated() && $data = $this->get_data() )
        { // Получили данные формы
            // Установим значения для формы
            $this->set_data($data);

            // Сформируем массив кодов
            $codes = array();
            $codes[] = $data->coupon_code1;
            $codes[] = $data->coupon_code2;
            $codes[] = $data->coupon_code3;
            $codes[] = $data->coupon_code4;
            $codes[] = $data->coupon_code5;

            $this->amount = $this->get_amount($codes);
        }

        return $this->amount;
    }

    function get_amount( $codes )
    {
        global $DB;

        $cost = $this->amount;
        $amount = $this->amount;

        // Массив отпечатков купонов (тип+курс+категория)
        $couponimprints = array();
        // Скидка
        $discount = 0;
        foreach ( $codes as $code )
        {
            // Получим купон
            $coupon = $DB->get_record('enrol_otpay_coupons',
                array(
                    'code' => $code
                ));
            if ( ! empty($coupon) )
            { // Купон найден
                if ( $coupon->status != 'active' )
                { // Статус не активен, пропускаем
                    continue;
                }
                if ( $coupon->lifetime > 0 )
                { // У купона установлено время действия
                    // Текущее время
                    $time = time();
                    // Время истечения действия купона
                    $limittime = $coupon->createtime + $coupon->lifetime;
                    if ( $time > $limittime )
                    { // Срок действия купона истек
                        continue;
                    }
                }
                if ( $coupon->courseid > 0 )
                { // У купона установлена принадлежность к курсу
                    if ( $this->courseid != $coupon->courseid )
                    { // Купон не для этого курса
                        continue;
                    }
                }

                // Получаем отпечаток купона
                $imprint = $coupon->type . $coupon->courseid . $coupon->catid;
                if ( in_array($imprint, $couponimprints) )
                { // Такой отпечаток купона уже есть в списке
                    continue;
                } else
                { // Учитываем купон
                    // Добавляем к списку
                    $couponimprints[] = $imprint;
                    // Просуммируем скидку
                    switch ( $coupon->discounttype )
                    {
                        // Процентная скидка
                        case 'percentage':
                            $discount += ($cost / 100) * $coupon->value;
                            break;
                        // Фактическая скидка
                        case 'amount':
                            $discount += $coupon->value;
                            break;
                    }
                }
                // Добавим к массиву для формирования хэша
                $this->coupons[] = $code;
            }
        }
        // Вычтем скидку
        $amount -= $discount;

        return $amount;
    }

    /**
     * Получить строку из кодов для отправки
     */
    function get_coupons()
    {
        return $this->coupons;
    }
}

/**
 * Форма добавления купонов
 *
 * @package enrol
 * @subpackage otpay
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class enrol_otpay_coupons_add_coupon_form extends moodleform
{

    /**
     * Объявление формы
     */
    function definition()
    {
        global $CFG, $DB, $COURSE;

        $mform = $this->_form;
        $customdata = $this->_customdata;

        // Шапка
        $mform->addElement('header', 'header', get_string('coupon_add_coupon', 'enrol_otpay'));

        // Категория
        $select = $this->get_coupon_categories_select();
        $mform->addElement('select', 'coupon_category',
            get_string('coupon_category', 'enrol_otpay'), $select);
        $mform->setDefault('coupon_category', 0);

        // Курс
        $select = array();
        $mycourses = $this->available_courses();
        $select[''] = array(
            0 => get_string('coupon_for_all_courses', 'enrol_otpay')
        );
        if ( ! empty($mycourses) )
        {
            $catlist = core_course_category::make_categories_list('', 0, ' / ');
            foreach ( $mycourses as $mycourse )
            {
                if ( empty($select[$catlist[$mycourse->category]]) )
                {
                    $select[$catlist[$mycourse->category]] = array();
                }
                $courselabel = $mycourse->fullname . ' (' . $mycourse->shortname . ')';
                $select[$catlist[$mycourse->category]][$mycourse->id] = $courselabel;
                if ( empty($mycourse->visible) )
                {
                    $hiddenlabel = ' ' . get_string('coupon_hidden_course', 'enrol_otpay');
                    $select[$catlist[$mycourse->category]][$mycourse->id] .= $hiddenlabel;
                }
            }
        }
        $mform->addElement('selectgroups', 'coupon_course',
            get_string('coupon_course', 'enrol_otpay'), $select);

        // Тип купона
        $select = array(
            'single' => get_string('coupon_type_single', 'enrol_otpay'),
            'multiple' => get_string('coupon_type_multiple', 'enrol_otpay')
        );
        $mform->addElement('select', 'coupon_type', get_string('coupon_type', 'enrol_otpay'),
            $select);
        $mform->setDefault('coupon_type', 'single');

        // Тип скидки купона
        $select = array(
            'percentage' => get_string('coupon_dtype_percentage', 'enrol_otpay'),
            'amount' => get_string('coupon_dtype_amount', 'enrol_otpay'),
            'freeaccess' => get_string('coupon_dtype_freeaccess', 'enrol_otpay')
        );
        $mform->addElement('select', 'coupon_dtype', get_string('coupon_dtype', 'enrol_otpay'),
            $select);
        $mform->addHelpButton('coupon_dtype', 'coupon_dtype', 'enrol_otpay');
        $mform->setDefault('coupon_dtype', 'percentage');

        // Сумма скидки
        $mform->addElement('text', 'coupon_value', get_string('coupon_value', 'enrol_otpay'));
        $mform->setDefault('coupon_value', 0);
        $mform->setType('coupon_value', PARAM_FLOAT);
        $mform->disabledIf('coupon_value', 'coupon_dtype', 'eq', 'freeaccess');

        // Время жизни
        $mform->addElement('duration', 'coupon_lifetime',
            get_string('coupon_lifetime', 'enrol_otpay'),
            [
                'optional' => false,
                'defaultunit' => 86400
            ]);
        $mform->setDefault('coupon_lifetime', 0);
        $mform->addHelpButton('coupon_lifetime', 'coupon_lifetime', 'enrol_otpay');
        $mform->setType('coupon_lifetime', PARAM_INT);
        // Количество купонов
        $mform->addElement('text', 'coupon_count', get_string('coupon_count', 'enrol_otpay'));
        $mform->setDefault('coupon_count', 1);
        $mform->setType('coupon_count', PARAM_INT);

        // Именование купона
        $mform->addElement('advcheckbox', 'addcouponname', get_string('addcouponname', 'enrol_otpay'));
        $mform->setDefault('addcouponname', 0);
        $mform->setType('addcouponname', PARAM_INT);
        $mform->addHelpButton('addcouponname', 'addcouponname', 'enrol_otpay');
        $mform->addElement('text', 'couponname', get_string('couponname', 'enrol_otpay'));
        $mform->setType('couponname', PARAM_TEXT);
        // Блокируем имя купона, если не отмечено создание именного купона
        $mform->disabledIf('couponname', 'addcouponname', 'eq', 0);
        // Блокируем количество купонов, если выбрано создание именного купона
        $mform->disabledIf('coupon_count', 'addcouponname', 'eq', 1);


        //описание условий применения нескольких купонов
        $mform->addElement('static', 'coupon_condition', '', get_string('coupon_condition', 'enrol_otpay'));

        $this->add_action_buttons(false, get_string('coupon_add_coupon', 'enrol_otpay'));
    }

    /**
     * Проверка данных формы
     *
     * @param array $data
     *            - данные, пришедшие из формы
     * @return array - массив ошибок, или пустой массив, если ошибок нет
     */
    function validation( $data, $files )
    {
        global $DB;
        $mform = &$this->_form;
        $errors = array();

        // Категория купона
        if ( ! empty($data['coupon_category']) )
        {
            $exists = $DB->record_exists('enrol_otpay_coupon_cat',
                array(
                    'id' => $data['coupon_category']
                ));
            if ( ! $exists )
            {
                $errors['coupon_category'] = get_string('coupon_error_invalid_category',
                    'enrol_otpay');
            }
        }
        // Курс
        if ( ! empty($data['coupon_course']) )
        {
            $exists = $DB->record_exists('course',
                array(
                    'id' => $data['coupon_course']
                ));
            if ( ! $exists )
            {
                $errors['coupon_course'] = get_string('coupon_error_invalid_course', 'enrol_otpay');
            }
        }
        // Тип купона
        if ( $data['coupon_type'] != 'single' && $data['coupon_type'] != 'multiple' )
        {
            $errors['coupon_type'] = get_string('coupon_error_invalid_type', 'enrol_otpay');
        }
        // Тип скидки
        if ( $data['coupon_dtype'] != 'percentage' && $data['coupon_dtype'] != 'amount' &&
             $data['coupon_dtype'] != 'freeaccess' )
        {
            $errors['coupon_dtype'] = get_string('coupon_error_invalid_dtype', 'enrol_otpay');
        }
        // Сумма скидки
        if ( ! empty($data['coupon_value']) )
        {
            if ( ($data['coupon_dtype'] == 'percentage' && $data['coupon_value'] > 100) ||
                 ($data['coupon_value'] < 0) )
            {
                $errors['coupon_value'] = get_string('coupon_error_invalid_value', 'enrol_otpay');
            }
        }
        // Время жизни купона
        if ( $data['coupon_lifetime'] < 0 )
        {
            $errors['coupon_lifetime'] = get_string('coupon_error_invalid_lifetime', 'enrol_otpay');
        }
        // Число купонов
        if ( $data['coupon_count'] < 1 )
        {
            $errors['coupon_count'] = get_string('coupon_error_invalid_count', 'enrol_otpay');
        }

        // Имя купонов
        if( ! empty($data['addcouponname']) )
        {// Если создаем именной купон
            if( empty($data['couponname']) && $data['couponname'] != '0' )
            {// и не указали имя купона
                $errors['couponname'] = get_string('coupon_error_invalid_couponname', 'enrol_otpay');
            } else
            {// или купон с указанным именем уже существует
                if( ! $this->validate_coupon_name($data['couponname']) )
                {
                    $errors['couponname'] = get_string('coupon_error_couponname_exists', 'enrol_otpay');
                }
            }
        }

        // Возвращаем ошибки, если они есть
        return $errors;
    }

    /**
     * Обработчик формы
     */
    function process()
    {
        global $DB;

        if ( $data = $this->get_data() )
        { // Получили данные формы
            // Массив купонов для добавления в БД
            $insert = array();

            // Параметры URL при редиректе
            $redirectarray = array();
            // Флаг ошибок
            $error = false;

            // Общие данные для всех купонов
            $coupon = new stdClass();
            $coupon->catid = $data->coupon_category;
            $coupon->courseid = $data->coupon_course;
            $coupon->type = $data->coupon_type;
            $coupon->discounttype = $data->coupon_dtype;
            $coupon->value = $data->coupon_value;
            $coupon->lifetime = $data->coupon_lifetime;
            $coupon->status = 'active';
            $couponname = isset($data->couponname) ? $data->couponname : '';
            // Создаем купоны
            for ($i = 0; $i < $data->coupon_count; $i ++)
            {
                try
                {// Пробуем получить имя купона
                    $coupon->code = $this->generate_coupon_code($couponname);
                } catch(moodle_exception $e)
                {// Если не получилось, сообщим об ошибке и продолжим
                    $error = true;
                    continue;
                }
                $coupon->createtime = time();
                // Добавим купон в очередь для сохранения в БД
                $result = $DB->insert_record('enrol_otpay_coupons', $coupon);
                if ( empty($result) )
                { // Ошибка при сохранении
                    $error = true;
                }
            }

            if ( $error )
            { // Передаем код ошибки - ошибка при добавлении в БД
                $redirectarray['error'] = 1;
            } else
            { // Добавление прошло успешно
                $redirectarray['success'] = 1;
            }
            // Формируем URL для редиректа
            $url = new moodle_url('/enrol/otpay/coupons.php', $redirectarray);
            // Редирект
            redirect($url);
        }
    }

    /**
     * Сформировать массив категорий купонов для отображения в виде выпадающего списка
     *
     * @return array - массив категорий в виде id => name
     */
    private function get_coupon_categories_select()
    {
        global $DB;
        // Получим все активные категории
        $records = $DB->get_records('enrol_otpay_coupon_cat',
            array(
                'status' => 'active'
            ));
        // Формируем массив для выпадающего списка
        $return = array(
            0 => get_string('without_category', 'enrol_otpay')
        );
        foreach ( $records as $item )
        {
            // Конвертируем в число ID категории
            $id = intval($item->id);
            $return[$id] = $item->name;
        }
        // Вернем массив
        return $return;
    }

    /**
     * Получить массив доступных пользователю курсов
     *
     * @return array - массив курсов
     */
    private function available_courses()
    {
        global $COURSE, $USER, $DB;

        $courses = array();
        $fields = 'fullname, shortname, idnumber, category, visible, sortorder';
        $mycourses = get_user_capability_course('moodle/grade:viewall', $USER->id, true, $fields,
            'sortorder');
        if ( $mycourses )
        {
            $ignorecourses = array(
                SITEID
            );
            foreach ( $mycourses as $mycourse )
            {
                if ( in_array($mycourse->id, $ignorecourses) )
                {
                    continue;
                }
                $courses[] = $mycourse;
            }
        }

        return $courses;
    }

    /**
     * Сгенерировать уникальный код купона
     *
     * @return string
     */
    private function generate_coupon_code($code = '')
    {
        global $DB;
        if( ! empty($code) || $code === '0' )
        {// Если передали конкретное имя купона
            $exist = $DB->record_exists('enrol_otpay_coupons',
                [
                    'code' => $code
                ]
            );
            if( $exist )
            {// Если такой купон уже существует, бросаем исключение
                $a = new stdClass();
                $a->code = $code;
                throw new moodle_exception('coupon_with_this_name_already_exists', 'enrol_otpay', '', $a);
            }
            // Если все хорошо, просто вернем имя купона
            return $code;
        }

        // Если имя не передали - сгенерируем его
        // Защита от бесконечного цикла
        $counter = 0;
        do
        {
            // Увеличим счетчик
            $counter ++;
            // Готовим код купона
            $code = '';

            // Доступные для генерации символы
            $characters = '0123456789abcdefghijklmnopqrstuvwxyz';

            for ($i = 0; $i < 8; $i ++)
            {
                $code .= $characters[mt_rand(0, strlen($characters) - 1)];
            }
            $exist = $DB->record_exists('enrol_otpay_coupons',
                array(
                    'code' => $code
                ));

            if ( $counter > 50 )
            { // Скрипт попытался найти свободный код и не смог...
                error(get_string('coupon_code_generate_error', 'enrol_otpay'));
            }
        } while ( $exist );
        // Вернем сгенерированный код
        return $code;
    }

    /**
     * Валидация имени купона
     * @param string $code код купона
     * @return boolean
     */
    private function validate_coupon_name($code)
    {
        if( empty($code) )
        {
            return false;
        }
        global $DB;
        $params = ['code' => $code, 'status' => 'active'];
        $coupons = $DB->get_records('enrol_otpay_coupons', $params);
        return ! empty($coupons) ? false : true;
    }
}

/**
 * Форма удаления купона
 *
 * @package enrol
 * @subpackage otpay
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class enrol_otpay_coupons_delete_coupon_form extends moodleform
{

    /**
     * Объявление формы
     */
    function definition()
    {
        global $CFG, $DB;

        $mform = $this->_form;
        $customdata = $this->_customdata;
        $id = $customdata['id'];
        // Шапка
        $mform->addElement('header', 'header', get_string('coupon_delete_coupon', 'enrol_otpay'));

        $mform->addElement('html', get_string('coupon_delete_coupon_message', 'enrol_otpay'));
        // ID удаляемого купона
        $mform->addElement('hidden', 'coupon_id');
        $mform->setDefault('coupon_id', $id);
        $mform->setType('coupon_id', PARAM_INT);

        $this->add_action_buttons(true, get_string('coupon_delete_coupon', 'enrol_otpay'));
    }

    /**
     * Обработчик формы
     */
    function process()
    {
        global $DB;

        if ( $this->is_cancelled() )
        {
            // Формируем URL для редиректа
            $url = new moodle_url('/enrol/otpay/coupons.php');
            // Редирект
            redirect($url);
        }
        if ( $this->is_submitted() && $this->is_validated() && $data = $this->get_data() )
        { // Получили данные формы


            $redirectarray = array();

            // Получим купон
            $result = $DB->get_record('enrol_otpay_coupons',
                array(
                    'id' => $data->coupon_id
                ));
            if ( empty($result) )
            { // Купон не найден
                $redirectarray['error'] = 2;
            } else
            {
                // Удалим купон
                $result = $DB->delete_records('enrol_otpay_coupons',
                    array(
                        'id' => $data->coupon_id
                    ));
                if ( $result )
                { // Добавление прошло успешно
                    $redirectarray['success'] = 2;
                } else
                { // Передаем код ошибки - ошибка при eудалении купона
                    $redirectarray['error'] = 2;
                }
            }

            // Формируем URL для редиректа
            $url = new moodle_url('/enrol/otpay/coupons.php', $redirectarray);
            // Редирект
            redirect($url);
        }
    }
}

/**
 * Форма добавления категорий для купонов
 *
 * @package enrol
 * @subpackage otpay
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class enrol_otpay_coupons_add_category_form extends moodleform
{

    /**
     * Объявление формы
     */
    function definition()
    {
        global $CFG, $DB;

        $mform = $this->_form;
        $customdata = $this->_customdata;

        // Шапка
        $mform->addElement('header', 'header', get_string('coupon_add_category', 'enrol_otpay'));

        // Имя категории
        $mform->addElement('text', 'coupon_category_name',
            get_string('coupon_category_name', 'enrol_otpay'));
        $mform->setDefault('coupon_category_name', '');
        $mform->setType('coupon_category_name', PARAM_TEXT);

        $this->add_action_buttons(false, get_string('coupon_add_category', 'enrol_otpay'));
    }

    /**
     * Проверка данных формы
     *
     * @param array $data
     *            - данные, пришедшие из формы
     * @return array - массив ошибок, или пустой массив, если ошибок нет
     */
    function validation( $data, $files )
    {
        global $DB;
        $mform = &$this->_form;
        $errors = array();

        // Имя категория купона
        if ( empty($data['coupon_category_name']) )
        {
            $errors['coupon_category_name'] = get_string('coupon_category_error_invalid_name',
                'enrol_otpay');
        }

        // Возвращаем ошибки, если они есть
        return $errors;
    }

    /**
     * Обработчик формы
     */
    function process()
    {
        global $DB;

        if ( $this->is_submitted() && $this->is_validated() && $data = $this->get_data() )
        { // Получили данные формы


            // Параметры URL при редиректе
            $redirectarray = array(
                'layout' => 'categorylist'
            );
            // Флаг ошибок
            $error = false;

            // Общие данные для всех купонов
            $category = new stdClass();
            $category->name = $data->coupon_category_name;
            $category->status = 'active';

            // Создаем категорию
            $result = $DB->insert_record('enrol_otpay_coupon_cat', $category);

            if ( $result )
            { // Добавление прошло успешно
                $redirectarray['success'] = 1;
            } else
            { // Передаем код ошибки - ошибка при добавлении в БД
                $redirectarray['error'] = 1;
            }

            // Формируем URL для редиректа
            $url = new moodle_url('/enrol/otpay/coupons.php', $redirectarray);
            // Редирект
            redirect($url);
        }
    }
}

/**
 * Форма удаления категорий для купонов
 *
 * @package enrol
 * @subpackage otpay
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class enrol_otpay_coupons_delete_category_form extends moodleform
{

    /**
     * Объявление формы
     */
    function definition()
    {
        global $CFG, $DB;

        $mform = $this->_form;
        $customdata = $this->_customdata;
        $id = $customdata['id'];
        // Шапка
        $mform->addElement('header', 'header', get_string('coupon_delete_category', 'enrol_otpay'));

        $mform->addElement('html', get_string('coupon_delete_category_message', 'enrol_otpay'));
        // ID удаляемого купона
        $mform->addElement('hidden', 'category_id');
        $mform->setDefault('category_id', $id);
        $mform->setType('category_id', PARAM_INT);

        $this->add_action_buttons(true, get_string('coupon_delete_category', 'enrol_otpay'));
    }

    /**
     * Обработчик формы
     */
    function process()
    {
        global $DB;

        if ( $this->is_cancelled() )
        {
            // Формируем URL для редиректа
            $url = new moodle_url('/enrol/otpay/coupons.php',
                array(
                    'layout' => 'categorylist'
                ));
            // Редирект
            redirect($url);
        }
        if ( $this->is_submitted() && $this->is_validated() && $data = $this->get_data() )
        { // Получили данные формы


            $redirectarray = array(
                'layout' => 'categorylist'
            );

            // Получим категорию
            $result = $DB->get_record('enrol_otpay_coupon_cat',
                array(
                    'id' => $data->category_id
                ));
            if ( empty($result) )
            { // Категория не найдена
                $redirectarray['error'] = 2;
            } else
            {
                // Удалим категорию
                $result = $DB->delete_records('enrol_otpay_coupon_cat',
                    array(
                        'id' => $data->category_id
                    ));
                if ( $result )
                { // Добавление прошло успешно
                    $redirectarray['success'] = 2;
                    // Удалим купоны категории
                    $couponresult = $DB->delete_records('enrol_otpay_coupons',
                        array(
                            'catid' => $data->category_id
                        ));
                } else
                { // Передаем код ошибки - ошибка при удалении категории
                    $redirectarray['error'] = 2;
                }
            }
            // Формируем URL для редиректа
            $url = new moodle_url('/enrol/otpay/coupons.php', $redirectarray);
            // Редирект
            redirect($url);
        }
    }
}