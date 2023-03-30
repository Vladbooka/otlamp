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
 * Плагин аутентификации OTOAuth. Языковой пакет.
 *
 * @package    auth
 * @subpackage otoauth
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// Base strings
$string['pluginname'] = 'OTOAuth';
$string['provider_google'] = 'Google';
$string['provider_google_corporate'] = 'Corporate Google account';
$string['provider_facebook'] = 'Facebook';
$string['provider_vk'] = 'Vkontakte';
$string['provider_messenger'] = 'MSN';
$string['provider_yandex'] = 'Yandex';
$string['provider_github'] = 'GitHub';
$string['provider_linkedin'] = 'Linkedin';
$string['provider_odkl'] = 'Odnoklassniki';
$string['provider_mailru'] = 'Mail.ru';
$string['provider_esia'] = 'ESIA';
$string['settings_googlesettings'] = 'Settings';
$string['couldnotgetgoogleaccesstoken'] = 'The authentication provider sent us a communication error. Please try to sign-in again.';
$string['couldnotlinkaccount'] = 'Could not link the account with this authentication provider';
$string['couldnotlinkanother'] = 'This account already linked to the Moodle account. If you would like to link to this account you need to disconnect previous link';
$string['cannotdisconnectlastlink'] = 'Could not unlink the last service from unconfirmed account.';
$string['emailaddressmustbeverified'] = 'Your email address is not verified by the authentication method you selected. You likely have forgotten to click on a "verify email address" link that Google or Facebook should have sent you during your subscribtion to their service.';
$string['moreproviderlink'] = 'Sign-in with another service.';
$string['noaccountyet'] = 'You do not have permission to use the site yet. Please contact your administrator and ask them to activate your account.';
$string['registernotallowed'] = 'OAuth-service registration disabled';
$string['signinwithanaccount'] = 'Log in with:';
$string['useremailduplicate'] = 'User with this e-mail address already exists.';
$string['useralreadyexists'] = 'Could not signup a new user: user already exists.';
$string['popupcloser_notification'] = 'Authorization was successful, you will be redirected to the page <a href="{$a}">{$a}</a>';
$string['popupcloser_title'] = 'Authorization was successful';
$string['popupenter_notification'] = 'You can log in using your account {$a} in the pop-up browser window that opens (on a mobile platform in a separate tab). After authorization, you can continue to work with the system.';
$string['popupenter_title'] = 'You should log in';

//Capabilities
$string['otoauth:receive_notifications_new_suspended_user'] = 'The right to receive notifications about the creation of a user requiring confirmation';
$string['messageprovider:messages_new_suspended_user'] = 'Send notification of user creation requiring confirmation otoauth';
$string['otoauth:managecustomproviders'] = 'Manage custom providers';

// SETTINGS
// Headers
$string['settings_main_header'] = 'General settings';
$string['settings_providers_header'] = 'Provider settings';
$string['settings_suspended_header'] = 'Admin Account Confirmation Settings';

// General settings
$string['settings_usenewredirect_label'] = 'Use new redirect uri';
$string['settings_usenewredirect'] = 'If selected <b>Yes</b>, all <b>redirect_uri</b> variables must be set to &quot;auth/otoauth/redirect.php&quot;';
$string['settings_requireconfirm_label'] = 'Require email confirmation';
$string['settings_requireconfirm'] = 'If selected <b>Yes</b>, all created accounts by Oauth2 must been confirm by administrator after creation';
$string['settings_allowregister_label'] = 'Allow registration of new accounts through OAuth2 services';
$string['settings_allowregister'] = 'If selected <b>Yes</b>, users can create new accounts through OAuth2 services';
$string['settings_suspended_label'] = 'Create accounts not activated';
$string['settings_suspended'] = 'If <b>Yes</b> is selected, all accounts created through Oauth2 must be activated by the administrator';
$string['settings_admin_message_suspended_label'] = 'Send notifications to administrator';
$string['settings_admin_message_suspended'] = 'If <b>Yes</b> is selected, information about all accounts created through Oauth2 will be sent to the administrator';
// Provider setings
$string['settings_facebookclientid'] = 'Your App ID/Secret can be generated in your <a href="https://developers.facebook.com/apps/">Facebook developer page</a>:
<br/>Site URL: {$a->siteurl}
<br/>Site domain: {$a->sitedomain}';
$string['settings_facebookclientid_label'] = 'Facebook App ID';
$string['settings_facebookclientsecret'] = 'See above.';
$string['settings_facebookclientsecret_label'] = 'Facebook App secret';
$string['settings_githubclientid'] = 'Your client ID/Secret can be generated in your <a href="https://github.com/settings/applications/new">Github register application page</a>:
<br/>Homepage URL: {$a->siteurl}
<br/>Authorization callback URL: {$a->callbackurl}';
$string['settings_githubclientid_label'] = 'Github client ID';
$string['settings_githubclientsecret'] = 'See above.';
$string['settings_githubclientsecret_label'] = 'Github client secret';

$string['settings_otoauthdescription'] = 'Allow a user to connect to the site with an external service: Google/Facebook/Vkontakte/Yandex. The first time the user connect with an external service, a new account is created. <a href="'.$CFG->wwwroot.'/admin/search.php?query=authpreventaccountcreation">Prevent account creation when authenticating</a> <b>must</b> be unset.
<br/><br/>
<i>Warning about Windows Live: Microsoft doesn\'t tell the plugin if the user\'s email address has been verified. More info in the <a href="https://github.com/mouneyrac/auth_googleoauth2/wiki/FAQ">FAQ</a>.</i>';
$string['settings_googleclientid'] = 'Your client ID/Secret can be generated in the <a href="https://code.google.com/apis/console">Google console API</a>:
<br/>
Google console API > API Access > Create another client ID...
<br/>
Redirect URLs: {$a->redirecturls}
<br/>
Javascript origins: {$a->jsorigins}';
$string['settings_googleclientid_label'] = 'Google Client ID';
$string['settings_googleclientsecret'] = 'See above.';
$string['settings_googleclientsecret_label'] = 'Google Client secret';
$string['settings_googleipinfodbkey'] = 'IPinfoDB is a service that let you find out what is the country and city of the visitor. This setting is optional. You can subscribe to <a href="http://www.ipinfodb.com/register.php">IPinfoDB</a> to get a free key.<br/>
Website: {$a->website}';
$string['settings_googleipinfodbkey_label'] = 'IPinfoDB Key';
$string['settings_googleuserprefix'] = 'The created user\'s username will start with this prefix. On a basic Moodle site you don\'t need to change it.';
$string['settings_googleuserprefix_label'] = 'Username prefix';
$string['settings_googleoauth2description'] = 'Allow a user to connect to the site with an external service: Google/Facebook/Windows Live. The first time the user connect with an external service, a new account is created. <a href="'.$CFG->wwwroot.'/admin/search.php?query=authpreventaccountcreation">Prevent account creation when authenticating</a> <b>must</b> be unset.
<br/><br/>
<i>Warning about Windows Live: Microsoft doesn\'t tell the plugin if the user\'s email address has been verified. More info in the <a href="https://github.com/mouneyrac/auth_googleoauth2/wiki/FAQ">FAQ</a>.</i>';

$string['settings_google_registration_domain_label'] = 'Enable registration only for current domain';
$string['settings_google_registration_domain'] = '';

$string['settings_google_corporateclientid'] = 'Your client ID/Secret can be generated in the <a href="https://code.google.com/apis/console">Google console API</a>:
<br/>
Google console API > API Access > Create another client ID...
<br/>
Redirect URLs: {$a->redirecturls}
<br/>
Javascript origins: {$a->jsorigins}';
$string['settings_google_corporateclientid_label'] = 'Google Client ID';
$string['settings_google_corporateclientsecret'] = '';
$string['settings_google_corporateclientsecret_label'] = 'Google Client secret';
$string['settings_google_corporate_domain'] = '';
$string['settings_google_corporate_domain_label'] = 'Google corporate domain';
$string['settings_google_corporate_registration_enabled'] = '';
$string['settings_google_corporate_registration_enabled_label'] = 'Google corporate registration options, will override general setting';
$string['google_corporate_registration_disabled'] = 'Registration denied';
$string['google_corporate_registration_duplicate_email_denied'] = 'Registration allowed. Will cancel process if duplicate email.';
$string['google_corporate_registration_duplicate_email_allow_blank_email'] = 'Registration allowed. Will clear email-field if duplicate value';
$string['settings_error_google_corporate_domain_empty'] = 'Domain not set';
$string['settings_linkedinclientid'] = 'Your API/Secret keys can be generated in your <a href="https://www.linkedin.com/secure/developer">Linkedin register application page</a>:
<br/>Website URL: {$a->siteurl}
<br/>OAuth 1.0 Accept Redirect URL: {$a->callbackurl}';
$string['settings_linkedinclientid_label'] = 'Linkedin API Key';
$string['settings_linkedinclientsecret'] = 'See above.';
$string['settings_linkedinclientsecret_label'] = 'Linkedin Secret key';
$string['settings_messengerclientid'] = 'Your Client ID/Secret can be generated in your <a href="https://account.live.com/developers/applications">Windows Live apps page</a>:
<br/>Redirect domain: {$a->domain}';
$string['settings_messengerclientid_label'] = 'Messenger Client ID';
$string['settings_messengerclientsecret'] = 'See above.';
$string['settings_messengerclientsecret_label'] = 'Messenger Client secret';
$string['settings_yandexclientid_label'] = 'ID приложения в Яндекс';
$string['settings_yandexclientid'] = 'ID вашего приложения - его можно получить тут: https://oauth.yandex.ru/client/my';
$string['settings_yandexclientsecret_label'] = 'Секретный ключ приложения на Яндекс';
$string['settings_yandexclientsecret'] = 'Секретный ключ вашего приложения - его можно получить тут: https://oauth.yandex.ru/client/my';
$string['settings_vkclientid_label'] = 'Vkontakte client_id';
$string['settings_vkclientid'] = 'Your client_id - it can be found at http://vk.com/editapp?act=create';
$string['settings_vkclientsecret_label'] = 'Vkontakte client_secret';
$string['settings_vkclientsecret'] = 'Your client_secret - it can be found at http://vk.com/editapp?act=create';
$string['settings_odklclientid_label'] = 'ID application on <b>Odnoklassniki</b>';
$string['settings_odklclientid'] = 'Your application client_id - it can be found on <a href="http://www.odnoklassniki.ru/dk?st.cmd=appsInfoMyDevList">Odnoklassniki add application page</a>';
$string['settings_odklclientsecret_label'] = 'Secret key in <b>Odnoklassniki</b>';
$string['settings_odklclientsecret'] = 'Your application client_secret. It will be sent to your e-mail after register app';
$string['settings_odklclientpublickey_label'] = 'Public key in <b>Odnoklassniki</b>';
$string['settings_odklclientpublickey'] = 'Your application public key. It will be sent to your e-mail after register app';
$string['settings_mailruclientid_label'] = 'ID application on <b>Mail.ru</b>';
$string['settings_mailruclientid'] = 'Your application client_id - it can be found on <a href="http://api.mail.ru/sites/my/add">Mail.ru add site page</a>';
$string['settings_mailruclientsecret_label'] = 'Secret key your application on <b>Mail.ru</b>';
$string['settings_mailruclientsecret'] = 'Secret key on <b>Mail.ru</b>';
$string['settings_mailruclientpublickey_label'] = 'Public key in <b>Mail.ru</b>';
$string['settings_mailruclientpublickey'] = 'Your application public key in <b>Mail.ru</b>';
$string['settings_salt'] = 'Unique plugin key';
$string['settings_salt_desc'] = 'A random set of characters used as a so-called salt to sign requests and then authenticate responses.';
$string['settings_facebookcheckusertokenexpiry_label'] = 'Track the validity of the Facebook access token';
$string['settings_facebookcheckusertokenexpiry_desc'] = 'In the event that the user has changed his password on the social network or requested the forced termination of the session, he will need to log in to the system again to continue working.';
$string['settings_githubcheckusertokenexpiry_label'] = 'Track the validity of the Github access token';
$string['settings_githubcheckusertokenexpiry_desc'] = 'In the event that the user has changed his password on the social network or requested the forced termination of the session, he will need to log in to the system again to continue working.';
$string['settings_googlecheckusertokenexpiry_label'] = 'Track the validity of the Google access token';
$string['settings_googlecheckusertokenexpiry_desc'] = 'In the event that the user has changed his password on the social network or requested the forced termination of the session, he will need to log in to the system again to continue working.';
$string['settings_google_corporatecheckusertokenexpiry_label'] = 'Track the validity of the Google Corporate access token';
$string['settings_google_corporatecheckusertokenexpiry_desc'] = 'In the event that the user has changed his password on the social network or requested the forced termination of the session, he will need to log in to the system again to continue working.';
$string['settings_linkedincheckusertokenexpiry_label'] = 'Track the validity of the LinkedIn access token';
$string['settings_linkedincheckusertokenexpiry_desc'] = 'In the event that the user has changed his password on the social network or requested the forced termination of the session, he will need to log in to the system again to continue working.';
$string['settings_mailrucheckusertokenexpiry_label'] = 'Track the validity of the Mail.ru access token';
$string['settings_mailrucheckusertokenexpiry_desc'] = 'In the event that the user has changed his password on the social network or requested the forced termination of the session, he will need to log in to the system again to continue working.';
$string['settings_messengercheckusertokenexpiry_label'] = 'Track the validity of Microsoft Messenger access token';
$string['settings_messengercheckusertokenexpiry_desc'] = 'In the event that the user has changed his password on the social network or requested the forced termination of the session, he will need to log in to the system again to continue working.';
$string['settings_odklcheckusertokenexpiry_label'] = 'Track the validity of the Odnoklassniki access token';
$string['settings_odklcheckusertokenexpiry_desc'] = 'In the event that the user has changed his password on the social network or requested the forced termination of the session, he will need to log in to the system again to continue working.';
$string['settings_vkcheckusertokenexpiry_label'] = 'Track the validity of the Vkontakte access token';
$string['settings_vkcheckusertokenexpiry_desc'] = 'In the event that the user has changed his password on the social network or requested the forced termination of the session, he will need to log in to the system again to continue working.';
$string['settings_yandexcheckusertokenexpiry_label'] = 'Monitor the validity of the Yandex access token';
$string['settings_yandexcheckusertokenexpiry_desc'] = 'In the event that the user has changed his password on the social network or requested the forced termination of the session, he will need to log in to the system again to continue working.';
$string['settings_esiaclientid'] = 'Client_id <b>ESIA</b> application';
$string['settings_esiaclientid_label'] = 'Client_id ESIA application';
$string['settings_esiaclientid_desc'] = '';
$string['settings_esiaclientsecret_desc'] = '';
$string['settings_esiacheckusertokenexpiry_label'] = 'Track the validity of the ESIA access token';
$string['settings_esiacheckusertokenexpiry_desc'] = '';
$string['settings_esiapublickey_label'] = 'Public key file';
$string['settings_esiapublickey_desc'] = 'The file is needed to generate client_secret ESIA';
$string['settings_esiaprivatekey_label'] = 'Private key file';
$string['settings_esiaprivatekey_desc'] = 'The file is needed to generate client_secret ESIA';
$string['settings_facebookenable_label'] = 'Enable authentication via <b>Facebook</b>';
$string['settings_facebookenable_desc'] = 'To enable authorization via Facebook and add the appropriate link to the authorization page, enable this setting';
$string['settings_githubenable_label'] = 'Enable authentication via <b>Github</b>';
$string['settings_githubenable_desc'] = 'To enable authorization via Github and add the appropriate link to the authorization page, enable this setting';
$string['settings_googleenable_label'] = 'Enable authentication via <b>Google</b>';
$string['settings_googleenable_desc'] = 'To enable authorization via Google and add the appropriate link to the authorization page, enable this setting';
$string['settings_google_corporateenable_label'] = 'Enable authentication via <b>Google Corporate</b>';
$string['settings_google_corporateenable_desc'] = 'To enable authorization via Google Corporate and add the appropriate link to the authorization page, enable this setting';
$string['settings_linkedinenable_label'] = 'Enable authentication via <b>Linkedin</b>';
$string['settings_linkedinenable_desc'] = 'To enable authorization via Linkedin and add the appropriate link to the authorization page, enable this setting';
$string['settings_mailruenable_label'] = 'Enable authentication via <b>Mail.ru</b>';
$string['settings_mailruenable_desc'] = 'To enable authorization via Mail.ru and add the appropriate link to the authorization page, enable this setting';
$string['settings_messengerenable_label'] = 'Enable authentication via <b>Microsoft Messenger</b>';
$string['settings_messengerenable_desc'] = 'To enable authorization via Microsoft Messenger and add the appropriate link to the authorization page, enable this setting';
$string['settings_odklenable_label'] = 'Enable authentication via <b>Odnoklassniki</b>';
$string['settings_odklenable_desc'] = 'To enable authorization via Odnoklassniki and add the appropriate link to the authorization page, enable this setting';
$string['settings_vkenable_label'] = 'Enable authentication via <b>Vkontakte</b>';
$string['settings_vkenable_desc'] = 'To enable authorization via Vkontakte and add the appropriate link to the authorization page, enable this setting';
$string['settings_yandexenable_label'] = 'Enable authentication via <b>Yandex</b>';
$string['settings_yandexenable_desc'] = 'To enable authorization via Yandex and add the appropriate link to the authorization page, enable this setting';
$string['settings_esiaenable_label'] = 'Enable authentication via <b>ESIA</b>';
$string['settings_esiaenable_desc'] = 'To enable authorization via ESIA and add the appropriate link to the authorization page, enable this setting';
$string['settings_esiamode_label'] = 'Authorization mode via ESIA';
$string['settings_esiamode_desc'] = 'In the test mode of operation, calls go to the test portal https://esia-portal1.test.gosuslugi.ru/';
$string['esiatest_mode'] = 'Test';
$string['esiamain_mode'] = 'Main';
$string['settings_esiakeypin_label'] = 'Private Key Container Password';
$string['settings_esiakeypin_desc'] = 'A password is needed to generate the signature required for authorization. If the container is passwordless, leave this field blank.';
$string['settings_esiasubjectname_query_label'] = 'The text of the certificate search request in the container';
$string['settings_esiasubjectname_query_desc'] = 'The certificate is searched in the container by SUBJECT_NAME, specify the CN certificate in this field';
$string['settings_esiatspaddres_label'] = 'Stamp service address (tsp addres)';
$string['settings_esiatspaddres_desc'] = 'Specify the address of the server that responds to time stamp requests. If you do not plan to use the stamp service, leave this field blank. You can read more at <a href="https://www.cryptopro.ru/products/pki/tsp/test"> Test TSP server </a>';
$string['settings_esiatrustedauth_label'] = 'Allow authorization for not trusted accounts?';
$string['settings_esiatrustedauth_desc'] = 'Allows or denies authorization for accounts that have not passed the verification procedure.';
$string['settings_esiadisplaypopup_label'] = 'Open the user authentication page in a new browser popup';
$string['settings_esiadisplaypopup_desc'] = '';
$string['settings_vkdisplaypopup_label'] = 'Open the user authentication page in a new browser popup';
$string['settings_vkdisplaypopup_desc'] = '';
$string['settings_updatelocal'] = 'Updating Moodle Account Fields';
$string['settings_updatelocal_desc'] = 'This setting allows you to specify when it is necessary to update Moodle user profile data when using authorization through social networks';
$string['updatelocal_oncreate'] = 'On create';
$string['updatelocal_onlogin'] = 'On login';
$string['updatelocal_onlink'] = 'On link';
$string['default_registration_confirmation_message_subject'] = 'User is awaiting registration confirmation';
$string['default_registration_confirmation_message_short'] = 'Hello, user {userfullname} has registered and is awaiting account verification';
$string['default_registration_confirmation_message_full'] = 'Hello!<br/><br/>The user {userfullname} has registered and is awaiting account confirmation.<br/>
                                                     To confirm user account, you need to go to the <a href="{userprofileediturl}"> user card </a> and deactivate the option "Account is blocked"';

// Email notifications
$string['message_subject'] = 'Notification Header';
$string['message_full'] = 'Full notice text';
$string['message_short'] = 'Short notice text';

$string['emaillinkconfirmation'] = 'Hi {$a->firstname},

An account link has been requested at \'{$a->sitename}\'
using your email address.

Your login: {$a->username}
Your password: {$a->password}

';
$string['emailnoconfirm'] = 'Hi {$a->firstname},

A new social account has been created at \'{$a->sitename}\'
using your email address.

Your login: {$a->username}
Your password: {$a->password}

';
$string['toaccept'] = '

To accept your account, please click the link below:
{$a->link}

In most mail programs, this should appear as a blue link
which you can just click on.  If that doesn\'t work,
then cut and paste the address into the address
line at the top of your web browser window.

';
$string['signature'] = 'If you need help, please contact the site administrator,
{$a->admin}';
$string['emailnewuser'] = 'User \'{$a->userlink}\' was created at \'{$a->sitename}\' site using {$a->authprovider} service';
$string['emailnewusersubject'] = 'User \'{$a->username}\' has created account using {$a->authprovider} service';
$string['emailuserlink'] = 'User \'{$a->userlink}\' has linked account with {$a->authprovider} at \'{$a->sitename}\' site using OAuth2.0 plugin.';
$string['emailuserlinksubject'] = 'User \'{$a->username}\' has linked account to the {$a->authprovider}';

// Events
$string['event_signin_error_name'] = 'Error authorization through external services';
$string['event_signin_error_desc'] = 'An error that occurred during user logon authentication using Oauth2';
$string['event_request_sent_name'] = 'Request to external service sent';
$string['event_request_sent_desc'] = 'Event triggered after sending request to external service and store response data. Works only with custom provider and only in debugdeveloper level';
$string['event_request_receive_name'] = 'Received a response from the authorization server';
$string['event_request_receive_desc'] = 'The event triggered when a response is received from the authorization server. Contains data about the name of the provider, received post and get data, user session.';

// Errors
$string['error_authorization_code_not_received'] = 'An error occurred during authentication: failed to get the authorization code from {$a}. Please contact the administrator.';
$string['error_externaluser_email_not_received'] = 'An error occurred during authentication. Please contact the administrator.';
$string['error_externaluser_email_not_valid'] = 'An error occurred during authentication. Please contact the administrator.';
$string['error_google_corporate_email_domain_notvalid'] = 'Account is disabled. Please use an account with an e-mail domain @{$a}';
$string['error_email_is_not_allowed'] = 'The use of e-mail account {$a} forbidden in the system. Please use another email.';
$string['error_externaluser_account_not_verified'] = 'You are trying to use an unconfirmed account to log in to the system.';
$string['error_externaluser_account_not_linked'] = 'Error Account binding.';
$string['error_signup_user_error'] = 'Error Account binding';
$string['error_authenticate_user_error'] = 'An error occurred during authentication. Please contact the administrator.';
$string['error_app_access_token_not_received'] = 'An error occurred during authorization: could not get the application token';
$string['error_cannot_read_the_public_certificate'] = 'Cannot read the public certificate';
$string['error_cannot_read_the_private_certificate'] = 'Cannot read the private certificate';
$string['error_cannot_sign_the_message'] = 'Cannot sign the message';
$string['error_certificate_does_not_exist'] = 'Certificate does not exist';
$string['error_private_key_does_not_exist'] = 'Private key does not exist';
$string['error_temporary_folder_is_not_found'] = 'Temporary folder is not found';
$string['error_temporary_folder_is_not_writable'] = 'Temporary folder is not writable';
$string['error_recieved_token_data_invalid'] = 'An error occurred during authorization: could not form the object';
$string['error_invalid_state'] = 'Failed to verify the transferred data, possibly an attempt to cross-site request forgery (CSRF)';
$string['error_build_url_fail'] = 'Failed to get url to redirect to social network login page';
$string['error_cryptopro_csp_license_expired'] = 'CryptoPro CSP License Expired';
$string['error_invalid_authprovider'] = 'An error occurred during authorization. Provider not specified.';

// Messages
$string['message_registration_disabled'] = 'Registration of new users using an account from an external system {$a} is disabled.';
$string['message_registration_duplicate_email'] = 'The system already has a user with this email. If this is your account, sign in using your login and password and link your local account to an external {$a} account to be able to log in using it later.';
$string['message_forceproviderlogout'] = 'Your session at {$a} has ended. Please log in again.';
$string['message_record_need_confirmation'] = 'Record sent to administrator for confirmation';
$string['message_record_waiting_confirmation'] = 'The account is blocked and is waiting for confirmation by the administrator.';

$string['settings_page_general'] = "General settings";
$string['settings_category_providers'] = "Providers";
$string['custom_providers'] = "Custom providers";
$string['customprovider_status_active'] = 'Active';
$string['customprovider_status_disabled'] = 'Disabled';
$string['provider_management'] = 'Provider management';
$string['provider_management_add'] = 'Add provider';
$string['provider_management_edit'] = 'Edit provider';
$string['provider_management_delete'] = 'Delete provider';
$string['provider_management_viewlist'] = 'View providers list';
$string['custom_providers_list_empty'] = 'Custom providers list is empty';
$string['custom_provider_delete'] = 'Delete';
$string['custom_provider_delete_confirm'] = 'Confirm deleting custom provider. Users will not be able to continue to log in through this provider.';
$string['custom_provider_delete_success'] = 'Custom provider was deleted';
$string['custom_provider_edit'] = 'Edit';

$string['custom_provider_property_id'] = 'id';
$string['custom_provider_property_id_help'] = '';
$string['custom_provider_property_code'] = 'Unique code';
$string['custom_provider_property_code_help'] = '';
$string['custom_provider_property_name'] = 'Name';
$string['custom_provider_property_name_help'] = '';
$string['custom_provider_property_description'] = 'Description';
$string['custom_provider_property_description_help'] = '';
$string['custom_provider_property_config'] = 'Config';
$string['custom_provider_property_config_help'] = '<div>Конфигурация настраиваемого провайдера производится в формате yaml и должна быть представлена в виде ассоциативного массива.</div>
<div>В результате использования настраиваемой конфигурации возможно осуществить авторизацию по протоколу OAuth2.0 с использованием типа авторизации \'authorization_code\'.</div>
<div>В качестве ключей этого массива могут служить:</div>
<ul>
<li>clientid - идентификатор клиента (id приложения), предоставляется сервером авторизации после регистрации приложения, используется далее для автоматической подстановки в требуемых запросах;</li>
<li>clientsecret - секретный ключ (пароль приложения), предоставляется сервером авторизации после регистрации приложения, используется далее для автоматической подстановки в требуемых запросах;</li>
<li>icon - путь до изображения на сервере или закодированное в base64 изображение, используется в качестве иконки вашего способа авторизации;</li>
<li>allowregister - разрешена ли регистрация пользователей при помощи настраиваемого провайдера
    <ul>
    <li>если нет (любое пустое значение или отсутствие параметра)- пользователь сможет авторизоваться только при наличии в СДО аккаунта, связанного с аккаунтом настраиваемого провайдера;</li>
    <li>если да (любое непустое значение, например, \'1\') - пользователю будет создана учетная запись при успешной авторизации;</li>
    </ul>
</li>
<li>authorize - конфигурация процесса вызова авторизации и получения авторизационного кода от сервера авторизации. Должна быть представлена в виде массива. В качестве ключей этого массива могут служить:
    <ul>
    <li>url - адрес, входная точка API авторизации (authorization endpoint)
    <li>parameters - массив параметров, требуемых сервером авторизации для данного запроса. Для автоматической замены, в значениях массива возможно использовать следующие подстановки:
        <ul>
            <li>{clientid} - подставляет идентификатор клиента (id приложения)
            <li>{redirect_uri} - подставляет ссылку переадресации, на которую будет осуществлено перенаправление после выдачи авторизационного кода
            <li>{state} - подставит автоматически сгенерированную строку, используемую для защиты от подделки запросов (cross-site request forgery)
        </ul>
    </ul>
    <div>
   Пример:
   <PRE>
   authorize:
      url: \'https://oauth.yandex.ru/authorize\'
      parameters:
         client_id: \'{clientid}\'
         redirect_uri: \'{redirect_uri}\'
         state: \'{state}\'
         response_type: \'code\'</PRE>
    </div>
</li>
<li>accesstoken - конфигурация процесса получения ключа доступа (токена) от сервера авторизации. Должна быть представлена в виде массива. В качестве ключей этого массива могут служить:
    <ul>
        <li>url - адрес, предназначенный для получения ключа доступа, токена (token endpoint)
        <li>parameters - массив параметров, требуемых сервером авторизации для данного запроса. Для автоматической замены, в значениях массива возможно использовать следующие подстановки:
            <ul>
                <li>{clientid} - подставляет идентификатор клиента (id приложения)
                <li>{clientsecret} - подставляет секретный ключ (пароль приложения)
                <li>{redirect_uri} - подставляет ссылку переадресации, на которую будет осуществлено перенаправление после выдачи ключа доступа
                <li>{authorization_code} - подставляет авторизационный код полученный в результате авторизации на предыдущем шаге
            </ul>
        <li>requesttype - метод осуществления запроса (\'get\' или \'post\')
        <li>curloptions - для случаев, когда требуется указать особенные параметры запроса, возможно передать массив с опциями, поддерживаемыми библиотекой curl
        <li>responsetype - ожидаемый формат ответа (\'plain\' или \'json\')
        <li>responsefields - сопоставление требуемых для авторизации полей с полями, пришедими в результатах запроса к авторизационному серверу
            <ul>
                <li>token - для данного поля требуется указать название поля из ответа сервера, в котором содержится ключ доступа
            </ul>
    </ul>
    <div>Пример:
   <PRE>
   accesstoken:
      url: \'https://oauth.yandex.ru/token\'
      parameters:
         client_id: \'{clientid}\'
         client_secret: \'{clientsecret}\'
         redirect_uri: \'{redirect_uri}\'
         code: \'{authorization_code}\'
         grant_type: \'authorization_code\'
      requesttype: \'post\'
      curloptions: []
      responsetype: \'json\'
      responsefields:
         token: \'access_token\'</PRE>
    </div>
</li>
<li>userinfo - конфигурация процесса получения сведений об авторизованном пользователе от сервера авторизации. Получение информации о пользователе в некоторых системах разделено на области, и для получения всех необходимых данных может потребоваться несколько запросов. В связи с этим, значением данного параметра должен являться массив запросов (обратите внимание на дефис в примере). Каждый запрос должен быть представлен также, в виде массива. В качестве ключей запроса могут служить:
    <ul>
        <li>url - адрес для получения необходимой области информации о пользователе
        <li>parameters - массив параметров, требуемых сервером авторизации для данного запроса. Для автоматической замены, в значениях массива возможно использовать следующие подстановки:
            <ul>
                <li>{access_token} - подставляет ключ доступа, полученный в результате исполнения предыдущих операций
            </ul>
        <li>requesttype - метод осуществления запроса (\'get\' или \'post\')
        <li>curloptions - для случаев, когда требуется указать особенные параметры запроса, возможно передать массив с опциями, поддерживаемыми библиотекой curl
        <li>responsetype - ожидаемый формат ответа (\'plain\' или \'json\')
        <li>responsefields - сопоставление полей пользователя СДО с полями, пришедими в результатах запроса к авторизационному серверу. Наиболее распространенные поля пользователя СДО:
            <ul>
                <li>username - логин пользователя в СДО
                <li>firstname - имя пользователя
                <li>lastname - фамилия пользователя
                <li>email - email-адрес пользователя
                <li>verified - подтверждена ли учетная запись, другими словами точно ли email принадлежит пользователю, который авторизовался; если провайдер предоставляет такие данные или вы уверены, что зарегистрироваться в провайдере можно только подтвердив аккаунт через mail или вы полностью доверяете провайдеру (например, это ваша система и вы точно знаете, что среди ваших пользователей не может быть злоумышленников), то можно использовать поле, предоставленное провайдером или подстановку \'{1}\' подтверждающую учетную запись без использования поля предоставленного провайдером
                <li>remoteuserid - идентификатор пользователя на стороне провайдера
                <li>lang - язык, предпочитаемый пользователем. Можно использовать, если значения соответствуют кодам языковых пакетов, например,
                <li>picture - адрес с изображением пользователя
            </ul>
    </ul>
    <div>Пример:
   <PRE>
   userinfo:
    - url: \'https://login.yandex.ru/info\'
      parameters:
         oauth_token: \'{access_token}\'
         format: \'json\'
      requesttype: \'get\'
      curloptions: []
      responsetype: \'json\'
      responsefields:
         username: \'login\'
         email: \'default_email\'
         verified: \'{1}\'
         firstname: \'first_name\'
         lastname: \'last_name\'
         remoteuserid: \'email\'</PRE>
    </div>
</li>
<li>refreshtoken - конфигурация процесса обновления ключа доступа (токена) от сервера авторизации.
   <!--div>Пример:
   <PRE></PRE>
    </div-->
</li>
<li>revoke - конфигурация процесса аннулирования ключа доступа (токена) от сервера авторизации.
   <!--div>Пример:
   <PRE></PRE>
    </div-->
</li>
</ul>';
$string['custom_provider_property_status'] = 'Status';
$string['custom_provider_property_status_help'] = '';
$string['custom_provider_overriden'] = 'Customprovider is overriden by config in moodle data';

$string['custom_provider_error_while_creating'] = 'An error occurred while creating the custom provider: {$a}';
$string['custom_provider_error_code_not_unique'] = 'Code is not unique';
$string['custom_provider_error_unknown_status'] = 'Unknown status';
$string['custom_provider_error_missing_required_property'] = 'Missing required parameters';
$string['custom_provider_error_missing_id'] = 'Missing id';

$string['cp_misconfig'] = 'Error occured while parsing custom provider{$a->id}config: {$a->message}';
$string['cp_misconfig_config_is_empty'] = 'config is empty';
$string['cp_misconfig_config_is_not_an_array'] = 'configured provider is not an array';
