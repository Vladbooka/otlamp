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
 * Класс авторизации через Linkedin
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
 * Класс авторизации через Linkedin
 *
 * @package    auth
 * @subpackage otoauth
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class linkedin extends provider
{
    protected $name = 'linkedin';
    
    protected $codeurl = 'https://www.linkedin.com/uas/oauth2/authorization';
    
    protected $scope = [
        'r_basicprofile',
        'r_emailaddress'
    ];
    
    protected $useraccesstokenurl = 'https://www.linkedin.com/uas/oauth2/accessToken';
    
    protected $userurl = 'https://api.linkedin.com/v1/people/~:(id,first-name,last-name,email-address,location:(name,country:(code)))';
    
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
        $params = ['oauth2_access_token' => $accesstoken, 'format' => 'json'];
        return $this->request($url, $params, 'get');
    }
    
    public function build_user($data)
    {
        $userdata = json_decode($data);
    
        if (empty($userdata)) { // Пользоватеь не получен
            return false;
        }
        $user = new stdClass();
        $user->email = $userdata->emailAddress;
        $user->remoteuserid = $userdata->id;
        $user->verified = 1;
        $user->firstname = $userdata->firstName;
        $user->lastname = $userdata->lastName;
        $user->country = $userdata->location->country->code;
        $user->city = $userdata->location->name;
        return $user;
    }
}