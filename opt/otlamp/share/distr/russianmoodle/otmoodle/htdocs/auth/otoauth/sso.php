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
 * Плагин аутентификации OTOAuth. Страница прозрачной аутентификации.
 *
 * @package    auth
 * @subpackage otoauth
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

die;

/**
 * Код для прозрачной авторизации/регистрации через OAuth2.0 [пока не используем]
 * 
 * 1. Получаем провайдера authprovider, через которого будем авторизоваться
 * 2. Получаем state, содержащий необходимые переменные:
 *     ->remoteuserid [например email]
 *     ->time = time()
 *     ->hash = [time + key + remoteuserid]
 *     ->wantsurl [optional] - если определён, то должен произойти в итоге redirect(wantsurl) - в случае правильного хеша
 * 3. Задаём $SESSION->wantsurl, если есть 
 * 4. Делаем curl-запрос (как будто пользователь уже нажал "Авторизоваться через authprovider")
 * 5. redirect(wantsurl)
 * 5. 
 */


require('../../config.php');
require('lib.php');
$authprovider = required_param('authprovider', PARAM_TEXT); //Google can return an error
$state = required_param('state', PARAM_TEXT); //Google can return an error

//$urlparams = array('code' => $code, 'authprovider' => $authprovider);
$urlparams = array('authprovider' => $authprovider);
$stateparams = return_state($state);
// Защита от подделки запросов http://en.wikipedia.org/wiki/Cross-site_request_forgery
if (!isset($stateparams->sesskey)) {
    print_error('invalidsesskey');
}
confirm_sesskey($stateparams->sesskey);

// Тут проверяем всё

// Задаём wantsurl, если есть
if (isset($stateparams->wantsurl)) {
    $urlparams['wantsurl'] = $stateparams->wantsurl; // Куда пользователь хотел попасть
}

// Отправляем пользователя на страницу авторизации [Moodle или OAuth2.0 сервиса (эмулируем клик по кнопке авторизации)]
$loginurl = '/login/index.php';
if (!empty($CFG->alternateloginurl)) {
    $loginurl = $CFG->alternateloginurl;
}
$url = new moodle_url($loginurl, $urlparams);
redirect($url);
