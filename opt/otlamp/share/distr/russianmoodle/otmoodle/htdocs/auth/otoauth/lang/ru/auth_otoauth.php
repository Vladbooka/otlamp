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

// Базовые строки
$string['pluginname'] = 'OTOAuth';
$string['provider_google'] = 'Google';
$string['provider_google_corporate'] = 'Корпоративный Google';
$string['provider_facebook'] = 'Facebook';
$string['provider_vk'] = 'Vkontakte';
$string['provider_messenger'] = 'MSN';
$string['provider_yandex'] = 'Yandex';
$string['provider_github'] = 'GitHub';
$string['provider_linkedin'] = 'Linkedin';
$string['provider_odkl'] = 'Odnoklassniki';
$string['provider_mailru'] = 'Mail.ru';
$string['provider_esia'] = 'ЕСИА';
$string['auth_googlesettings'] = 'Настройки';
$string['couldnotgetgoogleaccesstoken'] = 'Сервис oauth2-авторизации отправил сообщение об ошибке. Пожалуйста, попробуйте зайти еще раз.';
$string['couldnotlinkaccount'] = 'Не удалось присоединить аккаунт социальной сети';
$string['couldnotlinkanother'] = 'Данный аккаунт уже привязан к другой учётной записи Moodle. Если вы хотите привязать его к этой учётной записи, необходимо отключить предыдущую привязку';
$string['couldnotdisconnectlastlink'] = 'Данный аккаунт не прошёл процедуру проверки e-mail адреса. Если вы хотите отвязать последнюю привязку, сначала подтвердите аккаунт';
$string['emailaddressmustbeverified'] = 'Ваш почтовый адрес не прошел процедуру подтверждения в системе, через которую вы пытаетесь авторизоваться. Возможно вы забыли перейти по ссылке подтверждения, которую сервис выслал на ваш почтовый адрес.';
$string['moreproviderlink'] = 'Зайти через другой сервис.';
$string['noaccountyet'] = 'У вас нет прав пользоваться сайтом. Пожалуйста, свяжитесь с администратором и попросите активировать учётную запись.';
$string['registernotallowed'] = 'Регистрация через OAuth-сервисы отключена';
$string['signinwithanaccount'] = 'Авторизоваться через:';
$string['useremailduplicate'] = 'Пользователь с этим e-mail уже зарегистрирован в системе.';
$string['cannotdisconnectlastlink'] = 'Нельзя отвязать последний сервис от неподтвержденного аккаунта.';
$string['useralreadyexists'] = 'Невозможно зарегистрировать нового пользователя: пользователь уже существует.';
$string['popupcloser_notification'] = 'Авторизация прошла успешно, вы будете перенаправлены на страницу <a href="{$a}">{$a}</a>';
$string['popupcloser_title'] = 'Авторизация прошла успешно';
$string['popupenter_notification'] = 'Вы можете авторизоваться под своей учетной записью {$a} в открывшемся всплывающем окне браузера (на мобильной платформе в отдельной вкладке). После авторизации вы сможете продолжить работать с системой.';
$string['popupenter_title'] = 'Вы должны авторизоваться';

//Capabilities
$string['otoauth:receive_notifications_new_suspended_user'] = 'Право получать уведомления о создании пользователя требующего подтверждения';
$string['messageprovider:messages_new_suspended_user'] = 'Отправка уведомления о создании пользователя требующего подтверждения otoauth';
$string['otoauth:managecustomproviders'] = 'Управлять кастомными провайдерами';

// НАСТРОЙКИ
// Заголовки
$string['settings_main_header'] = 'Общие настройки';
$string['settings_providers_header'] = 'Настройки сервисов';
$string['settings_suspended_header'] = 'Настройки подтверждения аккаунтов администратором';
// Общие настройки
$string['settings_usenewredirect_label'] = 'Использовать новую ссылку перенаправления';
$string['settings_usenewredirect'] = 'Если выбрано <b>Да</b>, все переменные <b>redirect_uri</b> должны заканчиваться на &quot;auth/otoauth/redirect.php&quot;';
$string['settings_requireconfirm_label'] = 'Подтверждать электронную почту';
$string['settings_requireconfirm'] = 'Если выбрано <b>Да</b>, все созданные через Oauth2 аккаунты должны быть подтверждены пользователями';
$string['settings_allowregister_label'] = 'Позволить регистрировать новые аккаунты через OAuth2-сервисы';
$string['settings_allowregister'] = 'Если выбрано <b>Да</b>, пользователи могут регистрироваться через OAuth2-сервисы';
$string['settings_suspended_label'] = 'Создавать аккаунты неактивированными';
$string['settings_suspended'] = 'Если выбрано <b>Да</b>, все созданные через Oauth2 аккаунты должны быть активированы администратором';
$string['settings_admin_message_suspended_label'] = 'Отправлять администратору уведомления';
$string['settings_admin_message_suspended'] = 'Если выбрано <b>Да</b>, информация обо всех созданных через Oauth2 аккаунтах будет отправлена администратору';
// Настройки сервисов
$string['settings_facebookclientid'] = 'ID вашего приложения, его можно получить на <a href="https://developers.facebook.com/apps/">странице разработчика Facebook</a>:
<br/>URL сайта: {$a->siteurl}
<br/>Доменное имя сайта: {$a->sitedomain}';
$string['settings_facebookclientid_label'] = 'ID приложения в <b>Facebook</b>';
$string['settings_facebookclientsecret'] = 'Секретный ключ вашего приложения - его можно получить тут: https://developers.facebook.com/apps/.';
$string['settings_facebookclientsecret_label'] = 'Секретный ключ приложения в <b>Facebook</b>';
$string['settings_githubclientid'] = 'ID вашего приложения, его получить можно на <a href="https://github.com/settings/applications/new">странице регистрации приложений Github</a>:
<br/>Homepage URL: {$a->siteurl}
<br/>Authorization callback URL: {$a->callbackurl}';
$string['settings_githubclientid_label'] = 'ID приложения в <b>Github</b>';
$string['settings_githubclientsecret'] = 'Секретный ключ вашего приложения - его можно получить тут: https://github.com/settings/applications/new';
$string['settings_githubclientsecret_label'] = 'Секретный ключ приложения в <b>Github</b>';
$string['settings_googleclientid'] = 'ID вашего приложения, его можно получить в <a href="https://code.google.com/apis/console">контрольной панели Google API</a>:
<br/>
Google console API > API Access > Create another client ID...
<br/>
Redirect URLs: {$a->redirecturls}
<br/>
Javascript origins: {$a->jsorigins}';
$string['settings_googleclientid_label'] = 'ID приложения в <b>Google</b>';
$string['settings_googleclientsecret'] = 'Секретный ключ вашего приложения - его можно получить тут: https://code.google.com/apis/console/.';
$string['settings_googleclientsecret_label'] = 'Секретный ключ приложения в <b>Google</b>';
$string['settings_google_registration_domain_label'] = 'Разрешить регистрацию аккаунтов только для домена';
$string['settings_google_registration_domain'] = 'Если указан домен (в виде opentechnology.ru), то система произведет регистрацию новых аккаунтов c e-mail только этого домена';

$string['settings_googleipinfodbkey'] = 'IPinfoDB - это сервис, который позволяет вам определить страну и город посетителя вашего сайта. Это необязательное поле. Вы можете подписаться на <a href="http://www.ipinfodb.com/register.php">IPinfoDB</a>, чтобы получить бесплатный ключ.<br/>
Website: {$a->website}';
$string['settings_googleipinfodbkey_label'] = 'IPinfoDB Key';
$string['settings_googleuserprefix'] = 'Этот префикс будет использоваться для логина каждого пользователя, зарегистрированного через Google Oauth2. Измените его, если наблюдаются конфликты имен в вашей системе. На свежеустановленной системе можно не изменять его.';
$string['settings_googleuserprefix_label'] = 'Префикс имени пользователя';
$string['settings_otoauthdescription'] = 'Простой и удобный плагин для Oauth2-аутентификации в сервисах Google/Facebook/Яндекс/Вконтакте.
    Вы можете зарегистрироваться сразу в нескольких сервисах, при этом для каждого сервиса для вас будет создана отдельная учётная запись в  Moodle';

$string['settings_google_corporateclientid'] = 'ID вашего приложения, его можно получить в <a href="https://code.google.com/apis/console">контрольной панели Google API</a>:
<br/>
Google console API > API Access > Create another client ID...
<br/>
Redirect URLs: {$a->redirecturls}
<br/>
Javascript origins: {$a->jsorigins}';
$string['settings_google_corporateclientid_label'] = 'ID приложения в <b>Google</b> для корпоративных аккаунтов';
$string['settings_google_corporateclientsecret'] = 'Секретный ключ вашего приложения - его можно получить тут: https://code.google.com/apis/console/.';
$string['settings_google_corporateclientsecret_label'] = 'Секретный ключ приложения в <b>Google</b> для корпоративных аккаунтов';
$string['settings_google_corporate_domain_label'] = 'Корпоративный домен';
$string['settings_google_corporate_domain'] = 'Домен (например opentechnology.ru). Домен должен быть подключен к Почте Google ';
$string['settings_google_corporate_registration_enabled_label'] = 'Регистрация аккаунтов для корпоративного домена';
$string['settings_google_corporate_registration_enabled'] = 'Опция позволит системе автоматически регистрировать новых пользователей, которые вошли в систему под корпоративным аккаунтом. Переопределяет общую настройку. Это полезно для случаев, когда требуется открыть доступ к системе только для пользователей с электронным ящиком определенного домена';
$string['google_corporate_registration_disabled'] = 'Запрещена';
$string['google_corporate_registration_duplicate_email_denied'] = 'Разрешена. При дублировании email - отмена регистрации';
$string['google_corporate_registration_duplicate_email_allow_blank_email'] = 'Разрешена. При дублировании email - очистка поля email';


$string['settings_error_google_corporate_domain_empty'] = 'Домен должен быть указан в форме "example.com"';

$string['settings_linkedinclientid'] = 'ID вашего приложения, его можно получить на <a href="https://www.linkedin.com/secure/developer">странице регистрации приложений Linkedin</a>:
<br/>Website URL: {$a->siteurl}
<br/>OAuth 1.0 Accept Redirect URL: {$a->callbackurl}';
$string['settings_linkedinclientid_label'] = 'ID приложения в <b>Linkedin</b>';
$string['settings_linkedinclientsecret'] = 'Секретный ключ вашего приложения - его можно получить тут: https://www.linkedin.com/secure/developer';
$string['settings_linkedinclientsecret_label'] = 'Секретный ключ приложения в <b>Linkedin</b>';
$string['settings_messengerclientid'] = 'ID вашего приложения, его можно получить в <a href="https://account.live.com/developers/applications">центре приложений Windows Live</a>:
<br/>Redirect domain: {$a->domain}';
$string['settings_messengerclientid_label'] = 'ID приложения в <b>Microsoft Messenger</b>';
$string['settings_messengerclientsecret'] = 'Секретный ключ вашего приложения - его можно получить тут: https://account.live.com/developers/applications';
$string['settings_messengerclientsecret_label'] = 'Секретный ключ приложения в <b>Microsoft Messenger</b>';
$string['settings_yandexclientid_label'] = 'ID приложения в <b>Яндекс</b>';
$string['settings_yandexclientid'] = 'ID вашего приложения, его можно получить на <a href="https://oauth.yandex.ru/client/my">странице создания приложений Yandex</a>
<br/>Ссылка на приложение: {$a->siteurl}
<br/>Callback URI: {$a->callbackurl}';
$string['settings_yandexclientsecret_label'] = 'Секретный ключ приложения в <b>Яндекс</b>';
$string['settings_yandexclientsecret'] = 'Секретный ключ вашего приложения, его можно получить тут: https://oauth.yandex.ru/client/my';
$string['settings_vkclientid_label'] = 'ID приложения в <b>Вконтакте</b>';
$string['settings_vkclientid'] = 'ID вашего приложения, его можно получить на <a href="http://vk.com/editapp?act=create">странице создания приложений Vkontakte</a>';
$string['settings_vkclientsecret_label'] = 'Секретный ключ приложения в <b>Вконтакте</b>';
$string['settings_vkclientsecret'] = 'Секретный ключ вашего приложения, его можно получить тут: http://vk.com/editapp?act=create';
$string['settings_odklclientid_label'] = 'ID приложения в <b>Одноклассники</b>';
$string['settings_odklclientid'] = 'ID вашего приложения, его можно получить на <a href="http://www.odnoklassniki.ru/dk?st.cmd=appsInfoMyDevList">странице создания приложений Одноклассники</a>';
$string['settings_odklclientsecret_label'] = 'Секретный ключ приложения в <b>Одноклассники</b>';
$string['settings_odklclientsecret'] = 'Секретный ключ вашего приложения. Он будет выслан на ваш e-mail при регистрации приложения';
$string['settings_odklclientpublickey_label'] = 'Публичный ключ приложения в <b>Одноклассники</b>';
$string['settings_odklclientpublickey'] = 'Публичный ключ вашего приложения. Он будет выслан на ваш e-mail при регистрации приложения';
$string['settings_mailruclientid_label'] = 'ID приложения в <b>Mail.ru</b>';
$string['settings_mailruclientid'] = 'ID вашего приложения, его можно получить на <a href="http://api.mail.ru/sites/my/add">странице добавления сайта в Mail.ru</a>';
$string['settings_mailruclientsecret_label'] = 'Секретный ключ приложения в <b>Mail.ru</b>';
$string['settings_mailruclientsecret'] = 'Секретный ключ вашего приложения в <b>Mail.ru</b>';
$string['settings_mailruclientpublickey_label'] = 'Приватный ключ приложения в <b>Mail.ru</b>';
$string['settings_mailruclientpublickey'] = 'Приватный ключ вашего приложения в <b>Mail.ru</b>';
$string['settings_salt'] = 'Уникальный ключ плагина';
$string['settings_salt_desc'] = 'Случайный набор символов, используемый в качестве так называемой соли для подписи запросов и последующей проверки подлинности ответов';
$string['settings_facebookcheckusertokenexpiry_label'] = 'Отслеживать действительность маркера доступа Facebook';
$string['settings_facebookcheckusertokenexpiry_desc'] = 'В том случае, если пользователь сменил свой пароль в социальной сети или затребовал принудительное завершение сеанса, ему потребуется снова авторизоваться в системе для продолжения работы.';
$string['settings_githubcheckusertokenexpiry_label'] = 'Отслеживать действительность маркера доступа Github';
$string['settings_githubcheckusertokenexpiry_desc'] = 'В том случае, если пользователь сменил свой пароль в социальной сети или затребовал принудительное завершение сеанса, ему потребуется снова авторизоваться в системе для продолжения работы.';
$string['settings_googlecheckusertokenexpiry_label'] = 'Отслеживать действительность маркера доступа Google';
$string['settings_googlecheckusertokenexpiry_desc'] = 'В том случае, если пользователь сменил свой пароль в социальной сети или затребовал принудительное завершение сеанса, ему потребуется снова авторизоваться в системе для продолжения работы.';
$string['settings_google_corporatecheckusertokenexpiry_label'] = 'Отслеживать действительность маркера доступа Google Corporate';
$string['settings_google_corporatecheckusertokenexpiry_desc'] = 'В том случае, если пользователь сменил свой пароль в социальной сети или затребовал принудительное завершение сеанса, ему потребуется снова авторизоваться в системе для продолжения работы.';
$string['settings_linkedincheckusertokenexpiry_label'] = 'Отслеживать действительность маркера доступа Linkedin';
$string['settings_linkedincheckusertokenexpiry_desc'] = 'В том случае, если пользователь сменил свой пароль в социальной сети или затребовал принудительное завершение сеанса, ему потребуется снова авторизоваться в системе для продолжения работы.';
$string['settings_mailrucheckusertokenexpiry_label'] = 'Отслеживать действительность маркера доступа Mail.ru';
$string['settings_mailrucheckusertokenexpiry_desc'] = 'В том случае, если пользователь сменил свой пароль в социальной сети или затребовал принудительное завершение сеанса, ему потребуется снова авторизоваться в системе для продолжения работы.';
$string['settings_messengercheckusertokenexpiry_label'] = 'Отслеживать действительность маркера доступа Microsoft Messenger';
$string['settings_messengercheckusertokenexpiry_desc'] = 'В том случае, если пользователь сменил свой пароль в социальной сети или затребовал принудительное завершение сеанса, ему потребуется снова авторизоваться в системе для продолжения работы.';
$string['settings_odklcheckusertokenexpiry_label'] = 'Отслеживать действительность маркера доступа Одноклассники';
$string['settings_odklcheckusertokenexpiry_desc'] = 'В том случае, если пользователь сменил свой пароль в социальной сети или затребовал принудительное завершение сеанса, ему потребуется снова авторизоваться в системе для продолжения работы.';
$string['settings_vkcheckusertokenexpiry_label'] = 'Отслеживать действительность маркера доступа Вконтакте';
$string['settings_vkcheckusertokenexpiry_desc'] = 'В том случае, если пользователь сменил свой пароль в социальной сети или затребовал принудительное завершение сеанса, ему потребуется снова авторизоваться в системе для продолжения работы.';
$string['settings_yandexcheckusertokenexpiry_label'] = 'Отслеживать действительность маркера доступа Яндекс';
$string['settings_yandexcheckusertokenexpiry_desc'] = 'В том случае, если пользователь сменил свой пароль в социальной сети или затребовал принудительное завершение сеанса, ему потребуется снова авторизоваться в системе для продолжения работы.';
$string['settings_esiaclientid'] = 'Client_id приложения <b>ЕСИА</b>';
$string['settings_esiaclientid_label'] = 'Client_id приложения ЕСИА';
$string['settings_esiaclientid_desc'] = '';
$string['settings_esiaclientsecret_desc'] = '';
$string['settings_esiacheckusertokenexpiry_label'] = 'Отслеживать действительность маркера доступа ЕСИА';
$string['settings_esiacheckusertokenexpiry_desc'] = '';
$string['settings_esiapublickey_label'] = 'Файл публичного ключа';
$string['settings_esiapublickey_desc'] = 'Файл необходим для формирования client_secret ЕСИА';
$string['settings_esiaprivatekey_label'] = 'Файл приватного ключа';
$string['settings_esiaprivatekey_desc'] = 'Файл необходим для формирования client_secret ЕСИА';
$string['settings_facebookenable_label'] = 'Включить авторизацию через <b>Facebook</b>';
$string['settings_facebookenable_desc'] = 'Для включения возможности авторизации через Facebook и добавления соответствующей ссылки на страницу авторизации включите эту настройку';
$string['settings_githubenable_label'] = 'Включить авторизацию через <b>Github</b>';
$string['settings_githubenable_desc'] = 'Для включения возможности авторизации через Github и добавления соответствующей ссылки на страницу авторизации включите эту настройку';
$string['settings_googleenable_label'] = 'Включить авторизацию через <b>Google</b>';
$string['settings_googleenable_desc'] = 'Для включения возможности авторизации через Google и добавления соответствующей ссылки на страницу авторизации включите эту настройку';
$string['settings_google_corporateenable_label'] = 'Включить авторизацию через <b>Google Corporate</b>';
$string['settings_google_corporateenable_desc'] = 'Для включения возможности авторизации через Google Corporate и добавления соответствующей ссылки на страницу авторизации включите эту настройку';
$string['settings_linkedinenable_label'] = 'Включить авторизацию через <b>Linkedin</b>';
$string['settings_linkedinenable_desc'] = 'Для включения возможности авторизации через Linkedin и добавления соответствующей ссылки на страницу авторизации включите эту настройку';
$string['settings_mailruenable_label'] = 'Включить авторизацию через <b>Mail.ru</b>';
$string['settings_mailruenable_desc'] = 'Для включения возможности авторизации через Mail.ru и добавления соответствующей ссылки на страницу авторизации включите эту настройку';
$string['settings_messengerenable_label'] = 'Включить авторизацию через <b>Microsoft Messenger</b>';
$string['settings_messengerenable_desc'] = 'Для включения возможности авторизации через Microsoft Messenger и добавления соответствующей ссылки на страницу авторизации включите эту настройку';
$string['settings_odklenable_label'] = 'Включить авторизацию через <b>Одноклассники</b>';
$string['settings_odklenable_desc'] = 'Для включения возможности авторизации через Одноклассники и добавления соответствующей ссылки на страницу авторизации включите эту настройку';
$string['settings_vkenable_label'] = 'Включить авторизацию через <b>Вконтакте</b>';
$string['settings_vkenable_desc'] = 'Для включения возможности авторизации через Вконтакте и добавления соответствующей ссылки на страницу авторизации включите эту настройку';
$string['settings_yandexenable_label'] = 'Включить авторизацию через <b>Яндекс</b>';
$string['settings_yandexenable_desc'] = 'Для включения возможности авторизации через Яндекс и добавления соответствующей ссылки на страницу авторизации включите эту настройку';
$string['settings_esiaenable_label'] = 'Включить авторизацию через <b>ЕСИА</b>';
$string['settings_esiaenable_desc'] = 'Для включения возможности авторизации через ЕСИА и добавления соответствующей ссылки на страницу авторизации включите эту настройку';
$string['settings_esiamode_label'] = 'Режим работы авторизации через ЕСИА';
$string['settings_esiamode_desc'] = 'В тестовом режиме работы обращения идут к тестовому порталу https://esia-portal1.test.gosuslugi.ru/';
$string['esiatest_mode'] = 'Тестовый';
$string['esiamain_mode'] = 'Основной';
$string['settings_esiakeypin_label'] = 'Пароль контейнера закрытого ключа';
$string['settings_esiakeypin_desc'] = 'Пароль нужен для формирования подписи, необходимой для осуществления авторизации. Если контейнер без пароля, оставьте это поле пустым.';
$string['settings_esiasubjectname_query_label'] = 'Текст запроса на поиск сертификата в контейнере';
$string['settings_esiasubjectname_query_desc'] = 'Поиск сертификата в контейнере осуществляется по SUBJECT_NAME, укажите в этом поле CN сертификата';
$string['settings_esiatspaddres_label'] = 'Адрес службы штампов';
$string['settings_esiatspaddres_desc'] = 'Укажите адрес сервера, который отвечает на запросы штампов времени. Если не планируете использовать службу штампов, оставьте это поле пустым. Подробнее можно прочитать по ссылке <a href="https://www.cryptopro.ru/products/pki/tsp/test">Тестовый TSP-сервер</a>';
$string['settings_esiatrustedauth_label'] = 'Разрешить авторизацию для неподтвержденных аккаунтов?';
$string['settings_esiatrustedauth_desc'] = 'Разрешает или запрещает авторизацию для аккаунтов, не прошедших процедуру подтверждения';
$string['settings_updatelocal'] = 'Обновление полей учетной записи Moodle';
$string['settings_updatelocal_desc'] = 'Данная настройка позволяет указать, когда необходимо обновлять данные профиля пользователя Moodle при использовании авторизации через социальные сети';
$string['updatelocal_oncreate'] = 'При создании';
$string['updatelocal_onlogin'] = 'При каждом входе';
$string['updatelocal_onlink'] = 'Во время привязки';
$string['settings_esiadisplaypopup_label'] = 'Открывать страницу аутентификации пользователя в новом всплывающем окне браузера (в виде popup)';
$string['settings_esiadisplaypopup_desc'] = '';
$string['settings_vkdisplaypopup_label'] = 'Открывать страницу аутентификации пользователя в новом всплывающем окне браузера (в виде popup)';
$string['settings_vkdisplaypopup_desc'] = '';
$string['default_registration_confirmation_message_subject'] = 'Пользователь ожидает подтверждения регистрации';
$string['default_registration_confirmation_message_short'] = 'Здравствуйте, пользователь {userfullname} зарегистрировался и ожидает подтверждения учетной записи';
$string['default_registration_confirmation_message_full'] = 'Здравствуйте!<br /><br />Пользователь {userfullname} зарегистрировался и ожидает подтверждения учетной записи.<br />
                                                     Чтобы подтвердить учетную запись, требуется перейти на <a href="{userprofileediturl}">карточку пользователя</a> и деактивировать параметр "Учетная запись заблокирована"';

// E-mail уведомления
$string['message_subject'] = 'Заголовок уведомления';
$string['message_full'] = 'Полный текст уведомления';
$string['message_short'] = 'Короткий текст уведомления';

$string['emaillinkconfirmation'] = 'Здравствуйте, {$a->firstname}.

На сайте «{$a->sitename}» был запрос на присоединение учетной записи
с указанием Вашего адреса электронной почты.

Ваш логин: {$a->username}
Ваш пароль: {$a->password}

';
$string['emailnoconfirm'] = 'Здравствуйте, {$a->firstname}.

На сайте «{$a->sitename}» была создана социальная учётная запись
с указанием Вашего адреса электронной почты.

Ваш логин: {$a->username}
Ваш пароль: {$a->password}

';
$string['toaccept'] = '

Для того, чтобы подтвердить Ваш аккаунт нажмите на ссылку ниже:
{$a->link}

В большинстве почтовых программ этот адрес должен выглядеть как синяя ссылка,
на которую достаточно нажать. Если это не так, просто скопируйте этот адрес и
вставьте его в строку адреса в верхней части окна Вашего браузера.

';
$string['signature'] = 'С уважением, администратор сайта,
{$a->admin}';
$string['emailnewuser'] = 'На сайте \'{$a->sitename}\' создан пользователь {$a->userlink} через сервис {$a->authprovider}';
$string['emailnewusersubject'] = 'Пользователь \'{$a->username}\' создал учётную запись через сервис {$a->authprovider}';
$string['emailuserlink'] = 'Пользователь {$a->userlink} связал учётную запись с {$a->authprovider} на сайте \'{$a->sitename}\', используя плагин авторизации OAuth2.0';
$string['emailuserlinksubject'] = 'Пользователь \'{$a->username}\' привязал учётную запись к {$a->authprovider}';

// События
$string['event_signin_error_name'] = 'Ошибка авторизации через внешние сервисы';
$string['event_signin_error_desc'] = 'Ошибка, возникшая во время входа пользователя в систему с использованием Oauth2 авторизации';
$string['event_request_sent_name'] = 'Запрос к внешнему сервису отправлен';
$string['event_request_sent_desc'] = 'Событие срабатывает после отправки запроса к внешнему сервису. Работает только с настраиваемыми через конфигурационный файл провайдерами и только при включенном режиме отладки уровня разработчика.';
$string['event_request_receive_name'] = 'Получен ответ от сервера авторизации';
$string['event_request_receive_desc'] = 'Событие срабатывает при получении ответа от сервера авторизации. Содержит данные об имени провайдера, полученных post и get данных, сессии пользователя.';

// Ошибки
$string['error_authorization_code_not_received'] = 'Во время авторизации произошла ошибка: не удалось получить код авторизации от {$a}. Пожалуйста, обратитесь к администратору.';
$string['error_externaluser_email_not_received'] = 'Во время авторизации произошла ошибка. Пожалуйста, обратитесь к администратору.';
$string['error_externaluser_email_not_valid'] = 'Во время авторизации произошла ошибка. Пожалуйста, обратитесь к администратору.';
$string['error_google_corporate_email_domain_notvalid'] = 'Аккаунт запрещен. Пожалуйста, используйте учетную запись с электронной почтой домена @{$a}.';
$string['error_email_is_not_allowed'] = 'Использование электронной почты аккаунта {$a} запрешено в системе. Пожалуйста, используйте другой email.';
$string['error_externaluser_account_not_verified'] = 'Вы пытаетесь использовать неподтвержденный аккаунт для авторизации в системе.';
$string['error_externaluser_account_not_linked'] = 'Ошибка привязки аккаунта.';
$string['error_signup_user_error'] = 'Ошибка регистрации пользователя в системе';
$string['error_authenticate_user_error'] = 'Во время авторизации произошла ошибка. Пожалуйста, обратитесь к администратору.';
$string['error_app_access_token_not_received'] = 'Во время авторизации произошла ошибка: не удалось получить токен приложения';
$string['error_cannot_read_the_public_certificate'] = 'Не удалось прочитать публичный ключ';
$string['error_cannot_read_the_private_certificate'] = 'Не удалось прочитать приватный ключ';
$string['error_cannot_sign_the_message'] = 'Не удалось подписать сообщение';
$string['error_certificate_does_not_exist'] = 'Публичный ключ не найден';
$string['error_private_key_does_not_exist'] = 'Приватный ключ не найден';
$string['error_temporary_folder_is_not_found'] = 'Директория для временных файлов не найдена';
$string['error_temporary_folder_is_not_writable'] = 'На директорию для временных файлов отсутствуют права на запись';
$string['error_recieved_token_data_invalid'] = 'Во время авторизации произошла ошибка: не удалось сформировать объект токена пользователя для сохранения';
$string['error_invalid_state'] = 'Не удалось проверить переданные данные, возможно произошла попытка межсайтовой подделки запроса (CSRF)';
$string['error_build_url_fail'] = 'Не удалось получить url-адрес для перенаправления на страницу авторизации социальной сети';
$string['error_cryptopro_csp_license_expired'] = 'Истекла лицензия на КриптоПро CSP';
$string['error_invalid_authprovider'] = 'Возникла ошибка при авторизации. Не указан провайдер.';

// Сообщения
$string['message_registration_disabled'] = 'Регистрация новых пользователей с использованием аккаунта из внешней системы {$a} отключена.';
$string['message_registration_duplicate_email'] = 'В системе уже имеется пользователь с таким email. Если это ваша учетная запись, войдите, используя ваши логин и пароль и свяжите вашу учетную запись с внешним {$a} аккаунтом, чтобы иметь возможность входить в систему с его использованием позднее.';
$string['message_forceproviderlogout'] = 'Ваша сессия в {$a} закончилась. Пожалуйста, войдите в систему еще раз.';
$string['message_record_need_confirmation'] = 'Учетная запись отправлена ​​на подтверждение администратору';
$string['message_record_waiting_confirmation'] = 'Учетная запись заблокирована и ожидает подтверждения администратором.';

$string['settings_page_general'] = "Общие настройки";
$string['settings_category_providers'] = "Провайдеры";
$string['custom_providers'] = "Настраиваемые провайдеры";
$string['customprovider_status_active'] = 'Активен';
$string['customprovider_status_disabled'] = 'Отключен';
$string['provider_management'] = 'Управление провайдерами';
$string['provider_management_add'] = 'Добавление настраиваемого провайдера';
$string['provider_management_edit'] = 'Редактирование настраиваемого провайдера';
$string['provider_management_delete'] = 'Удаление настраиваемого провайдера';
$string['provider_management_viewlist'] = 'Просмотр списка провайдеров';
$string['custom_providers_list_empty'] = 'Нет зарегистрированных настраиваемых провайдеров';
$string['custom_provider_delete'] = 'Удалить';
$string['custom_provider_delete_confirm'] = 'Вы собираетесь удалить настраиваемый провайдер. Пользователи больше не смогут авторизовываться через него. Продолжить?';
$string['custom_provider_delete_success'] = 'Настраиваемый провайдер успешно удален';
$string['custom_provider_edit'] = 'Редактировать';


$string['custom_provider_property_id'] = 'Идентификатор записи';
$string['custom_provider_property_id_help'] = '';
$string['custom_provider_property_code'] = 'Уникальный код';
$string['custom_provider_property_code_help'] = '';
$string['custom_provider_property_name'] = 'Наименование';
$string['custom_provider_property_name_help'] = '';
$string['custom_provider_property_description'] = 'Описание';
$string['custom_provider_property_description_help'] = '';
$string['custom_provider_property_config'] = 'Конфигурация';
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
$string['custom_provider_property_status'] = 'Статус';
$string['custom_provider_property_status_help'] = '';
$string['custom_provider_overriden'] = 'Настройки в moodle data переопределяют текущие настройки настраиваемого провайдера';

$string['custom_provider_error_while_creating'] = 'Во время регистрации нового провайдера произошла ошибка: {$a}';
$string['custom_provider_error_code_not_unique'] = 'Код не уникален, попробуйте другой';
$string['custom_provider_error_unknown_status'] = 'Статус передан не верно';
$string['custom_provider_error_missing_required_property'] = 'Не были переданы обязательные параметры';
$string['custom_provider_error_missing_id'] = 'Не был передан идентификатор записи';

$string['cp_misconfig'] = 'Во время анализа конфигурации настраиваемого провадера{$a->id}произошла ошибка: {$a->message}';
$string['cp_misconfig_config_is_empty'] = 'конфигурация пуста';
$string['cp_misconfig_config_is_not_an_array'] = 'настройки провайдера, полученные в результате анализа конфигурации должны быть представлены в виде массива';
