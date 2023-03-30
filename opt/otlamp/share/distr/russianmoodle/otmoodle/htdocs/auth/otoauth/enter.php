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
 * Прокси страница для перенаправления пользователя на страницу получения кода авторизации
 * Необходима для решения проблемы проверки данных в разных сессиях при авторизации через модальное окно авторизации
 *
 * @package    auth
 * @subpackage otoauth
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require('../../config.php');
$authprovider = required_param('provider', PARAM_TEXT);
$popup = optional_param('popup', null, PARAM_INT);
$link = optional_param('link', 0, PARAM_INT);
$secret = optional_param('secret', null, PARAM_RAW_TRIMMED);
$wantsurl = optional_param('wantsurl', null, PARAM_URL);
$plugin = get_auth_plugin('otoauth');
$provider = $plugin->get_provider($authprovider);
if (is_null($popup) && !empty($provider->display_popup())) {
    // Если перешли не с кнопки и настройка включена, то подключаем js-обработчик, открывающий popup окно
    global $PAGE;
    require_once ($CFG->libdir . '/classes/notification.php');
    $PAGE->set_url('/auth/otoauth/enter.php');
    $PAGE->set_context(context_system::instance());
    $PAGE->set_title(get_string('popupenter_title', 'auth_otoauth'));
    $PAGE->set_heading(get_string('popupenter_title', 'auth_otoauth'));
    $provider->call_popup_js();
    echo $OUTPUT->header();
    // Сообщим на странице, что пользователю нужно авторизоваться в popup окне, которое открылось
    \core\notification::info(get_string('popupenter_notification', 'auth_otoauth', get_string('provider_' . $authprovider, 'auth_otoauth')));
    echo $OUTPUT->footer();
} else {
    // Если перешли по кнопке или настройка не включена - идем обычному пути
    $_SESSION['USER']->authprovider = $authprovider;
    if ($link > 0) {
        $_SESSION['USER']->{$provider->get_name() . 'link'} = $link;
    }
    if (!is_null($secret)) {
        $_SESSION['USER']->{$provider->get_name() . 'secret'} = $secret;
    }
    if (!is_null($wantsurl)) {
        $_SESSION['USER']->{$provider->get_name() . 'wantsurl'} = $wantsurl;
    }
    $provider->redirect($popup);
}
