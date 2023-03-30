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
 * Плагин определения заимствований Антиплагиат. Языковые переменные.
 *
 * @package    plagiarism
 * @subpackage apru
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$string['pluginname'] = 'Плагин "Антиплагиат"';
$string['apru'] = 'Антиплагиат';
$string['apru:enable'] = 'Включить антиплагиат';
$string['apru:viewsimilarityscore'] = 'Посмотреть процент уникальности';
$string['apru:viewfullreport'] = 'Посмотреть отчёт, включающий процент заимствований';
$string['apru:enableindexstatus'] = 'Добавлять документы в индекс Антиплагиата';
$string['apru:disableindexstatus'] = 'Удалять документы из индекса Антиплагиата';

$string['apruconfig'] = 'Конфигурация плагина плагиаризма "Антиплагиат"';
$string['aprudefaults'] = 'Настройки плагина плагиаризма "Антиплагиат" по умолчанию';
$string['aprupluginsettings'] = 'Настройки плагина плагиаризма "Антиплагиат"';
$string['checklistresource'] = 'Использовать поисковый индекс "{$a}"';
$string['checklist_wikipedia'] = 'Википедия';
$string['checklist_internet'] = 'Интернет';
$string['config'] = 'Конфигурация ';
$string['config:host'] = 'Имя хоста';
$string['config:companyname'] = 'Название';
$string['config:siteurl'] = 'URL для генерации отчётов';
$string['config:siteurl_help'] = 'Точка входа для генерации отчётов (по умолчанию используется формат http://COMPANYNAME.antiplagiat.ru)';
$string['configupdated'] = 'Конфигурация обновлена';
$string['defaults'] = 'Настройки по умолчанию';
$string['defaultsdesc'] = 'Следующие установки являются установками по умолчанию, когда "Антиплагиат" включается внутри модуля активности';
$string['defaultupdated'] = 'Значения по умолчанию "Антиплагиат" обновлены';
$string['defaultupdateerror'] = 'Произошла ошибка при попытке обновить значение установки по умолчанию в базе данных';
$string['estimatedwait'] = 'Приблизительное время ожидания: {$a} секунд';
$string['noconnection'] = 'Нет соединения с сервером...';
$string['originality'] = 'Оригинальность: {$a}%';
$string['processingyet'] = 'На проверке...';
$string['processingfailed'] = 'Ошибка при загрузке';
$string['notupload'] = 'Загружается...';
$string['reportlink'] = 'Ссылка на отчёт';
$string['studentreports'] = 'Отобразить cвидетельства оригинальности для студентов';
$string['studentreports_help'] = 'Позволяет Вам показывать свидетельства оригинальности "Антиплагиат" студентам-пользователям. Если установлен на да, то свидетельства оригинальности генерируемые "Антиплагиат" доступны для просмотра студентами.';
$string['submissioncheck'] = 'Загруженный ответ будет протестирован на наличие заимствований в системе "Антиплагиат"';
$string['use_assignment'] = 'Использовать в элементе курса "Задание"';
$string['use_forum'] = 'Использовать в элементе курса "Форум"';
$string['use_workshop'] = 'Использовать в элементе курса "Семинар"';
$string['useapru'] = 'Включить "Антиплагиат"';
$string['useapru_mod'] = 'Использовать в элементе курса {$a}';
$string['otapi'] = 'Тарифный план';
$string['setting_mod_assign_confirmation_required'] = 'Требовать блокировки ответа для проверки';
$string['setting_docs_for_check'] = 'Количество документов, отправляемых за раз на проверку в Антиплагиат';
$string['setting_docs_for_update'] = 'Количество документов, отправляемых за раз на синхронизацию';

$string['notice_author_not_set'] = 'Автор не указан';
$string['attribute_name_onlinexext'] = 'Текст, добавленный в рамках модуля курса {$a}';
$string['attribute_name_file'] = 'Файл, добавленный в рамках модуля курса {$a}';

/** OT serial **/
$string['pageheader'] = 'Получение серийного номера';
$string['otkey'] = 'Секретный ключ';
$string['otserial'] = 'Серийный номер СЭО 3KL';

$string['get_otserial'] = 'Получить серийный номер';
$string['get_otserial_fail'] = 'Не удалось получить серийный номер СЭО 3KL на сервере api.opentechnology.ru. Сервер сообщил ошибку: {$a}';
$string['reset_otserial'] = "Сбросить серийный номер";
$string['already_has_otserial'] = 'Инсталляция уже зарегистрирована и получила серийный номер, нет необходимости получать ещё один.';
$string['already_has_serial'] = 'Инсталляция уже зарегистрирована и получила серийный номер, нет необходимости получать ещё один.';
$string['otserial_check_ok'] = 'Серийный номер действителен.';
$string['otserial_check_fail'] = 'Серийный номер не прошел проверку на сервере.
Причина: {$a}. Если Вы считаете, что этого не должно было
произойти, пожалуйста, обратитесь в службу технической поддержки.';
$string['otserial_tariff_wrong'] = "Тарифный план недоступен для данного продукта. Обратитесь в службу технической поддержки.";

$string['otservice'] = 'Тарифный план: <u>{$a}</u>';
$string['otservice_send_order'] = "Заполнить заявку на заключение договора об обслуживании";
$string['otservice_renew'] = 'Заполнить заявку на продление';
$string['otservice_change_tariff'] = 'Сменить тарифный план';

$string['otservice_expired'] = 'Срок действия Вашего тарифного плана истёк. Если Вы желаете продлить срок, пожалуйста, свяжитесь с менеджерами ООО "Открытые технологии".';
$string['otservice_active'] = 'Тарифный план действителен до {$a}';
$string['otservice_unlimited'] = 'Тарифный план действует бессрочно';

$string['demo_settings'] = 'Для активации плагина обратитесь в <a href="http://antiplagiat.ru/">Антиплагиат</a>, или компанию <a href="http://opentechnology.ru/">Открытые Технологии</a>.';

$string['save'] = 'Сохранить';

$string['index_status_select_enable'] = 'Работа в индексе антиплагиата';
$string['index_status_select_disable'] = 'Работа не в индексе антиплагиата';

/** Ошибки **/
$string['error_checkservices'] = 'Один из следующих индексов цитирования недоступен для проверки документов: {$a}';
$string['error_document_not_found'] = 'Документ {$a} не найден';
$string['error_document_externalid_not_set'] = 'Внешний идентификатор документа не указан';
$string['error_access_enableindexstatus_denied'] = 'У Вас нет доступа к добавлению документа в Индекс';
$string['error_access_disableindexstatus_denied'] = 'У Вас нет доступа к удалению документа из Индекса';
$string['error_document_index_status_not_changed'] = 'Статус индексации документа не был изменен';
$string['error_hashfile_not_found'] = 'Файл с указанных хэшем {$a} не найден';
$string['error_hashfile_is_directory'] = 'Файл с указанных хэшем {$a} - это папка';
$string['error_adding_file_to_queue'] = 'Файл с указанных хэшем {$a} не добавлен в очередь на загрузку в систему Антиплагиат';
$string['error_documenttype_not_supported'] = 'Тип документа {$a} не поддерживается системой Антипагиат';
$string['error_connection'] = 'Ошибка соединния с сервисом Антиплагиат';
$string['error_connection_upload_file'] = 'Ошибка загрузки документа в сервис Антиплагиат';
$string['error_service_checking_document'] = 'Ошибка установки документа на проверку в сервисе Антиплагиат';
$string['error_service_deleting_document'] = 'Ошибка удаления документа в сервисе Антиплагиат';
$string['error_service_uploading_document'] = 'Ошибка загрузки документа в сервис Антиплагиат';
$string['error_service_getting_document_checkstatus'] = 'Ошибка получения данных о проверке документа в сервисе Антиплагиат';
$string['error_service_getting_document_report'] = 'Ошибка получения отчета о документе в сервисе Антиплагиат';
$string['error_service_getting_enumerate_documents'] = 'Ошибка получения набора документов из сервиса Антиплагиат';
$string['error_service_get_tariff_info'] = 'Ошибка получения информации по тарифу';

/** События **/
$string['event_set_indexed_status_title'] = 'Смена статуса нахождения в индексе';
$string['event_set_indexed_status_desc'] = 'Смена статуса нахождения в индексе у документа с ID {$a}';
$string['event_send_document_title'] = 'Отправка документа в систему Антиплагиат';
$string['event_send_document_desc'] = 'Отправки документа с ID {$a} в систему Антиплагиат для проверки уникальности';

/** Периодические задачи **/
$string['task_send_documents_title'] = 'Загрузка документов в систему Антиплагиат';
$string['task_check_documents_title'] = 'Установка документов на проверку в системе Антиплагиат';
$string['upload_successful'] = 'Успешная загрузка документа с внешним идентификатором {$a}';
$string['upload_failed'] = 'Загрузка документа с внутренним идентификатором {$a} не удалась';

/** Информация по тарифу **/
$string['apru_tarif_name'] = 'Тарифный план';
$string['apru_tarif_subscriptiondate'] = 'Дата подписки';
$string['apru_tarif_expirationdate'] = 'Дата окончания подписки';
$string['apru_tarif_totalcheckscount'] = 'Количество доступных проверок по тарифу';
$string['apru_tarif_remainedcheckscount'] = 'Количество оставшихся проверок';
$string['apru_tarif_no_information'] = 'Информация недоступна';
$string['apru_tarif_get_information_failed'] = 'Не удалось получить информацию о тарифе';
$string['apru_tarif_connection_failed'] = 'Не удалось установить соединение';
$string['apru_tarif_tarif_information'] = 'Информация о тарифе';
$string['apru_update_reporturl'] = 'Ссылка на отчет о проверке документа ID={$a->id} обновлена - {$a->reporturl}';

/** Таски **/
$string['task_update_documents_title'] = 'Выполнить синхронизацию данных';

/** Страница просмотра задания **/
$string['add_to_index'] = 'Добавить в индекс';
$string['remove_from_index'] = 'Убрать из индекса';


