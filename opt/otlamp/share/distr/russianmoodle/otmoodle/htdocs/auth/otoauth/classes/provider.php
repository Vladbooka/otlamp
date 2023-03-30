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
 * Базовый класс провайдера авторизации
 *
 * @package    auth
 * @subpackage otoauth
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace auth_otoauth;

use stdClass;
use moodle_exception;
use cache;
use cache_store;
use curl;
use Exception;
use moodle_url;
use admin_settingpage;
use admin_setting_configtext;
use admin_setting_configselect;
use admin_setting_configcheckbox;
use \core\output\notification;
use \auth_otoauth\customprovider;

/**
 * Базовый класс провайдера авторизации
 *
 * @package    auth
 * @subpackage otoauth
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
abstract class provider
{
    const DEFAULT_RESPONSE = 'json';
    
    const DEFAULT_REQUEST = 'post';
    
    const DEFAULT_CURL_OPTIONS = [];
    
    /**
     * Флаг возможности проверки действительности маркера доступа
     * @var bool
     */
    protected $checkuseraccesstokenexpiry = false;
    
    /**
     * Конфиг провайдера
     * @var mixed
     */
    protected $config = null;

    /**
     * Конфиг провайдера в yaml разметке
     * @var string
     */
    protected $yaml = '';
    
    /**
     * Поля, необходимые для корректного сохранения токена доступа пользователя
     * @var array
     */
    protected $useraccesstokenproperties = ['token'];
    
    /**
     * Конфиг плагина (настройки)
     * @var stdClass
     */
    protected $authconfig = null;
    
    /**
     * Имя провайдера, должно быть определено в дочернем классе
     * @var string
     */
    protected $name = '';
    
    /**
     * Токен доступа пользователя
     * @var string
     */
    protected $useraccesstoken = null;
    
    /**
     * Урл, куда должно осуществляться перенаправление со стороны провайдера
     * @var string
     */
    protected $redirecturi = '/auth/otoauth/redirect.php';
    
    /**
     * По умолчанию запрос доступа осуществляется с помощью кода авторизации. При необходимости переопределяется в дочернем классе.
     * @var string
     */
    protected $responsetype = 'code';
    
    /**
     * Токен доступа приложения
     * @var string
     */
    protected $appaccesstoken = null;
    
    /**
     * Урл для запроса токена доступа приложения
     * @var string
     */
    protected $appaccesstokenurl = '';
    
    /**
     * Урл для запроса токена доступа пользователя
     * @var string
     */
    protected $useraccesstokenurl = '';
    
    /**
     * Тип доступа, по умолчанию автономный
     * @var string
     */
    protected $accesstype = 'offline';
    
    /**
     * Область видимости данных пользователя
     * @var string
     */
    protected $scope = '';
    
    /**
     * Строка state, передаваемая в запросах
     * @var string
     */
    protected  $state = '';
    
    /**
     * Формат данных, ожидаемый при запросе на получение токена доступа пользователя.
     * По умолчанию json, при необходимости переопределяется в дочернем классе.
     * @var string
     */
    protected $useraccesstokenresponsetype = 'json';
    
    /**
     * Урл-адрес для запроса на сброс авторизации
     * @var string
     */
    protected $revokeurl = '';
    
    /**
     * Режим отладки
     * @var bool
     */
    protected $debug = false;
    
    /**
     * Тестовый режим
     * @var bool
     */
    protected $testmode = false;
    
    /**
     * Список поддерживаемых полей токена доступа пользователя, которые могут быть сохранены 
     * в переменные класса для использования в макроподстановках
     * для нестандартных интеграций
     * @var array
     */
    protected $supporteduseraccesstokenfields = ['email', 'expires_in', 'refresh_token'];
    
    protected $provider_email_field = '';
    
    protected $provider_expires_in_field = '';
    
    protected $provider_refresh_token_field = '';
    
    /**
     * Конструктор
     */
    public function __construct()
    {
        global $CFG;
        // Выставляем конфиг плагина
        $this->set_auth_config();
        $this->debug = !empty($CFG->debugdeveloper);
    }
    
    /**
     * Установить имя провайдера
     * @param string $name
     */
    public function set_name($name) {
        $this->name = $name;
    }
    
    /**
     * Устанавливает конфиг провайдера (если не передан, то парсит yaml конфиг провайдера)
     * @param array $config конфиг. Формат конфига:
     * $config = [
     *       'clientid' => '',
     *       'clientsecret' => '',
     *       'authorize' => [
     *           'url' => 'https://accounts.google.com/o/oauth2/auth?&scope=https://www.googleapis.com/auth/userinfo.profile https://www.googleapis.com/auth/userinfo.email',
     *           'parameters' => [
     *               'client_id' => '{clientid}',
     *               'redirect_uri' => '{redirect_uri}',
     *               'state' => '{state}',
     *               'response_type' => 'code'
     *           ]
     *       ],
     *       'accesstoken' => [
     *           'url' => 'https://accounts.google.com/o/oauth2/token',
     *           'parameters' => [
     *               'client_id' => '{clientid}',
     *               'client_secret' => '{clientsecret}',
     *               'redirect_uri' => '{redirect_uri}',
     *               'code' => '{authorization_code}',
     *               'grant_type' => 'authorization_code'
     *           ],
     *           'requesttype' => 'post',
     *           'curloptions' => [],
     *           'responsetype' => 'json',
     *           'responsefields' => [
     *               'token' => 'access_token'
     *           ]
     *       ],
     *       'refreshtoken' => [
     *           'url' => '',
     *           'parameters' => [],
     *           'requesttype' => 'post',
     *           'curloptions' => [],
     *           'responsetype' => 'json'
     *       ],
     *       'revoke' => [
     *           'url' => 'https://accounts.google.com/o/oauth2/revoke',
     *           'parameters' => [],
     *           'requesttype' => 'post',
     *           'curloptions' => [],
     *           'responsetype' => 'json'
     *       ],
     *       'userinfo' => [
     *           [
     *               'url' => 'https://www.googleapis.com/oauth2/v1/userinfo',
     *               'parameters' => [
     *                   'access_token' => '{access_token}',
     *                   'alt' => 'json'
     *               ],
     *               'requesttype' => 'get',
     *               'curloptions' => [],
     *               'responsetype' => 'json',
     *               'responsefields' => [
     *                   'email' => 'email',
     *                   'firstname' => 'given_name',
     *                   'lastname' => 'family_name',
     *                   'lang' => 'locale',
     *                   'url' => 'link',
     *                   'picture' => 'picture',
     *                   'verified' => 'verified_email',
     *                   'remoteuserid' => 'email'
     *               ]
     *           ],
     *           [
     *               'url' => 'https://www.googleapis.com/userinfo/email',
     *               'parameters' => [
     *                   'access_token' => '{access_token}',
     *                   'alt' => 'json'
     *               ],
     *               'requesttype' => 'get',
     *               'curloptions' => [],
     *               'responsetype' => 'json',
     *               'responsefields' => [
     *                   'email' => 'data/email',
     *                   'verified' => 'data/isVerified',
     *                   'remoteuserid' => 'data/email'
     *               ]
     *           ]
     *       ],
     *       'icon' => 'data:image/jpeg;base64,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',
     *       'allowregister' => '1'
     *   ];
     */
    public function set_config($config = null)
    {
        if (is_null($config)) {
            // Если конфиг явно не передали, то берем его из yaml разметки
//             $this->config = customprovider::parse_config($this->get_yaml());
        } else {
            $this->config = $config;
        }
    }
    
    /**
     * Получить yaml-конфиг провайдера
     * Зашитые классы возвращают свой зашитый конфиг
     * Кастомные классы возвращают свой конфиг из настроек
     */
    public function get_yaml() {
        return $this->yaml;
    }
    
    /**
     * Проверить действительность маркера доступа пользователя. Переопределяется в дочернем классе.
     * @param int userid идентификатор пользователя
     */
    public function check_user_access_token_expiry($userid)
    {
        return;
    }
    
    /**
     * Возможна ли проверка действительности маркера доступа?
     * @return boolean
     */
    public function possible_check_user_access_token_expiry()
    {
        return $this->checkuseraccesstokenexpiry;
    }
    
    /**
     * Получить имя провайдера
     * @return string
     */
    public function get_name()
    {
        return $this->name;
    }
    
    /**
     * Получить урл для запроса токена доступа пользователя
     * @return string
     */
    public function get_user_access_token_url()
    {
        return $this->useraccesstokenurl;
    }

    /**
     * Получить урл для запроса токена доступа приложения
     * @return string
     */
    public function get_app_access_token_url()
    {
        return $this->appaccesstokenurl;
    }
    
    /**
     * Проверяет валиден ли объект токена, чтобы его можно было сохранить и использовать
     * @param stdClass $token
     * @return boolean
     */
    protected function isuseraccesstokenvalid($token)
    {
        $result = true;
        foreach ($this->useraccesstokenproperties as $prop) {
            if (property_exists($token, $prop) && ! empty($token->$prop)) {
                $result = $result && true;
            } else {
                $result = false;
                break;
            }
        }
        return $result;
    }
    
    /**
     * Получить конфиг плагина
     * @return stdClass
     */
    public function get_auth_config()
    {
        return $this->authconfig;
    }
    
    /**
     * Устанавливает конфиг плагина
     */
    private function set_auth_config()
    {
        $authplugin = get_auth_plugin('otoauth');
        $this->authconfig = $authplugin->get_config();
    }
    
    /**
     * Получить clien_id
     * @return string
     */
    public function get_client_id()
    {
        return $this->authconfig->{$this->get_name() . 'clientid'};
    }
    
    /**
     * Получить client_secret
     * @return string
     */
    public function get_client_secret()
    {
        return $this->authconfig->{$this->get_name() . 'clientsecret'};
    }
    
    /**
     * Получить урл перенаправления
     * @return string
     */
    public function get_redirect_url()
    {
        if (!empty($this->authconfig->usenewredirect))
        {
            $url = new moodle_url($this->redirecturi);
        } else {
            $url = new moodle_url('/auth/otoauth/' . $this->get_name() . '_redirect.php');
        }
        
        return $url->out(false);
    }
    
    /**
     * Сформировать state для запросов
     * @return string
     */
    public function get_state()
    {
        $state = new stdClass();
        $state->sesskey = sesskey();
        // Запоминаем провайдера, которого передавали
        $state->authprovider = $this->get_name();
        $json = json_encode($state);
        return base64UrlEncode($json);
    }
    
    public function check_state($statestr)
    {
        $json = base64UrlDecode($statestr);
        $state = json_decode($json);
        // Защита от подделки запросов http://en.wikipedia.org/wiki/Cross-site_request_forgery
        if (!isset($state->sesskey)) {
            print_error('invalidsesskey');
        }
        confirm_sesskey($state->sesskey);
        return $state;
    }
    
    /**
     * Получить тип запроса доступа
     * @return string
     */
    public function get_response_type()
    {
        return $this->responsetype;
    }
    
    /**
     * Сформировать урл-адрес для получения кода, необходимого для получения токена доступа пользователя
     * @param int $popup флаг открытия страницы авторизации соцсети в новом всплывающем окне браузера (в виде popup)
     * @return moodle_url|false если использование провайдера выключено
     */
    public function build_url($popup = 0)
    {
        $url = $this->get_code_url();
        $params = [
            'client_id' => $this->authconfig->{$this->get_name() . 'clientid'},
            'redirect_uri' => $this->get_redirect_url(),
            'response_type' => $this->get_response_type(),
            'state' => $this->get_state(),
            'scope' => $this->get_scope()
        ];
        if (!empty($popup)) {
            $this->add_popup_params($params);
        }
        
        return new moodle_url($url, $params);
    }
    
    /**
     * Добавить к параметрам построения урл-адреса для получения кода, 
     * необходимого для получения токена доступа пользователя,
     * параметры для открытия окна авторизации в соцсети во всплывающем окне в виде popup
     * @param array $params массив параметров
     */
    public function add_popup_params(& $params) {
        $params['display'] = 'popup';
    }
    
    /**
     * Получить базовый урл без параметров для получения кода, необходимого для получения токена доступа пользователя
     * @return string
     */
    public function get_code_url()
    {
        return $this->codeurl;
    }
    
    /**
     * Получить строку scope для запросов
     * @return string
     */
    public function get_scope()
    {
        return implode(' ', $this->scope);
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
        
        $response = $this->request($url, $params);
        return $this->extract_response($response, $this->useraccesstokenresponsetype);
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
        if ($this->isuseraccesstokenvalid($token)) {
            $this->useraccesstoken = $token;
        } else {
            throw new moodle_exception(get_string('error_recieved_token_data_invalid', 'auth_otoauth'));
        }
    }
    
    /**
     * Сохранить в свойствах класс данные из токена доступа пользователя
     * Сохраняются только поддерживаемые поля
     * @param stdClass $data ответ от сервера авторизации на запрос токена доступа пользователя
     */
    public function save_supported_user_access_token_fields($data) {
        foreach ($this->supporteduseraccesstokenfields as $field) {
            if (isset($data->{$field})) {
                $this->{'provider_' . $field . '_field'} = $data->{$field};
            }
        }
    }
    
    /**
     * Сохранить токен с привязкой к пользователю
     * @param int $linkid идентификатор связи пользователя с аккаунтом в соцсети
     */
    public function cache_user_access_token($linkid)
    {
        $this->useraccesstoken->linkid = $linkid;
        $cache = cache::make_from_params(cache_store::MODE_SESSION, 'auth_otoauth', 'user_access_tokens');
        $cache->set($linkid, json_encode($this->useraccesstoken));
    }
    
    /**
     * Сформировать параметры, необходимые для получения токена доступа пользователя
     * @return array
     */
    public function build_params()
    {
        return [
            'client_id' => $this->authconfig->{$this->get_name() . 'clientid'},
            'client_secret' => $this->authconfig->{$this->get_name() . 'clientsecret'},
            'redirect_uri' => $this->get_redirect_url(),
            'grant_type' => 'authorization_code'
        ];
    }
    
    /**
     * Получить информацию о пользователе. Метод должен быть определен в дочерних классах.
     * @param string $accesstoken
     */
    abstract public function get_user_info($accesstoken);
    
    /**
     * Получить урл для запроса информации о пользователе
     * @return string
     */
    public function get_user_info_url()
    {
        return $this->userurl;
    }
    
    /**
     * Получить тип доступа
     * @return string
     */
    public function get_access_type()
    {
        return $this->accesstype;
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
        
        // Добавление стандартных настроек "идентификатор" и "ключ" для сервиса
        $enablename = $this->get_name() . 'enable';
        $clientidname = $this->get_name() . 'clientid';
        $clientsecretname = $this->get_name() . 'clientsecret';
        
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
        
        $name = 'auth_otoauth/' . $clientsecretname;
        $visiblename = get_string('settings_' . $clientsecretname . '_label', 'auth_otoauth');
        $description = get_string('settings_' . $clientsecretname, 'auth_otoauth', [
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
        return;
    }
    
    /**
     * Получить урл-перенаправления для формирования прокси-ссылки на страницу получения кода авторизации
     * @param string $wantsurl The relative url fragment the user wants to get to.
     * @return moodle_url
     */
    public function build_redirect_url($wantsurl = '')
    {
        $params = ['provider' => $this->get_name()];
        if (!empty($wantsurl)) {
            $params['wantsurl'] = $wantsurl;
        }
        return new moodle_url('/auth/otoauth/enter.php', $params);
    }
    
    /**
     * Перенаправить пользователя на страницу получения кода авторизации
     * @param int $popup флаг открытия страницы авторизации соцсети в новом всплывающем окне браузера (в виде popup)
     */
    public function redirect($popup = 0)
    {
        $url = $this->build_url($popup);
        if ($url) {
            redirect($url);
        } else {
            // Логин сам отредиректит куда надо, если че
            redirect('/login/index.php', get_string('error_build_url_fail', 'auth_otoauth'), null, notification::NOTIFY_ERROR);
        }
    }
    
    /**
     * Процесс завершения авторизации пользователя 
     * @param string $wantsurl урл, на который нужно перенаправить пользователя после успешной авторизации
     */
    public function complete_user_auth($wantsurl) {
        if (!empty($this->authconfig->{$this->get_name() . 'displaypopup'})) {
            $url = new moodle_url('/auth/otoauth/popupcloser.php', ['wantsurl' => $wantsurl]);
            redirect($url);
        } else {
            redirect($wantsurl);
        }
    }
    
    /**
     * Подключение js-обработчика для открытия popup окна авторизации
     */
    public function call_popup_js() {
        global $PAGE;
        $PAGE->requires->js_call_amd('auth_otoauth/displaypopup', 'init', [$this->get_name()]);
    }
    
    /**
     * Функция для выполнения curl-запроса с настройками провайдера
     *
     * @param string $url - адрес для запроса
     * @param array $params - массив параметров, которые необходимо передать в запросе
     * @param string $method - тип запроса (get, post)
     * @param array $curloptions - опции curl
     *
     * @return обработанный результат запроса
     */
    public function request($url, $params, $method = 'post', $curloptions = []) {
        global $CFG;
        
        $curl = new curl();
        switch($method)
        {
            case 'get':
                $response = $curl->get($url, $params, $curloptions);
                break;
            case 'post':
            default:
                $response = $curl->post($url, http_build_query($params, null, '&'), $curloptions);
                break;
        }
        
        // В случае, если включена отладка уровня Разработчик, логируем данные запросов
        if (!empty($CFG->debugdeveloper)) {
            // Логирование отправки запроса
            $otherdata = [
                'provider' => $this->get_name(),
                'url' => $url,
                'params' => $params,
                'response' => (array)$response
            ];
            $eventdata = [
                'other' => $otherdata
            ];
            $event = \auth_otoauth\event\request_sent::create($eventdata);
            $event->trigger();
        }
        return $response;
    }
    
    /**
     * Обработать ответ в соответствии с нужным форматом и вернуть данные
     * @param mixed $response полученный ответ
     * @param string $responsetype ожидаемый формат данных
     */
    public function extract_response($response, $responsetype = 'json') {
        switch ($responsetype) {
            case 'plain':
                return $response;
                break;
            case 'querystring':
                parse_str($response, $returnvalues);
                return json_decode(json_encode($returnvalues));
                break;
            case 'json':
            default:
                return json_decode($response);
                break;
                /**
                 * @todo добавить обработку других возможных вариантов
                 */
        }
    }
    
    /**
     * Сформировать объект пользователя по полученным данным из соцсети
     * Метод должен быть определен в дочерних классах
     * @param mixed $data
     * @return stdClass объект с данными пользователя для сохранения
     */
    public abstract function build_user($data);
    
    /**
     * Получить кнопку для авторизации во внешней системе
     * @param string $wantsurl The relative url fragment the user wants to get to.
     * @return array кнопка в заданном формате
     */
    public function loginpage_idp($wantsurl = '') {
        return [
            'url' => $this->build_redirect_url($wantsurl),
            'icon' => new \pix_icon(
                $this->get_name(),
                '',
                'auth_otoauth',
                [
                    'class' => 'oauthicon ' . $this->get_name(),
                ]
            ),
            'name' => $this->get_name(),
            'iconclass' => 'oauthicon ' . $this->get_name(),
            'localname' => get_string('provider_' . $this->get_name(), 'auth_otoauth'),
            'data-attr' => 'data-provider=' . $this->get_name()
        ];
    }
    
    /**
     * Разрешена ли регистрация аккаунтов через провайдер
     * По умолчанию используется настройка, если настройка не задана, то регистрация разрешена
     * @return int (0|1|2 - запрещена|разрешена без дубликации емейлов|разрешена с созданием пустых емейлов при дублировании)
     */
    public function allow_register() {
        return isset($this->authconfig->allowregister) ? $this->authconfig->allowregister : 1;
    }
    
    /**
     * Получить урл-адрес для отправки запроса на сброс авторизации
     * @return string
     */
    public function get_revoke_url() {
        return $this->revokeurl;
    }
    
    public function get_revoke_params() {
        return [];
    }
    
    public function get_revoke_requesttype() {
        return $this->get_default_requesttype();
    }
    
    public function get_revoke_responsetype() {
        return $this->get_default_responsetype();
    }
    
    public function get_revoke_curl_options() {
        $this->get_default_curl_options();
    }
    
    /**
     * Сбросить авторизацию пользователя в соцсети
     * @param string $code код авторизации 
     * @return mixed
     */
    public function user_signout($code) {
        $url = $this->get_revoke_url();
        $params = $this->get_revoke_params();
        $params['code'] = $code;
        $requesttype = $this->get_revoke_requesttype();
        $curloptions = $this->get_revoke_curl_options();
        
        $response = $this->request($url, $params, $requesttype, $curloptions);
        return $this->extract_response($response, $this->$this->get_revoke_responsetype());
    }
    
    public function get_default_responsetype() {
        return self::DEFAULT_RESPONSE;
    }
    
    public function get_default_requesttype() {
        return self::DEFAULT_REQUEST;
    }
    
    public function get_default_curl_options() {
        return self::DEFAULT_CURL_OPTIONS;
    }
    
    /**
     * Отображать окно авторизации в модальном окне
     * @return int
     */
    public function display_popup() {
        return $this->authconfig->{$this->get_name() . 'displaypopup'} ?? 0;
    }
}