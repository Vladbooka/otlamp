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
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle. If not, see <http://www.gnu.org/licenses/>.

/**
 * Плагин записи на курс OTPAY. Языковые переменные плагина.
 *
 * @package    enrol
 * @subpackage otpay
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once($CFG->dirroot . '/enrol/otpay/locallib.php');

// Базовые языковые переменные
$string['pluginname'] = 'OT Pay';
$string['enrol_otpay'] = 'Плагин записи на курс OT Pay';
$string['title'] = 'Способ записи на курс OT Pay';
$string['otpay:config'] = 'Настраивать способ записи';
$string['otpay:appsmanage'] = 'Право на использование панели администрирования заявок';
$string['otpay:manage'] = 'Редактировать OTPay-подписки на курс';
$string['otpay:unenrol'] = 'Отписывать от курса пользователей, подписанных через OTPay';
$string['otpay:unenrolself'] = 'Отписываться от курса самостоятельно';
$string['description_for_user'] = 'Краткое описание для пользователя';
$string['otpay_basecost'] = 'Стоимость:';
$string['otpay_amount'] = 'Стоимость со скидкой:';
$string['otpay_enrolstartdate'] = 'Начало обучения:';
$string['otpay_enrolstartdate_currenttime'] = 'Сразу после подписки';
$string['otpay_enrolperiod'] = 'Период обучения:';
$string['otpay_enrolenddate'] = 'Окончание регистрации на курс:';
$string['otpay_couponrequired'] = 'Для подписки на курс требуется ввести код купона';
$string['otpay_wrong_data'] = 'В процессе обработки платежа произошла ошибка. Банк вернул неверные данные. Обратитесь в техподдержку.';
$string['future_enrollment'] = 'Вы уже подписаны на этот курс. Дата начала действия подписки: {$a}';
$string['otpay_currency_643'] = '{$a}₽';//&#8381;
$string['otpay_currency_840'] = '${$a}';
$string['otpay_currency_398'] = '{$a}₸';//&#8376;

$string['return_to_course'] = 'Вернуться в курс';
$string['accountgenerate_form'] = 'Заполнение формы';

// Служебные строки
$string['required'] = 'Заполните поле!';
$string['send'] = 'Отправить';
$string['submit_request'] = 'Отправить заявку';

// Общие языковые строки
$string['lastname'] = 'Фамилия: {$a}';
$string['firstname'] = 'Имя: {$a}';
$string['phone'] = 'Телефон: {$a}';
$string['email'] = 'Электронная почта: {$a}';
$string['comment'] = 'Комментарий: {$a}';
$string['company'] = 'Название организации: {$a}';
$string['type'] = 'Тип: {$a}';
$string['payer'] = 'Наименование/ФИО: {$a}';
$string['payeremail'] = 'Электронная почта: {$a}';
$string['payeraddr'] = 'Юридический адрес: {$a}';
$string['payeraddrmail'] = 'Почтовый адрес: {$a}';
$string['payerphone'] = 'Телефон: {$a}';
$string['payerinn'] = 'ИНН: {$a}';
$string['payerkpp'] = 'КПП: {$a}';
$string['account_number'] = 'Номер счета: {$a}';
$string['payerfio'] = 'ФИО: {$a}';
$string['payername'] = 'Наименование: {$a}';
$string['part_of_payerkpp'] = ', КПП ';
$string['unenrolselfconfirm'] = 'Вы действительно хотите исключить себя из курса «{$a}»?';

// Плейсхолдеры
$string['placeholder_payeremail'] = 'example@example.ru';
$string['placeholder_payeraddr'] = '123456, г. Москва, Первый пр-т, д.19';
$string['placeholder_payeraddrmail'] = '123456, г. Москва, Второй пр-т, д.19';
$string['placeholder_payerphone'] = '89876543211';
$string['placeholder_payerinn'] = '7776665554';
$string['placeholder_payerkpp'] = '644901001';
$string['placeholder_payername'] = 'ООО «Компания»';
$string['placeholder_fio'] = 'Иванов Иван';
$string['placeholder_payer'] = $string['placeholder_payer'] = 'Иванов Иван';

// Служебные поля
$string['sum_amount_643'] = '{$a->int} руб. {$a->fract} коп.';
$string['kopecks'] = 'копеек';
$string['withyear'] = '{$a} г.';
$string['for_payment'] = 'За «{$a}»';
$string['for_account_number_course_code'] = 'За «{$a->course_code}» по счету №{$a->account_number} от {$a->date} г.';
$string['recipient_name'] = 'ООО «Компания»';

// Поля вкладок
$string['recipient'] = 'Получатель';
$string['kpp'] = 'КПП';
$string['inn'] = 'ИНН';
$string['oktmo'] = 'ОКТМО';
$string['raccount'] = 'Р/сч.';
$string['rinn'] = 'Банк';
$string['bik'] = 'БИК';
$string['kaccount'] = 'К/сч.';
$string['kbk'] = 'Код бюджетной классификации (КБК)';
$string['payment'] = 'Платеж';
$string['settings_account_number'] = 'Алгоритм формирования номера счета';
$string['settings_account_number_id'] = 'Генерация по идентификатору';
$string['settings_account_number_hash'] = 'Генерация по хешу';

// Типы вкладок
$string['individual'] = 'Физ. лицо';
$string['entity'] = 'Юр. лицо';
$string['entity_ip'] = 'ИП';
$string['simple'] = 'Простая оплата через банк';
$string['receipt'] = 'Квитанция ПД-4';
$string['sitecall'] = 'Заявка на курс';

// Ссылки
$string['url_admin_panel'] = 'Панель администрирования заявок';
$string['url_coupon_panel'] = 'Панель управления купонами';

// Статусы
$string['draft'] = 'Ожидание';
$string['confirmed'] = 'Оплачено';
$string['used'] = 'Использован';
$string['needdebit'] = 'В процессе списания';
$string['waitdebit'] = 'Ожидание оплаты';
$string['rejected'] = 'Отклонен';
$string['active'] = 'Активен';

// Провайдеры
$string['otpay_method'] = 'Способ оплаты';
$string['otpay_kazkom'] = 'Казкоммерцбанк';
$string['otpay_acquiropay'] = 'AcquiroPay';
$string['otpay_coupon'] = 'Зачисление по купону';
$string['otpay_sberbank'] = 'Сбербанк';
$string['otpay_yandex'] = 'ЮKassa';
$string['otpay_accountgenerate'] = 'Генерация счета';

// Платежные системы
$string['otpay_paysystem_alfabank'] = 'Альфабанк';
$string['otpay_paysystem_amex'] = 'American Express';
$string['otpay_paysystem_beeline'] = 'Билайн';
$string['otpay_paysystem_maestro'] = 'Maestro';
$string['otpay_paysystem_mastercard'] = 'Mastercard';
$string['otpay_paysystem_mir'] = 'МИР';
$string['otpay_paysystem_mts'] = 'МТС';
$string['otpay_paysystem_paypal'] = 'Paypal';
$string['otpay_paysystem_pochtaros'] = 'Почта России';
$string['otpay_paysystem_qiwi'] = 'QIWI';
$string['otpay_paysystem_sberbank'] = 'Сбербанк';
$string['otpay_paysystem_visa'] = 'Visa';
$string['otpay_paysystem_visaelectron'] = 'Visa Electron';
$string['otpay_paysystem_webmoney'] = 'Webmoney';
$string['otpay_paysystem_yoomoney'] = 'ЮMoney';

// Ставка НДС
$string['settings_tax_first'] = 'Без НДС';
$string['settings_tax_second'] = 'НДС по ставке 0%';
$string['settings_tax_third'] = 'НДС чека по ставке 10%';
$string['settings_tax_fourth'] = ' НДС чека по ставке 20%';
$string['settings_tax_fifth'] = 'НДС чека по расчетной ставке 10/110';
$string['settings_tax_sixth'] = 'НДС чека по расчетной ставке 20/120';

// Формы настроек
$string['form_field_status'] = 'Способ подписки активен';
$string['form_field_roleid'] = 'Роль';
$string['form_return_url'] = 'После оплаты перенаправлять пользователя';
$string['return_url_course'] = 'На страницу курса';
$string['return_url_localcrw'] = 'На страницу описания курса';
$string['return_url_wantsurl'] = 'На последнюю посещенную страницу';
$string['form_field_enrolstartdate'] = 'Дата начала подписки';
$string['form_field_enrolstartdate_help'] = 'Пользователь не сможет начать обучение ранее этой даты';
$string['form_field_enrolperiod'] = 'Период обучения по подписке';
$string['form_field_enrolperiod_help'] = 'Отрезок времени, на который пользователь должен быть подписан';
$string['form_field_enrolenddate'] = 'Дата окончания подписки';
$string['form_field_enrolenddate_help'] = 'Пользователь не сможет подписываться на курс после этой даты';
$string['form_field_enrolmentnotify'] = 'Рассылать уведомления о новых подписках';
$string['form_field_enrolmentnotify_help'] = 'Уведомления отправляются контактам курса, по умолчанию ими считаются пользователи с ролью учителя';
$string['form_field_expirynotify'] = 'Рассылать уведомления об окончании подписки';
$string['form_field_expirynotify_help'] = 'По умолчанию уведомления отправляются учителям';
$string['form_field_notifyall'] = 'Добавить слушателей к рассылке уведомлений об окончании подписки';
$string['form_field_notifyall_help'] = 'Если вы выберете этот пункт, то рассылка осуществится не только на адреса учителей, но еще и на адреса слушателей';
$string['form_field_use_displayenrol_startperiod'] = 'Настроить начало отображения в зависимости от предыдущей подписки';
$string['form_field_use_displayenrol_startperiod_help'] = 'Включите, если хотите ограничить дату начала отображения этого способа записи в зависимости от даты окончания предыдущей подписки';
$string['form_field_displayenrol_startperiod'] = 'Период с момента окончания предыдущей подписки, после которого должен отобразится этот способ записи';
$string['form_field_displayenrol_startperiod_help'] = 'Пример: если вы установите здесь 0, то данный способ записи не будет доступен пользователю до тех пор, пока не состоится завершение другой его подписки';
$string['form_field_use_displayenrol_endperiod'] = 'Настроить окончание отображения в зависимости от предыдущей подписки';
$string['form_field_use_displayenrol_endperiod_help'] = 'Включите, если хотите ограничить дату окончания отображения этого способа записи в зависимости от даты окончания предыдущей подписки';
$string['form_field_displayenrol_endperiod'] = 'Период с момента окончания предыдущей подписки, после которого не должен отображатся этот способ записи';
$string['form_field_displayenrol_endperiod_help'] = 'Пример: если вы установите здесь 0, то данный способ записи будет доступен только при условии, что у пользователя не было завершено ни одной подписки (он подписывается на курс впервые).
        Если устновите 1 день, то данный способ записи перестанет отображаться через 1 день после окончания предыдущей подписки. Такую настройку удобно делать, чтобы предоставить льготный период подписки на курс.';
$string['form_field_displayenrol_otpayonly'] = 'Для ограничения отображения использовать только способы записи OTPay';
$string['form_field_displayenrol_otpayonly_help'] = 'Если выбран этот пункт, то для вычисления периода прошедшего с момента последней подписки, берутся в расчет только те завершившиеся подписки, которые имеют отношение к способу записи OTPay';
$string['form_field_allowearlyenrol'] = 'Разрешать пользователю подписываться раньше даты начала подписки';
$string['form_field_allowearlyenrol_help'] = 'Пользователь сможет подписаться (оплатить) курс, но он все равно будет не доступен до даты начала';
$string['kazkom_form_field_currency'] = 'Валюта';
$string['kazkom_form_field_cost'] = 'Цена';
$string['kazkom_form_field_couponsupports'] = 'Поддержка скидочных купонов';
$string['acquiropay_form_field_currency'] = 'Валюта';
$string['acquiropay_form_field_cost'] = 'Цена';
$string['acquiropay_form_field_couponsupports'] = 'Поддержка скидочных купонов';
$string['coupon_form_field_nofields'] = 'Дополнительные настройки не требуются';
$string['sberbank_form_field_currency'] = 'Валюта';
$string['sberbank_form_field_cost'] = 'Цена';
$string['sberbank_form_field_couponsupports'] = 'Поддержка скидочных купонов';
$string['yandex_form_field_currency'] = 'Валюта';
$string['yandex_form_field_cost'] = 'Цена';
$string['yandex_form_field_couponsupports'] = 'Поддержка скидочных купонов';
$string['accountgenerate_form_field_account_types'] = 'Сценарий';
$string['accountgenerate_form_field_currency'] = 'Валюта';
$string['accountgenerate_form_field_cost'] = 'Цена';
$string['accountgenerate_form_field_couponsupports'] = 'Поддержка скидочных купонов';

$string['display_unauthorized'] = 'Отображать в витрине неавторизованным';
$string['display_unauthorized_help'] = 'Если опция включена, то даже неавторизованным пользователям будет отображена информация о данном способе зачисления, а также кнопка "Записаться на курс", которая будет вести на авторизацию/регистрацию.';
$string['availability_conditions'] = 'Условия доступа';
$string['availability_conditions_help'] = 'Если настроенные условия не будут выполнены, пользователь не сможет записаться на курс при помощи данного способа';
$string['availability_hide_unavailable'] = 'Скрывать когда условия не выполнены';
$string['availability_hide_unavailable_help'] = 'В случае, если условия доступа, настроенные выше не выполняются, пользователю вместо кнопки для записи на курс отображается сообщение с объяснением причин, из-за которых способ записи не доступен. А если в таком случае настроена опция "Скрывать когда условия не выполнены", то способ записи совсем не отображается без объяснения причин.';
$string['availability_conditions_explanations'] = 'Запись на курс не доступна в связи с тем, что не выполнены обязательные условия: {$a->explanations}';

// Формы оплаты
$string['kazkom_payform_field_submit'] = 'Перейти на страницу оплаты';
$string['acquiropay_payform_field_submit'] = 'Перейти на страницу оплаты';
$string['acquiropay_payform_field_productname'] = 'Подписка на курс [{$a}]';
$string['yandex_payform_field_submit'] = 'Перейти на страницу оплаты';
$string['accountgenerate_payform_field_submit'] = 'Сформировать счет';
$string['coupon_payform_field_enter_code'] = 'Для подписки на курс введите код купона';
$string['coupon_payform_field_placeholder_coupon_code'] = 'код купона';
$string['coupon_payform_field_submit'] = 'Записаться на курс';
$string['coupon_more_codes'] = 'ещё купоны...';
$string['coupon_hide_codes'] = 'скрыть другие...';
$string['kazkom_free_enrol_field_submit'] = 'Войти в курс';
$string['acquiropay_free_enrol_field_submit'] = 'Войти в курс';
$string['yandex_free_enrol_field_submit'] = 'Войти в курс';
$string['sberbank_free_enrol_field_submit'] = 'Войти в курс';
$string['accountgenerate_free_enrol_field_submit'] = 'Войти в курс';

// Настройки плагина
$string['settings_general'] = 'Общие настройки OTPay';
$string['settings_general_desc'] = '';
$string['settings_status'] = 'Активность способа записи по умолчанию';
$string['settings_status_desc'] = '';
$string['settings_roleid'] = 'Роль по умолчанию';
$string['settings_roleid_desc'] = '';
$string['settings_expiredaction'] = 'Действие при окончании подписки';
$string['settings_expiredaction_desc'] = 'При окончании срока действия пользовательской подписки на курс, на нее будет направлено выбранное действие. Следует иметь в виду, что если вы выберете удаление подписки, система не сможет распознать сколько времени прошло с последней подписки, так как она будет удалена';
$string['settings_emailtransfer'] = 'Разрешить передачу email пользователя во время оплаты';
$string['settings_emailtransfer_desc'] = 'Если настройка включена, email пользователя будет передаваться в процессе обмена данными во время оплаты, и может быть использован на стороне эквайринга, например, для автоподстановки в поле формы оплаты.';
$string['settings_tax'] = 'Ставка НДС';
$string['settings_tax_desc'] = '';
$string['settings_kassa'] = 'Интеграция с онлайн-кассой';
$string['settings_kassa_desc'] = 'Перед включением убедитесь, что магазин настроен на работу с онлайн-кассой.';
$string['kazkom_settings_general'] = 'Настройки способа оплаты Казкоммерцбанк';
$string['kazkom_settings_general_desc'] = '';
$string['kazkom_settings_mode'] = 'Режим работы';
$string['kazkom_settings_mode_desc'] = 'Если выбран тестовый режим, то настройки способа оплаты игнорируются и взаимодействие с банком производится через настройки для тестовых работ';
$string['kazkom_settings_value_testmode'] = 'Тестовый режим';
$string['kazkom_settings_value_workmode'] = 'Рабочий режим';
$string['kazkom_settings_url'] = 'URL для авторизации суммы в банке';
$string['kazkom_settings_url_desc'] = '';
$string['kazkom_settings_urlcontrol'] = 'URL для подтверждения-анулирования авторизации';
$string['kazkom_settings_urlcontrol_desc'] = '';
$string['kazkom_settings_merchant_certificate_id'] = 'Серийный номер сертификата';
$string['kazkom_settings_merchant_certificate_id_desc'] = '';
$string['kazkom_settings_merchant_name'] = 'Имя магазина(сайта)';
$string['kazkom_settings_merchant_name_desc'] = '';
$string['kazkom_settings_merchant_id'] = 'ID продавца в платежной системе';
$string['kazkom_settings_merchant_id_desc'] = '';
$string['kazkom_settings_privateuserkey'] = 'Приватный ключ пользователя';
$string['kazkom_settings_privateuserkey_desc'] = '';
$string['kazkom_settings_privateuserkeypassword'] = 'Пароль к приватному ключу пользователя';
$string['kazkom_settings_privateuserkeypassword_desc'] = '';
$string['kazkom_settings_publicbankkey'] = 'Публичный ключ банка';
$string['kazkom_settings_publicbankkey_desc'] = '';
$string['kazkom_settings_available_paysystems'] = 'Поддерживаемые платежные системы';
$string['kazkom_settings_available_paysystems_desc'] = 'Указанные иконки будут доступны в блоке записи на курс';
$string['acquiropay_settings_general'] = 'Настройки способа оплаты AcquiroPay';
$string['acquiropay_settings_general_desc'] = '';
$string['acquiropay_settings_mode'] = 'Режим работы';
$string['acquiropay_settings_mode_desc'] = 'Если выбран тестовый режим, то настройки способа оплаты игнорируются и взаимодействие с банком производится через настройки для тестовых работ';
$string['acquiropay_settings_value_testmode'] = 'Тестовый режим';
$string['acquiropay_settings_value_workmode'] = 'Рабочий режим';
$string['acquiropay_settings_url'] = 'URL для взаимодействия с банком';
$string['acquiropay_settings_url_desc'] = '';
$string['acquiropay_settings_merchantid']='Merchant_id';
$string['acquiropay_settings_merchantid_desc']='Идентификатор предоставляется системой AcquiroPay';
$string['acquiropay_settings_productid']='Product_id';
$string['acquiropay_settings_productid_desc']='Идентификатор предоставляется системой AcquiroPay';
$string['acquiropay_settings_secret']='Secret_word';
$string['acquiropay_settings_secret_desc']='Секретное слово предоставляется системой AcquiroPay';
$string['acquiropay_settings_available_paysystems'] = 'Поддерживаемые платежные системы';
$string['acquiropay_settings_available_paysystems_desc'] = 'Указанные иконки будут доступны в блоке записи на курс';
$string['coupon_settings_general'] = 'Настройки способа подписки купоном';
$string['coupon_settings_general_desc'] = '';
$string['sberbank_settings_general'] = 'Настройки способа оплаты Сбербанк';
$string['sberbank_settings_general_desc'] = '';
$string['sberbank_settings_requesturl'] = 'Адрес сервера для запросов';
$string['sberbank_settings_requesturl_desc'] = 'Для использования тестового режима, введите адрес https://3dsec.sberbank.ru';
$string['sberbank_settings_login'] = 'Логин';
$string['sberbank_settings_login_desc'] = '';
$string['sberbank_settings_password'] = 'Пароль';
$string['sberbank_settings_password_desc'] = '';
$string['sberbank_settings_payment_authorization_waiting_period'] = 'Период ожидания авторизации платежа от банка';
$string['sberbank_settings_payment_authorization_waiting_period_desc'] = 'В течение указанного периода система будет выполнять периодические запросы статуса проведения платежей. В случе, если по истечению данного периода платеж не будет подтвержден банком - он будет отменен.';
$string['sberbank_settings_available_paysystems'] = 'Поддерживаемые платежные системы';
$string['sberbank_settings_available_paysystems_desc'] = 'Указанные иконки будут доступны в блоке записи на курс';
$string['yandex_settings_general'] = 'Настройки способа оплаты ЮKassa';
$string['yandex_settings_general_desc'] = '';
$string['yandex_settings_connection'] = 'Способ подключения к ЮKassa';
$string['yandex_settings_connection_desc'] = 'В личном кабинете ЮKassa, в настройках магазина отображается способ подключения';
$string['yandex_settings_connection_api'] = 'Протокол API';
$string['yandex_settings_connection_http'] = 'Протокол HTTP';
$string['yandex_settings_requesturl'] = 'Адрес сервера для запросов';
$string['yandex_settings_requesturl_desc'] = '';
$string['yandex_settings_shopid'] = 'Идентификатор магазина (shopId)';
$string['yandex_settings_shopid_desc'] = '';
$string['yandex_settings_shoppassword'] = 'Пароль магазина (shop password)';
$string['yandex_settings_shoppassword_desc'] = '';
$string['yandex_settings_scid'] = 'Номен витрины (scid)';
$string['yandex_settings_scid_desc'] = '';
$string['yandex_settings_taxsystem'] = 'Система налогообложения (СНО)';
$string['yandex_settings_taxsystem_desc'] = '';
$string['yandex_settings_available_paysystems'] = 'Поддерживаемые платежные системы';
$string['yandex_settings_available_paysystems_desc'] = '';
$string['accountgenerate_settings_general'] = 'Настройки способа генерации формы';
$string['accountgenerate_settings_general_desc'] = '';

// Типы СНО
$string['yandex_settings_taxsystem_first'] = 'Общая СН';
$string['yandex_settings_taxsystem_second'] = 'Упрощенная СН (доходы)';
$string['yandex_settings_taxsystem_third'] = 'Упрощенная СН (доходы минус расходы)';
$string['yandex_settings_taxsystem_fourth'] = 'Единый налог на вмененный доход';
$string['yandex_settings_taxsystem_fifth'] = 'Единый сельскохозяйственный налог';
$string['yandex_settings_taxsystem_sixth'] = 'Патентная СН';

// Пользовательские строки
$string['user_enrolment_description'] = 'Подписка пользователя {$a->user_fullname} на курс {$a->course_fullname}';
$string['user_enrolment_description_couponcodes'] = '{$a}';
$string['sberbank_payform_field_submit'] = 'Перейти на страницу оплаты';

// Ошибки
$string['error_enrol_record'] = 'Способ записи не найден в системе!';
$string['error_form_process'] = 'Во время обработки формы произошла ошибка';
$string['error_form_validation_enrolenddate'] = 'Проверьте, чтобы даты были указаны верно';
$string['kazkom_error_form_validation_cost'] = 'Ошибка в стоимости';
$string['kazkom_error_form_validation_costminamount'] = 'Стоимость не должна быть меньше минимальной суммы оплаты: {$a}';
$string['acquiropay_error_form_validation_cost'] = 'Ошибка в стоимости';
$string['acquiropay_error_form_validation_costminamount'] = 'Стоимость не должна быть меньше минимальной суммы оплаты: {$a}';
$string['coupon_error_form_validation_emptycouponcode'] = 'Введите код купона';
$string['coupon_error_form_validation_badcouponcode'] = 'Купон не действителен';
$string['error_provider_acquiropay_form_edit_enrol_validation_currency'] = 'Недопустимый тип валюты';
$string['error_provider_kazkom_form_edit_enrol_validation_currency'] = 'Недопустимый тип валюты';
$string['error_provider_sberbank_form_edit_enrol_validation_currency'] = 'Недопустимый тип валюты';
$string['error_provider_sberbank_action_register'] = 'Ошибка во время регистрации оплаты';
$string['error_provider_sberbank_action_register_enrolment_not_found'] = 'Ошибка регистрации оплаты подписки на курс через Сбербанк. Подписка пользователя не найдена.';
$string['error_provider_sberbank_action_register_connection_failed'] = 'Ошибка регистрации оплаты подписки на курс через Сбербанк. Соединение с API Сбербанка не установлено.';
$string['error_provider_sberbank_action_register_response_errorcode_undefined'] = 'Ошибка регистрации оплаты подписки на курс через Сбербанк. Неизвестная ошибка.';
$string['error_provider_sberbank_action_register_response_errorcode_order_already_exist'] = 'Ошибка регистрации оплаты подписки на курс через Сбербанк. Номер заявки уже был зарегистрирован ранее.';
$string['error_provider_sberbank_action_register_response_errorcode_invalid_currency'] = 'Ошибка регистрации оплаты подписки на курс через Сбербанк. Недопустимый\Неизвестный тип валюты {$a->currency}.';
$string['error_provider_sberbank_action_register_response_errorcode_required_param_not_found'] = 'Ошибка регистрации оплаты подписки на курс через Сбербанк. Не указан обязательный параметр запроса.';
$string['error_provider_sberbank_action_register_response_errorcode_invalid_required_param'] = 'Ошибка регистрации оплаты подписки на курс через Сбербанк. Параметр запроса не валиден.';
$string['error_provider_sberbank_action_register_response_errorcode_sberbank_api_systemerror'] = 'Ошибка регистрации оплаты подписки на курс через Сбербанк. Внутренняя системаня ошибка API Сбербанка';
$string['error_provider_sberbank_action_register_response_errorcode_no_response'] = 'Ошибка регистрации оплаты подписки на курс через Сбербанк. Ответ от API Сбербанка не получен.';
$string['error_provider_sberbank_action_getorderstatus'] = 'Ошибка во время запроса статуса оплаты';
$string['error_provider_sberbank_action_getorderstatus_enrolment_not_found'] = 'Ошибка запроса статуса оплаты подписки на курс через Сбербанк. Подписка пользователя не найдена.';
$string['error_provider_sberbank_action_getorderstatus_connection_failed'] = 'Ошибка запроса статуса оплаты подписки на курс через Сбербанк. Соединение с API Сбербанка не установлено.';
$string['error_provider_sberbank_action_getorderstatus_response_errorcode_undefined'] = 'Ошибка запроса статуса оплаты подписки на курс через Сбербанк. Неизвестная ошибка.';
$string['error_provider_sberbank_action_getorderstatus_response_errorcode_invalid_payment_details'] = 'Ошибка запроса статуса оплаты подписки на курс через Сбербанк. Заказ отклонен по причине ошибки в реквизитах платежа.';
$string['error_provider_sberbank_action_getorderstatus_response_errorcode_invalid_required_param'] = 'Ошибка запроса статуса оплаты подписки на курс через Сбербанк. Ошибка значения параметра запроса.';
$string['error_provider_sberbank_action_getorderstatus_response_errorcode_invalid_orderid'] = 'Ошибка запроса статуса оплаты подписки на курс через Сбербанк. Неверно указан номер заказа.';
$string['error_provider_sberbank_action_getorderstatus_response_errorcode_no_response'] = 'Ошибка запроса статуса оплаты подписки на курс через Сбербанк.  Ответ от API Сбербанка не получен.';
$string['error_provider_sberbank_action_getorderstatus_response_errorcode_order_not_found'] = 'Ошибка запроса статуса оплаты подписки на курс через Сбербанк. На стороне банка не был найден заказ.';
$string['error_provider_sberbank_action_reverse_enrolment_not_found'] = 'Ошибка отмены оплаты подписки на курс через Сбербанк. Подписка пользователя не найдена.';
$string['error_provider_sberbank_action_reverse_connection_failed'] = 'Ошибка отмены оплаты подписки на курс через Сбербанк. Соединение с API Сбербанка не установлено.';
$string['error_provider_sberbank_action_reverse_response_errorcode_invalid_required_param'] = 'Ошибка отмены оплаты подписки на курс через Сбербанк. Ошибка значения параметра запроса.';
$string['error_provider_sberbank_action_reverse_response_errorcode_invalid_orderid'] = 'Ошибка отмены оплаты подписки на курс через Сбербанк. Неверно указан номер заказа.';
$string['error_provider_sberbank_action_reverse_response_errorcode_system_error'] = 'Ошибка отмены оплаты подписки на курс через Сбербанк. Системная ошибка.';
$string['error_provider_sberbank_action_reverse_response_errorcode_undefined'] = 'Ошибка отмены оплаты подписки на курс через Сбербанк. Неизвестная ошибка.';
$string['error_provider_sberbank_init_connection_errorcode_settings_invalid'] = 'Невозможно установить соединение с сервисами Сбербанка. Плагин не настроен.';
$string['error_provider_yandex_form_edit_enrol_validation_cost'] = 'Ошибка в стоимости';
$string['error_provider_yandex_form_edit_enrol_validation_costminamount'] = 'Стоимость не должна быть меньше минимальной суммы оплаты: {$a}';
$string['error_provider_yandex_form_edit_enrol_validation_currency'] = 'Недопустимый тип валюты';
$string['error_provider_yandex_action_create_payment'] = 'Во время создания платежа возникла ошибка. Попробуйте позже или свяжитесь с тех.поддержкой, если ошибка повторяется.';
$string['error_provider_yandex_action_register'] = 'Ошибка во время регистрации оплаты';
$string['error_provider_yandex_action_register_enrolment_not_found'] = 'Ошибка регистрации оплаты подписки на курс через сервис "ЮKassa". Подписка пользователя не найдена.';
$string['error_provider_yandex_init_connection_errorcode_settings_invalid'] = 'Невозможно установить соединение с сервисами "ЮKassa". Плагин не настроен.';
$string['error_provider_accountgenerate_form_edit_enrol_validation_scnerious_doesnt_exists'] = 'Выбранный сценарий отсутствует в системе';
$string['error_provider_accountgenerate_form_edit_enrol_validation_costminamount'] = 'Стоимость не должна быть меньше минимальной суммы оплаты: {$a}';
$string['error_provider_accountgenerate_form_edit_enrol_validation_currency'] = 'Недопустимый тип валюты';

// События
$string['eventresponseobtained'] = 'Получен ответ (от платежной системы)';
$string['event_payment_confirmed'] = 'Платеж подтвержден';

// Уведомления
$string['messageprovider:expiry_notification'] = 'Уведомление об окончании подписки';
$string['expirymessageenrolledsubject'] = 'Уведомление об окончании подписки';
$string['expirymessageenrolledbody'] = 'Уважаемый(-ая) {$a->user},

Уведомляем Вас о том, что Ваша подписка на курс \'{$a->course}\' окончится {$a->timeend}.

Если возникнут вопросы, пожалуйста, обратитесь к {$a->enroller}.';
$string['expirymessageenrollersubject'] = 'Уведомление об окончании подписки';
$string['expirymessageenrollerbody'] = 'Подписка в курсе \'{$a->course}\' окончится в течении {$a->threshold} для следующих пользователей:

{$a->users}

Чтобы продлить их подписку, проследуйте по ссылке {$a->extendurl}';
$string['messageprovider:otpay_enrolment'] = 'Уведомление о новой подписке';
$string['enrolmentnew'] = 'Новая подписка на курс с помощью плагина OTpay';
$string['enrolmentnewuser'] = 'Пользователь {$a->user} подписался на курс {$a->course} с помощью плагина подписки OTpay';

$string['form_create_enrol_save_success'] = 'Запрос успешно обработан!';

// Задания
$string['task_process_stucked_payments'] = 'Обработка зависших (заблокированных у пользователя, но не списаных) платежей';
$string['send_expiry_notifications'] = 'Отправка уведомлений о завершении подписки OTPay';
$string['process_expirations'] = 'Обработка окончившихся подписок';
$string['task_process_draft_payments'] = 'Обработка черновиков платежей, для которых не приходил ответ от банка о совершении платежа.';

// Панель администрирования
$string['admin_panel'] = 'Панель администрирования заявок';
$string['course_applications'] = 'Заявки курса';
$string['enrol_noname'] = 'Способ оплаты "{$a->paymethod}" ({$a->enrolid})';
$string['enrol_applications'] = 'Заявки способа записи';
$string['admin_panel_date'] = 'Дата';
$string['admin_panel_fio'] = 'ФИО';
$string['admin_panel_course'] = 'Курс';
$string['admin_panel_enroltype'] = 'Способ записи';
$string['admin_panel_enrolname'] = 'Название способа записи';
$string['admin_panel_comment'] = 'Комментарий';
$string['admin_panel_price'] = 'Сумма';
$string['admin_panel_status'] = 'Статус';
$string['apanel_couponcodes'] = 'Введенные купоны: {$a}';
$string['apanel_vat'] = 'НДС: {$a}';

// Купоны
$string['coupon_system'] = 'Панель управления купонами';
$string['coupon_system_link'] = 'Перейти в панель управления';
$string['coupon_system_tab_couponlist'] = 'Купоны';
$string['coupon_system_tab_categorylist'] = 'Категории';
$string['coupon_system_actions'] = 'Действия';
$string['coupon_system_actions_delete'] = 'Удалить';
$string['coupon_system_actions_edit'] = 'Редактировать';
$string['coupon_system_coupon_view'] = 'История использования купона';

// Купоны, системные сообщения
$string['coupon_code_generate_error'] = 'Ошибка при генерации кода купона';
$string['coupon_add_coupon_error'] = 'Ошибка при добавлении купонов';
$string['coupon_add_coupon_success'] = 'Купоны добавлены';
$string['coupon_add_category_error'] = 'Ошибка при добавлении категории';
$string['coupon_add_category_success'] = 'Категория добавлена';
$string['coupon_delete_coupon_error'] = 'Ошибка при удалении купона';
$string['coupon_delete_coupon_success'] = 'Купон удален успешно';
$string['coupon_delete_coupon_message'] = 'Вы действительно хотите удалить это купон?';
$string['coupon_delete_category_error'] = 'Ошибка при удалении категории';
$string['coupon_delete_category_success'] = 'Категория удалена успешно';
$string['coupon_delete_category_message'] = 'Вы действительно хотите удалить эту категорию? Все купоны, принадлежащие этой категории также будут удалены.';

// Форма ввода кодов купонов
$string['coupon_codes'] = 'Скидочные купоны';
$string['coupon_codes_description'] = 'Если у вас имеются скидочные купоны, введите их в форму ниже';
$string['coupon_codes_insert'] = 'Применить';
$string['coupon_code'] = '';

// Форма просмотра истории купона
$string['coupon_coupon_view_course'] = 'Курс';
$string['coupon_coupon_view_user'] = 'Пользователь';
$string['coupon_coupon_view_time'] = 'Время использования';

// Форма добавления купонов
$string['coupon_add_coupon'] = 'Создать купоны';
$string['without_category'] = 'Без категории';
$string['coupon_category'] = 'Категория';
$string['coupon_for_all_courses'] = 'Для всех курсов';
$string['coupon_hidden_course'] = '*Скрытый*';
$string['coupon_course'] = 'Курс';
$string['coupon_type'] = 'Тип купона';
$string['coupon_type_single'] = 'Одноразовый';
$string['coupon_type_multiple'] = 'Многоразовый';
$string['coupon_dtype'] = 'Купон предоставляет';
$string['coupon_dtype_help'] = 'Если требуется зачисление на курс по купону, то в курс должен быть добавлен способ записи OTPay с возможностью зачисления по купону';
$string['coupon_dtype_percentage'] = 'Скидку в %';
$string['coupon_dtype_amount'] = 'Скидку в размере указанной суммы';
$string['coupon_dtype_freeaccess'] = 'Зачисление по купону';
$string['coupon_value'] = 'Величина скидки';
$string['coupon_lifetime'] = 'Продолжительность действия купона';
$string['coupon_lifetime_help'] = 'Если вы укажете 0, то купон будет действовать без ограничения по сроку';
$string['coupon_count'] = 'Число купонов';
$string['coupon_condition'] = 'Если пользователь введет несколько купонов, то скидка может быть просуммирована, при условии, что купоны были с разными настройками. Должен быть либо другой тип (одноразовый/многоразовый), либо другая категория купонов.';
$string['addcouponname'] = 'Создать именные купоны';
$string['couponname'] = 'Имя купонов';
$string['addcouponname_help'] = 'Разрешает создание купона с заданным именем. Создать купон с определенным именем можно только 1 раз. Если вы планируете использовать именной купон более одного раза, указывайте при создании тип купона "Многоразовый".';

// Форма добавления купонов, ошибки
$string['coupon_error_invalid_category'] = 'Категория не найдена';
$string['coupon_error_invalid_course'] = 'Курс не найден';
$string['coupon_error_invalid_type'] = 'Ошибка при определении типа купона';
$string['coupon_error_invalid_dtype'] = 'Ошибка при определении типа скидки купона';
$string['coupon_error_invalid_value'] = 'Недопустимое значение скидки купона';
$string['coupon_error_invalid_lifetime'] = 'Недопустимое время существования купона';
$string['coupon_error_invalid_count'] = 'Недопустимое число купонов';
$string['coupon_error_coupon_not_found'] = 'Купон не найден';
$string['coupon_error_invalid_couponname'] = 'Имя купонов не может быть пустым';
$string['coupon_error_couponname_exists'] = 'Купон с таким именем уже существует';
$string['coupon_with_this_name_already_exists'] = 'Во время создания купона произошла ошибка. Купон с именем {$a->code} уже существует.';

// Форма удаления купонов
$string['coupon_delete_coupon'] = 'Удалить купон';

// Таблица купонов
$string['coupon_coupon_list_code'] = 'Код';
$string['coupon_coupon_list_category'] = 'Категория';
$string['lost_category'] = 'Категория не найдена';
$string['coupon_coupon_list_courseid'] = 'Курс';
$string['coupon_coupon_list_type'] = 'Тип купона';
$string['coupon_coupon_list_discounttype'] = 'Тип скидки';
$string['coupon_coupon_list_value'] = 'Скидка';
$string['coupon_coupon_list_createtime'] = 'Создан';
$string['coupon_coupon_list_lifetime'] = 'Срок действия';
$string['coupon_coupon_list_lifetime_forever'] = 'Бессрочно';
$string['coupon_coupon_list_status'] = 'Статус';
$string['coupon_coupon_list_status_active'] = 'Активен';
$string['coupon_coupon_list_status_used'] = 'Использован';

// Форма добавления категории купонов
$string['coupon_add_category'] = 'Создать категорию';
$string['coupon_category_name'] = 'Категория';
$string['coupon_category_create'] = 'Создать категорию';

// Форма добавления категории купонов, ошибки
$string['coupon_category_error_invalid_name'] = 'Ошибка при указании имени категории';

// Таблица категории купонов
$string['coupon_category_list_name'] = 'Код';
$string['coupon_category_list_count'] = 'Активных купонов';
$string['coupon_category_list_status'] = 'Статус';
$string['coupon_category_list_status_active'] = 'Активна';

// Форма удаления категоии купонов
$string['coupon_delete_category'] = 'Удалить категорию';

// Сценарии
$string['scenario_client'] = 'Клиентский сценарий';
$string['scenario_simple'] = 'Простая оплата через банк';
$string['scenario_sitecall'] = 'Заявка на курс';

////////////////////////////////////////
// OT serial
$string['pageheader'] = 'Получение серийного номера';
$string['otkey'] = 'Секретный ключ';
$string['otserial'] = 'Серийный номер СЭО 3KL';

$string['get_otserial'] = 'Получить серийный номер';
$string['get_otserial_fail'] = 'Не удалось получить серийный номер СЭО 3KL на сервере api.opentechnology.ru. Сервер сообщил ошибку: {$a}';
$string['reset_otserial'] = 'Сбросить серийный номер';
$string['already_has_otserial'] = 'Инсталляция уже зарегистрирована и получила серийный номер, нет необходимости получать ещё один.';
$string['otserial_check_ok'] = 'Серийный номер действителен.';
$string['otserial_check_fail'] = 'Серийный номер не прошел проверку на сервере.
Причина: {$a}. Если Вы считаете, что этого не должно было
произойти, пожалуйста, обратитесь в службу технической поддержки.';

// Service
$string['otservice'] = 'Тарифный план: <u>{$a}</u>';
$string['otservice_send_order'] = 'Заполнить заявку на заключение договора об обслуживании';
$string['otservice_renew'] = 'Заполнить заявку на продление';
$string['otservice_change_tariff'] = 'Сменить тарифный план';

$string['otservice_expired'] = 'Срок действия Вашего тарифного плана истёк. Если Вы желаете продлить срок, пожалуйста, свяжитесь с менеджерами ООО "Открытые технологии".';
$string['otservice_active'] = 'Тарифный план действителен до {$a}';
$string['otservice_unlimited'] = 'Тарифный план действует бессрочно';
$string['otserial_tariff_wrong'] = 'Тарифный план недоступен для данного продукта. Обратитесь в службу технической поддержки.';

$string['Jan'] = 'января';
$string['Feb'] = 'февраля';
$string['Mar'] = 'марта';
$string['Apr'] = 'апреля';
$string['May'] = 'мая';
$string['Jun'] = 'июня';
$string['Jul'] = 'июля';
$string['Aug'] = 'августа';
$string['Sep'] = 'сентября';
$string['Oct'] = 'октября';
$string['Nov'] = 'ноября';
$string['Dec'] = 'декабря';

/**
 * Отображение в витрине неавторизованным
 */
$string['otpay_login'] = "Записаться на курс";
$string['guest_should_login'] = 'Вы находитесь в режиме гостевого доступа. Для записи на курс потребуется авторизоваться под обычным (не гостевым) аккаунтом.';
$string['unauthorized_should_login'] = 'Для записи на курс требуется авторизация. {$a}';
$string['unauthorized_can_signup'] = 'Если у вас еще нет аккаунта, вы можете зарегистрироваться.';

/**
 * Дополнительные языковые строки кастомных сценариев
 */
enrol_otpay_include_custom_scenario_language_strings('ru', $string);
