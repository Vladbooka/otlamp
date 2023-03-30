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
 * Класс авторизации через кастомный провайдер
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
use context_system;
use core\notification;
use \auth_otoauth\customprovider;

defined('MOODLE_INTERNAL') || die();

class custom extends provider
{
    protected $name = '';
    private $refreshtoken;
    protected $revokeurl;
    protected $useraccesstoken = null;

    /**
     * Установить имя провайдера
     * @param string $name
     */
    public function set_name($name) {
        if (strpos($name, 'cp_') === 0) {
            // Имя передали уже с префиксом
            $this->name = $name;
        } else {
            // Передали без префикса - добавим его сами
            $this->name = 'cp_' . $name;
        }
    }

    /**
     * Устанавливает конфиг провайдера
     * {@inheritDoc}
     * @see \auth_otoauth\provider::set_config()
     */
    public function set_config($config = null) {
        global $CFG;
        if (is_null($config)) {
            // файлом можно переопределить конфиг, заданный через интерфейс
            if (file_exists($CFG->dataroot . '/plugins/auth_otoauth/custom.php')) {
                //имеется файл с настройками кастомных провайдеров
                include($CFG->dataroot . '/plugins/auth_otoauth/custom.php');
                if (!empty($customproviders)) {
                    if (array_key_exists(substr($this->get_name(), 3), $customproviders)) {
                        //имеется переопределение в moodledata - берем конфиг оттуда
                        $this->config = $customproviders[substr($this->get_name(), 3)];
                    }
                }
            }
            if (is_null($this->config)) {
                // если конфиг не переопределен, берем конфиг из yaml-разметки
                $this->config = customprovider::parse_config($this->get_yaml());
            }
        } else {
            $this->config = $config;
        }
    }

    public function get_yaml() {
        global $DB;
        if (!empty($this->yaml)) {
            // Если уже получали конфиг - вернем его
            return $this->yaml;
        }
        // Если не получали - получим его из базы
        if ($cprecord = $DB->get_record('auth_otoauth_custom_provider', ['code' => substr($this->get_name(), 3)])) {
            $this->yaml = $cprecord->config;
        }
        return $this->yaml;
    }

    /**
     * Получить clien_id
     * @return string
     */
    public function get_client_id() {
        return $this->config['clientid'];
    }

    /**
     * Получить client_secret
     * @return string
     */
    public function get_client_secret() {
        return $this->config['clientsecret'];
    }

    /**
     * Сформировать урл-адрес для получения кода, необходимого для получения токена доступа пользователя
     * @param int $popup флаг открытия страницы авторизации соцсети в новом всплывающем окне браузера (в виде popup)
     * @return moodle_url|false если использование провайдера выключено
     */
    public function build_url($popup = 0) {
        $url = $this->get_code_url();
        $params = $this->replace_template_values($this->config['authorize']['parameters']);
        return new moodle_url($url, $params);
    }

    /**
     * Получить базовый урл без параметров для получения кода, необходимого для получения токена доступа пользователя
     * @return string
     */
    public function get_code_url() {
        return $this->config['authorize']['url'];
    }

    /**
     * Получить урл для запроса токена доступа пользователя
     * @return string
     */
    public function get_user_access_token_url() {
        return $this->config['accesstoken']['url'];
    }

    /**
     *
     * {@inheritDoc}
     * @see \auth_otoauth\provider::build_params()
     */
    public function build_params() {
        $params = [
            'client_id' => $this->get_client_id(),
            'client_secret' => $this->get_client_secret(),
            'redirect_uri' => $this->get_redirect_url(),
            'grant_type' => $this->config['accesstoken']['parameters']['grant_type']
        ];
        if (!empty($this->config['accesstoken']['parameters']['scope'])) {
            $params['scope'] = $this->config['accesstoken']['parameters']['scope'];
        }
        return $params;
    }

    /**
     * Установить токена доступа пользователя в переменную для дальнейшего использования
     * @param stdClass $data
     * @throws moodle_exception
     */
    public function set_user_access_token($data) {
        $token = new stdClass();
        if (property_exists($data, $this->config['accesstoken']['responsefields']['token'])) {
            $token->token = $data->{$this->config['accesstoken']['responsefields']['token']};
        }
        if ($this->isuseraccesstokenvalid($token)) {
            $this->useraccesstoken = $token;
        } else {
            throw new moodle_exception(get_string('error_recieved_token_data_invalid', 'auth_otoauth'));
        }
    }

    /**
     * Проверяет валиден ли объект токена, чтобы его можно было сохранить и использовать
     * @param stdClass $token
     * @return boolean
     */
    protected function isuseraccesstokenvalid($token) {
        $result = true;
        $useraccesstokenproperties = $this->config['accesstoken']['tokenproperties'] ?? [];
        foreach ($useraccesstokenproperties as $prop) {
            if (property_exists($token, $prop) && ! empty($token->$prop)) {
                $result = $result && true;
            } else {
                $result = false;
                break;
            }
        }
        return $result;
    }

    public function get_user_info($accesstoken) {
        $user = new stdClass();
        foreach ($this->config['userinfo'] as $uir) {
            //в параметры запроса подставляем полученный токен в нужное поле
            $params = $this->replace_template_values($uir['parameters'], ['{access_token}' => $accesstoken]);
            //посылаем запрос
            $response = $this->request($uir['url'], $params, $uir['requesttype'], $uir['curloptions']);
            $userdata = $this->extract_response($response, $uir['responsetype']);
            //результаты запроса собираем в нужные параметры объекта
            $this->collect_response_data($user, $userdata, $uir['responsefields']);
        }
        //пока сохраним email
        $user->remoteuserid = $user->email;
        return $user;
    }

    /**
     * Сформировать объект пользователя по полученным данным из соцсети
     * @param mixed $data
     * @return stdClass объект с данными пользователя для сохранения
     */
    public function build_user($data) {
        // Объект пользователя уже собран на этапе get_user_info, просто вернем его
        return $data;
    }

    /**
     * The returned value is expected to be associative array with
     * string keys:
     *
     * - url => (moodle_url|string) URL of the page to send the user to for authentication
     * - name => (string) Human readable name of the IdP
     * - iconurl => (moodle_url|string) URL of the icon representing the IdP (since Moodle 3.3)
     *
     * @param string $wantsurl The relative url fragment the user wants to get to.
     * @return array Associative array with keys url, name, iconurl|icon
     */
    public function loginpage_idp($wantsurl = '') {
        //возвращаем сформированный массив требующийся для отображения кнопки
        return [
            'url' => $this->build_redirect_url($wantsurl),
            'icon' => new \pix_icon($this->get_name(), '', 'auth_otoauth', [
                'class' => 'oauthicon ' . $this->get_name(),
                'style' => 'background-image: url(' . $this->get_icon() . '); background-size: contain;'
            ]),
            'name' => '',
            'iconclass' => 'oauthicon ' . $this->get_name(),
            'iconstyle' => 'background-image: url(' . $this->get_icon() . '); background-size: contain;'
        ];
    }

    /**
     * Получить имя провайдера
     * @return string
     */
    public function get_name() {
        if (strpos($this->name, 'cp_') === 0) {
            return $this->name;
        } else {
            return 'cp_' . $this->name;
        }
    }


    /**
     * Получить токен доступа к сервису
     *
     * Производит авторизацию в сервисе с результатом в виде токена,
     * который используется при любом последующем обращении системы к сервису
     *
     * @param string $authorizationcode - код авторизации, полученный от провайдера
     * @return обработанный результат запроса к провайдеру на получение токена
     */
    public function get_user_access_token($code)
    {
        $url = $this->get_user_access_token_url();
        $params = $this->build_params();
        $params['code'] = $code;

        $response = $this->request($url, $params, $this->config['accesstoken']['requesttype'], $this->config['accesstoken']['curloptions']);
        return $this->extract_response($response, $this->config['accesstoken']['responsetype']);
    }

    /**
     * Дополняет объект1 (результат) данными из объекта2 (ответ от провайдера) при соответствии шаблону
     *
     * Например, в ответе от провайдера (объект2) имеется свойство given_name,
     * нужное в результирующем объекте, но под названием firstname.
     * Шаблон должен содержать в массиве пару 'firstname' => 'given_name'
     *
     * @param stdClass $object - объект, в который требуется внести найденные значения, соответствующие шаблону
     * @param stdClass $data - объект, в котором выполняется поиск шаблонных значений
     * @param array $templatearr - массив шаблонов
     * @return stdClass - измененный объект
     */
    private function collect_response_data($object, $data, $templatearr) {
        $data = json_decode(json_encode($data), true);
        //создаем такой же массив шаблонов, только с обработанными макроподстановками
        $replacedtemplatearr = $this->replace_template_values($templatearr);
        foreach($templatearr as $k=>$v)
        {
            //данные, которые пришли в ответе от сервера
            $value = $data;
            //было ли найдено искомое значение
            $notfound = false;
            $v = (string)$v;
            if ( $v != '' )
            {
                foreach(explode('/',$v) as $par)
                {//разбиваем значение из конфига на параметра
                    if(isset($value[$par]))
                    {//параметр в ответе от сервера существует - берем его
                        $value = $value[$par];
                    } else
                    {//в ответе от сервера ничего похожего на значение заданное в конфиге не найдено
                        $notfound=true;
                    }
                }
            } else
            {
                $notfound = true;
            }
            if($notfound)
            {//берем значение которое было в конфиге (если там была макроподстановка - она применится)
                $object->$k = $replacedtemplatearr[$k];
            } else
            {//берем значение из ответа сервера
                $object->$k = $value;
            }
        }
        return $object;
    }

    /**
     * Замена шаблонных значений в массиве (макроподстановки)
     *
     * @param array $array - массив, в котором выполняется поиск шаблонных значений
     * @param $replaces - массив для замен (ключ - искомая строка, значение - результирующая строка)
     * @return array - измененный массив
     */
    private function replace_template_values($array = [], $replaces = []) {
        foreach($array as $key=>$value)
        {
            $v = strtolower($value);
            switch($v)
            {
                case '{redirect_uri}':
                    $array[$key] = $this->get_redirect_url();
                    break;
                case '{clientid}':
                    $array[$key] = $this->get_client_id();
                    break;
                case '{clientsecret}':
                    $array[$key] = $this->get_client_secret();
                    break;
                case '{state}':
                    //формируем объект, который передадим в state для защиты от CSRF
                    $state = new stdClass();
                    $state->sesskey = sesskey();
                    // Запоминаем провайдера, которого передавали
                    $state->authprovider = $this->get_name();
                    $array[$key] = make_state_string($state);
                    break;
                case '{true}':
                    $array[$key] = true;
                    break;
                case '{false}':
                    $array[$key] = false;
                    break;
                case '{1}':
                    $array[$key] = 1;
                    break;
                case '{0}':
                    $array[$key] = 0;
                    break;
                case '{provider_email_field}':
                    $array[$key] = $this->provider_email_field;
                    break;
                case '{provider_expires_in_field}':
                    $array[$key] = $this->provider_expires_in_field;
                    break;
                case '{provider_refresh_token_field}':
                    $array[$key] = $this->provider_refresh_token_field;
                    break;
                default:
                    break;
            }
            if(array_key_exists($v, $replaces))
            {
                $array[$key] = $replaces[$v];
            }
        }
        return $array;
    }

    /**
     * Получить урл перенаправления
     * @return string
     */
    public function get_redirect_url() {
        // Для кастомного провайдера всегда отправляем на стандартный урл
        $url = new moodle_url($this->redirecturi);
        return $url->out(false);
    }

    public function get_icon() {
        return $this->config['icon'];
    }

    /**
     * Разрешена ли регистрация аккаунтов через провайдер
     * {@inheritDoc}
     * @see \auth_otoauth\provider::allow_register()
     */
    public function allow_register() {
        return isset($this->config['allowregister']) ? $this->config['allowregister'] : 1;
    }

    /**
     * Подключение js-обработчика для открытия popup окна авторизации
     */
    public function call_popup_js() {
        global $PAGE;
        $PAGE->requires->js_call_amd('auth_otoauth/displaypopup', 'init', [$this->get_name()]);
    }

    /**
     * Процесс завершения авторизации пользователя
     * @param string $wantsurl урл, на который нужно перенаправить пользователя после успешной авторизации
     */
    public function complete_user_auth($wantsurl) {
        redirect($wantsurl);
    }

    /**
     * Получить урл-адрес для отправки запроса на сброс авторизации
     * @return string
     */
    public function get_revoke_url() {
        return $this->config['revoke']['url'] ?? '';
    }

    public function get_revoke_params() {
        return $this->replace_template_values($this->config['revoke']['parameters'] ?? []);
    }

    public function get_revoke_requesttype() {
        return $this->config['revoke']['requesttype'] ?? 'post';
    }

    public function get_revoke_responsetype() {
        return $this->get_default_responsetype();
    }

    public function get_revoke_curl_options() {
        return $this->config['revoke']['curloptions'] ?? [];
    }

    /**
     * Отображать окно авторизации в модальном окне
     * @return int
     */
    public function display_popup() {
        return $this->config['displaypopup'] ?? 0;
    }

    protected function get_popup_param_name() {
        return $this->config['popupparamname'] ?? null;
    }

    protected function get_popup_param_value() {
        return $this->config['popupparamvalue'] ?? null;
    }

    /**
     * Добавить к параметрам построения урл-адреса для получения кода,
     * необходимого для получения токена доступа пользователя,
     * параметры для открытия окна авторизации в соцсети во всплывающем окне в виде popup
     * @param array $params массив параметров
     */
    public function add_popup_params(& $params) {
        $name = $this->get_popup_param_name();
        $value = $this->get_popup_param_value();
        if (!is_null($name) && !is_null($value)) {
            $params[$name] = $value;
        }
    }
}