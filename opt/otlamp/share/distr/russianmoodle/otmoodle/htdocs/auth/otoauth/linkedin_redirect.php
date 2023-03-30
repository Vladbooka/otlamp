<?php

/*
 * Get facebook code and call the normal login page
 * Needed to add the parameter authprovider in order to identify the authentication provider
 */
require('../../config.php');
require_once('lib.php');
require_once($CFG->libdir . '/classes/notification.php');

$authprovider = 'linkedin';
$code = optional_param('code', '', PARAM_TEXT); //Google can return an error
$state = required_param('state', PARAM_TEXT);
$urlparams = array('code' => $code, 'authprovider' => $authprovider);

if (!empty($state)) {
    $stateparams = return_state($state);
    // Защита от подделки запросов http://en.wikipedia.org/wiki/Cross-site_request_forgery
    if (!isset($stateparams->sesskey)) {
        print_error('invalidsesskey');
    }
    confirm_sesskey($stateparams->sesskey);
    if (isset($stateparams->link) && isset($stateparams->secret)) {
        // Параметры для присоединения аккаунта к существующему
        $urlparams['link'] = $stateparams->link;
        $urlparams['secret'] = $stateparams->secret;
    }
    if (isset($stateparams->wantsurl)) {
        $urlparams['wantsurl'] = $stateparams->wantsurl; // Куда пользователь хотел попасть
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
$url = new moodle_url($loginurl, $urlparams);
redirect($url);
