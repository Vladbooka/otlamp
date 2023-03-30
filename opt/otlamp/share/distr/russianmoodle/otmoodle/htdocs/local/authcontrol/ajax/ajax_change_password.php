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
 * Панель управления доступом в СДО
 * 
 * Смена пароля по AJAX
 * 
 * @package    local_authcontrol
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_authcontrol;

define('AJAX_SCRIPT', true);

require_once('../../../config.php');
require_once($CFG->dirroot . '/local/authcontrol/lib.php');
require_once($CFG->dirroot . '/user/lib.php');

use context_course;
use coding_exception;

global $DB;

// ПАРАМЕТРЫ ЗАПРОСА
// ID - пользователя
$userid = required_param('userid', PARAM_INT);
// Токен доступа
$token = required_param('token', PARAM_RAW_TRIMMED);
// Токен доступа
$courseid = required_param('courseid', PARAM_RAW_TRIMMED);
// Токен доступа
$newpassword = required_param('newpassword', PARAM_RAW_TRIMMED);
// Требуется авторизация в системе
require_login();

$context = context_course::instance($courseid);
$data = [];
if ( ! has_capability('local/authcontrol:use', $context) || empty($context) )
{
    $data['error'] = get_string('ajax_error_invalid_param', 'local_authcontrol');
} else
{
    // Сравнение подписей запроса
    $currenttoken = md5(sesskey().$userid.$courseid);
    if ( $token !== $currenttoken )
    {// Подписи не совпадают
        $data['error'] = get_string('ajax_error_invalid_token', 'local_authcontrol');
    } else 
    {
        // По ссылке получим статус
        $errmessage = '';
        if ( ! check_password_policy($newpassword, $errmessage) ) 
        {// Не прошел политику паролей
            $data['describe'] = $errmessage;
            $data['complete'] = get_string('ajax_password_fail_password_policy' , 'local_authcontrol');
        } elseif ( user_is_previously_used_password($userid, $newpassword) ) 
        {// Пароль уже использовался пользователем
            $data['complete'] = get_string('ajax_password_used_before', 'local_authcontrol');
        } else 
        {
            // Получим пользователя
            if ( $user = $DB->get_record('user', ['id' => $userid]) )
            {
                // AUTH - плагин для пользователя
                $userauth = get_auth_plugin($user->auth);

                if ( ! $userauth->user_update_password($user, $newpassword))
                {// Ошибка при смене пароля
                    $data['complete'] = get_string('ajax_password_fail', 'local_authcontrol');
                } else 
                {
                    // Добавим в историю паролей
                    user_add_password_history($userid, $newpassword);
                    
                    // Убьем все сесии пользователя
                    local_authcontrol_kill_user_sessions($userid);
                    
                    // Сбрасываем локи аккаунта
                    login_unlock_account($user);
                    try 
                    {
                        unset_user_preference('auth_forcepasswordchange', $user);
                        unset_user_preference('create_password', $user);
                        $data['complete'] = get_string('ajax_password_success', 'local_authcontrol');
                    } catch (coding_exception $e)
                    {
                        $data['complete'] = get_string('ajax_password_fail', 'local_authcontrol');
                    }
                }
            } else 
            {// Не нашли пользователя
                $data['complete'] = get_string('ajax_password_user_not_found', 'local_authcontrol');
            }
        }
        $data['html'] = get_string('form_main_access_reset_password', 'local_authcontrol');
    }
    echo json_encode($data);
}
die;