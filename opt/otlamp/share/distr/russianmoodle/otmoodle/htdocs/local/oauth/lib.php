<?php

ini_set('arg_separator.output', '&');

function oauth_get_server() {
    global $CFG;

    // Autoloading (composer is preferred, but for this example let's just do this)
    require_once($CFG->dirroot.'/local/oauth/OAuth2/Autoloader.php');
    OAuth2\Autoloader::register();

    $storage = new OAuth2\Storage\Moodle(array());

    // Pass a storage object or array of storage objects to the OAuth2 server class
    $server = new OAuth2\Server($storage);
    $server->setConfig('enforce_state', false);

    // Add the "Client Credentials" grant type (it is the simplest of the grant types)
    $server->addGrantType(new OAuth2\GrantType\ClientCredentials($storage));

    // Add the "Authorization Code" grant type (this is where the oauth magic happens)
    $server->addGrantType(new OAuth2\GrantType\AuthorizationCode($storage));

    return $server;
}

function get_authorization_from_form($url, $clientid, $scopes = false) {
    global $CFG, $OUTPUT, $USER;
    require_once("{$CFG->libdir}/formslib.php");
    require_once('forms.php');

    if (!$scopes) {
        $scopes = 'login';
    }
    $scopesarr = explode(' ', $scopes);
    
    if (is_scopes_authorized_by_user($USER->id, $clientid, $scopesarr))
    {
        return true;
    }

    $mform = new local_oauth_authorize_form($url);
    if ($mform->is_cancelled())
    {
        return false;
    } else if ($mform->get_data() and confirm_sesskey())
    {
        authorize_user_scopes($USER->id, $clientid, $scopesarr);
        return true;
    }

    echo $OUTPUT->header();
    $mform->display();
    echo $OUTPUT->footer();
    die();
}

/**
 * Проверка, одобрены ли запрошенные области доступа
 *
 * @param int $userid - идентификатор пользователя
 * @param string $clientid - идентификатор приложения клиента
 * @param array $needscopes - массив необходимых областей доступа
 *
 * @return bool
 */
function is_scopes_authorized_by_user($userid, $clientid, $needscopes) {
    // Массив одобренных для доступа областей даных
    $authorizedscopes = get_authorized_scopes($userid, $clientid);
    // Массив недостающих доступов
    $missingscopes = array_diff($needscopes, $authorizedscopes);
    // Доступ должен быть, если нет недостающих доступов
    return empty($missingscopes);
}

/**
 * Получение одобренных областей доступа
 *
 * @param int $userid - идентификатор пользователя
 * @param string $clientid - идентификатор приложения клиента
 * @param boolean $usepreapproved - учитывать ли предодобренные для приложения области данных
 *
 * @return array[] - массив одобренных областей доступа
 */
function get_authorized_scopes($userid, $clientid, $usepreapproved=true)
{
    global $DB;
    
    $authorizedscopes = [];
    
    if ($usepreapproved)
    {
        // доступы, которые предодобрены для клиента
        $client = $DB->get_record('oauth_clients', ['client_id' => $clientid]);
        if (!empty($client->preapproved_scopes))
        {
            $authorizedscopes = explode(' ', $client->preapproved_scopes);
        }
    }
    
    // доступы, которые одобрил пользователь
    $userscopes = $DB->get_records('oauth_user_auth_scopes', [
        'client_id' => $clientid,
        'user_id' =>  $userid
    ]);
    if (!empty($userscopes))
    {
        foreach($userscopes as $userscope)
        {
            if (!in_array($userscope, $authorizedscopes))
            {
                $authorizedscopes[] = $userscope->scope;
            }
        }
    }
    
    return $authorizedscopes;
}

/**
 * Одобрение доступа к области данных пользователем
 *
 * @param int $userid - идентификатор пользователя
 * @param string $clientid - идентификатор приложения клиента
 * @param array $scopes - массив областей доступа
 */
function authorize_user_scopes($userid, $clientid, $scopes) {
    global $DB;
    // Массив одобренных для доступа областей даных самим пользователем
    $authorizedscopes = get_authorized_scopes($userid, $clientid, false);
    
    foreach($scopes as $scope)
    {
        if (!in_array($scope, $authorizedscopes))
        {
            $record = new StdClass();
            $record->client_id = $clientid;
            $record->user_id = $userid;
            $record->scope = $scope;
        
            $DB->insert_record('oauth_user_auth_scopes', $record);
        }
    }
}