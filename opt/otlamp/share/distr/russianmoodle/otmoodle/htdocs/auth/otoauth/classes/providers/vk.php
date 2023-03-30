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
 * Класс авторизации через Vk
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
use admin_setting_configselect;

defined('MOODLE_INTERNAL') || die();

/**
 * Класс авторизации через Vk
 *
 * @package    auth
 * @subpackage otoauth
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class vk extends provider
{
    protected $name = 'vk';
    
    protected $codeurl = 'https://oauth.vk.com/authorize';
    
    protected $scope = ['email'];
    
    protected $useraccesstokenproperties = ['token', 'remoteuserid', 'email'];
    
    protected $useraccesstokenurl = 'https://oauth.vk.com/access_token';
    
    protected $userurl = 'https://api.vk.com/method/users.get';
    
    private $apiversion = '5.102';
    
    public function set_user_access_token($data)
    {
        $token = new stdClass();
        if (property_exists($data, 'access_token')) {
            $token->token = $data->access_token;
        }
        if (property_exists($data, 'user_id')) {
            $token->remoteuserid = $data->user_id;
        }
        if (property_exists($data, 'email')) {
            $token->email = $data->email;
        }
        if ($this->isuseraccesstokenvalid($token)) {
            $this->useraccesstoken = $token;
        } else {
            throw new moodle_exception(get_string('error_recieved_token_data_invalid', 'auth_otoauth'));
        }
    }
    
    public function build_url($popup = 0)
    {
        $url = $this->get_code_url();
        $params = [
            'client_id' => $this->authconfig->{$this->get_name() . 'clientid'},
            'redirect_uri' => $this->get_redirect_url(),
            'response_type' => $this->get_response_type(),
            'state' => $this->get_state(),
            'scope' => $this->get_scope(),
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
            'access_token' => $accesstoken,
            'uids' => $this->useraccesstoken->remoteuserid,
            'fields' => '
                verified,
                uid,
                nickname,
                first_name,
                last_name,
                site,
                connections,
                skype,
                contacts,
                mobile_phone,
                city,
                country,
                photo_max_orig,
                ',
            'v' => $this->apiversion
        ];
        return $this->request($url, $params, 'get');
    }
    
    public function build_user($data)
    {
        $userdata = json_decode($data);
    
        if (empty($userdata)) { // Пользоватеь не получен
            return false;
        }
        
        if ( isset($userdata->response[0]) )
        {
            $userdata = $userdata->response[0];
        } else
        {
            return false;
        }
        $user = new stdClass();
        // Email пользователя
        if ( ! empty($this->useraccesstoken->email) )
        {
            $user->email = $this->useraccesstoken->email;
            // Подтвержденные профили - это профили прошедшие верификацию по определенным правилам - https://vk.com/page-22079806_49614257
            // Т.к. это не большинство людей, мы выставляем флаг по наличию email
            $user->verified = 1;
        }
        // Логин пользователя
        if ( ! empty($userdata->nickname) )
        {
            $user->username = $userdata->nickname;
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
        if ( ! empty($userdata->site) )
        {
            $user->site = $userdata->site;
        }
        
        // Skype пользователя
        if ( ! empty($userdata->skype) )
        {
            $user->skype = $userdata->skype;
        }
        
        // Телефон пользователя
        if ( ! empty($userdata->mobile_phone) )
        {
            $user->phone1 = $userdata->mobile_phone;
        }
        
        // Страна пользователя
        if ( ! empty($userdata->country) )
        {
            // Запрашиваем страну пользователя
            $url = 'https://api.vk.com/method/database.getCountriesById';
            $additional = ['access_token' => $this->useraccesstoken->token, 'country_ids' => $userdata->country];
            $country = $this->request($url, $additional, 'get');
            // Получаем данные сервиса из json массива
            $countryresponce = json_decode($country);
        
            if ( isset($countryresponce->response[0]->name) )
            {// Имя страны получено
                // Получаем страны Moodle
                $countries = get_string_manager()->get_list_of_countries();
                $countrycode = array_search ( $countryresponce->response[0]->name, $countries );
                if ( ! empty($countrycode) )
                {// Код получен
                    $user->country = $countrycode;
                }
            }
        }
        
        // Город пользователя
        if ( ! empty($userdata->city) )
        {
            // Запрашиваем город пользователя
            $url = 'https://api.vk.com/method/database.getCitiesById';
            $additional = ['access_token' => $this->useraccesstoken->token, 'city_ids' => $userdata->city];
            $city = $this->request($url, $additional, 'get');
            // Получаем данные сервиса из json массива
            $cityresponce = json_decode($city);
        
            if ( isset($cityresponce->response[0]->name) )
            {// Имя города получено
                if ( ! empty($cityresponce->response[0]->name) )
                {// Код получен
                    $user->city = $cityresponce->response[0]->name;
                }
            }
        }
        
        // Аватар пользователя
        if ( ! empty($userdata->photo_max_orig)  )
        {
            $user->picture = $userdata->photo_max_orig;
        }
        
        $user->remoteuserid = $user->email;
        return $user;
    }
    
    /**
     * Добавление индивидуальных настроек провайдера. Метод переопределяется в дочерник классах
     * @param admin_settingpage $settings
     */
    public function add_custom_settings(admin_settingpage $settings)
    {
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
}