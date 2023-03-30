<?php

/*
 * Удаляет привязку к социальному аккаунту по id и возвращает на главную страницу или wantsurl
 */
require ('../../config.php');
require_sesskey();
$id = required_param('id', PARAM_TEXT); //Google can return an error
$wantsurl = optional_param('wantsurl', '/', PARAM_URL);

$authplugin = get_auth_plugin('otoauth');
$success = $authplugin->disconnect_link($id);
$url = new moodle_url($wantsurl);
$status = get_string('fail', 'install');
if ($success) {
    $status = get_string('success');
    redirect($url);
}
redirect($url, $status, 3);
?>
