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
 * Класс авторизации через Google
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
 * Класс авторизации через Google
 *
 * @package    auth
 * @subpackage otoauth
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class google extends provider
{
    protected $name = 'google';
    
    protected $codeurl = 'https://accounts.google.com/o/oauth2/auth';
    
    protected $scope = [
        'https://www.googleapis.com/auth/userinfo.profile',
        'https://www.googleapis.com/auth/userinfo.email'
    ];
    
    protected $useraccesstokenurl = 'https://accounts.google.com/o/oauth2/token';
    
    protected $userurl = 'https://www.googleapis.com/oauth2/v1/userinfo';
    
    /**
     * Урл-адрес для запроса на сброс авторизации
     * @var string
     */
    protected $revokeurl = 'https://accounts.google.com/o/oauth2/revoke';
    
    public function get_user_info($accesstoken)
    {
        $url = $this->get_user_info_url();
        $params = ['access_token' => $accesstoken, 'alt' => 'json'];
        return $this->request($url, $params, 'get');
    }
    
    public function build_user($data)
    {
        // Получаем пользователя сервиса из json массива
        $userdata = json_decode($data);
        if (empty($userdata)) { // Пользоватеь не получен
            return false;
        }
        $user = new stdClass();
        // Получение email пользователя
        $user->email = '';
        $user->verified = 0;
        if (isset($userdata->email) && ! empty($userdata->email)) { // Email получен из профиля
            $user->email = $userdata->email;
        } else { // Email скрыт
            // Отдельный запрос на email
            $url = 'https://www.googleapis.com/userinfo/email';
            $params = ['access_token' => $this->useraccesstoken->token, 'alt' => 'json'];
            $useremails = $this->request($url, $params, 'get');
            $useremails = json_decode($useremails);
            if (isset($useremails->data->email)) { // Получен email
                $user->email = $useremails->data->email;
            }
            if (isset($useremails->data->email)) { // Подтверждение email
                $user->verified = $useremails->data->isVerified;
            }
        }
    
        // Имя пользователя
        if (! empty($userdata->given_name)) {
            $user->firstname = $userdata->given_name;
        } else {
            $user->firstname = '';
        }
    
        // Фамилия пользователя
        if (! empty($userdata->family_name)) {
            $user->lastname = $userdata->family_name;
        } else {
            $user->lastname = '';
        }
    
        // Язык пользователя
        if (! empty($userdata->locale)) {
            $user->lang = $userdata->locale;
        }
    
        // URL пользователя
        if (! empty($userdata->link)) {
            $user->url = $userdata->link;
        }
    
        // Аватар пользователя
        if (! empty($userdata->picture)) {
            $user->picture = $userdata->picture;
        }
    
        // Подтверждение email
        if (! empty($userdata->verified_email)) {
            $user->verified = $userdata->verified_email;
        }
    
        $user->remoteuserid = $user->email;
        return $user;
    }
}