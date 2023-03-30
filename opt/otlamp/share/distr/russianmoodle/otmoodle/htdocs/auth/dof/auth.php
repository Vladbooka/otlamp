<?php
// This file is not a part of Moodle - http://moodle.org/
// This is a none core contributed module.
//
// This is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// This is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// The GNU General Public License
// can be see at <http://www.gnu.org/licenses/>.

/**
 * Плагин аутентификации Деканата. Класс плагина.
 *
 * @package    auth_dof
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir.'/authlib.php');
require_once($CFG->dirroot.'/user/profile/lib.php');
require_once($CFG->dirroot .'/user/editlib.php');
require_once($CFG->dirroot .'/auth/dof/locallib.php');

use core\message\message;
use auth_dof\forms\auth_dof_signup_form;
use auth_dof\modifiers\search as mod_search;


class auth_plugin_dof extends auth_plugin_base
{
    /**
     * Конструктор плагина
     */
    public function __construct()
    {
        // Код плагина
        $this->authtype = 'dof';
        // Конфигурация плагина
        $this->config = get_config('auth_dof');
    }

    /**
     * Получение конфига плагина
     * @return object
     */
    public function get_config()
    {
        return $this->config;
    }

    /**
     * Позволяет ли плагин вести регистрацию пользователей
     */
    public function can_signup()
    {
        return true;
    }

    /**
     * Вернуть объект формы регистрации пользователей
     *
     * @return auth_dof_signup_form - Объект формы регистрации
     */
    public function signup_form()
    {
        global $PAGE;
        // Получение обработчика сообщений
        $messageprocessors = $this->message_processors();
        if ( empty($messageprocessors) )
        {// Обработчик сообщений не определен
            print_error('error_signup_disabled', 'auth_dof', '');
        }
        //require_once($CFG->dirroot.'/auth/dof/forms.php');
        $PAGE->requires->js_call_amd('auth_dof/form-controller', 'init');
        // Шаг регистрации
        $step = optional_param('step', 1, PARAM_INT);
        $url = new moodle_url('/login/signup.php', ['step' => $step]);
        return new auth_dof_signup_form(
            $url, ['step' => $step], 'post', '', ['autocomplete' => 'on', 'class' => 'signup_form']);
    }

    /**
     * Обработчик формы рeгистрации
     *
     * @param object $user - Объект нового пользователя
     * @param boolean $notify - Отобразить уведомление о создании
     */
    public function user_signup($user, $notify = true)
    {
        global $CFG;

        $step = optional_param('step', 1, PARAM_INT);
        $externalrecord = null;
        // Массив полей пользователя для его создания
        $prepareuf = new stdClass();
        if ($step == 2) {
            if (isset($_SESSION['auth_dof_step_registration_1'])
                && is_object($prepareuf = json_decode($_SESSION['auth_dof_step_registration_1'])))
            {
                $step1cfgfields = auth_dof_prepare_fields(1);
                // Получение записи из источника соответствующего условиям поисковых полей первого шага
                $externalrecord = auth_dof_get_source_data($step1cfgfields, (array)$prepareuf);
                // Заменяет данные формы значениями из внешнего источника для полей поискового модификатора
                mod_search::replase_form_data_by_src_values(
                    $prepareuf, $step1cfgfields, $externalrecord);
            } else {
                print_error('Wrong data in server session variable');
            }
        }
        // Выполним модификаторы текущего шага
        $this->step_process($prepareuf, $step, $user, $externalrecord);
        if ($step == 1) {
            if (! auth_dof_is_displayed_fields_in_step(2)) {
                // Выполним модификаторы второго шага если нет полей формы к отображению.
                $this->step_process($prepareuf, 2, $user,
                    auth_dof_get_source_data(auth_dof_prepare_fields(1), (array)$prepareuf));
            } else {
                $_SESSION['auth_dof_step_registration_1'] = json_encode($prepareuf);
                redirect(new moodle_url('/login/signup.php', ['step' => 2]));
            }
        }
        unset($_SESSION['auth_dof_step_registration_1']);
        $prepareuf->secret = $user->secret;
        $prepareuf->auth = $user->auth;

        list($user, $userpassword) = $this->process_signup($prepareuf);

        // если включено подтверждение учетки и автологин
        if (!empty($this->config->confirmation) && !empty($this->config->auth_after_reg))
        {
            // принудительно отключаем вывод результатов полученных в процессе отправки уведомлений
            // потому что при текущих настройках нам надо выполнить авторизацию
            // а печать результатов завершается exit'ом - нам не подходит
            $this->process_user_signup_confirmation($user, $userpassword, false);
            // мы только что зарегистрировали пользователя
            // у нас настроена необходимость подтверждения аккаунта
            // это значит, что пользователь будет должен перейти из почты по ссылке для первой авторизации
            // поэтому мы допускаем возможность авторизации сразу после регистрации, в обход привычных правил
            // - для следующей авторизации ему все равно придется подтвердить аккаунт
            $user = get_complete_user_data('id', $user->id);
            complete_user_login($user);
            redirect($CFG->wwwroot);
        }

        $confirmationresult = $this->process_user_signup_confirmation($user, $userpassword, $notify);
        if (!$notify) {
            return $confirmationresult;
        }
    }

   /**
    * Обработчик шага рeгистрации выполняет модификаторы
    *
    * @param object $prepareuf
    * @param string $step
    * @param object $user
    * @param array|null $externalrecord
    */
    private function step_process(object $prepareuf, string $step, object $user, $externalrecord) {
        $usercfgfields = auth_dof_prepare_fields($step);
        $modifiers = auth_dof_get_handlers('modifiers');
        $groupmodifiers = auth_dof_get_handlers('group_modifiers');
        // Обработаем модификаторы
        foreach ($usercfgfields as $fldname => $fldcfg) {
            if (isset($fldcfg['mod'])) {
                if (is_array($modcfg = json_decode($fldcfg['mod'], true))) {
                    // Посчитаем модификаторы которые возвращают денные
                    $rdatcount = 0;
                    // Запустим включенные модификаторы для поля
                    foreach ($modifiers as $modname => $str) {
                        if (! empty($modcfg[$modname])) {
                            $classname = '\\auth_dof\\modifiers\\' . $modname;
                            if (class_exists($classname)) {
                                $modifier = new $classname(
                                    $fldname, $usercfgfields, $this->config, $externalrecord);
                                $modifier->process($user, $prepareuf);
                                $rdatcount += $modifier->is_field_data_returned() ? 1 : 0;
                            }
                        }
                    }
                    if ($rdatcount == 0) {
                        // Нет модификаторов возвращающих данные поля, возмем их формы
                        $fldname = auth_dof\modifiers_base::get_form_field_name($fldname);
                        if (isset($user->{$fldname})) {
                            $prepareuf->{$fldname} = $user->{$fldname};
                        } else {
                            print_error('No field "' . $fldname . '" data returned from reg form');
                        }
                    } elseif ($rdatcount > 1) {
                        print_error('More then one modifier return data in process');
                    }
                }
            }
        }
        // Обработаем групповые модификаторы
        foreach ($groupmodifiers as $modname => $str) {
            $classname = '\\auth_dof\\group_modifiers\\' . $modname;
            if (class_exists($classname)) {
                $modifier = new $classname(auth_dof_prepare_fields(), $this->config, $step);
                $modifier->process($user, $prepareuf);
            }
        }
    }

    public function process_user_signup_confirmation($user, $userpassword, $notify = true)
    {
        global $CFG, $PAGE, $OUTPUT, $SESSION;

        $processors = $this->message_processors();

        if( empty($this->config->confirmation) )
        {
            $message = '';
            $result = true;

            foreach($processors as $sendmethod=>$processor)
            {
                // Отправка данных с логином и паролем
                $sendresult = $this->send_registration_data($sendmethod, $user, $userpassword);
                if ( empty($sendresult) )
                {
                    $message .= html_writer::div(get_string($sendmethod.'_send_error_message', 'auth_dof'));
                } else
                {
                    $message .= html_writer::div(get_string($sendmethod.'_send_success_message', 'auth_dof'));
                }
                $result = $result && $sendresult;
            }

            // Уведомление пользователя
            if ( $notify )
            {// Требуется уведомление
                if ( empty($result) )
                {
                    $title = get_string('send_error_title', 'auth_dof');
                    $url = "$CFG->wwwroot/index.php";
                } else
                {
                    $title = get_string('send_success_title', 'auth_dof');
                    $url = get_login_url();
                    if ( ! empty($CFG->alternateloginurl) )
                    {
                        $url = $CFG->alternateloginurl;
                    }
                }
                $PAGE->navbar->add($title);
                $PAGE->set_title($title);
                $PAGE->set_heading($PAGE->course->fullname);
                echo $OUTPUT->header();
                notice($message, $url);
            } else
            {
                return $result;
            }
        } else
        {
            if( ! key_exists('email', $processors) )
            {
                print_error('auth_emailnoemail', 'auth_dof');
            }

            // Save wantsurl against user's profile, so we can return them there upon confirmation.
            if (!empty($SESSION->wantsurl)) {
                set_user_preference('auth_email_wantsurl', $SESSION->wantsurl, $user);
            }

            if (! send_confirmation_email($user)) {
                print_error('auth_emailnoemail', 'auth_dof');
            }

            if ($notify) {
                $emailconfirm = get_string('emailconfirm');
                $PAGE->navbar->add($emailconfirm);
                $PAGE->set_title($emailconfirm);
                $PAGE->set_heading($PAGE->course->fullname);
                echo $OUTPUT->header();
                notice(get_string('emailconfirmsent', '', $user->email), "$CFG->wwwroot/index.php");
            } else {
                return true;
            }
        }
    }

    public function process_user_signup($user)
    {
        // Генерация логина
        if (! isset($user->username)) {
            $geninst = new \auth_dof\modifiers\generated('username');
            $geninst->process('', $user);
        }
        // Генирация пароля пользователя
        if (! isset($user->password)) {
            // Генерация пароля
            $geninst = new \auth_dof\modifiers\generated('password');
            $geninst->process('', $user);
        }
        $this->process_signup($user);
    }

    public function process_signup($user) {
        global $CFG;
        // Подключение библиотек пользователя
        require_once($CFG->dirroot.'/user/profile/lib.php');
        require_once($CFG->dirroot.'/user/lib.php');

        // Поля к инициализации пустых значений если не заданы
        $initfields = ['email', 'phone1', 'phone2',
            'firstnamephonetic', 'lastnamephonetic', 'alternatename'];
        foreach ($initfields as $field) {
            if (! property_exists($user, $field)) {
                $user->{$field} = '';
            }
        }
        // Это для совместимости со старой версией плагина где на форме был phone
        if (! isset($user->phone2) && isset($user->phone)) {
            $user->phone2 = $user->phone;
        }
        $user->phone1 = (string)$this->clean_phonenumber($user->phone1);
        $user->phone2 = (string)$this->clean_phonenumber($user->phone2);
        // Формирование дополнительной информации о пользователе
        if (empty($user->calendartype))
        {// Установка типа календаря по умолчанию
            $user->calendartype = $CFG->calendartype;
        }
        // Нормализация пароля пользователя
        $userpassword = '';
        if (isset($user->password)) {// Пароль указан
            // Хэширование пароля
            $userpassword = $user->password;
            $user->password = hash_internal_user_password($user->password);
        }
        $user->confirmed = empty($this->config->confirmation) ? 1 : 0;
        $user->deleted = 0;
        $user->mnethostid = $CFG->mnet_localhost_id;
        $user->id = user_create_user($user, false, false);
        user_add_password_history($user->id, $userpassword);
        // Добавить пользователя в Деканат
        $this->add_user_to_dof($user);
        // Сохранить информацию о дополнительных полях пользователя
        profile_save_data($user);
        // Событие создания пользователя
        \core\event\user_created::create_from_userid($user->id)->trigger();
        return [$user, $userpassword];
    }

    /**
     * Returns true if plugin allows confirming of new users.
     *
     * @return bool
     */
    public function can_confirm() {
        return true;
    }

    /**
     * Returns true if the username and password work and false if they are
     * wrong or don't exist.
     *
     * @param string $username The username (with system magic quotes)
     * @param string $password The password (with system magic quotes)
     *
     * @return bool Authentication success or failure.
     */
    public function user_login($username, $password)
    {
        global $CFG, $DB;

        if ( $user = $DB->get_record('user', array('username' => $username, 'mnethostid' => $CFG->mnet_localhost_id) ) )
        {
            return validate_internal_user_password($user, $password);
        }
        return false;
    }

    /**
     * Updates the user's password.
     *
     * called when the user password is updated.
     *
     * @param  object  $user        User table object  (with system magic quotes)
     * @param  string  $newpassword Plaintext password (with system magic quotes)
     * @return boolean result
     *
     */
    public function user_update_password($user, $newpassword) {
        $user = get_complete_user_data('id', $user->id);
        return update_internal_user_password($user, $newpassword);
    }

    public function prevent_local_passwords() {
        return false;
    }

    /**
     * Явлеется ли плагин внутренним
     *
     * @return bool
     */
    public function is_internal()
    {
        return true;
    }

    /**
     * Возможность изменения пароля
     *
     * @return bool
     */
    public function can_change_password()
    {
        return true;
    }

    /**
     * Возвращает ссылку на восстановление пароля.
     *
     * @return string
     */
    public function change_password_url()
    {
        // Ссылка по умолчанию
        return '';
    }

    /**
     * Возможность сброса пароля
     *
     * @return bool
     */
    public function can_reset_password()
    {
        return true;
    }

    /**
     * Возможность ручной работы с плагином
     *
     * Например, при создании пользователей через CSV
     *
     * @return bool
     */
    public function can_be_manually_set()
    {
        return true;
    }

    /**
     * Подтверждение зарегистрированного пользователя.
     */
    public function user_confirm($username, $confirmsecret = null)
    {
        global $DB;
        $user = get_complete_user_data('username', $username);

        if (!empty($user)) {
            if ($user->auth != $this->authtype) {
                return AUTH_CONFIRM_ERROR;

            } else if ($user->secret == $confirmsecret && $user->confirmed) {
                return AUTH_CONFIRM_ALREADY;

            } else if ($user->secret == $confirmsecret) {
                if ( ! $DB->set_field("user", "confirmed", 1, array("id" => $user->id) )) {
                    return AUTH_CONFIRM_FAIL;
                }

                $syscontext = context_system::instance();
                \auth_dof\event\auth_confirmed::create([
                    'courseid' => $syscontext->instanceid,
                    'relateduserid' => $user->id,
                    'objectid' => $user->id,
                    'contextid' => $syscontext->id,
                ])->trigger();

                return AUTH_CONFIRM_OK;
            }
        }

        return AUTH_CONFIRM_ERROR;
    }

    /**
     * Доступность капчи
     *
     * @return bool
     */
    public function is_captcha_enabled() {
        global $CFG;
        return ( isset($CFG->recaptchapublickey) &&
                 isset($CFG->recaptchaprivatekey) &&
                 get_config('auth_dof', 'recaptcha')
               );
    }

    /**
     * Получить номер телефона без спецсимволов
     */
    public function clean_phonenumber($phone)
    {
        $phone = preg_replace("([^0-9])", "", $phone);
        if ( strlen($phone) === 11 )
        {
            return $phone;
        }
        return null;
    }

    /**
     * Обновить пароль пользователя и выслать смс с данными
     *
     * @param stdClass $user
     */
    public function send_registration_data($sendmethod, $user, $password = '')
    {
        global $CFG;

        $result = true;

        if ( empty($password) )
        {// Формирование пароля
            $password = generate_password();
            update_internal_user_password($user, $password, true);
        }

        // Получение данных для формирования сообщения
        $site = get_site();
        $supportuser = core_user::get_support_user();
        $targetuser = get_complete_user_data('id', $user->id);

        // Формирование сообщения
        $a = new stdClass();
        $a->firstname   = $targetuser->firstname;
        $a->lastname    = $targetuser->lastname;
        $a->sitename    = format_string($site->fullname);
        $a->username    = $targetuser->username;
        $a->newpassword = $password;
        $a->link        = $CFG->httpswwwroot .'/login/index.php';
        $a->signoff     = generate_email_signoff();
        $message = new message();
        $message->component         = 'auth_dof';
        $message->name              = '';
        $message->userfrom          = $supportuser;
        $message->userto            = $targetuser;
        $message->subject           = get_string('newaccount');
        $message->fullmessage       = get_string('newuserfull', 'auth_dof', $a);
        $message->fullmessageformat = FORMAT_HTML;
        $message->fullmessagehtml   = text_to_html(get_string('newuserfull', 'auth_dof', $a), false, false, true);
        $message->smallmessage      = get_string('newusershort', 'auth_dof', $a);
        $message->notification      = 1;

        // Не сохраняем запись в таблицу notifications, т.к. мы не храним пароли в открытом виде
        // В результате, при отправке сообщения в обход стандартного метода message_send()
        // в объект сообщения при передаче его конечному провайдеру не попадает свойство savedmessageid
        // в результате чего мы получаем notification при регистрации через auth_dof
        // Notice: Undefined property: stdClass::$savedmessageid in htdocs/message/output/email/message_output_email.php on line 96
        // поэтому мы делаем заглушку в виде savedmessageid = null
        $message->savedmessageid    = null;

        $customdata = new stdClass();
        $customdata->otsms = [];
        $customdata->otsms['transliteration'] = false;
        $customdata->otsms['addsubject'] = false;
        $message->customdata = $customdata;

        // Получение обработчика сообщений
        $messageprocessors = $this->message_processors();
        if( array_key_exists($sendmethod, $messageprocessors) )
        {
            $messageprocessor = $messageprocessors[$sendmethod];
            return $messageprocessor->send_message($message, false);
        } else
        {
            return false;
        }
    }

    /**
     * Метод получения доступных подразделений
     */
    public function get_available_dof_departments()
    {
        global $CFG, $DB;

        $stringnotadd = get_string('dof_departments_not_add', 'auth_dof');
        $departmentslist = [ 0 => $stringnotadd ];

        // Добавление секции с информацией о пользовательском портфолио
        $dofexist = $DB->record_exists('block_instances', ['blockname' => 'dof']);
        if ( ! empty($dofexist) )
        {// Блок деканата найден в системе
            $plugin = block_instance('dof');
            if ( ! empty($plugin) )
            {// Экземпляр деканата получен
                // Подключение библиотек деканата
                require_once($CFG->dirroot .'/blocks/dof/locallib.php');
                global $DOF;
                // Проверка существования API
                $exist = $DOF->plugin_exists('storage', 'departments');
                $storageversion = $DOF->storage('departments')->version();
                if ( ! empty($exist) && $storageversion > 2015120000 )
                {// API доступен
                    $options = [];
                    $exist = $DOF->plugin_exists('workflow', 'departments');
                    if ( ! empty($exist) )
                    {// Статусы подразделений доступны
                        $statuses = $DOF->workflow('departments')->get_meta_list('active');
                        $options['statuses'] = array_keys($statuses);
                    }
                    // Получение подразделений
                    $departments = $DOF->storage('departments')->get_departments(0, $options);
                    if ( ! empty($departments) )
                    {// Подразделения получены
                        foreach ( $departments as $department )
                        {
                            $departmentslist[$department->id] = $department->name;
                        }
                    }
                } else
                {
                    $stringversiondeperror = get_string('dof_departments_version_error', 'auth_dof');
                    $departmentslist = [ 0 => $stringversiondeperror ];
                }
            }
        }
        return $departmentslist;
    }

    /**
     * Метод получения доступных методов отправки данных о регистрации
     */
    public function get_available_send_methods()
    {
        $methods = [];
        $processors = get_message_processors(true);

        if ( isset($processors['email']->enabled) && $processors['email']->enabled == 1 )
        {
            $methods['email'] = get_string('pluginname', 'message_email');
        }
        if ( isset($processors['otsms']->enabled) && $processors['otsms']->enabled == 1 )
        {
            $methods['otsms'] = get_string('pluginname', 'message_otsms');
        }
        return $methods;
    }

    /**
     * Получение обработчика сообщений
     *
     * @return message_output|NULL - Объект обработчика сообщений
     * или NULL, если обработчик не найден или не активен
     */
    protected function message_processors()
    {
        $processors = [];
        if ( isset($this->config->sendmethod) )
        {// Процессоры выбраны в настройках
            $sendmethodsettings = explode(',', $this->config->sendmethod);
            foreach($sendmethodsettings as $sendmethod)
            {
                $allprocessors = get_message_processors(true);
                if ( isset($allprocessors[$sendmethod]->enabled) &&
                     $allprocessors[$sendmethod]->enabled == 1 )
                {// Процессор доступен и включен
                    if ( is_object($allprocessors[$sendmethod]->object) )
                    {// Объект процессора доступен
                        $processors[$sendmethod] = $allprocessors[$sendmethod]->object;
                    }
                }
            }
        }
        return $processors;
    }

    /**
     * Добавление пользователя в Деканат
     *
     */
    protected function add_user_to_dof($user)
    {
        global $CFG, $DB;

        // Получение деканата
        $dofexist = $DB->record_exists('block_instances', ['blockname' => 'dof']);
        if ( ! empty($dofexist) )
        {// Блок деканата найден в системе
            $plugin = block_instance('dof');
            if ( ! empty($plugin) )
            {// Экземпляр деканата получен
                // Подключение библиотек деканата
                require_once($CFG->dirroot .'/blocks/dof/locallib.php');
                global $DOF;
                // Проверка существования API
                $exist = $DOF->plugin_exists('storage', 'persons');
                if ( ! empty($exist) )
                {// API доступен
                    if( ! empty($this->config->dof_departmentid) && $this->config->dof_departmentid > 0 )
                    {
                        // Создать пользователя
                        $id = $DOF->storage('persons')->reg_moodleuser($user);
                        if ( empty($id) )
                        {
                            return false;
                        }
                        $person = new stdClass();
                        $person->id  = $id;
                        if ( isset($user->middlename) )
                        {
                            $person->middlename  = $user->middlename;
                        }
                        $person->departmentid  = $this->config->dof_departmentid;
                        $person->phonecell = $user->phone2;
                        $res = $DOF->storage('persons')->update($person);
                        if ( empty($res) )
                        {
                            return false;
                        }
                    }
                }
            }
        }
        return true;
    }

    /**
     * Post authentication hook.
     * This method is called from authenticate_user_login() for all enabled auth plugins.
     *
     * @param object $user user object, later used for $USER
     * @param string $username (with system magic quotes)
     * @param string $password plain text password (with system magic quotes)
     */
    function user_authenticated_hook(&$user, $username, $password) {
        global $CFG, $SESSION;
        $disableandadmin = ! empty($CFG->disabledualauth) && is_siteadmin($user);
        if(isset($this->config->dualauth) && $this->config->dualauth && ! isguestuser($user) && ! $disableandadmin )
        {
            // устанавливаем начальное время жизни ключа
            set_user_preference('auth_dof_dualauth_creation_time', time(), $user);
            // генерим ключ
            $code =  $this->generatePassword(4, 4, 0, false);
            set_user_preference('auth_dof_dualauth_code', $code, $user);
            // Новый код - новые попытки ввода
            set_user_preference('auth_dof_dualauth_attemptsentrycode', 0, $user);
                // определяем куда хочет пользователь
            if (isset($SESSION->wantsurl) and (strpos($SESSION->wantsurl, $CFG->wwwroot) === 0 or strpos($SESSION->wantsurl, str_replace('http://', 'https://', $CFG->wwwroot)) === 0)) {
                $urltogo = $SESSION->wantsurl; // Because it's an address in this site.
                unset($SESSION->wantsurl);
            } else {
                // No wantsurl stored or external - go to homepage.
                $urltogo = $CFG->wwwroot . '/';
                unset($SESSION->wantsurl);
            }

            // If the url to go to is the same as the site page, check for default homepage.
            if ($urltogo == ($CFG->wwwroot . '/')) {
                $homepage = get_home_page();
                // Go to my-moodle page instead of site homepage if defaulthomepage set to homepage_my.
                if ($homepage == HOMEPAGE_MY && ! is_siteadmin() && ! isguestuser()) {
                    if ($urltogo == $CFG->wwwroot or $urltogo == $CFG->wwwroot . '/' or $urltogo == $CFG->wwwroot . '/index.php') {
                        $urltogo = $CFG->wwwroot . '/my/';
                    }
                }
            }
            set_user_preference('auth_dof_dualauth_urltogo', $urltogo, $user);
            // Пробуем выйти всеми возможными методами
            $authsequence = get_enabled_auth_plugins(); // auths, in sequence
            foreach($authsequence as $authname) {
                $authplugin = get_auth_plugin($authname);
                $authplugin->logoutpage_hook();
            }
            require_logout();
            // отправляем проверочный код
            $this->send_verification_code($user, $code);

            // редиректим на страницу подтверждения пароля
            redirect(new moodle_url('/auth/dof/authorization.php', ['userid' => $user->id]));
            die;
        }
    }

    /**
     * Создать пароль
     *
     * @param int $length - длина пароля
     *
     * @return string - сгенерированный пароль
     */
    protected function generatePassword($length=4, $numnumbers=2, $numsymbols=0, $insertletters = true)
    {
        $password = '';
        $letters = '';
        $numbers = '';
        $symbols = '';

        if( (int)$length < 4 )
        {
            $length = 4;
        }

        $passgroups = array(
            'letters' => 'abcdefghijkmnpqrstuvwxyzABCDEFGHJKLMNPQRSTUVWXYZ',
            'numbers' => '23456789',
            'symbols' => '-!@_^#$()',
        );

        $rangeletters = strlen($passgroups['letters']);
        $rangenumbers = strlen($passgroups['numbers']);
        $rangesymbols = strlen($passgroups['symbols']);

        // генерируем буквы
        for ($i=0; $i < $length; $i++)
        {
            $letters .= $passgroups['letters'][rand(0,$rangeletters-1)];
        }
        // добавляем строго указанное количество чисел
        for ($i=0; $i < $numnumbers; $i++)
        {
            $numbers .= $passgroups['numbers'][rand(0,$rangenumbers-1)];
            if( strlen($letters) > 0 )
            {
                $letters = substr($letters, 0, -1);
            }
        }
        // добавляем строго указанное количество символов
        for ($i=0; $i < $numsymbols; $i++)
        {
            $symbols .= $passgroups['symbols'][rand(0,$rangesymbols-1)];
            if( strlen($letters) > 0 )
            {
                $letters = substr($letters, 0, -1);
            }
        }


        // обрежем пароль под нужную длину, укороченную на 2 символа
        $password = substr($numbers . $symbols . $letters, 0, ($insertletters ? $length-2 : $length));

        // замешаем пароль
        $password = str_shuffle($password);

        // в качестве первого и последнего символа вставим буквы если нужно
        if( $insertletters )
        {
            $password = $passgroups['letters'][rand(0,$rangeletters-1)]
            . $password
            . $passgroups['letters'][rand(0,$rangeletters-1)];
        }

        return $password;
    }

    /**
     * Отправка проверочного кода пользователю
     *
     * @param object $user
     * @param string $code
     */
    protected function send_verification_code($user, $code){
        global $CFG;
        // Получение данных для формирования сообщения
        $site = get_site();
        $supportuser = core_user::get_support_user();
        $targetuser = get_complete_user_data('id', $user->id);

        // Формирование сообщения
        $a = new stdClass();
        $a->firstname   = $targetuser->firstname;
        $a->lastname    = $targetuser->lastname;
        $a->sitename    = format_string($site->fullname);
        $a->username    = $targetuser->username;
        $a->link        = $CFG->httpswwwroot .'/auth/dof/authorization.php?code='.$code.'&userid='.$user->id;
        $a->code        = $code;

        $customdata = new stdClass();
        $customdata->otsms = [];
        $customdata->otsms['transliteration'] = false;

        $message = new message();
        $message->component         = 'auth_dof';
        $message->name              = 'dualauthsendmethod';
        $message->userfrom          = $supportuser;
        $message->userto            = $targetuser;
        $message->subject           = get_string('subject_verification_code', 'auth_dof');
        $message->fullmessage       = get_string('verification_code_full', 'auth_dof', $a);
        $message->fullmessageformat = FORMAT_HTML;
        $message->fullmessagehtml   = text_to_html(get_string('verification_code_full', 'auth_dof', $a), false, false, true);
        $message->smallmessage      = get_string('verification_code_short', 'auth_dof', $a);
        $message->notification      = 1;
        $message->customdata        = $customdata;

        message_send($message);
    }
}

