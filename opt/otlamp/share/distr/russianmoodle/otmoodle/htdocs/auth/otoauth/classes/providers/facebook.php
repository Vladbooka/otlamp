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
 * Класс авторизации через Facebook
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
use Exception;
use moodle_url;

defined('MOODLE_INTERNAL') || die();

/**
 * Класс авторизации через Facebook
 *
 * @package    auth
 * @subpackage otoauth
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class facebook extends provider
{
    protected $checkuseraccesstokenexpiry = true;
    
    protected $name = 'facebook';
    
    protected $useraccesstokenproperties = ['token', 'type', 'expires_in'];
    
    protected $codeurl = 'https://www.facebook.com/dialog/oauth';
    
    protected $scope = ['email'];
    
    protected $useraccesstokenurl = 'https://graph.facebook.com/oauth/access_token';
    
    protected $appaccesstokenurl = 'https://graph.facebook.com/oauth/access_token';
    
    protected $userurl = 'https://graph.facebook.com/me?fields=id,email,first_name,last_name';
    
    private $errorcodes = [458, 460, 463];
    
    /**
     * Проверить действительность маркера доступа пользователя
     * {@inheritDoc}
     * @see \auth_otoauth\provider::check_token_expiry()
     */
    public function check_user_access_token_expiry($userid)
    {
        global $DB;
        if (!empty($this->authconfig->{$this->get_name() . 'checkusertokenexpiry'})) {
            if ($link = $DB->get_record('auth_otoauth', ['userid' => $userid, 'service' => $this->get_name()])) {
                $cache = cache::make_from_params(cache_store::MODE_SESSION, 'auth_otoauth', 'user_access_tokens');
                $useraccesstoken = $cache->get($link->id);
                if ($useraccesstoken !== false && $appaccesstoken = $this->get_app_access_token()) {
                    $useraccesstoken = json_decode($useraccesstoken);
                    $url = 'https://graph.facebook.com/debug_token';
                    $params = [
                        'input_token' => $useraccesstoken->token,
                        'access_token' => $appaccesstoken
                    ];
                    $postreturnvalues = $this->request($url, $params, 'get');
                    $postreturnvalues = json_decode($postreturnvalues);
                    if (property_exists($postreturnvalues->data, 'error')) {
                        if ($postreturnvalues->data->error->code == 190 && in_array($postreturnvalues->data->error->subcode, $this->errorcodes)) {
                            // Пользователь вышел из приложения или изменил свой пароль
                            // Выставляем свойство forceproviderlogout для последующего вывода сообщения пользователю
                            set_user_preference('forceproviderlogout', $this->get_name(), $userid);
                            // И завершаем его сессию в системе
                            require_logout();
                        }
                    }
                }
            }
        }
    }
    
    /**
     * Создает объект с данными, необходимыми для сохранения токена
     * @param stdClass $data данные, полученные при запросе токена
     */
    public function set_user_access_token($data)
    {
        $token = new stdClass();
        if (property_exists($data, 'access_token')) {
            $token->token = $data->access_token;
        }
        if (property_exists($data, 'token_type')) {
            $token->type = $data->token_type;
        }
        if (property_exists($data, 'expires_in')) {
            $token->expires_in = $data->expires_in;
        }
        if ($this->isuseraccesstokenvalid($token)) {
            $this->useraccesstoken = $token;
        } else {
            throw new moodle_exception(get_string('error_recieved_token_data_invalid', 'auth_otoauth'));
        }
    }
    
    public function build_params()
    {
        return [
            'client_id' => $this->authconfig->{$this->get_name() . 'clientid'},
            'client_secret' => $this->authconfig->{$this->get_name() . 'clientsecret'},
            'redirect_uri' => $this->get_redirect_url(),
        ];
    }
    
    public function get_app_access_token()
    {
        $cache = cache::make_from_params(cache_store::MODE_APPLICATION, 'auth_otoauth', 'app_access_tokens');
        $appaccesstoken = $cache->get($this->get_name());
        if ($appaccesstoken === false) {
            $appaccesstoken = null;
            $config = $this->get_auth_config();
            $url = $this->get_app_access_token_url();
            if (! empty($config->{$this->get_name() . 'clientid'}) && ! empty($config->{$this->get_name() . 'clientsecret'})) {
                $params = [
                    'client_id' => $config->{$this->get_name() . 'clientid'},
                    'client_secret' => $config->{$this->get_name() . 'clientsecret'},
                    'grant_type' => 'client_credentials'
                ];
                $postreturnvalues = $this->request($url, $params, 'get');
                $postreturnvalues = json_decode($postreturnvalues);
                if (! empty($postreturnvalues->access_token)) {
                    $appaccesstoken = $postreturnvalues->access_token;
                }
            }
            if (! is_null($appaccesstoken)) {
                $cache->set($this->get_name(), $appaccesstoken);
                return $appaccesstoken;
            } else {
                throw new moodle_exception(get_string('error_app_access_token_not_received', 'auth_otoauth'));
            }
        } else {
            return $appaccesstoken;
        }
    }
    
    public function get_user_info($accesstoken)
    {
        $url = $this->get_user_info_url();
        $params = ['access_token' => $accesstoken];
        return $this->request($url, $params, 'get');
    }
    
    public function build_user($data)
    {
        $userdata = json_decode($data);
        $user = new stdClass();
        $user->email = $userdata->email;
        $user->remoteuserid = $userdata->id;
        // verified упразднено при переходе на graph-api v3.0 - https://developers.facebook.com/docs/graph-api/changelog/version3.0
        $user->verified = 1;
        $user->firstname = $userdata->first_name;
        $user->lastname = $userdata->last_name;
        return $user;
    }
}