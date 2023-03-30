<?php
////////////////////////////////////////////////////////////////////////////
//                                                                        //
// NOTICE OF COPYRIGHT                                                    //
//                                                                        //
// Dean`s Office for Moodle                                               //
// Электронный деканат                                                    //
// <http://deansoffice.ru/>                                               //
//                                                                        //
//                                                                        //
// This program is free software: you can redistribute it and/or modify   //
// it under the terms of the GNU General Public License as published by   //
// the Free Software Foundation, either version 3 of the Licensen.        //
//                                                                        //
// This program is distributed in the hope that it will be useful,        //
// but WITHOUT ANY WARRANTY; without even the implied warranty of         //
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the          //
// GNU General Public License for more details.                           //
//                                                                        //
// You should have received a copy of the GNU General Public License      //
// along with this program.  If not, see <http://www.gnu.org/licenses/>.  //
//                                                                        //
////////////////////////////////////////////////////////////////////////////

/**
 * Плагин аутентификации Деканата. Языковые переменные.
 *
 * @package    auth_dof
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// Базовые переменные
$string['pluginname'] = 'Аутентификация СЭО 3KL';
$string['auth_settings_title'] = 'Авторизация с синхронизацией Free Deans Office';
$string['auth_dofdescription'] = 'Авторизация для плагина <a href=\'http://deansoffice.ru\' target=\'_blank\'>Free Deans Office</a>';
$string['messageprovider:dualauthsendmethod'] = 'Проверочный код при двухфакторной авторизации';

// Настройки
$string['settings_page_general'] = 'Общие настройки';
$string['settings_signupfields_header'] = 'Настройки полей формы регистрации';
$string['settings_recaptcha'] = 'Добавляет форму для подтверждения визуального/звукового элемента на страницу регистрации пользователей. Это защищает ваш сайт от спамеров. Более подробную информацию смотрите на http://www.google.com/recaptcha.';
$string['settings_recaptcha_label'] = 'Включить reCAPTCHA';
$string['settings_passwordfieldtype_label'] = 'Какой тип поля использовать для ввода пароля?';
$string['settings_passwordfieldtype'] = '';
$string['passwordfieldtype_passwordunmask'] = 'Поле с возможностью посмотреть введенный пароль';
$string['passwordfieldtype_password'] = 'Поле без возможности посмотреть введенный пароль';
$string['settings_passwordrepeat_label'] = 'Добавить поле для повтора пароля?';
$string['settings_passwordrepeat'] = '';
$string['settings_confirmation_label'] = 'Включить подтверждение учетной записи по электронной почте?';
$string['settings_confirmation'] = '';
$string['settings_auth_after_reg_label'] = 'Мгновенная авторизация после регистрации';
$string['settings_auth_after_reg_desc'] = 'Данная опция доступна только в сочетании с опцией подтверждения аккаунта по почте. Если включено, пользователь сразу после регистрации оказывается авторизованным. Но для следующей авторизации ему потребуется все-таки выполнить подтверждение.';
$string['settings_title'] = 'Настройки';
$string['settings_dof_departmentid_label'] = 'Подразделение';
$string['settings_dof_departmentid'] = 'Подразделение Электронного Деканата, в которое следует добавлять зарегистрированных пользователей. ';
$string['settings_sendmethod_label'] = 'Способ доставки сообщений';
$string['settings_sendmethod'] = 'Метод отправки данных о регистрации. Требуется выбрать способ отправки данных о регистрации';
$string['settings_signupfields_hide'] = 'Скрыть';
$string['settings_signupfields_show'] = 'Показать';
$string['settings_dual_auth'] = 'Двухфакторная аутентификация';
$string['settings_enable_dual_auth_label'] = 'Включить двухфакторную аутентификацию';
$string['settings_enable_dual_auth'] = 'Включить';
$string['settings_code_live_time_label'] = 'Срок жизни кода';
$string['settings_code_live_time'] = 'Рекомендуемое значение: 2 - 30 минут';
$string['settings_number_of_allowed_code_entry_attempts_label'] = 'Количество разрешенных попыток ввода проверочного кода';
$string['settings_number_of_allowed_code_entry_attempts_desc'] = 'Данный параметр ограничивает количество возможных попыток ввода проверочного кода, препятствуя возможному перебору со стороны злоумышленников.';

$string['limiting_registration_attempts'] = 'Ограничение попыток поиска во внешнем источнике при регистрации по предварительным спискам';
$string['settings_enable_limiting_registration_attempts_label'] = 'Включить ограничение попыток поиска во внешнем источнике';
$string['settings_plist_reg_retry_time_label'] = 'Время до восстановления попыток';
$string['settings_plist_reg_attempts_label'] = 'Количество разрешенных попыток поиска данных';
$string['settings_enable_limiting_registration_attempts_desc'] = 'Включить';
$string['settings_plist_reg_retry_time_desc'] = 'Если пользователь использовал все попытки поиска данных во внешнем источнике ему придется подождать восстановления попыток указанное в данном параметре время.';
$string['settings_plist_reg_attempts_desc'] = 'Определяет количество попыток поиска данных во внешнем источнике';

$string['settings_error_signupfield_email_must_be_shown'] = 'Поле email не настроено на странице настроек пользовательских полей формы';
$string['settings_error_signupfield_phone_must_be_shown'] = 'Поле мобильный телефон не настроено на странице настроек пользовательских полей формы';

$string['dof_departments_not_add'] = 'Не добавлять пользователей в Деканат';
$string['dof_departments_not_found'] = 'ПОДРАЗДЕЛЕНИЕ НЕ НАЙДЕНО';
$string['dof_departments_version_error'] = 'Требуется обновление хранилища подразделений';
$string['send_method_not_set'] = 'Регистрация не доступна';
$string['send_method_not_found'] = 'ОБРАБОТЧИК СООБЩЕНИЙ НЕ НАЙДЕН';

$string['settings_dof_registrationtype_label'] = 'Выбор типа регистрации';
$string['settings_dof_registrationtype'] = 'Предусмотрены следующие способы регистрации:<br/>
                                           <b>Предварительные списки</b> - реализует регистрацию пользователя с использованием данных из внешнего источника.
                                           Для регистрации по предварительным спискам требуется:<br/>
                                           <ul>
                                                <li>Добавить не менее одного внешнего источника на страницу <a href="/auth/dof/external_sources_settings.php">настроек внешних источников данных</a></li>
                                                <li>Настроить "Поисковые" и "Транслируемые" поля на странице <a href="/auth/dof/registration_fields_settings.php">настроек пользовательских полей формы регистрации</a></li>
                                           </ul>
                                           <b>Самостоятельная регистрация</b> - классический метод регистрации, пользователь самостоятельно заполняет указанные на странице <a href="/auth/dof/registration_fields_settings.php">настроек пользовательских полей формы регистрации</a> администратором поля.<br/>';
$string['registration_fields_settings'] = 'Настройки полей формы регистрации';
$string['external_sources_settings'] = 'Настройки внешних источников данных';
$string['registration_settings'] = 'Настройки регистрации';
$string['additional_fields_settings'] = 'Дополнительные настройки по полям регистрации';

$string['src_connection'] = 'Имя подключения к внешнему источнику';
$string['src_table'] = 'Таблица внешнего источника';
$string['src_config_header'] = 'Внешний источник: {$a->src_name} ({$a->cfg_name})';
$string['not_selected']  = 'Поле соответствия не выбрано';
$string['src_fields'] = 'Поля внешнего источника';
$string['form_save_success'] = 'Форма сохранена успешно';
$string['form_has_errors'] = 'Есть ошибки при валидации формы, сохранение не выполнено';
$string['delete_src'] = 'Вы уверены, что хотите удалить этот источник?';
$string['field_removed_from_reg_form'] = 'Поле "{$a}" более не будет отображаться на форме регистрации при использовании плагина регистрации СЭО 3KL';
$string['field_add_to_reg_form'] = 'Поле "{$a}" добавлено на первый шаг формы регистрации плагина регистрации СЭО 3KL';

$string['fld_display'] = 'Режим отображения поля';
$string['ext_src_compare'] = 'Сопоставление с внешними источниками';
$string['form_has_chenges'] = 'Порядок сортировки изменен, сохраните форму';

$string['display_none'] = 'Не показывать поле';
$string['display_on_step_1'] = 'Для первого этапа регистрации';
$string['display_on_step_2'] = 'Для второго этапа регистрации';
$string['need_visible_field_on_step1'] = 'На первом шаге регистрации должно отображаться по крайней мере одно поле';

$string['src_db'] = 'Внешняя база данных';
$string['db_connection_configs_list'] = 'Список настроенных подключений к внешней базе данных';
$string['db_table'] = 'Таблица во внешней базе данных';
$string['db_connection_configs_list_desc'] = '<p>Для работы источника необходимо выбрать подключение к базе данных. Создать подключение к базе данных можно на странице <a href="/local/opentechnology/dbconnection_management.php">Управление подключениями к внешним БД</a></p>';

$string['user_fields_header'] = 'Поля профиля пользователя';
$string['mod_broadcast'] = 'Транслируемое поле';
$string['mod_generated'] = 'Генерируемое поле';
$string['mod_required'] = 'Обязательное поле';
$string['mod_hidden'] = 'Скрытое поле';
$string['mod_search'] = 'Поисковое поле';

$string['group_mod_unique'] = 'Поле для проверки уникальности';

$string['add_source_header'] = 'Добавить источник';
$string['select_source'] = 'Тип источника';
$string['add_source_btn'] = 'Добавить';
$string['get_src_fields_btn'] = 'Получить поля';

$string['no_need_use_in_source_fields'] = 'Данному типу поля не должно быть сопоставлено полей из источников';
$string['error_get_src_fields'] = 'Ошибка при получении полей из внешнего источника';
$string['plist_registration_attempts'] = 'Попытки исчерпаны, {$a}';
$string['check_attempts_exhausted_all_wait'] = 'до восстановления осталось {$a} мин.';

$string['generated_field_cannnot_be_search'] = 'Поисковое поле не может быть генерируемым';
$string['generated_field_cannnot_be_broadcast'] = 'Транслируемое поле не может быть генерируемым';
$string['generated_field_cannnot_be_unique'] = 'Поле проверка уникальности не может быть генерируемым';

$string['search_field_only_on_step1'] = 'Поисковые поля могут отображаться только на первом этапе в форме регистрации';
$string['search_field_need_source_comparison'] = 'При использовании модификатора "Поисковое поле" должно быть настроено соответствие со всеми внешними источниками';

$string['broadcast_field_need_source_comparison'] = 'При использовании модификатора "Транслируемое поле" должно быть настроено соответствие со всеми внешними источниками';
$string['broadcast_field_only_on_step2'] = 'Транслируемые поля могут отображаться только на втором этапе в форме регистрации';
$string['broadcast_field_need_search_fields'] = 'Для работы транслируемых полей требуется как минимум одно поисковое поле на первом этапе регистрации';

$string['hidden_field_cannnot_be_search'] = 'Поисковое поле не может быть скрытым';
$string['hidden_field_requirements'] = 'Поле без модификатора "Генерируемое поле" или  "Транслируемое поле" не может быть скрыто';

// Форма регистрации
$string['error_signup_disabled'] = 'Регистрация не доступна';
$string['error_signup_username_not_generated'] = 'Ошибка регистрации';
$string['registration'] = 'Регистрация';
$string['createaccount'] = 'Зарегистрироваться';
$string['phone_not_valid'] = 'Некорректный номер телефона';
$string['phone_exists'] = 'Номер телефона уже указан в системе';
$string['otsms_send_success_message'] = 'На указанный Вами номер телефона было выслано SMS с данными для входа в систему.';
$string['otsms_send_error_message'] = 'Во время отправки SMS произошла ошибка. Пожалуйста, свяжитесь с администратором сайта.';
$string['otsms_send_error_title'] = 'Ошибка отправки SMS';
$string['otsms_send_success_title'] = 'Отправка SMS с данными для входа';
$string['email_send_success_message'] = 'На указанный Вами адрес электронной почты было выслано письмо с данными для входа в систему.';
$string['email_send_error_message'] = 'Во время отправки письма произошла ошибка. Пожалуйста, свяжитесь с администратором сайта.';
$string['email_send_error_title'] = 'Ошибка отправки Email';
$string['email_send_success_title'] = 'Отправка электронного письма с данными для входа';
$string['send_error_title'] = 'Во время отправки данных для входа в систему произошла ошибка';
$string['send_success_title'] = 'Отправка данных для входа в систему прошла успешно';

$string['src_no_queryresult'] = 'Не удалось получить данные из внешнего источника: {$a}';
$string['src_many_entries_by_conditions'] = 'Во внешнем источнике "{$a}" по переданным условиям найдено более одной записи.';
$string['src_no_entries_by_conditions'] = 'Во внешнем источнике "{$a}" по переданным условиям не найдено записей.';
$string['no_records_found'] = 'Не найдено записей во внешнем источнике удовлетворяющих условиям, измените ввод или обратитесь к администратору.';
$string['no_valid_broadcast_fields'] = 'Данные из внешнего источника не прошли валидацию, обратитесь к администратору.';
$string['similar_data_found'] = 'Пользователь с аналогичными данными уже зарегистрирован в системе, обратитесь к администратору';
$string['src_connection_error'] = 'Ошибка подключения {$a}';
$string['field_value_too_long'] = 'Длинна поля, полученного из внешнего источника, больше максимальной разрешеноой длинны для "{$a}"';

$string['fff_datetime_not_valid'] = 'Дата находится вне заданного настройками периода';
$string['fff_menu_not_valid'] = 'Значение отсутствует в списке возможных значений для select заданного в настройках.';
$string['fff_checkbox_not_valid'] = 'Значение для checkbox не валидно';

// Форма двойной авторизации
$string['dual_auth'] = 'Подтверждение авторизации';
$string['dual_auth_text'] = 'Введите полученый код:';
$string['no_user_id'] = 'Не передан индентефикатор пользователя';
$string['auth_time_expiried'] = 'Время жизни проверочного кода истекло';
$string['wrong_code'] = 'Введен не верный проверочный код';
$string['exhausted_all_attempts'] = 'Исчерпаны все попытки ввода проверочного кода';
$string['dualauth_error_code_missed'] = 'Во время авторизации произошла ошибка';
$string['confirm'] = 'Подтвердить';

$string['subject_verification_code'] = 'Проверочный код';
$string['verification_code_full'] = ' Здравствуйте, {$a->firstname}!
При авторизации на сайте \'{$a->sitename}\' для Вас был создан проверочный код:

{$a->code}

Чтобы завершить авторизацию пройдите по ссылке {$a->link}

С уважением, администратор сайта \'{$a->sitename}\'';
$string['verification_code_short'] = 'Проверочный код: {$a->code}';


$string['newuserfull'] = ' Здравствуйте, {$a->firstname}!
На сайте \'{$a->sitename}\' для Вас была создана новая учетная запись.
Вы можете зайти на сайт по следующим данным:

Логин: {$a->username}
Пароль: {$a->newpassword}

Чтобы начать использование сайта \'{$a->sitename}\',
пройдите по ссылке {$a->link}

С уважением, администратор сайта \'{$a->sitename}\', {$a->signoff}';
$string['newusershort'] = 'Логин: {$a->username}'."\n".'Пароль: {$a->newpassword}';
$string['passwordrepeat'] = 'Введите пароль еще раз';
$string['missingpasswordrepeat'] = 'Заполните поле';
$string['error_password_mismatch'] = 'Введенные пароли не совпадают';
$string['auth_emailnoemail'] = 'Отправить вам письмо не удалось!';

$string['event_auth_confirmed'] = "Регистрация пользователя подтверждена";