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
 * Блок согласования мастер-курса. Веб-сервисы
 *
 * @package    block_mastercourse
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// We defined the web service functions to install.
$functions = [
    'block_mastercourse_accept_coursedata' => [
        'classname'   => 'block_mastercourse\external',
        'methodname'  => 'accept_coursedata',
        'classpath'   => '',
        'description' => 'Allows to accept current mastercourse version',
        'type'        => 'write',
        'capabilities' => 'block/mastercourse:respond_requests',
        'ajax'        => true,
        'services'    => []
    ],
    'block_mastercourse_decline_coursedata' => [
        'classname'   => 'block_mastercourse\external',
        'methodname'  => 'decline_coursedata',
        'classpath'   => '',
        'description' => 'Allows to decline current mastercourse version',
        'type'        => 'write',
        'capabilities' => 'block/mastercourse:respond_requests',
        'ajax'        => true,
        'services'    => []
    ],
    'block_mastercourse_request_coursedata_verification' => [
        'classname'   => 'block_mastercourse\external',
        'methodname'  => 'request_coursedata_verification',
        'classpath'   => '',
        'description' => 'Allows to request verification of current mastercourse version',
        'type'        => 'write',
        'capabilities' => 'block/mastercourse:request_verification',
        'ajax'        => true,
        'services'    => []
    ],
];

$services = array(
    'Educational portal web service' => array(
        'functions' => [
            'core_user_get_users_by_field',
            'auth_dof_create_user',
            'core_user_get_user_preferences',
            'auth_userkey_request_login_url'
        ],
        'restrictedusers' => 1,
        'enabled' => 1,
    )
);