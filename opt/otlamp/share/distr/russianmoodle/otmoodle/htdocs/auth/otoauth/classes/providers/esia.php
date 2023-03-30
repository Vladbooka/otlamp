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
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Класс авторизации через ЕСИА
 *
 * @package    auth
 * @subpackage otoauth
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace auth_otoauth\providers;

use auth_otoauth\provider;
use stdClass;
use moodle_exception;
use cache;
use cache_store;
use curl;
use Exception;
use moodle_url;
use auth_otoauth\helper\signerpkcs7;
use admin_settingpage;
use admin_setting_configtext;
use admin_setting_configselect;
use admin_setting_configcheckbox;
use context_system;
use core\notification;

defined('MOODLE_INTERNAL') || die();

/**
 * Класс авторизации через Facebook
 *
 * @package    auth
 * @subpackage otoauth
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class esia extends provider
{
    protected $checkuseraccesstokenexpiry = true;

    protected $name = 'esia';

    protected $useraccesstoken = null;

    protected $useraccesstokenproperties = ['token', 'type', 'expires_in', 'state', 'refresh_token'];
    
    private $signer = null;
    
    private $portalurl = 'https://esia.gosuslugi.ru/';
    
    private $testportalurl = 'https://esia-portal1.test.gosuslugi.ru/';
    
    private $codeuri = 'aas/oauth2/ac';
    
    private $tokenuri = 'aas/oauth2/te';
    
    private $useruri = 'rs/prns';
    
    private $avataruri = 'esia-rs/api/public/v1/pso';
    
    protected $scope = [
        'fullname',
        'birthdate',
        'gender',
        'email',
        'mobile',
        'id_doc',
        'contacts',
    ];
    
    /**
     * Внутренний идентификатор пользователя ЕСИА
     * @var int
     */
    private $oid = null;
    
    public function __construct() {
        parent::__construct();
        $mode = $this->authconfig->{$this->get_name() . 'mode'} ?? 1;
        $this->testmode = ((int)$mode == 1);
        $this->set_signer();
    }
    
    /**
     * Инициализирует объект для подписи
     */
    public function set_signer()
    {
        $this->signer = new signerpkcs7();
        if (isset($this->authconfig->{$this->get_name() . 'keypin'})) {
            $keypin = $this->authconfig->{$this->get_name() . 'keypin'};
        } else {
            $keypin = '';
        }
        $this->signer->set_keypin($keypin);
        $this->signer->set_sign_type($this->signer::PKCS7_TYPE);
        if (isset($this->authconfig->{$this->get_name() . 'subjectname_query'})) {
            $subjectname_query = $this->authconfig->{$this->get_name() . 'subjectname_query'};
        } else {
            $subjectname_query = '';
        }
        $this->signer->set_subject_name_query($subjectname_query);
        
        if (isset($this->authconfig->{$this->get_name() . 'tspaddres'})) {
            $tspaddres = $this->authconfig->{$this->get_name() . 'tspaddres'};
        } else {
            $tspaddres = '';
        }
        if (!empty($tspaddres))
        {
            $this->signer->set_tspaddres($tspaddres);
        }
    }

    /**
     * Проверить действительность маркера доступа пользователя
     * {@inheritDoc}
     * @see \auth_otoauth\provider::check_token_expiry()
     */
    public function check_user_access_token_expiry($userid)
    {
        return;
    }
    
    /**
     * Установить токена доступа пользователя в переменную для дальнейшего использования
     * @param stdClass $data
     * @throws moodle_exception
     */
    public function set_user_access_token($data)
    {
        $token = new stdClass();
        if (property_exists($data, 'access_token')) {
            $token->token = $data->access_token;
        }
        if (property_exists($data, 'expires_in')) {
            $token->expires_in = $data->expires_in;
        }
        if (property_exists($data, 'state')) {
            $token->state = $data->state;
        }
        if (property_exists($data, 'token_type')) {
            $token->type = $data->token_type;
        }
        if (property_exists($data, 'refresh_token')) {
            $token->refresh_token = $data->refresh_token;
        }
        if ($this->isuseraccesstokenvalid($token)) {
            $this->useraccesstoken = $token;
        } else {
            throw new moodle_exception(get_string('error_recieved_token_data_invalid', 'auth_otoauth'));
        }
    }

    /**
     *
     * {@inheritDoc}
     * @see \auth_otoauth\provider::get_user_access_token_url()
     */
    public function get_user_access_token_url()
    {
        if ($this->testmode) {
            return $this->testportalurl . $this->tokenuri;
        } else {
            return $this->portalurl . $this->tokenuri;
        }
    }
    
    /**
     *
     * {@inheritDoc}
     * @see \auth_otoauth\provider::build_url()
     */
    public function build_url($popup = 0)
    {
        try {
            $scope = $this->get_scope();
            $timestamp = date('Y.m.d H:i:s O');
            $clientid = $this->authconfig->{$this->get_name() . 'clientid'};
            $state = $this->get_state();
            
            $message = $scope . $timestamp . $clientid . $state;
            if ($clientsecret = $this->signer->sign($message)) {
                $url = $this->get_code_url();
                $params = [
                    'client_id' => $clientid,
                    'client_secret' => $clientsecret,
                    'redirect_uri' => $this->get_redirect_url(),
                    'scope' => $scope,
                    'response_type' => $this->get_response_type(),
                    'state' => $state,
                    'access_type' => $this->get_access_type(),
                    'timestamp' => $timestamp,
                ];
                
                if (!empty($popup)) {
                    $this->add_popup_params($params);
                }
                
                return new moodle_url($url, $params);
            } else {
                return false;
            }
        } catch (Exception $e) {
            notification::error($e->getMessage());
            if ($e->getMessage() === 'Internal error. (0x8007065B)') {
                notification::error(get_string('error_cryptopro_csp_license_expired', 'auth_otoauth'));
            }
            return false;
        }
    }
    
    /**
     * Получить урл перенаправления
     * @return string
     */
    public function get_redirect_url()
    {
        $url = new moodle_url($this->redirecturi);
    
        return $url->out(false);
    }
    
    /**
     *
     * {@inheritDoc}
     * @see \auth_otoauth\provider::get_scope()
     */
    public function get_scope()
    {
        return implode(' ', $this->scope);
    }
    
    /**
     *
     * {@inheritDoc}
     * @see \auth_otoauth\provider::get_state()
     */
    public function get_state()
    {
        try {
            if (!isset($_SESSION['USER'])) {
                // This should never happen,
                // do not mess with session and globals here,
                // let any checks fail instead!
                return false;
            }
            $_SESSION['USER']->{$this->get_name() . 'state'} = sprintf(
                '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
                random_int(0, 0xffff),
                random_int(0, 0xffff),
                random_int(0, 0xffff),
                random_int(0, 0x0fff) | 0x4000,
                random_int(0, 0x3fff) | 0x8000,
                random_int(0, 0xffff),
                random_int(0, 0xffff),
                random_int(0, 0xffff)
            );
            return $_SESSION['USER']->{$this->get_name() . 'state'};
        } catch (Exception $e) {
            throw new moodle_exception(get_string('error_cannot_generate_random_integer', 'auth_otoauth', $e->getMessage()));
        }
    }
    
    /**
     *
     * {@inheritDoc}
     * @see \auth_otoauth\provider::check_state()
     */
    public function check_state($statestr)
    {
        if ($_SESSION['USER']->{$this->get_name() . 'state'} == $statestr) {
            $state = new stdClass();
            $state->authprovider = $this->get_name();
            return $state;
        } else {
            throw new moodle_exception(get_string('error_invalid_state', 'auth_otoauth'));
        }
    }
    
    /**
     *
     * {@inheritDoc}
     * @see \auth_otoauth\provider::get_code_url()
     */
    public function get_code_url()
    {
        if ($this->testmode) {
            return $this->testportalurl . $this->codeuri;
        } else {
            return $this->portalurl . $this->codeuri;
        }
    }
    
    /**
     *
     * {@inheritDoc}
     * @see \auth_otoauth\provider::get_response_type()
     */
    public function get_response_type()
    {
        return $this->responsetype;
    }
    
    /**
     *
     * {@inheritDoc}
     * @see \auth_otoauth\provider::get_access_type()
     */
    public function get_access_type()
    {
        return $this->accesstype;
    }
    
    /**
     *
     * {@inheritDoc}
     * @see \auth_otoauth\provider::build_params()
     */
    public function build_params()
    {
        $scope = $this->get_scope();
        $timestamp = date('Y.m.d H:i:s O');
        $clientid = $this->authconfig->{$this->get_name() . 'clientid'};
        $state = $this->get_state();
        
        $message = $scope . $timestamp . $clientid . $state;
        if ($clientsecret = $this->signer->sign($message)) {
            return [
                'client_id' => $clientid,
                'grant_type' => 'authorization_code',
                'client_secret' => $clientsecret,
                'state' => $state,
                'redirect_uri' => $this->get_redirect_url(),
                'scope' => $scope,
                'timestamp' => $timestamp,
                'token_type' => 'Bearer',
            ];
        } else
        {
            return [];
        }
    }
    
    /**
     *
     * {@inheritDoc}
     * @see \auth_otoauth\provider::get_user_info()
     */
    public function get_user_info($accesstoken)
    {
        $url = $this->get_user_info_url();
        $params = ['scope' => $this->get_scope()];
        $curloptions = [
            'CURLOPT_HTTPHEADER' => [
                'Authorization: Bearer ' . $accesstoken
            ]
        ];
        return $this->request($url, $params, 'get', $curloptions);
    }
    
    /**
     *
     * {@inheritDoc}
     * @see \auth_otoauth\provider::get_user_info_url()
     */
    public function get_user_info_url()
    {
        if ($this->testmode) {
            return $this->testportalurl . $this->useruri. '/' . $this->oid;
        } else {
            return $this->portalurl . $this->useruri. '/' . $this->oid;
        }
    }
    
    /**
     * Получить урл-адрес для запроса списка контактов пользователя
     * @return string
     */
    public function get_user_contacts_info_url()
    {
        if ($this->testmode) {
            return $this->testportalurl . $this->useruri. '/' . $this->oid . '/ctts';
        } else {
            return $this->portalurl . $this->useruri. '/' . $this->oid . '/ctts';
        }
    }
    
    /**
     * Получить урл-адрес для запроса аватара пользователя
     * @return string
     */
    public function get_user_avatar_info_url()
    {
        if ($this->testmode) {
            return $this->testportalurl . $this->avataruri. '/' . $this->oid . '/avt/square';
        } else {
            return $this->portalurl . $this->avataruri. '/' . $this->oid . '/avt/square';
        }
    }
    
    /**
     *
     * @param mixed $data
     * @return stdClass
     */
    public function build_user($data)
    {
        $userdata = json_decode($data);
        $user = new stdClass();
        $user->remoteuserid = $this->oid;
        if (!isset($this->authconfig->{$this->get_name() . 'trustedauth'})
            || !empty($this->authconfig->{$this->get_name() . 'trustedauth'})) {
            $user->verified = 1;
        } else {
            $user->verified = isset($userdata->trusted) ? (int)$userdata->trusted : 0;
        }
        $user->firstname = $userdata->firstName;
        $user->lastname = $userdata->lastName;
        
        $url = $this->get_user_contacts_info_url();
        $params = [];
        $curloptions = [
            'CURLOPT_HTTPHEADER' => [
                'Authorization: Bearer ' . $this->useraccesstoken->token
            ]
        ];
        $contacts = $this->request($url, $params, 'post', $curloptions);
        $contacts = json_decode($contacts);
        $user->email = '';
        if (!empty($contacts->elements)) {
            foreach($contacts->elements as $element) {
                $response = json_decode($this->request($element, $params, 'post', $curloptions));
                if ($response->type === 'EML' && $response->vrfStu === 'VERIFIED') {
                    // Берем первую найденную подтвержденную почту
                    $user->email = $response->value;
                    break;
                }
            }
        }
        return $user;
    }
    
    /**
     * Добавление стандартных настроек для провайдера (идентификатор и секрет приложения)
     * @param admin_settingpage $settings
     */
    public function add_main_settings(admin_settingpage $settings)
    {
        global $CFG;
        $baseurl = parse_url($CFG->wwwroot);
        // Получение объекта плагина
        $authplugin = get_auth_plugin('otoauth');
    
        // Добавление настройки идентификатора приложения
        $enablename = $this->get_name() . 'enable';
        $clientidname = $this->get_name() . 'clientid';
        
        $name = 'auth_otoauth/' . $enablename;
        $visiblename = get_string('settings_' . $enablename . '_label', 'auth_otoauth');
        $description = get_string('settings_' . $enablename . '_desc', 'auth_otoauth');
        $defaultsetting = 0;
        if (!isset($this->authconfig->{$this->get_name() . 'enable'})
            && !empty($this->authconfig->{$this->get_name() . 'clientid'})) {
            $defaultsetting = 1;
        }
        $settings->add(new admin_setting_configcheckbox(
            $name,
            $visiblename,
            $description,
            $defaultsetting
        ));
    
        $name = 'auth_otoauth/' . $clientidname;
        $visiblename = get_string('settings_' . $clientidname . '_label', 'auth_otoauth');
        $description = get_string('settings_' . $clientidname, 'auth_otoauth', [
            'jsorigins' => $baseurl['scheme'] . '://' . $baseurl['host'],
            'siteurl' => $CFG->httpswwwroot,
            'domain' => $CFG->httpswwwroot,
            'redirecturls' => $this->get_redirect_url(),
            'callbackurl' => $this->get_redirect_url(),
            'sitedomain' => $baseurl['host']
        ]);
        $settings->add(new admin_setting_configtext(
            $name,
            $visiblename,
            $description,
            '',
            PARAM_TEXT
        ));
    
        if ($this->possible_check_user_access_token_expiry()) {
            // Настройка отслеживания действительности маркера доступа
            $settingname = $this->get_name() . 'checkusertokenexpiry';
            $name = 'auth_otoauth/' . $settingname;
            $visiblename = get_string('settings_' . $settingname . '_label', 'auth_otoauth');
            $description = get_string('settings_' . $settingname. '_desc', 'auth_otoauth');
            $settings->add(new admin_setting_configselect(
                $name,
                $visiblename,
                $description,
                0,
                [
                    0 => get_string('no'),
                    1 => get_string('yes')
                ]
            ));
        }
    }
    
    /**
     * Добавление индивидуальных настроек провайдера. Метод переопределяется в дочерник классах
     * @param admin_settingpage $settings
     */
    public function add_custom_settings(admin_settingpage $settings)
    {
        // Пароль от контейнера с сертификатом
        $settingname = $this->get_name() . 'keypin';
        $name = 'auth_otoauth/' . $settingname;
        $visiblename = get_string('settings_' . $settingname . '_label', 'auth_otoauth');
        $description = get_string('settings_' . $settingname. '_desc', 'auth_otoauth');
        $settings->add(new admin_setting_configtext(
            $name,
            $visiblename,
            $description,
            '',
            PARAM_TEXT
        ));
        
        // Запрос на поиск сертификата в контейнере (CN сертификата)
        $settingname = $this->get_name() . 'subjectname_query';
        $name = 'auth_otoauth/' . $settingname;
        $visiblename = get_string('settings_' . $settingname . '_label', 'auth_otoauth');
        $description = get_string('settings_' . $settingname. '_desc', 'auth_otoauth');
        $settings->add(new admin_setting_configtext(
            $name,
            $visiblename,
            $description,
            '',
            PARAM_TEXT
        ));
        
        // Адрес службы штампов
        $settingname = $this->get_name() . 'tspaddres';
        $name = 'auth_otoauth/' . $settingname;
        $visiblename = get_string('settings_' . $settingname . '_label', 'auth_otoauth');
        $description = get_string('settings_' . $settingname. '_desc', 'auth_otoauth');
        $settings->add(new admin_setting_configtext(
            $name,
            $visiblename,
            $description,
            '',
            PARAM_TEXT
        ));
        
        // Разрешена ли авторизация для неподтвержденных аккаунтов
        $settingname = $this->get_name() . 'trustedauth';
        $name = 'auth_otoauth/' . $settingname;
        $visiblename = get_string('settings_' . $settingname . '_label', 'auth_otoauth');
        $description = get_string('settings_' . $settingname. '_desc', 'auth_otoauth');
        $settings->add(new admin_setting_configselect(
            $name,
            $visiblename,
            $description,
            1,
            [
                0 => get_string('no'),
                1 => get_string('yes'),
            ]
        ));
        
        // Режим работы
        $settingname = $this->get_name() . 'mode';
        $name = 'auth_otoauth/' . $settingname;
        $visiblename = get_string('settings_' . $settingname . '_label', 'auth_otoauth');
        $description = get_string('settings_' . $settingname. '_desc', 'auth_otoauth');
        $settings->add(new admin_setting_configselect(
            $name,
            $visiblename,
            $description,
            1,
            [
                1 => get_string($this->get_name() . 'test_mode', 'auth_otoauth'),
                0 => get_string($this->get_name() . 'main_mode', 'auth_otoauth'),
            ]
        ));
        
        // Открывать страницу аутентификации пользователя в новом всплывающем окне браузера (в виде popup)
        $settingname = $this->get_name() . 'displaypopup';
        $name = 'auth_otoauth/' . $settingname;
        $visiblename = get_string('settings_' . $settingname . '_label', 'auth_otoauth');
        $description = get_string('settings_' . $settingname. '_desc', 'auth_otoauth');
        $settings->add(new admin_setting_configselect(
            $name,
            $visiblename,
            $description,
            0,
            [
                0 => get_string('no'),
                1 => get_string('yes'),
            ]
        ));
    }
    
    /**
     * Получить ответ сервера авторизации при запросе токена доступа пользователя в обмен на код
     * @param string $code
     * @return mixed
     */
    public function get_user_access_token($code)
    {
        $url = $this->get_user_access_token_url();
        $params = $this->build_params();
        $params['code'] = $code;
        $curloptions = [
            'CURLOPT_HTTPHEADER' => [
                'Content-Type' => 'application/x-www-form-urlencoded'
            ]
        ];
        $response = $this->request($url, $params, 'post', $curloptions);
        $result = $this->extract_response($response, $this->useraccesstokenresponsetype);
        $this->setoid($result->access_token);
        return $result;
    }
    
    protected function setoid($useraccesstoken)
    {
        $chunks = explode('.', $useraccesstoken);
        $payload = json_decode($this->base64UrlSafeDecode($chunks[1]), true);
        $this->oid = $payload['urn:esia:sbj_id'];
    }
    
    private function base64UrlSafeDecode($string): string
    {
        $base64 = strtr($string, '-_', '+/');
        return base64_decode($base64);
    }
}