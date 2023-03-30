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
 * Класс авторизации через Yandex
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
 * Класс авторизации через Yandex
 *
 * @package    auth
 * @subpackage otoauth
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class yandex extends provider
{
    protected $name = 'yandex';
    
    protected $codeurl = 'https://oauth.yandex.ru/authorize';
    
    protected $scope = [];
    
    protected $useraccesstokenurl = 'https://oauth.yandex.ru/token';
    
    protected $userurl = 'https://login.yandex.ru/info';
    
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
        $params = ['oauth_token' => $accesstoken, 'format' => 'json'];
        return $this->request($url, $params, 'get');
    }
    
    public function build_user($data)
    {
        $userdata = json_decode($data);
    
        if (empty($userdata)) { // Пользоватеь не получен
            return false;
        }
    
        $user = new stdClass();
        // Логин пользователя
        if (isset($userdata->login) && ! empty($userdata->login)) {
            $user->username = $userdata->login;
        }
        
        // Получение email пользователя
        $user->email = '';
        $user->verified = 0;
        if (isset($userdata->default_email) && ! empty($userdata->default_email)) { // Email получен из профиля
            $user->email = $userdata->default_email;
            $user->verified = 1;
        } else { // Email по умолчанию не указан
            if (isset($userdata->emails) && ! empty($userdata->emails)) { // Есть список email адресов пользователя
                if (isset($userdata->emails[0])) { // Возьмем первый
                    $user->email = $userdata->emails[0];
                    $user->verified = 1;
                }
            }
        }
        
        // Имя пользователя
        if (isset($userdata->first_name) && ! empty($userdata->first_name)) {
            $user->firstname = $userdata->first_name;
        } else {
            $user->firstname = '';
        }
        
        // Фамилия пользователя
        if (isset($userdata->last_name) && ! empty($userdata->last_name)) {
            $user->lastname = $userdata->last_name;
        } else {
            $user->lastname = '';
        }
        
        // Аватар пользователя
        if (isset($userdata->default_avatar_id) && ! empty($userdata->default_avatar_id)) {
            $user->picture = 'https://avatars.yandex.net/get-yapic/' . $userdata->default_avatar_id . '/islands-200';
        }
        
        $user->remoteuserid = $user->email;
        return $user;
    }
}