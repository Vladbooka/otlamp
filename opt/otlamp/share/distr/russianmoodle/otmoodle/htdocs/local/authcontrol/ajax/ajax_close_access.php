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
 * Сброс сессий пользователя по AJAX
 * 
 * @package    local_authcontrol
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_authcontrol;

define('AJAX_SCRIPT', true);

require_once('../../../config.php');
require_once($CFG->dirroot . '/local/authcontrol/lib.php');

use context_course;

// ПАРАМЕТРЫ ЗАПРОСА
// ID - пользователя
$userid = required_param('userid', PARAM_INT);
// Токен доступа
$token = required_param('token', PARAM_RAW_TRIMMED);
// Токен доступа
$courseid = required_param('courseid', PARAM_RAW_TRIMMED);
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
        if ( local_authcontrol_kill_user_sessions($userid) && local_authcontrol_save_access_info($courseid, [$userid], '', 0) )
        {
            $data['complete'] = get_string('ajax_access_success', 'local_authcontrol');
            $data['status'] = get_string('form_main_access_student_off', 'local_authcontrol');
            $data['context'] = get_string('form_main_access_student_area_empty', 'local_authcontrol');
            $data['html'] = get_string('form_main_access_close_access', 'local_authcontrol');
        }
        else 
        {
            $data['complete'] = get_string('ajax_access_fail', 'local_authcontrol');
        }
    }
    echo json_encode($data);
}
die;