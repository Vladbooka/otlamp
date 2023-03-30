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
 * Класс авторизации через Mail.ru
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
use admin_settingpage;
use admin_setting_configtext;
use admin_setting_configselect;

defined('MOODLE_INTERNAL') || die();

/**
 * Класс авторизации через Mail.ru
 *
 * @package    auth
 * @subpackage otoauth
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class mailru extends provider
{
    protected $name = 'mailru';
    
    protected $codeurl = 'https://connect.mail.ru/oauth/authorize';
    
    protected $scope = [];
    
    protected $useraccesstokenurl = 'https://connect.mail.ru/oauth/token';
    
    protected $userurl = 'https://www.appsmail.ru/platform/api';
    
    public function build_url($popup = 0)
    {
        $url = $this->get_code_url();
        $params = [
            'client_id' => $this->authconfig->{$this->get_name() . 'clientid'},
            'redirect_uri' => $this->get_redirect_url(),
            'response_type' => $this->get_response_type(),
            'state' => $this->get_state()
        ];
        
        if (!empty($popup)) {
            $this->add_popup_params($params);
        }
    
        return new moodle_url($url, $params);
    }
    
    public function get_user_info($accesstoken)
    {
        $url = $this->get_user_info_url();
        $params = [
            'session_key' => $accesstoken,
            'method' => 'users.getInfo',
            'app_id' => $this->authconfig->{$this->get_name() . 'clientid'},
            'format' => 'json',
            'secure' => 1 // Используем безопасную схему "сервер-сервер"
        ];
        // Строка параметров для вычисления подписи
        $paramssigstr = '';
        // Отсортируем параметры по ключу
        ksort($params);
        foreach ( $params as $key => $value )
        {
            $paramssigstr .= "$key=$value";
        }
        // Подпись запроса
        $params['sig'] = md5($paramssigstr . $this->authconfig->{$this->get_name() . 'clientsecret'});
        return $this->request($url, $params, 'get');
    }
    
    public function build_user($data)
    {
        $userdata = current(json_decode($data));
    
        if (empty($userdata)) { // Пользоватеь не получен
            return false;
        }
    
        $user = new stdClass();
        // Email пользователя
        if ( ! empty($userdata->email) )
        {
            $user->email = $userdata->email;
            $user->verified = 1;
        } else
        {
            $user->email = '';
            $user->verified = 0;
        }
        
        // Имя пользователя
        if ( ! empty($userdata->first_name) )
        {
            $user->firstname = $userdata->first_name;
        } else
        {
            $user->firstname = '';
        }
        // Фамилия пользователя
        if ( ! empty($userdata->last_name) )
        {
            $user->lastname = $userdata->last_name;
        } else
        {
            $user->lastname = '';
        }
        
        // URL пользователя
        if ( ! empty($userdata->link) )
        {
            $user->site = $userdata->link;
        }
        
        // Страна пользователя
        if ( ! empty($userdata->location->country->name) )
        {
            // Получаем страны Moodle
            $countries = get_string_manager()->get_list_of_countries();
            $countrycode = array_search ( $userdata->location->country->name, $countries );
            if ( ! empty($countrycode) )
            {// Код получен
                $user->country = $countrycode;
            }
        }
        
        // Город пользователя
        if ( ! empty($userdata->location->city->name) )
        {
            $user->city = $userdata->location->city->name;
        }
        
        // Аватар пользователя
        if ( ! empty($userdata->pic_big)  )
        {
            $user->picture = $userdata->pic_big;
        }
        
        $user->remoteuserid = $user->email;
    
        return $user;
    }
    
    public function add_custom_settings(admin_settingpage $settings)
    {
        global $CFG;
        $baseurl = parse_url($CFG->wwwroot);
        // Получение объекта плагина
        $authplugin = get_auth_plugin('otoauth');
        
        // Добавление публичного ключа
        $name = 'auth_otoauth/' . $this->get_name() . 'clientpublickey';
        $visiblename = get_string('settings_' . $this->get_name() . 'clientpublickey_label', 'auth_otoauth');
        $description = get_string('settings_' . $this->get_name() . 'clientpublickey', 'auth_otoauth', [
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
    }
}