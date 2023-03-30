<?php
// This file is not a part of Moodle - http://moodle.org/
// This is a none core contributed module.
//
// This is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// This is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// The GNU General Public License
// can be see at <http://www.gnu.org/licenses/>.

/**
 * Плагин аутентификации OTOAuth. Общая точка входа после авторизации для всех внешних сервисов.
 *
 * @package    auth
 * @subpackage otoauth
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require('../../config.php');
require_once('lib.php');
require_once ($CFG->libdir . '/classes/notification.php');

global $PAGE;
$PAGE->set_context(null);

// Состояние, должно передаваться по умолчанию всеми OAuth2.0 сервисами
$state = required_param('state', PARAM_TEXT);
if (!empty($_SESSION['USER']->authprovider)) {
    $authprovider = $_SESSION['USER']->authprovider;
} else {
    throw new moodle_exception(get_string('error_invalid_authprovider', 'auth_otoauth'));
}
$authplugin = get_auth_plugin('otoauth');
$provider = $authplugin->get_provider($authprovider);
$stateparams = $provider->check_state($state);

$code = optional_param('code', '', PARAM_TEXT); //Google can return an error
$urlparams = array('code' => $code, 'authprovider' => $authprovider);
if (!empty($state)) {
    if (isset($_SESSION['USER']->{$provider->get_name() . 'link'}) && isset($_SESSION['USER']->{$provider->get_name() . 'secret'})) {
        // Параметры для присоединения аккаунта к существующему
        $urlparams['link'] = $_SESSION['USER']->{$provider->get_name() . 'link'};
        $urlparams['secret'] = $_SESSION['USER']->{$provider->get_name() . 'secret'};
    }
    if (isset($_SESSION['USER']->{$provider->get_name() . 'wantsurl'})) {
        $urlparams['wantsurl'] = $_SESSION['USER']->{$provider->get_name() . 'wantsurl'}; // Куда пользователь хотел попасть
    }
}

if (empty($code)) {
    $authproviderstr = get_string('provider_' . $authprovider, 'auth_otoauth');
    \core\notification::error(get_string('error_authorization_code_not_received', 'auth_otoauth', $authproviderstr));
}

$loginurl = '/login/index.php';
if (!empty($CFG->alternateloginurl)) {
    $loginurl = $CFG->alternateloginurl;
}
if (!empty($CFG->debugdeveloper)) {
    // Логирование полученных данных от сервера авторизации
    $otherdata = [
        'provider' => $provider->get_name(),
        'request' => $_REQUEST,
        'session' => isset($_SESSION['USER']) ? (array)$_SESSION['USER'] : null,
        'checkstate' => (array)$stateparams
    ];
    $eventdata = [
        'other' => $otherdata
    ];
    $event = \auth_otoauth\event\request_received::create($eventdata);
    $event->trigger();
}
$url = new moodle_url($loginurl, $urlparams);
redirect($url);
