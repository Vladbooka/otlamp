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
 * Плагин аутентификации OTOAuth. Библиотека функций плагина.
 *
 * @package    auth
 * @subpackage otoauth
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Получить код кнопки для авторизации в сервисе
 *
 * @param string   $authurl - URL для перехода на страницу авторизации в сервисе
 * @param stdClass $provider - Объект с данными сервиса
 * @param string   $providerdisplaystyle - Дополнительный CSS для стилизации кнопки
 *
 * @return string - HTML код кнопки
 */
function otoauth_html_button($authurl, $provider, $providerdisplaystyle = '')
{
    return '<a class="otoauth_signin" href="' . $authurl . '" style="' . $providerdisplaystyle .'">
                  <div class="social-button ' . $provider->sskstyle . '">' .
                  get_string('signinwithanaccount', 'auth_otoauth', $provider->readablename) .
                  '</div>
            </a>';
}

/**
 * Получить список всех преднастроенных провайдеров
 * @return array массив кодов провайдеров
 */
function otoauth_provider_list()
{
    return [
        'google',
        'google_corporate',
        'yandex',
        'facebook',
        'vk',
        'mailru',
        'odkl',
        'github',
        'linkedin',
        'messenger',
        'esia'
    ];
}

/**
 * Получить список всех кастомных провайдеров
 * @return array массив кодов провайдеров
 */
function otoauth_custom_provider_list() {
    global $CFG;
    $result = [];
    $cp = \auth_otoauth\customprovider::get_custom_providers();
    foreach ($cp as $provider) {
        $result[] = 'cp_' . $provider->name;
    }
    if (file_exists($CFG->dataroot . '/plugins/auth_otoauth/custom.php')) {
        //имеется файл с настройками кастомных провайдеров
        include($CFG->dataroot . '/plugins/auth_otoauth/custom.php');
        if (!empty($customproviders)) {
            foreach ($customproviders as $code => $config) {
                if (strpos('cp_', $code) !== 0) {
                    $code = 'cp_' . $code;
                }
                if (array_search($code, $result) === false) {
                    $result[] = $code;
                }
            }
        }
    }
    return $result;
}

function generate_user_secret($userid)
{
    global $DB;
    
    // Получение объекта плагина
    $authplugin = get_auth_plugin('otoauth');
    // Получение настроек плагина
    $config = $authplugin->get_config();
    
    $user = $DB->get_record('user', ['id' => $userid]);
    
    return sha1($user->id . $user->timecreated . $config->salt);
}

/**
 * Проверка соответствия ключа
 *
 * @param int $userid - идентификатор пользователя
 * @param string $secret - ключ
 * @return boolean
 */
function check_user_secret($userid, $secret)
{
    // проверка подмены запроса
    return generate_user_secret($userid) == $secret;
}

/**
 * Создать строку состояния
 *
 * Для отправки OAuth-сервису и получения её обратно на login/index.php
 *
 * @param array|object $input - Данные для формирования состояния
 *
 * @return string - Строка состояния
 */
function make_state_string($input)
{
    $json = json_encode($input);
    return base64UrlEncode($json);
}

/**
 * Получить из параметры строки состояния
 *
 * Для отправки OAuth-сервису и получения обратно
 *
 * @param string $statestring - Строка состояния
 *
 * @return object - Объект с данными
 */
function return_state($statestring)
{
    $json = base64UrlDecode($statestring);
    return json_decode($json);
}

/**
 * Закодировать строку в base64, заменяя символы для безопасной передачи через URL
 *
 * @param string $inputstr
 *
 * @return string
 */
function base64UrlEncode($inputstr)
{
    return strtr(base64_encode($inputstr), '+/=', '-_,');
}

/**
 * Декодировать из безопасного кодирования строки в base64
 *
 * @param string $inputstr
 *
 * @return string
 */
function base64UrlDecode($inputstr)
{
    return base64_decode(strtr($inputstr, '-_,', '+/='));
}

/**
 * Составление шаблона, генерация и запись в базу данных пароля
 * и отправление шаблона на адрес электронной почты пользователя.
 *
 * @param int $userid - id пользователя в таблице user
 * @return boolean - результат выполнения
 */
function auth_otoauth_send_confirmation_email($userid) {
    global $DB, $CFG;
    
    // Получение объекта плагина
    $authplugin = get_auth_plugin('otoauth');
    // Получение настроек плагина
    $config = $authplugin->get_config();

    $user = $DB->get_record('user', array('id' => $userid));
    $site = get_site();
    $supportuser = core_user::get_support_user();
    
    $data            = new stdClass();
    $data->firstname = fullname($user);
    $data->sitename  = format_string($site->fullname);
    $data->admin     = generate_email_signoff();
    $data->username  = $user->username;
    $data->password  = generate_password();

    $subject = get_string('emailconfirmationsubject', '', format_string($site->fullname));
    if( $config->requireconfirm )
    {
        // В случае, если мы присоединяем учётную запись
        $message = get_string('emaillinkconfirmation', 'auth_otoauth', $data);
    } else
    {
        // Просто письмо с логином и паролем
        $message = get_string('emailnoconfirm', 'auth_otoauth', $data);
    }
    // Если пользователь не подтверждён и требуется подтверждение
    if( $user->confirmed == 0 && $config->requireconfirm )
    {
        // кодируем строку для data так: $uid/$hash
        // $hash = sha1($uid . $datacreate);
        $confirmstring = $user->id . '/' . generate_user_secret($user->id);
        // затем кодируем в base64_encode и обёртываем в urlencode (из-за знаков '+/=')
        $datastring = urlencode(base64_encode($confirmstring));
        $data->link = $CFG->wwwroot .'/auth/otoauth/confirm.php?data='. $datastring;
        $message .= get_string('toaccept', 'auth_otoauth', $data);
    }
    $message .= get_string('signature', 'auth_otoauth', $data);
    $messagehtml = text_to_html($message, false, false, true);
    $user->mailformat = 1;  // Always send HTML version as well
    $result = update_password($user, $data->password);
    
    if ($result) {
        // directly email rather than using the messaging system to ensure its not routed to a popup or jabber
        $result = email_to_user($user, $supportuser, $subject, $message, $messagehtml);
    }
    
    return $result;
}

/**
 * Изменяет пароль пользователя на передаваемый
 *
 * @param stdClass $user - объект пользователя
 * @param string $newpassword - новый пароль.
 * @return boolean - результат выполнения.
 */
function update_password($user, $newpassword) {
    if (!empty($newpassword)) {
        return update_internal_user_password($user, $newpassword);
    }
    return false;
}

/**
 * Сгенерировать уникальный логин для нового пользователя
 *
 * @return string - Новый уникальный username
 */
function otoauth_generate_login()
{
    global $DB;
    
    // Получение объекта плагина
    $authplugin = get_auth_plugin('otoauth');
    // Получение настроек плагина
    $config = $authplugin->get_config();

    // Получение префикса
    $prefix = core_text::strtolower($config->googleuserprefix);
    
    // Получение счетчика пользователей
    $lastusernumber = $config->lastusernumber;
    $lastusernumber = empty($lastusernumber) ? 1 : $lastusernumber++;
    
    // Формирование логина
    $username = (string)$prefix.$lastusernumber;
    
    // Проверка наличия логина в системе
    $exist = $DB->get_record('user', ['username' => $username]);
    
    while ( ! empty($exist) )
    {
        // Следующий логин
        $lastusernumber++;
        // Формирование логина
        $username = (string)$prefix.$lastusernumber;
        // Проверка наличия логина в системе
        $exist = $DB->get_record('user', ['username' => $username]);
    }
    // Сохранение текущего счетчика
    set_config('lastusernumber', $lastusernumber, 'auth_otoauth');

    return $username;
}

/**
 * Получить имя сервиса
 *
 * @param string $providercode - Код сервиса
 *
 * @return string - Имя сервиса
 */
function otoauth_provider_name($providercode)
{
    $strcode = 'provider_' . $providercode;
    $name = '';
    if ( get_string_manager()->string_exists($strcode, 'auth_otoauth') )
    {
        $name = get_string($strcode, 'auth_otoauth');
    }
    return $name;
}

function auth_otoauth_before_http_headers()
{
    global $USER;
    // Проверяем действительность маркера доступа пользователя
    if (! \core\session\manager::is_loggedinas() && ! isguestuser() && $USER->id > 0) { // Проверяем, если мы не гость и не зашли под другим пользователем
        try {
            $plugin = get_auth_plugin('otoauth');
            $list = $plugin->get_active_providers_list();
            foreach ($list as $name) {
                $provider = $plugin->get_provider($name);
                $provider->check_user_access_token_expiry($USER->id);
            }
        } catch (Exception $e) {
            /**
             * @todo добавить логирование ошибок
             */
        }
    }
}

function auth_otoauth_pluginfile($course, $cm, $context, $filearea, $args, $forcedownload, array $options = array())
{
    $itemid = array_shift($args);

    $filename = array_pop($args); // The last item in the $args array.
    if (!$args) {
        $filepath = '/'; // $args is empty => the path is '/'
    } else {
        $filepath = '/'.implode('/', $args).'/'; // $args contains elements of the filepath
    }

    $fs = get_file_storage();
    $file = $fs->get_file($context->id, 'auth_otoauth', $filearea, $itemid, $filepath, $filename);

    if (!$file) {
        return false; // The file does not exist.
    }

    \core\session\manager::write_close();
    send_stored_file($file, null, 0, $forcedownload, $options);
}
