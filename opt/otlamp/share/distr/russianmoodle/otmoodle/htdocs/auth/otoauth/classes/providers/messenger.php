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
 * Класс авторизации через Messenger
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
 * Класс авторизации через Messenger
 *
 * @package    auth
 * @subpackage otoauth
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class messenger extends provider
{
    protected $name = 'messenger';
    
    protected $codeurl = 'https://oauth.live.com/authorize';
    
    protected $scope = ['wl.basic', 'wl.emails', 'wl.signin'];
    
    protected $useraccesstokenurl = 'https://oauth.live.com/token';
    
    protected $userurl = 'https://apis.live.net/v5.0/me';
    
    public function get_user_access_token($code)
    {
        $url = $this->get_user_access_token_url();
        $params = $this->build_params();
        $params['code'] = $code;
        
        $response = $this->request($url, $params, 'get');
        return $this->extract_response($response, $this->useraccesstokenresponsetype);
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
        
        if (empty($userdata)) { // Пользоватеь не получен
            return false;
        }
        
        $user = new stdClass();
        // Email пользователя
        $user->email = '';
        $user->verified = 0;
        if (isset($userdata->emails->account) && ! empty($userdata->emails->account)) {
            $user->email = $userdata->emails->account;
            $user->verified = 1;
        } elseif (isset($userdata->emails->preferred) && ! empty($userdata->emails->preferred)) {
            $user->email = $userdata->emails->preferred;
            $user->verified = 1;
        } elseif (isset($userdata->emails->personal) && ! empty($userdata->emails->personal)) {
            $user->email = $userdata->emails->personal;
            $user->verified = 1;
        } else {
            $user->email = '';
            $user->verified = 0;
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
        
        $user->remoteuserid = $user->email;
        return $user;
    }
}