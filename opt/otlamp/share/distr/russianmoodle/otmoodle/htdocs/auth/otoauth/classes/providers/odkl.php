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
 * Класс авторизации через Одноклассники
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
 * Класс авторизации через Одноклассники
 *
 * @package    auth
 * @subpackage otoauth
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class odkl extends provider
{
    protected $name = 'odkl';
    
    protected $codeurl = 'https://www.odnoklassniki.ru/oauth/authorize';
    
    protected $scope = [];
    
    protected $useraccesstokenurl = 'https://api.odnoklassniki.ru/oauth/token.do';
    
    protected $userurl = 'http://api.odnoklassniki.ru/fb.do';
    
    public function get_user_access_token($code)
    {
        $url = $this->get_user_access_token_url();
        $params = $this->build_params();
        $params['code'] = $code;
        // одноклассники не дружат с пост-запросами через
        // moodle curl поэтому отправляем напрямую
        if (! function_exists('curl_init')) {
            throw new moodle_exception('no_curl_enabled');
        }
        $curl = curl_init($url);
        curl_setopt($curl, CURLOPT_POST, 1);
        $paramsstr = 'code=' . $params['code'] . '&redirect_uri=' . $params['redirect_uri'] . '&grant_type=' . $params['grant_type'] . '&client_id=' . $params['client_id'] . '&client_secret=' . $params['client_secret'];
        curl_setopt($curl, CURLOPT_POSTFIELDS, $paramsstr);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        $response = curl_exec($curl);
        curl_close($curl);
        return $this->extract_response($response, $this->useraccesstokenresponsetype);
    }
    
    public function get_user_info($accesstoken)
    {
        $url = $this->get_user_info_url();
        $params = [
            'access_token' => $accesstoken,
            'application_key' => $this->authconfig->{$this->get_name() . 'clientpublickey'},
            'method' => 'users.getCurrentUser',
            'fields' => 'uid,locale,first_name,last_name,location,pic320min,url_profile,email'
        ];
        $params['sig'] = strtolower(md5('application_key='.$params['application_key'].'fields='.$params['fields'].'method='.$params['method'].md5($params['access_token'].$this->authconfig->odklclientsecret)));
        return $this->request($url, $params, 'get');
    }
    
    public function build_user($data)
    {
        $userdata = json_decode($data);
    
        if (empty($userdata)) { // Пользоватеь не получен
            return false;
        }
    
        $user = new stdClass();
        // Одноклассники не возвращают почту
        $user->email = '';
        $user->remoteuserid = $userdata->uid;
        $user->verified = 1;
        
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
        
        // Язык пользователя
        if ( ! empty($userdata->locale) )
        {
            $user->lang = $userdata->locale;
        }
        
        // Страна пользователя
        if ( ! empty($userdata->location->countryCode) )
        {
            // Получаем страны Moodle
            $countries = get_string_manager()->get_list_of_countries();
            if ( isset($countries[$userdata->location->countryCode]) )
            {// Код валидный
                $user->country = $userdata->location->countryCode;
            }
        }
        
        // Город пользователя
        if ( ! empty($userdata->location->city) )
        {
            $user->city = $userdata->location->city;
        }
        
        // Аватар пользователя
        if ( ! empty($userdata->pic320min)  )
        {
            $user->picture = $userdata->pic320min;
        }
    
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