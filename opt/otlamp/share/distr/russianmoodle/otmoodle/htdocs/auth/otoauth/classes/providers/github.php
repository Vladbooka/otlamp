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
 * Класс авторизации через Github
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
 * Класс авторизации через Github
 *
 * @package    auth
 * @subpackage otoauth
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class github extends provider
{
    protected $name = 'github';
    
    protected $codeurl = 'https://github.com/login/oauth/authorize';
    
    protected $scope = ['user:email'];
    
    protected $useraccesstokenurl = 'https://github.com/login/oauth/access_token';
    
    protected $userurl = 'https://api.github.com/user';
    
    protected $useraccesstokenresponsetype = 'querystring';
    
    public function get_user_info($accesstoken)
    {
        $url = $this->get_user_info_url();
        $params = ['access_token' => $accesstoken];
        return $this->request($url, $params, 'get');
    }
    
    public function get_user_info_url()
    {
        return $this->userurl;
    }
    
    public function build_user($data)
    {
        // Получаем пользователя сервиса из json массива
        $userdata = json_decode($data);
        if (empty($userdata)) { // Пользоватеь не получен
            return false;
        }
        
        $user = new stdClass();
        // Получение username пользователя
        if (isset($userdata->login) && ! empty($userdata->login)) { // Username получен из профиля
            $user->username = $userdata->login;
        }
        
        // Получение email пользователя
        $user->email = '';
        $user->verified = 0;
        if (isset($userdata->email) && ! empty($userdata->email)) { // Email получен из профиля
            $user->email = $userdata->email;
            $user->verified = 1;
        } else { // Email скрыт, необходим отдельный запрос
            // Получаем все email пользователя
            $url = 'https://api.github.com/user/emails';
            $params = ['access_token' => $this->useraccesstoken->token];
            $useremails = $this->request($url, $params, 'get');
            $useremails = json_decode($useremails);
            if (! empty($useremails)) { // Адреса получены
                foreach ($useremails as $useremail) { // Ищем первичный email
                    if ($useremail->primary == 1) { // Адрес первичный
                        $user->email = $useremail->email;
                        if (isset($useremail->verified)) { // Перенесем данные подтверждения email
                            $user->verified = $useremail->verified;
                        }
                    }
                }
            }
        }
        
        // Получение имени пользователя
        if (isset($userdata->name) && ! empty($userdata->name)) { // Имя получено из профиля
          // Разбиваем имя
            $githubusername = explode(' ', $userdata->name, 2);
            if (isset($githubusername[0]) && ! empty($githubusername[0])) { // Имя есть
                $user->firstname = $githubusername[0];
            } else { // Имени нет
                $user->firstname = '';
            }
            if (isset($githubusername[1]) && ! empty($githubusername[1])) { // Фамилия есть
                $user->lastname = $githubusername[1];
            } else { // Фамилии нет
                $user->lastname = '';
            }
        }
        
        // Получение url пользователя
        if (isset($userdata->blog) && ! empty($userdata->blog)) { // Url получен из профиля
            $user->url = $userdata->blog;
        } else {
            if (isset($userdata->html_url) && ! empty($userdata->html_url)) { // Установим url профиля github
                $user->url = $userdata->html_url;
            } else {
                $user->url = '';
            }
        }
        
        // Получение местоположения пользователя
        if (isset($userdata->location) && ! empty($userdata->location)) { // Данные получены
            $user->city = $userdata->location;
        }
        
        // Получение аватара пользователя
        if (isset($userdata->avatar_url) && ! empty($userdata->avatar_url)) { // Данные получены
            $user->picture = $userdata->avatar_url;
        }
        
        // Получение компании пользователя
        if (isset($userdata->company) && ! empty($userdata->company)) { // Данные получены
            $user->institution = $userdata->company;
        }
        
        $user->remoteuserid = $user->email;
        return $user;
    }
}