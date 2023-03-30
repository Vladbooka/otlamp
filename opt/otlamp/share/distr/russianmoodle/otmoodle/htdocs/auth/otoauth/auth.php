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
 * Плагин аутентификации OTOAuth. Класс плагина.
 *
 * @package    auth
 * @subpackage otoauth
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use auth_otoauth\customprovider_exception;
use auth_otoauth\providers\custom;
use \auth_otoauth\customprovider;

defined('MOODLE_INTERNAL') || die();

require_once ($CFG->libdir . '/authlib.php');
require_once ($CFG->dirroot . '/user/lib.php');
require_once ($CFG->dirroot . '/auth/otoauth/lib.php');
require_once ($CFG->libdir . '/gdlib.php');
require_once ($CFG->libdir . '/filelib.php');
require_once ($CFG->libdir . '/classes/notification.php');

use auth_otoauth\helper\send_notifications_by_capabilities;

class auth_plugin_otoauth extends auth_plugin_base
{
    /**
     * Массив объектов классов провайдера
     * @var array
     */
    private $provider = [];
    
    /**
     * Конструктор
     */
    public function __construct()
    {
        set_config('field_lock_email', 'unlocked', 'auth_otoauth');
        
        // Код плагина
        $this->authtype = 'otoauth';
        $this->roleauth = 'auth_otoauth';
        $this->errorlogtag = '[AUTH_OTOAUTH] ';
        // Конфигурация плагина
        $this->config = get_config('auth_otoauth');
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
     * Поддержка плагином механизма подтверждения пользователя
     *
     * @return boolean
     */
    public function can_confirm()
    {
        // Получение настройки необходимости подтверждения учетонй записи
        return (bool)$this->config->requireconfirm;
    }
    
    /**
     * Запрет генерации пароля пользователя в authenticate_user_login()
     *
     * @return boolean
     */
    public function prevent_local_passwords()
    {
        // Генерация разрешена
        return false;
    }
    
    /**
     * Authenticates user against the selected authentication provide (Google, Facebook...)
     *
     * @param string $username
     *            The username (with system magic quotes)
     * @param string $password
     *            The password (with system magic quotes)
     * @return bool Authentication success or failure.
     */
    public function user_login($username, $password)
    {
        global $DB, $CFG;
        
        // retrieve the user matching username
        $user = $DB->get_record('user', [
                'username' => $username,
                'mnethostid' => $CFG->mnet_localhost_id
        ]);
        
        // username must exist and have the right authentication method
        if ( ! empty($user) )
        {
            $code = optional_param('code', false, PARAM_TEXT);
            if ( $code === false )
            {
                // Код не передали, попробуем залогинить так
                return validate_internal_user_password($user, $password);
            }
            return true;
        }

        return false;
    }
    
    /**
     * Зарегистрировать пользователя
     *
     * Регистрация нового пользователя в системе.
     * Пароль доступен в текстовом виде.
     *
     * @param object $externaluser - Данные пользователя из внешнего сервиса
     * @param boolean $notify -
     *
     * @return object|bool $user - Возвращает объект нового пользователя или FALSE
     */
    public function user_signup($externaluser, $notify = FALSE)
    {
        global $DB, $CFG;

        $providerstring = otoauth_provider_name($externaluser->authprovider);
        
        // Проверка на запрет регистрации на уровне системы
        if ( $CFG->authpreventaccountcreation )
        {
            throw new moodle_exception('message_registration_disabled', 'auth_otoauth', $externaluser->wantsurl, $providerstring);
        }

        $provider = $this->get_provider($externaluser->authprovider);
        
        //выборка пользователей с таким же email (у удаленных email заменяется на другое значение, можно не учитывать)
        $userduplicateemail = $DB->get_records('user', ['email' => $externaluser->email]);
        switch ($provider->allow_register())
        {
            case 0:
                // Регистрация новых пользователей запрещена
                throw new moodle_exception('message_registration_disabled', 'auth_otoauth', $externaluser->wantsurl, $providerstring);
                break;
            case 1:
                if ( ! empty($userduplicateemail) )
                {
                    //@todo когда появится страница внутри блока с возможностью привязки аккаунтов, то надо ставить ее wantsurl
                    $externaluser->wantsurl = new moodle_url('/my');
                    //регистрация с дублирующим email запрещена - советуем авторизоваться и привязать внешний аккаунт
                    throw new moodle_exception('message_registration_duplicate_email', 'auth_otoauth', $externaluser->wantsurl, $providerstring);
                }
                break;
            case 2:
                if ( ! empty($userduplicateemail) )
                {
                    //для регистрации очищаем email и пользвоателю будет создан аккаунт для входа в который потребуется заполнить email
                    $externaluser->email='';
                }
                break;
            default:
                if ( ! empty($userduplicateemail) )
                {
                    //@todo когда появится страница внутри блока с возможностью привязки аккаунтов, то надо ставить ее wantsurl
                    $externaluser->wantsurl = new moodle_url('/my');
                    //регистрация с дублирующим email запрещена - советуем авторизоваться и привязать внешний аккаунт
                    throw new moodle_exception('message_registration_duplicate_email', 'auth_otoauth', $externaluser->wantsurl, $providerstring);
                }
                break;
        }
        
        // Создание пользователя
        $username = otoauth_generate_login();
        $password = generate_password();
        
        // Создание пользователя
        create_user_record($username, $password, 'otoauth');
        
        // Получение созданного пользователя
        $user = $DB->get_record('user', ['username' => $username]);
        if ( ! empty($user) )
        {// Пользователь найден
            $updatelocal = $this->get_updatelocal();
            if (strpos($updatelocal, 'oncreate') !== false) {
                // Добавление данных пользователю
                $this->update_user_account($user, $externaluser, true);
            }
            
            // Добавление привязки к созданному аккаунту
            $this->connect_link($user->id, $externaluser->authprovider, $externaluser->remoteuserid);
            
            // Статус подтверждения почты
            if ( $this->config->requireconfirm )
            {// Требуется подтверждение почты
                $DB->set_field('user', 'confirmed', 0, ['id' => $user->id]);
            }
            
            // Статус активации аккаунта
            if ( ! empty($this->config->admin_suspended))
            {// Требуется подтверждение аккаунта
                $DB->set_field('user', 'suspended', 1, ['id' => $user->id]);
                // Отправлять администратору уведомления
                if ( ! empty($this->config->admin_message_suspended)) {
                    $notifyinstance = new send_notifications_by_capabilities(
                        $this->config->admin_message_suspended_subject,
                        $this->config->admin_message_suspended_short,
                        $this->config->admin_message_suspended_full
                        );
                }
            }
            
            // Получение итогового пользователя
            $user = $DB->get_record('user', ['id' => $user->id]);
            if (isset($notifyinstance)) {
                $status = $notifyinstance->send($user, 'auth/otoauth:receive_notifications_new_suspended_user');
                if ($status !== true) {
                    \core\notification::error('send notification error: ' . $status);
                }
            }
            return $user;
        }
        return false;
    }
    
    /**
     * Проверяет наличие пользователя otoauth в базе данных.
     *
     * @todo больше параметров для проверки?
     *
     * @param string $username
     *            - имя пользователя {$username} в таблице user.
     * @return boolean - результат проверки: true если пользователь существует, иначе false.
     */
    public function user_exists($username)
    {
        global $DB;
        return $DB->record_exists('user', ['username' => $username]);
    }
    
    /**
     * Returns true if this authentication plugin is 'internal'.
     *
     * @return bool
     */
    public function is_internal()
    {
        return false;
    }
    
    /**
     * Returns true if this authentication plugin can change the user's
     * password.
     *
     * @return bool
     */
    public function can_change_password()
    {
        return true;
    }

    /**
     * Returns the URL for changing the user's pw, or empty if the default can
     * be used.
     *
     * @return moodle_url
     */
    public function change_password_url() {
        return null;
    }

    /**
     * Returns true if plugin allows resetting of internal password.
     *
     * @return bool
     */
    public function can_reset_password() {
        return true;
    }
    
    /**
     * Обработчик блока соцсетей на странице входа.
     *
     *
     * Позволяет войти в систему с помощью аккаунта соцсетей и других сервисов
     *
     * @throws moodle_exception
     */
    public function loginpage_hook()
    {
        global $CFG, $DB, $PAGE, $OUTPUT, $SITE, $USER;
        
        // Код авторизации в сервисе
        $authorization_code = optional_param('code', '', PARAM_TEXT);
        // Идентификатор пользователя для привязки аккаунта
        $linkaccount = optional_param('link', 0, PARAM_INT);
        // Хэш для проверки владения аккаунтом для привязки
        $secret = optional_param('secret', NULL, PARAM_RAW);
        // Ссылка для возврата
        $wantsurl = optional_param('wantsurl', '', PARAM_URL);
        
        if ( empty($authorization_code) )
        {// Код авторизации не получен, завершить хук
            return null;
        }
        
        // Получение имени сервиса, через который происходит авторизация
        $authprovider = required_param('authprovider', PARAM_ALPHANUMEXT);
        
        $provider = $this->get_provider($authprovider);
        // Получение токена доступа от сервиса
        $postreturnvalues = $provider->get_user_access_token($authorization_code);
        if ( ! isset($postreturnvalues->access_token) || empty($postreturnvalues->access_token) )
        {
            // Логирование ошибки
            $otherdata = [];
            $otherdata['errortype'] = 'authorization_code_not_received';
            $otherdata['provider'] = $authprovider;
            if ( ! empty($postreturnvalues) )
            {
                $otherdata['provider_response'] = json_encode($postreturnvalues);
            }
            $eventdata = [
                'other' => $otherdata
            ];
            $event = \auth_otoauth\event\signin_error::create($eventdata);
            $event->trigger();

            // Выкинуть исключение для отображения ошибки пользователю
            throw new moodle_exception('error_authorization_code_not_received', 'auth_otoauth', $wantsurl);
        }
        
        // Преобразовываем полученные данные в объект, подходящий для сохранения
        $provider->set_user_access_token($postreturnvalues);
        // Сохраняем данные из токена в переменные класса, если это нужно для нестандартной интеграции
        $provider->save_supported_user_access_token_fields($postreturnvalues);
        
        // Получение данных пользователя из сервиса
        $externaluserdata = $provider->get_user_info($postreturnvalues->access_token);
        $externaluser = $provider->build_user($externaluserdata);
        $externaluser->authprovider = $authprovider;
        
        $useremail = $externaluser->email;
        $remoteuserid = $externaluser->remoteuserid;
        $verified = $externaluser->verified;

        // ПРОВЕРКМ EMAIL ПОЛЬЗОВАТЕЛЯ
        if ( ! isset($externaluser->email) )
        {// Пользовательский email не получен
            // Сброс авторизации в сервисе
            $this->signout_service($authprovider, $authorization_code);
            
            // Логирование ошибки
            $otherdata = [];
            $otherdata['errortype'] = 'externaluser_email_not_received';
            $otherdata['provider'] = $authprovider;
            $otherdata['email'] = '';
            if ( ! empty($externaluser) )
            {
                $otherdata['external_user'] = json_encode($externaluser);
            }
            $eventdata = [
                'other' => $otherdata
            ];
            $event = \auth_otoauth\event\signin_error::create($eventdata);
            $event->trigger();
            
            // Выкинуть исключение для отображения ошибки пользователю
            throw new moodle_exception('error_externaluser_email_not_received', 'auth_otoauth', $wantsurl);
        }
        if ( ! empty($externaluser->email) && $externaluser->email != clean_param($externaluser->email, PARAM_EMAIL) )
        {// Невалидный email
            // Сброс авторизации в сервисе
            $this->signout_service($authprovider, $authorization_code);
            
            // Логирование ошибки
            $otherdata = [];
            $otherdata['errortype'] = 'externaluser_email_not_valid_or_empty';
            $otherdata['provider'] = $authprovider;
            $otherdata['email'] = $externaluser->email;
            if ( ! empty($externaluser) )
            {
                $otherdata['external_user'] = json_encode($externaluser);
            }
            $eventdata = [
                'other' => $otherdata
            ];
            $event = \auth_otoauth\event\signin_error::create($eventdata);
            $event->trigger();
            
            // Выкинуть исключение для отображения ошибки пользователю
            throw new moodle_exception('error_externaluser_email_not_valid', 'auth_otoauth', $wantsurl);
        }
        // Запретить вход, если используется корпоративный доступ для невалидного домена
        if ( $authprovider == 'google_corporate' )
        {
            // Корпоративный домен
            $domain = $this->config->google_corporate_domain;
        
            if ( ! strripos($useremail, $domain) )
            {// Email не корпоративного домена
                
                // Сброс авторизации в сервисе
                $this->signout_service($authprovider, $authorization_code);
                
                // Логирование ошибки
                $otherdata = [];
                $otherdata['errortype'] = 'google_corporate_email_domain_notvalid';
                $otherdata['provider'] = $authprovider;
                $otherdata['email'] = $externaluser->email;
                if ( ! empty($externaluser) )
                {
                    $otherdata['external_user'] = json_encode($externaluser);
                }
                $eventdata = [
                    'other' => $otherdata
                ];
                $event = \auth_otoauth\event\signin_error::create($eventdata);
                $event->trigger();
                
                throw new moodle_exception('error_google_corporate_email_domain_notvalid', 'auth_otoauth', $wantsurl, $domain);
            }
        }
        // Проверка глобального запроета email домена в системе
        if ( $err = email_is_not_allowed($useremail) )
        {
            // Сброс авторизации в сервисе
            $this->signout_service($authprovider, $authorization_code);
            
            // Логирование ошибки
            $otherdata = [];
            $otherdata['errortype'] = 'email_is_not_allowed';
            $otherdata['provider'] = $authprovider;
            $otherdata['email'] = $externaluser->email;
            if ( ! empty($externaluser) )
            {
                $otherdata['external_user'] = json_encode($externaluser);
            }
            $eventdata = [
                'other' => $otherdata
            ];
            $event = \auth_otoauth\event\signin_error::create($eventdata);
            $event->trigger();
            
            // Выкинуть исключение для отображения ошибки пользователю
            throw new moodle_exception('error_email_is_not_allowed', 'auth_otoauth', $wantsurl, $externaluser->email);
        }
        // Проверка верификации аккаунта пользователем
        if ( ! $verified )
        {// Аккаунт пользователя не подтвержден
            // Сброс авторизации в сервисе
            $this->signout_service($authprovider, $authorization_code);
            
            // Логирование ошибки
            $otherdata = [];
            $otherdata['errortype'] = 'externaluser_account_not_verified';
            $otherdata['provider'] = $authprovider;
            $otherdata['email'] = $externaluser->email;
            if ( ! empty($externaluser) )
            {
                $otherdata['external_user'] = json_encode($externaluser);
            }
            $eventdata = [
                'other' => $otherdata
            ];
            $event = \auth_otoauth\event\signin_error::create($eventdata);
            $event->trigger();
            
            // Выкинуть исключение для отображения ошибки пользователю
            throw new moodle_exception('error_externaluser_account_not_verified', 'auth_otoauth', $wantsurl);
        }
        // Привязка аккаунта
        if ( ! empty($linkaccount) && isset($secret) )
        {// Указан ID пользователя, с которым требуется связать внешний аккаунт пользователя
            if ($linkaccount == $USER->id && check_user_secret($linkaccount, $secret))
            {
                // Привязка аккаунта
                $linkeduser = $this->connect_link($linkaccount, $authprovider, $remoteuserid);
                if ( $linkeduser )
                {// Аккаунт привязан
                    $updatelocal = $this->get_updatelocal();
                    // Если нужно обновить данные профиля во время привзяки - выполним это
                    if (strpos($updatelocal, 'onlink') !== false) {
                        // Обновить данные пользователя из внешнего аккаунта
                        $this->update_user_account($linkaccount, $externaluser);
                        // Обновляем данные по пользователю в сессии, чтобы изменения были видны сразу
                        $user = get_complete_user_data('username', $USER->username);
                        \core\session\manager::set_user($user);
                        unset($user);
                    }
                    $this->redirect($wantsurl);
                } else
                {// Ошибка во время привязки аккаунта
                    // Сброс авторизации в сервисе
                    $this->signout_service($authprovider, $authorization_code);
                    
                    // Логирование ошибки
                    $otherdata = [];
                    $otherdata['errortype'] = 'external_account_not_linked';
                    $otherdata['provider'] = $authprovider;
                    if ( ! empty($externaluser) )
                    {
                        $otherdata['external_user'] = json_encode($externaluser);
                    }
                    $eventdata = [
                        'other' => $otherdata
                    ];
                    $event = \auth_otoauth\event\signin_error::create($eventdata);
                    $event->trigger();
                    
                    // Выкинуть исключение для отображения ошибки пользователю
                    throw new moodle_exception('error_externaluser_account_not_linked', 'auth_otoauth', $wantsurl);
                }
            } else
            {
                // Выкинуть исключение для отображения ошибки пользователю
                throw new moodle_exception('error_externaluser_account_not_linked', 'auth_otoauth', $wantsurl);
            }
        }
        
        // Поиск привязки внешнего пользователя к аккаунту в системе
        $user = null;
        if ( $DB->record_exists('auth_otoauth', [
                'remoteuserid' => $remoteuserid,
                'service' => $authprovider,
                'active' => '1']
            ))
        {// Связь найдена
            // Получение связи внешнего аккаунта с внутренним
            $account = $DB->get_record('auth_otoauth', [
                    'remoteuserid' => $remoteuserid,
                    'service' => $authprovider,
                    'active' => '1'
            ]);
            
            // Получение пользователя в системе
            $user = $DB->get_record('user', [
                    'id' => $account->userid,
                    'deleted' => 0,
                    'mnethostid' => $CFG->mnet_localhost_id
            ]);

            if ( ! empty($user) )
            {// Пользователь найден
                // Обновить время доступа
                $update = new stdClass();
                $update->id = $account->id;
                $update->lastaccess = time();
                $DB->update_record('auth_otoauth', $update);
                $updatelocal = $this->get_updatelocal();
                if (strpos($updatelocal, 'onlogin') !== false) {
                    // Обновить данные пользователя из внешнего аккаунта
                    $this->update_user_account($user->id, $externaluser);
                    // Положим обновленные данные в переменную
                    $user = $DB->get_record('user', ['id' => $user->id]);
                }
            } else
            {// Пользователь не найден при активной связи
                // Удаление всех связей пользователя с внешними аккаунтами
                $DB->delete_records('auth_otoauth', ['userid' => $account->userid]);
            }
        }
        
        $isnewuser = false;
        if ( empty($user) )
        {// Пользователь не найден
            
            $externaluser->wantsurl = $wantsurl;
            // Регистрация пользователя в системе
            try {
                $user = $this->user_signup($externaluser);
                if ( empty($user) )
                {// Не удалось создать пользователя
                    // Сброс авторизации в сервисе
                    $this->signout_service($authprovider, $authorization_code);
                    
                    // Логирование ошибки
                    $otherdata = [];
                    $otherdata['errortype'] = 'user_signup_error';
                    $otherdata['provider'] = $authprovider;
                    if ( ! empty($externaluser) )
                    {
                        $otherdata['external_user'] = json_encode($externaluser);
                    }
                    $eventdata = [
                        'other' => $otherdata
                    ];
                    $event = \auth_otoauth\event\signin_error::create($eventdata);
                    $event->trigger();
                    
                    // Выкинуть исключение для отображения ошибки пользователю
                    throw new moodle_exception('error_signup_user_error', 'auth_otoauth', $wantsurl);
                }
            } catch (moodle_exception $e) {
                redirect($wantsurl, $e->getMessage(), null, \core\notification::ERROR);
            }
            
            if ($user->suspended) {
                \core\notification::info(get_string('message_record_need_confirmation','auth_otoauth'));
            } else {
                $isnewuser = true;
                
                // Аутентификация пользователя
                if ( ! $autheduser = authenticate_user_login($user->username, $user->password) )
                {
                    // Сброс авторизации в сервисе
                    $this->signout_service($authprovider, $authorization_code);
                
                    // Логирование ошибки
                    $otherdata = [];
                    $otherdata['errortype'] = 'user_authenticate_error';
                    $otherdata['provider'] = $authprovider;
                    if ( ! empty($externaluser) )
                    {
                        $otherdata['external_user'] = json_encode($externaluser);
                    }
                    if ( ! empty($externaluser) )
                    {
                        $otherdata['user'] = json_encode($user);
                    } else
                    {
                        $otherdata['user'] = null;
                    }
                    $eventdata = [
                        'other' => $otherdata
                    ];
                    $event = \auth_otoauth\event\signin_error::create($eventdata);
                    $event->trigger();
                
                    // Выкинуть исключение для отображения ошибки пользователю
                    throw new moodle_exception('error_authenticate_user_error', 'auth_otoauth', $wantsurl);
                }
                
                //у нового пользователя может не быть email, если при создании не было таких данных (vk)
                //или включена настройка с очисткой email при создании учетки с дублирующей почтой
                if ( $isnewuser && ! empty($user->email) )
                {// Отправка уведомления о создании учетной записи
                    auth_otoauth_send_confirmation_email($user->id);
                }
            }
        } else if($user->suspended) {
            \core\notification::info(get_string('message_record_waiting_confirmation','auth_otoauth'));
        }
        if (! $user->suspended) {
            if ( $this->can_confirm() && $user->confirmed == 0 )
            {// Учетная запись не подтверждена
                $PAGE->set_title(get_string('mustconfirm'));
                $PAGE->set_heading($SITE->fullname);
                echo $OUTPUT->header();
                echo $OUTPUT->heading(get_string('mustconfirm'));
                echo $OUTPUT->box(get_string('emailconfirmsent', '', $user->email), 'generalbox boxaligncenter');
                echo $OUTPUT->footer();
                die;
            }
            complete_user_login($user);
        }
        
        if ($linkid = $this->connect_link($user->id, $externaluser->authprovider, $externaluser->remoteuserid)) {
            $provider->cache_user_access_token($linkid);
        }
        // Редирект
        $urltogo = $this->get_return_url($wantsurl);
        $provider->complete_user_auth($urltogo);
    }
    
    /**
     * Изменяет пароль пользователя на передаваемый
     *
     * @param stdClass $userid
     *            - объект пользователя
     * @param string $newpassword
     *            - новый пароль.
     * @return boolean - результат выполнения.
     */
    public function user_update_password($user, $newpassword)
    {
        return update_password($user, $newpassword);
    }
    
    /**
     * Сформировать массив кнопок для авторизации во внешних системах
     *
     * @param string $wantsurl - URL для перехода пользователей после авторизации во внешних системах
     *
     * @return array - Массив кнопок в заданном формате
     *              [
     *                  [
     *                      'url' => 'http://someurl',
     *                      'icon' => new pix_icon(...),
     *                      'name' => get_string('somename', 'auth_otoauth'),
     *                  ],
     *              ]
     */
    public function loginpage_idp_list($wantsurl)
    {
        $icons = [];
        
        $providers = $this->get_active_providers_list();
        foreach ($providers as $name)
        {
            try {
                $provider = $this->get_provider($name);
                $icons[$provider->get_name()] = $provider->loginpage_idp($wantsurl);
            } catch (Exception $e) {
                /**
                 * @todo добавить обработку ошибок
                 */
            }
        }
        return $icons;
    }
    
    /**
     * Called when the user record is updated.
     *
     * We check there is no hack-attempt by a user to change his/her email address
     *
     * @param mixed $olduser
     *            Userobject before modifications (without system magic quotes)
     * @param mixed $newuser
     *            Userobject new modified userobject (without system magic quotes)
     * @return boolean result
     */
    public function user_update($olduser, $newuser)
    {
        global $DB;
        
        if ( empty($olduser->email) )
        {
            if ( ! empty($newuser->email) )
            {
                $newuser->confirmed = 1;
                $DB->update_record('user', $newuser);
                auth_otoauth_send_confirmation_email($newuser->id);
            }
        }
        return true;
    }
    
    /**
     * Confirm the new user as registered.
     * This should normally not be used,
     * but it may be necessary if the user auth_method is changed to manual
     * before the user is confirmed.
     *
     * @param string $username
     * @param string $confirmsecret
     */
    public function user_confirm($username, $confirmsecret = null)
    {
        global $DB;
        
        $user = get_complete_user_data('username', $username);
        
        if ( ! empty($user) )
        {
            if ( $user->confirmed )
            {
                return AUTH_CONFIRM_ALREADY;
            } else
            {
                $DB->set_field("user", "confirmed", 1, array (
                        "id" => $user->id
                ));
                if ( $user->firstaccess == 0 )
                {
                    $DB->set_field("user", "firstaccess", time(), array (
                            "id" => $user->id
                    ));
                }
                return AUTH_CONFIRM_OK;
            }
        } else
        {
            return AUTH_CONFIRM_ERROR;
        }
    }
    
    /**
     * Подтверждение нового пользователя как зарегистрированного владельца аккаунта moodle
     *
     * @param string $uid
     *            - номер записи (id) в auth_otoauth
     * @param string $confirmsecret
     *            - hash от id, времени создания записи в auth_otoauth и ключевой строки
     * @param string $password
     *            - пароль для аккаунта
     * @return int - код результата
     */
    public function user_confirm_email($uid, $confirmsecret = null)
    {
        global $DB;
        
        // Злоумышленник должен знать секретную строку, id пользователя
        // и время создания записи, чтобы скомпрометировать скрипт: uid+secret.
        // На почту пользователя отсылается $uid и sha1($uid+time()).
        
        $user = $DB->get_record('auth_otoauth', array (
                'userid' => $uid
        ));
        $user->confirmed = $DB->get_field('user', 'confirmed', array (
                'id' => $uid
        ));
        $user->auth = $DB->get_field('user', 'auth', array (
                'id' => $uid
        ));
        $user->timecreated = $DB->get_field('user', 'timecreated', array (
                'id' => $uid
        ));
        $user->id = $uid;
        
        if ( $this->config->requireconfirm == 0 )
        {
            return AUTH_CONFIRM_ERROR;
        }
        
        if ( ! empty($user) )
        {
            // Если пользователь уже подтвержден
            if ( $user->confirmed )
            { // confirmed = 1
                return AUTH_CONFIRM_ALREADY;
            }
            
            // Если хэш-суммы совпадают
            if ( check_user_secret($uid, $confirmsecret) )
            {
                // Меняем провайдера с какого-либо на auth (todo а надо ли?)
                $this->update_user_auth_plugin($user, $user->auth, 'otoauth');
                // Подтверждаем аккаунт пользователя
                $DB->set_field('user', 'confirmed', 1, array (
                        'id' => $uid
                ));
                // Отмечаем время последнего доступа (логина) нынешним
                $DB->set_field('auth_otoauth', "lastaccess", time(), array (
                        'id' => $uid
                ));
                
                return AUTH_CONFIRM_OK;
            }
            // Хэши не совпали
            return AUTH_CONFIRM_FAIL;
        }
        // Произошло что-то ужасное (переменная $user пуста)
        return AUTH_CONFIRM_ERROR;
    }
    
    /**
     * Смена плагина auth в таблице user
     *
     * @param object $user
     *            - объект пользователя
     * @param string $oldplugin
     *            - наименование старого плагина
     * @param string $newplugin
     *            - наименование нового плагина
     * @return boolean - результат выполнения
     */
    public function update_user_auth_plugin($user, $oldplugin, $newplugin)
    {
        global $DB;
        
        $result = false;
        if ( $user->auth === $oldplugin && $oldplugin !== $newplugin )
        {
            $userprovider = new stdClass();
            $userprovider->id = $user->id;
            $userprovider->auth = $newplugin;
            $result = $DB->update_record('user', $userprovider);
        }
        return $result;
    }
    
    /**
     * Удаляет привязку пользователя к социальной сети по id
     *
     * @param int $id
     *            - id привязки
     * @return bool - результат операции
     */
    public function disconnect_link($id)
    {
        global $DB, $USER;
        // Проверим сессию
        require_sesskey();
        $userid = $DB->get_field('auth_otoauth', 'userid', array (
                'id' => $id
        ));
        
        if ( ! $DB->record_exists('auth_otoauth', array (
                'id' => $id
        )) )
        {
            return false;
        }
        if ( ! is_siteadmin() && $USER->id != $userid )
        {
            // Нельзя снимать привязки чужого пользователя
            return false;
        }
        // Нельзя отвязать последнюю привязку, если аккаунт не подтверждён
        $user = $DB->get_record('user', array (
                'id' => $userid
        ), 'id, confirmed');
        if ( ! empty($user) && $user->confirmed == 0 )
        {
            $count = $DB->count_records('auth_otoauth', array (
                    'userid' => $userid,
                    'active' => 1
            ));
            // Если привязка одна и зарегистрирован через привязку
            if ( $count == 1 )
            {
                // То нельзя удалить привязку
                throw new moodle_exception('cannotdisconnectlastlink', 'auth_otoauth');
            }
        }
        // Обновляем таблицу, снимая привязку
        $otoauth = new stdClass();
        $otoauth->id = $id;
        $otoauth->active = 0;
        return $DB->update_record('auth_otoauth', $otoauth);
    }
    
    /**
     * Привязывает пользователя к социальной сети
     *
     * @param int $userid - id пользователя из таблицы user
     * @param string $service - имя OAuth-сервиса
     * @param string $remoteuserid - пользовательский id на сервисе
     *
     * @return mixed|bool|int - результат операции или id привязки в таблице auth_otoauth
     */
    public function connect_link($userid, $service, $remoteuserid)
    {
        global $DB;
        
        $userid = (int)$userid;
        
        // Проверка входных данных
        if ( empty($userid) || ! is_string($service) || empty($remoteuserid) )
        {
            debugging('incorrect params:', DEBUG_DEVELOPER);
            return false;
        }
        
        // Проверка на валидность сервиса
        $services = otoauth_provider_list();
        $cp = otoauth_custom_provider_list();
        if (array_search($service, $services) === false && array_search($service, $cp) === false) {
            debugging('no such service', DEBUG_DEVELOPER);
            return false;
        }
        
        // Условия поиска линковки
        $conditions = array ('service' => $service, 'remoteuserid' => $remoteuserid);
        if ( $DB->record_exists('auth_otoauth', $conditions) )
        {// Линковка уже присутствует в базе
            // ПОлучим линковку
            $existingauth = $DB->get_record('auth_otoauth', $conditions);
        } else
        {// Привязка не создавалась ранее
            // Прилинкуем текущего пользователя к аккаунту сервиса
            $record = new stdClass();
            $record->userid = $userid;
            $record->service = $service;
            $record->remoteuserid = $remoteuserid;
            $record->datacreate = time();
            $record->lastaccess = $record->datacreate;
            $record->active = 1;
            $result = $DB->insert_record('auth_otoauth', $record, true);
            // Не меняем способ авторизации, чтобы не сломать основной способ авторизации пользователя
            return $result;
        }
        
        // Если уже есть привязка и она активная
        if ( $existingauth->active == 1 )
        {
            if ( $existingauth->userid != $userid )
            {// Нельзя манипулировать с привязками чужого пользователя!
                throw new moodle_exception('couldnotlinkanother', 'auth_otoauth');
            }
            return $existingauth->id;
        } else
        { // Неактивную привязку делаем своей привязкой, даже если она была до этого чужой
            $record = new stdClass();
            $record->id = $existingauth->id;
            $record->userid = $userid;
            $record->active = 1;
            if ($DB->update_record('auth_otoauth', $record)) {
                return $record->id;
            }
        }
        return false;
    }
    
    /**
     * Сбросить авторизацию в сервисе
     *
     * Производит сброс авторизации пользователя во внешнем сервисе. Пользователю
     * придется заново производить авторизацию для получения нового кода авторизации и
     * последующего входа в Moodle.
     *
     * @param string $authprovider - Сервис, для которого требуется сбросить авторизацию
     * @param string $authorization_code - Код авторизации пользователя в сервисе
     *
     * @return boolean - Результат сброса кода авторизации
     */
    public function signout_service($authprovider, $authorization_code)
    {
        // Получение списка внешних сервисов
        $services = otoauth_provider_list();
        $cp = otoauth_custom_provider_list();
        
        if (array_search($authprovider, $services) === false &&  array_search($authprovider, $cp) === false) {
            // Неизвестный сервис
            return false;
        }
        
        $provider = $this->get_provider($authprovider);
        if (!empty($provider->get_revoke_url())) {
            $provider->user_signout($authorization_code);
        }
    }
    
    /**
     * Редирект пользователей
     *
     * Редирект в зависимости от состояния пользователя
     *
     * @param string $wantsurl - ссылка для перенаправления пользователя
     */
    private function redirect($wantsurl)
    {
        $urltogo = $this->get_return_url($wantsurl);
        redirect($urltogo);
    }
    
    /**
     * Получить url-адрес для перенаправления пользователя
     * @param string $wantsurl
     * @return string
     */
    private function get_return_url($wantsurl = '') {
        global $CFG, $SESSION, $USER;
        if ( user_not_fully_set_up($USER) )
        {// Пользователь создан не полностью
            $urltogo = $CFG->wwwroot . '/user/edit.php';
        } else if ( isset($SESSION->wantsurl) and (strpos($SESSION->wantsurl, $CFG->wwwroot) === 0) )
        {
            $urltogo = $SESSION->wantsurl;
            unset($SESSION->wantsurl);
        } else if ( ! empty($wantsurl) and (strpos($wantsurl, $CFG->wwwroot) === 0) )
        {
            $urltogo = $wantsurl;
        } else
        {
            $urltogo = $CFG->wwwroot . '/';
            unset($SESSION->wantsurl);
        }
        return $urltogo;
    }
    
    /**
     * Обновить профиль пользователя
     *
     * @param int|stdClass $user - id пользователя или объект, содержащий id пользователя
     * @param stdClass $externaluser - Данные пользователя из сервиса
     * @param bool $emailupdate - флаг необходимости обновления поля email
     */
    public function update_user_account($user, $externaluser, $emailupdate = false)
    {
        global $DB;
        
        unset($externaluser->username);
        unset($externaluser->password);
        if (!$emailupdate) {
            unset($externaluser->email);
        }
        
        // Нормализация
        if (is_number($user)) {
            $user = $DB->get_record('user', array ('id' => $user));
        }
        if ( empty($user) )
        {// Пользователь не найден
            return false;
        }
        
        // Сформируем данные для обновления
        $updateduser = new stdClass();
        
        foreach ( $user as $field => $value )
        {
            if ( isset($externaluser->$field) )
            {
                if ( $field == 'picture')
                {
                    $newpicture = $user->picture;
                    if ( empty($newpicture) )
                    {
                        $newpicture = 1;
                    }
                    $fs = get_file_storage();
                    $context = context_user::instance($user->id);
                    $file_record = array (
                            'contextid' => $context->id,
                            'component' => 'user',
                            'filearea' => 'icon',
                            'itemid' => 0,
                            'filepath' => '/',
                            'filename' => $newpicture,
                            'timecreated' => time(),
                            'timemodified' => time()
                    );
                    
                    $fs->delete_area_files($context->id, 'user', 'icon', 0);
                    $file = $fs->create_file_from_url($file_record, $externaluser->picture);
                    $iconfile = $file->copy_content_to_temp();
                    $newrev = process_new_icon($context, 'user', 'icon', 0, $iconfile);
                    $updateduser->picture = $newrev;
                    continue;
                }
                $updateduser->$field = $externaluser->$field;
            }
        }
        if ( ! isset($externaluser->country) || ! isset($externaluser->city) )
        {
            $googleipinfodbkey = $this->config->googleipinfodbkey;
            if ( ! empty($googleipinfodbkey) )
            {// Получение данных местоположения по IP
                $curl = new curl();
                $locationdata = $curl->get('http://api.ipinfodb.com/v3/ip-city/?key=' . $googleipinfodbkey . '&ip=' . getremoteaddr() . '&format=json');
                $locationdata = json_decode($locationdata);
            }
            if ( ! empty($locationdata) )
            {// Добавление данных о местоположении
                $updateduser->country = isset($updateduser->country) ? isset($updateduser->country) : $locationdata->countryCode;
                $updateduser->city = isset($updateduser->city) ? isset($updateduser->city) : $locationdata->cityName;
            }
        }
        $updateduser->id = $user->id;
        // Обновим запись
        user_update_user($updateduser, false);
    }
    
    /**
     * Получить список активных провайдеров
     * @return array массив активных провайдеров
     */
    public function get_active_providers_list()
    {
        global $CFG;
        $result = [];
        $services = otoauth_provider_list();
        foreach ($services as $provider) {
            // Если указан clientid приложения, считаем, что провайдер используется
            if (!empty($this->config->{$provider . 'enable'})) {
                $result[] = $provider;
            }
        }
        $result = array_merge($result, array_map(function($code) {return 'cp_' . $code;}, array_keys(customprovider::get_custom_providers(['status' => 'active']))));
        if (file_exists($CFG->dataroot . '/plugins/auth_otoauth/custom.php')) {
            //имеется файл с настройками кастомных провайдеров
            include($CFG->dataroot . '/plugins/auth_otoauth/custom.php');
            if (!empty($customproviders)) {
                foreach ($customproviders as $code => $config) {
                    if (strpos('cp_', $code) !== 0) {
                        $code = 'cp_' . $code;
                    }
                    if (array_search($code, $result) === false) {
                        $result[] = $code;
                    }
                }
            }
        }
        return $result;
    }
    
    /**
     * Получить объект класса провайдера по имени
     * @param string $name
     * @return mixed
     */
    public function get_provider($name)
    {
        if (!array_key_exists($name, $this->provider)) {
            if (strpos($name, 'cp_') === 0) {
                // Кастомный провайдер
                $classname = '\auth_otoauth\providers\custom';
            } else {
                $classname = '\auth_otoauth\providers\\' . $name;
            }
            if (class_exists($classname))
            {
                $provider = new $classname;
                $provider->set_name($name);
                $provider->set_config();
                $this->provider[$name] = $provider;
                return $this->provider[$name];
            } else {
                throw new moodle_exception(get_string('invalid_provider', 'auth_otoauth'));
            }
        } else
        {
            return $this->provider[$name];
        }
    }
    
    /**
     *
     * {@inheritDoc}
     * @see auth_plugin_base::postlogout_hook()
     */
    public function postlogout_hook($user)
    {
        if ($provider = get_user_preferences('forceproviderlogout', null, $user)) {
            \core\notification::info(get_string('message_forceproviderlogout', 'auth_otoauth', get_string('provider_' . $provider, 'auth_otoauth')));
            unset_user_preference('forceproviderlogout', $user);
        }
    }
    
    /**
     * Получить выбранный режим обновления учетной записи или его значение по умолчанию в случае, если режим не установлен
     * @return string
     */
    public function get_updatelocal() {
        $updatelocal = $this->get_config()->updatelocal ?? null;
        if (empty($updatelocal)) {
            $updatelocal = 'oncreate';
        };
        return $updatelocal;
    }
}
