<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Языковые строки
 *
 * @package    local_opentechnology
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$string['pluginname'] = 'Техническая поддержка СЭО 3KL';
$string['get'] = 'получить';
$string['save'] = 'сохранить';

$string['pageheader'] = 'Техническая поддержка СЭО 3KL';
$string['otkey'] = 'Секретный ключ';
$string['otserial'] = 'Серийный номер';

$string['get_otserial'] = 'Получить серийный номер';
$string['get_otserial_fail'] = 'Не удалось получить серийный номер СЭО 3KL на сервере api.opentechnology.ru. Сервер сообщил ошибку: {$a}';
$string['reset_otserial'] = "Сбросить серийный номер";
$string['already_has_otserial'] = 'Инсталляция уже зарегистрирована и получила серийный номер, нет необходимости получать ещё один.';
$string['otserial_check_ok'] = 'Серийный номер действителен.';
$string['otserial_check_fail'] = 'Серийный номер не прошел проверку на сервере.
Причина: {$a}. Если Вы считаете, что этого не должно было
произойти, пожалуйста, обратитесь в службу технической поддержки.';
$string['otserial_tariff_wrong'] = "Тарифный план недоступен для данного продукта. Обратитесь в службу технической поддержки.";

// Service
$string['otservice'] = 'Тарифный план: <u>{$a}</u>';
$string['otservice_send_order'] = "Заполнить заявку на заключение договора об обслуживании";
$string['otservice_renew'] = 'Заполнить заявку на продление';
$string['otservice_change_tariff'] = 'Сменить тарифный план';

$string['otservice_expired'] = 'Срок действия Вашего тарифного плана истёк. Если Вы желаете продлить срок, пожалуйста, свяжитесь с менеджерами ООО "Открытые технологии".';
$string['otservice_active'] = 'Тарифный план действителен до {$a}';
$string['otservice_unlimited'] = 'Тарифный план действует бессрочно';

$string['opentechnology:see_manager_hints'] = 'Видеть подсказки для менеджера';
$string['opentechnology:see_coursecreator_hints'] = 'Видеть подсказки для редактора курса';
$string['opentechnology:see_editingteacher_hints'] = 'Видеть подсказки для учителя';
$string['opentechnology:see_student_hints'] = 'Видеть подсказки для студента';
$string['opentechnology:reset_site_identifier'] = 'Сбросить идентификатор инсталляции';
$string['opentechnology:view_about'] = 'Просматривать техническую информацию об инсталляции';
$string['nopermissions'] = 'У вас нет права делать это: {$a}';

$string['shortcode:courseid'] = 'Идентификатор курса';
$string['shortcode:coursefullname'] = 'Полное название курса';
$string['shortcode:currentyear'] = 'Текущий год';
$string['shortcode:currentmonthnumberzero'] = 'Порядковый номер месяца с ведущим нулём';
$string['shortcode:currentmonthstr'] = 'Полное наименование месяца';
$string['shortcode:currentdaynumberzero'] = 'День месяца, 2 цифры с ведущим нулём';
$string['shortcode:currentdaynumber'] = 'День месяца, с ведущим пробелом, если он состоит из одной цифры';
$string['shortcode:currentdaystr'] = 'Полное наименование дня недели';
$string['shortcode:release3kl'] = 'Релиз СЭО 3KL';

// Классы настроек
$string['admin_setting_button_text'] = 'Настроить';
$string['admin_setting_dialogue_header'] = 'Настройки';
$string['frontend_handler_not_found'] = 'Указанный обработчик поля не найден. Возможно вы забыли положить файл ({$a}).';

// Reset site identifier
$string['reset_site_identifier'] = 'Сброс идентификатора инсталляции';
$string['about'] = 'Техническая информация';
$string['reset_site_identifier_title'] = 'Сброс идентификатора инсталляции';
$string['about_title'] = 'Техническая информация';
$string['reset_form_submit'] = 'Сбросить идентификатор';
$string['unregister_successfull'] = 'Отмена регистрации инсталляции в каталоге прошла успешно';
$string['reset_site_identifier_successfull'] = 'Сброс идентификатора инсталляции прошел успешно';
$string['unset_site_identifier_successfull'] = 'Удаление идентификатора инсталляции прошло успешно';
$string['unregister_failed'] = 'Во время отмены регистрации в каталоге прозошли ошибки';
$string['reset_site_identifier_failed'] = 'Во время сброса идентификатора инсталляции прозошли ошибки';
$string['unset_site_identifier_failed'] = 'Во время удаления идентификатора инсталляции прозошли ошибки';
$string['site_identifier_not_found'] = 'Указанный идентификатора инсталляции не найден';

// About
$string['system_info'] = 'Системная информация';
$string['system_info_desc'] = '';
$string['moodle_version'] = 'Версия Moodle';
$string['moodle_release'] = 'Релиз Moodle';
$string['our_build'] = 'Билд 3kl';
$string['maturity'] = 'Стадия разработки 3kl';
$string['database_size'] = 'Размер базы данных';
$string['moodledata_size'] = 'Размер moodledata';
$string['useful_volume'] = 'Полезный объем';
$string['go_to_report_coursesize'] = 'Перейти к отчету с размером курсов *';
$string['report_coursesize_comment'] = '* Cуммарный размер по курсам может не совпадать с размером по данным этого модуля. Отчет по курсам не учитывает файлы, используемые вне курсов.';
$string['moodle_size_limit'] = 'Лимит на полезный объем';
$string['moodle_size_limit_disabled'] = 'Отсутствует';
$string['moodle_size_limit_enabled'] = 'Безусловный';
$string['moodle_size_limit_exceeded'] = 'Включено ограничение на загрузку файлов';
$string['users_count'] = 'Количество пользователей';
$string['online_users_count'] = 'Количество пользователей онлайн';
$string['courses_count'] = 'Количество курсов';

// Errors
$string['error_failed_to_get_moodledata_size'] = 'Не удалось определить размер moodledata';
$string['error_failed_to_get_database_size'] = 'Не удалось определить размер базы данных';
$string['error_failed_to_get_useful_volume'] = 'Не удалось определеить размер полезного объема';
$string['error_failed_to_get_free_diskspace'] = 'Не удалось получить размер свободного дискового пространства';
$string['error_failed_to_get_ifconfig'] = 'Не удалось получить параметры сетевых интерфейсов';

$string['about_was_replaced'] = 'Вы были перемещены в раздел технической поддержки СЭО 3KL, так как сведения о технической информации были перенесены сюда.';


$string['otserial_settingspage_visiblename'] = 'Тарифный план';
$string['otserial_settingspage_otserial'] = 'Серийный номер';
$string['otserial_settingspage_issue_otserial'] = 'Получить серийный номер';
$string['otserial_settingspage_otservice'] = 'Тарифный план: <u>{$a}</u>';

$string['otserial_exception_already_has_serial'] = 'Серийный номер уже был получен';
$string['otserial_exception_not_configured'] = 'Не хватает обязательных настроек';
$string['otserial_exception_status_ko'] = 'Вернулся неверный статус';
$string['otserial_exception_unknown'] = 'Неизвестная ошибка';
$string['otserial_exception_expirytime_wrong'] = 'Срок действия тарифного плана настроен не верно. Обратитесь в службу технической поддержки.';


$string['otserial_error_get_otserial_fail'] = 'Не удалось получить серийный номер СЭО 3KL на сервере api.opentechnology.ru. Сервер сообщил ошибку: {$a}';
$string['otserial_error_otserial_check_fail'] = 'Серийный номер не прошел проверку на сервере.
Причина: {$a}. Если Вы считаете, что этого не должно было
произойти, пожалуйста, обратитесь в службу технической поддержки.';
$string['otserial_error_tariff_wrong'] = "Тарифный план недоступен для данного продукта. Обратитесь в службу технической поддержки.";
$string['otserial_error_otservice_expired'] = 'Срок действия Вашего тарифного плана истёк. Если Вы желаете продлить срок, пожалуйста, свяжитесь с менеджерами ООО "Открытые технологии".';

$string['otserial_notification_otserial_check_ok'] = 'Серийный номер действителен.';
$string['otserial_notification_otservice_active'] = 'Тарифный план действителен до {$a}';
$string['otserial_notification_otservice_unlimited'] = 'Тарифный план действует бессрочно';

$string['diskspace_monitoring'] = 'Мониторинг свободного дискового пространства на сервере';
$string['diskspace_comment'] = 'На сервере должно всегда оставаться не менее 20% свободного места. Исчерпание свободного места приводит к немедленному зависанию сервера, а при неудачном стечении обстоятельств - к потере данных. Ни в коем случае не перезагружайте сервер, зависший из-за нехватки места - СУБД должна записать не сохраненные данные на диск, иначе вы потеряете вашу базу данных. Рекомендуем хранить базу данных, файлы Moodle, временные файлы и резервные копии на разных разделах накопителя.';
$string['partition_purpose'] = 'Назначение раздела';
$string['free_diskspace_bytes'] = 'Свободного места (GB)';
$string['free_diskspace_percentage'] = 'Свободного места (%)';
$string['additional_info'] = 'Дополнительная информация о продукте';
$string['our_release'] = 'Версия СЭО 3KL:';
$string['admins_additional_info'] = 'Дополнительная информация для диспетчера-администратора';
$string['dg_not_specified'] = 'Не определен';
$string['network_interface_parameters'] = 'Параметры сетевых интерфейсов на сервере';
$string['default_gateway'] = 'Шлюз по умолчанию';
$string['dns_server_list'] = 'DNS серверы';
$string['if_name'] = 'Название';
$string['inet_addr'] = 'IPv4';
$string['net_mask'] = 'Маска подсети';

// Менеджер подключений к внешним БД

$string['dbconnection_management'] = 'Управление подключениями к внешним БД';
$string['dbconnection_name'] = 'Название подключения';
$string['dbconnection_new'] = 'Создать новое подключение';
$string['dbconnection_delete'] = 'Удалить это подключение';
$string['dbconnection_host'] = 'Сервер базы данных';
$string['dbconnection_type'] = 'База данных';
$string['dbconnection_database'] = 'Название базы данных';
$string['dbconnection_user'] = 'Пользователь базы данных';
$string['dbconnection_pass'] = 'Пароль';
$string['dbconnection_setupsql'] = 'Команда настройки SQL';
$string['dbconnection_extencoding'] = 'Кодировка внешней базы данных';
$string['dbconnection_name_should_not_be_empty'] = 'Имя соединения не должно быть пустым';
$string['dbconnection_check_connection'] = 'Проверить подключение';
$string['dbconnection_check_connection_successful'] = 'Подключение успешно';
$string['dbconnection_check_connection_failed'] = 'Подключение не удалось. Сообщение об ошибке: {$a}';
$string['dbconnection_back_to_dbconnections'] = 'Вернуться к подключениям';
$string['connection'] = 'Подключение';

// Права
$string['opentechnology:manage_db_connections'] = 'Настраивать подключения к внешним источникам';

// Условия доступа
$string['ac_add'] = 'Добавить';
$string['ac_remove'] = 'Удалить';

$string['empty_string'] = '(Пустая строка)';

$string['logical_group_and'] = 'Логическая группа "И"';
$string['logical_group_and_desc'] = 'Все условия внутри группы должны выполняться';
$string['logical_group_and_userdesc'] = 'Все условия внутри группы должны выполняться {$a}';
$string['logical_group_or'] = 'Логическая группа "ИЛИ"';
$string['logical_group_or_desc'] = 'Хотя бы одно условие внутри группы должно выполниться';
$string['logical_group_or_userdesc'] = 'Хотя бы одно условие внутри группы должно выполниться {$a}';

$string['comparison_operator_eq'] = 'Равно';
$string['comparison_operator_eq_desc'] = 'Первый аргумент должен быть равен второму';
$string['comparison_operator_eq_userdesc'] = 'Значение {$a->arg1} должно быть равно значению {$a->arg2}';
$string['comparison_operator_gt'] = 'Больше';
$string['comparison_operator_gt_desc'] = 'Первый аргумент должен быть больше второго';
$string['comparison_operator_gt_userdesc'] = 'Значение {$a->arg1} должно быть больше значения {$a->arg2}';
$string['comparison_operator_gte'] = 'Больше или равно';
$string['comparison_operator_gte_desc'] = 'Первый аргумент должен быть больше или равен второму';
$string['comparison_operator_gte_userdesc'] = 'Значение {$a->arg1} должно быть больше или равно значению {$a->arg2}';
$string['comparison_operator_lt'] = 'Меньше';
$string['comparison_operator_lt_desc'] = 'Первый аргумент должен быть меньше второго';
$string['comparison_operator_lt_userdesc'] = 'Значение {$a->arg1} должно быть меньше значения {$a->arg2}';
$string['comparison_operator_lte'] = 'Меньше или равно';
$string['comparison_operator_lte_desc'] = 'Первый аргумент должен быть меньше или равен второму';
$string['comparison_operator_lte_userdesc'] = 'Значение {$a->arg1} должно быть меньше или равно значению {$a->arg2}';
$string['comparison_operator_neq'] = 'Не равно';
$string['comparison_operator_neq_desc'] = 'Первый аргумент должен быть не равен второму';
$string['comparison_operator_neq_userdesc'] = 'Значение {$a->arg1} должно быть не равно значению {$a->arg2}';

$string['replacement_userfield'] = 'Поле пользователя';
$string['replacement_profilefield'] = 'Поле профиля';
$string['replacement_string'] = 'Ввод с клавиатуры';



