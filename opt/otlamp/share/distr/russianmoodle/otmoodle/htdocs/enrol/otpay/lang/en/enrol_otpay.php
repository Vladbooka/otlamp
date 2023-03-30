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
$string['enrol_otpay'] = 'OT Pay enrol plugin';
$string['title'] = 'OT Pay enrol method';
$string['otpay:config'] = 'Configure enrolment';
$string['otpay:appsmanage'] = 'View the request\'s panel';
$string['otpay:manage'] = 'Edit user enrolment';
$string['otpay:unenrol'] = 'Unenrol users';
$string['otpay:unenrolself'] = 'Unenrol self';
$string['description_for_user'] = 'Short description for the user';
$string['otpay_basecost'] = 'Base amount:';
$string['otpay_amount'] = 'Total:';
$string['otpay_enrolperiod'] = 'Period of education:';
$string['otpay_enrolenddate'] = 'Course registration end date:';
$string['otpay_enrolstartdate'] = 'Start of education:';
$string['otpay_enrolstartdate_currenttime'] = 'immediatley after subscription';
$string['otpay_couponrequired'] = 'There is coupon code required to subscribe to the course';
$string['otpay_wrong_data'] = 'An error occurred while processing your payment. The Bank returned invalid data. Contact your technical support.';
$string['future_enrollment'] = 'You have already signed up for this course. Date of commencement of the subscription: {$a}';
$string['otpay_currency_643'] = '{$a}₽';//&#8381;
$string['otpay_currency_840'] = '${$a}';
$string['otpay_currency_398'] = '{$a}₸';//&#8376;

$string['return_to_course'] = 'Return to course';
$string['accountgenerate_form'] = 'Form filling';

// Служебные строки
$string['required'] = 'Fill the field!';
$string['send'] = 'Send';
$string['submit_request'] = 'Send an application';

// Общие языковые строки
$string['lastname'] = 'Lastname: {$a}';
$string['firstname'] = 'Firstname: ';
$string['phone'] = 'Phone: {$a}';
$string['email'] = 'Email: {$a}';
$string['comment'] = 'Comment: {$a}';
$string['company'] = 'Company: {$a}';
$string['type'] = 'Type: {$a}';
$string['payer'] = 'Name/Fullname: {$a}';
$string['payeremail'] = 'Email: {$a}';
$string['payeraddr'] = 'Legal address: {$a}';
$string['payeraddrmail'] = 'Mailing address: {$a}';
$string['payerphone'] = 'Phone: {$a}';
$string['payerinn'] = 'TIN: {$a}';
$string['payerkpp'] = 'KIP: {$a}';
$string['account_number'] = 'Account number: {$a}';
$string['payerfio'] = 'Fullname: {$a}';
$string['payername'] = 'Name: {$a}';
$string['part_of_payerkpp'] = ', KIP ';
$string['unenrolselfconfirm'] = 'Do you really want to unenrol yourself from course "{$a}"?';

// Плейсхолдеры
$string['placeholder_payeremail'] = 'example@example.ru';
$string['placeholder_payeraddr'] = '123456, Moscow, Pervaia ulitsa, 19';
$string['placeholder_payeraddrmail'] = '123456, Moscow, Vtoraia ulitsa, 19';
$string['placeholder_payerphone'] = '89876543211';
$string['placeholder_payerinn'] = '7776665554';
$string['placeholder_payerkpp'] = '644901001';
$string['placeholder_payername'] = 'Ltd. «Company»';
$string['placeholder_fio'] = $string['placeholder_payer'] = 'Ivanov Ivan';

// Служебные поля
$string['sum_amount_643'] = '{$a->int} rubles {$a->fract} kopecks';
$string['kopecks'] = 'kopecks';
$string['withyear'] = '{$a} y.';
$string['for_payment'] = 'For «{$a}»';
$string['for_account_number_course_code'] = 'For «{$a->course_code}» on account №{$a->account_number} of {$a->date}';
$string['recipient_name'] = 'Ltd. «Company»';

// Поля вкладок
$string['recipient'] = 'Recipient';
$string['kpp'] = 'CPR';
$string['inn'] = 'TIN';
$string['oktmo'] = 'OCTMO';
$string['raccount'] = 'Checking account.';
$string['rinn'] = 'Bank';
$string['bik'] = 'BIK';
$string['kaccount'] = 'Correspondent account';
$string['kbk'] = 'Budget classification code (CSC)';
$string['payment'] = 'Payment';
$string['settings_account_number'] = 'Algorithm for generating account numbers';
$string['settings_account_number_id'] = 'Generation by ID';
$string['settings_account_number_hash'] = 'Generation by hash';

// Типы вкладок
$string['individual'] = 'Individual';
$string['entity'] = 'Entity';
$string['simple'] = 'Simple payment through the bank';
$string['entity_ip'] = 'IE';
$string['receipt'] = 'Receipt PD-4';
$string['sitecall'] = 'Application for the course';

// Ссылки
$string['url_admin_panel'] = 'Application management panel';
$string['url_coupon_panel'] = 'Coupon management panel';

// Статусы
$string['draft'] = 'Waiting';
$string['confirmed'] = 'Paid';
$string['used'] = 'Used';
$string['needdebit'] = 'Debiting money';
$string['waitdebit'] = 'Waiting for payment';
$string['rejected'] = 'Rejected';
$string['active'] = 'Active';

// Провайдеры
$string['otpay_method'] = 'Payment provider';
$string['otpay_kazkom'] = 'Kazkommertsbank';
$string['otpay_acquiropay'] = 'AcquiroPay';
$string['otpay_coupon'] = 'Enrolment by coupon';
$string['otpay_sberbank'] = 'Sberbank';
$string['otpay_yandex'] = 'ЮKassa';
$string['otpay_accountgenerate'] = 'Account Generation';

// Платежные системы
$string['otpay_paysystem_alfabank'] = 'Alfabank';
$string['otpay_paysystem_amex'] = 'American Express';
$string['otpay_paysystem_beeline'] = 'Beeline';
$string['otpay_paysystem_maestro'] = 'Maestro';
$string['otpay_paysystem_mastercard'] = 'Mastercard';
$string['otpay_paysystem_mir'] = 'MIR';
$string['otpay_paysystem_mts'] = 'MTS';
$string['otpay_paysystem_paypal'] = 'Paypal';
$string['otpay_paysystem_pochtaros'] = 'Почта России';
$string['otpay_paysystem_qiwi'] = 'QIWI';
$string['otpay_paysystem_sberbank'] = 'Sberbank';
$string['otpay_paysystem_visa'] = 'Visa';
$string['otpay_paysystem_visaelectron'] = 'Visa Electron';
$string['otpay_paysystem_webmoney'] = 'Webmoney';
$string['otpay_paysystem_yoomoney'] = 'ЮMoney';

// Ставка НДС
$string['settings_tax_first'] = 'Without VAT';
$string['settings_tax_second'] = 'VAT at a rate of 0%';
$string['settings_tax_third'] = 'VAT check at a rate of 10%';
$string['settings_tax_fourth'] = 'VAT check at a rate of 20%';
$string['settings_tax_fifth'] = 'VAT check at the estimated rate of 10/110';
$string['settings_tax_sixth'] = 'VAT check at the estimated rate of 20/120';

// Формы настроек
$string['form_field_status'] = 'Subscription method enabled';
$string['form_field_roleid'] = 'Role';
$string['form_return_url'] = 'After payment, redirect user on';
$string['return_url_course'] = 'Course page';
$string['return_url_localcrw'] = 'Course description page';
$string['return_url_wantsurl'] = 'Last visited page';
$string['form_field_enrolstartdate'] = 'Enrolment start date';
$string['form_field_enrolstartdate_help'] = 'This user will not be able to start training before that date';
$string['form_field_enrolperiod'] = 'The training period for subscription';
$string['form_field_enrolperiod_help'] = 'The length of time for which the user must be signed';
$string['form_field_enrolenddate'] = 'Enrolment end date';
$string['form_field_enrolenddate_help'] = 'This user will not be able to subscribe to the course after that date';
$string['form_field_enrolmentnotify'] = 'Send new enrolment notifications';
$string['form_field_enrolmentnotify_help'] = 'Notifications are sent to the contacts of the course, teachers are considered to be users with the role of the default';
$string['form_field_expirynotify'] = 'Send ending enrolment notifications';
$string['form_field_expirynotify_help'] = 'By default, teachers are sent a notification';
$string['form_field_notifyall'] = 'Add listeners to send notifications about subscription end';
$string['form_field_notifyall_help'] = 'If you select this option, the delivery is not realized only addresses teachers, but also to address the listeners';
$string['form_field_use_displayenrol_startperiod'] = 'Set to start the display depending on previous subscription';
$string['form_field_use_displayenrol_startperiod_help'] = 'Enable if you want to restrict the display of the start date of the recording method, depending on the date of the previous subscription';
$string['form_field_displayenrol_startperiod'] = 'The period since the end of the previous subscription, after which should be displayed this way of writing';
$string['form_field_displayenrol_startperiod_help'] = 'Example: if you set this to 0, this method of recording will not be available to the user until the completion of the other will not take his subscription';
$string['form_field_use_displayenrol_endperiod'] = 'Set the end of the display, depending on previous subscription';
$string['form_field_use_displayenrol_endperiod_help'] = 'Enable if you want to limit the end date of the display of the recording method, depending on the date of the previous subscription';
$string['form_field_displayenrol_endperiod'] = 'The period since the end of the previous subscription, after which the check should not be this way of writing';
$string['form_field_displayenrol_endperiod_help'] = 'Example: if you set this to 0, this method of recording will be available only on the condition that the user has not completed any subscription (he signed for the course for the first time).
        If the Install a day 1, this recording method is no longer displayed after 1 day after the end of the previous subscription. The adjustment can be done easily to provide a grace period of a subscription to the course. ';
$string['form_field_displayenrol_otpayonly'] = 'To limit the display to use only methods OTPay record';
$string['form_field_displayenrol_otpayonly_help'] = 'If this option is selected, then to calculate the period elapsed since the last subscriptions are taken into account only those ended subscriptions, which are related to a method for recording OTPay';
$string['form_field_allowearlyenrol'] = 'Allow users to subscribe before the start date of the subscription';
$string['form_field_allowearlyenrol_help'] = 'Users can subscribe (pay) rate, but he still will not be available before the start date';
$string['kazkom_form_field_currency'] = 'Currency';
$string['kazkom_form_field_cost'] = 'Price';
$string['kazkom_form_field_couponsupports'] = 'Coupon support';
$string['acquiropay_form_field_currency'] = 'Currency';
$string['acquiropay_form_field_cost'] = 'Price';
$string['acquiropay_form_field_couponsupports'] = 'Coupon support';
$string['coupon_form_field_nofields'] = 'Advanced Settings are required';
$string['sberbank_form_field_currency'] = 'Currency';
$string['sberbank_form_field_cost'] = 'Price';
$string['sberbank_form_field_couponsupports'] = 'Coupon support';
$string['yandex_form_field_currency'] = 'Currency';
$string['yandex_form_field_cost'] = 'Price';
$string['yandex_form_field_couponsupports'] = 'Coupon support';
$string['accountgenerate_form_field_account_types'] = 'Scenarious';
$string['accountgenerate_form_field_currency'] = 'Currency';
$string['accountgenerate_form_field_cost'] = 'Price';
$string['accountgenerate_form_field_couponsupports'] = 'Coupon support';

$string['display_unauthorized'] = 'Display to unauthorized';
$string['display_unauthorized_help'] = 'If this option is enabled, information about this method of enrollment will be displayed even to unauthorized users. Also, a button "Sign up for a course" will be displayed, which will direct the user to authorization / registration.';
$string['availability_conditions'] = 'Availability conditions';
$string['availability_conditions_help'] = 'If the configured conditions are not met, the user will not be able to enroll in the course using this method.';
$string['availability_hide_unavailable'] = 'Hide when conditions are not met';
$string['availability_hide_unavailable_help'] = 'If the access conditions configured above are not met, instead of a button to enroll in the course, the user is shown a message explaining the reasons why the enrollment method is not available. And if in this case the "Hide when conditions are not met" option is configured, the enrol method is not displayed at all without explaining the reasons.';
$string['availability_conditions_explanations'] = 'Enrolment is not available due to conditions not met: {$a->explanations}';

// Формы оплаты
$string['kazkom_payform_field_submit'] = 'Pay';
$string['acquiropay_payform_field_submit'] = 'Pay';
$string['acquiropay_payform_field_productname'] = 'Course enrolment [{$a}]';
$string['yandex_payform_field_submit'] = 'Pay';
$string['accountgenerate_payform_field_submit'] = 'Generate account';
$string['coupon_payform_field_enter_code'] = 'Enter coupon code';
$string['coupon_payform_field_placeholder_coupon_code'] = 'coupon code';
$string['coupon_payform_field_submit'] = 'Use coupon';
$string['coupon_more_codes'] = 'more codes...';
$string['coupon_hide_codes'] = 'hide others...';
$string['kazkom_free_enrol_field_submit'] = 'Enter the course';
$string['acquiropay_free_enrol_field_submit'] = 'Enter the course';
$string['yandex_free_enrol_field_submit'] = 'Enter the course';
$string['sberbank_free_enrol_field_submit'] = 'Enter the course';
$string['accountgenerate_free_enrol_field_submit'] = 'Enter the course';

// Настройки плагина
$string['settings_general'] = 'General OTPay settings';
$string['settings_general_desc'] = '';
$string['settings_status'] = 'The default method for recording activity';
$string['settings_status_desc'] = '';
$string['settings_roleid'] = 'default role';
$string['settings_roleid_desc'] = '';
$string['settings_expiredaction'] = 'Action at the end of the subscription';
$string['settings_expiredaction_desc'] = 'At the end of the term of the user subscription to the course, the selected action will be directed at her. It should be borne in mind that if you choose to remove a subscription, the system can not recognize how much time has passed since the last subscription, as it will be deleted ';
$string['settings_emailtransfer'] = 'Allow to send user email during payment';
$string['settings_emailtransfer_desc'] = 'If the setting is enabled, the user\'s email will be transmitted during the data exchange during payment, and can be used on the acquiring side, for example, for auto-substitution in the form of the payment form.';
$string['settings_tax'] = 'VAT rate';
$string['settings_tax_desc'] = '';
$string['settings_kassa'] = 'Cashbox integration';
$string['settings_kassa_desc'] = 'Make sure your shop is configured to work with online-cashbox';
$string['kazkom_settings_general'] = 'Settings Kazkommertsbank payment method';
$string['kazkom_settings_general_desc'] = '';
$string['kazkom_settings_mode'] = 'Operating mode';
$string['kazkom_settings_mode_desc'] = 'If you select a test mode, the settings are ignored and the method of payment to the interplay between the bank made through the settings for the test work';
$string['kazkom_settings_value_testmode'] = 'test mode';
$string['kazkom_settings_value_workmode'] = 'Operation';
$string['kazkom_settings_url'] = 'URL to log the amount of a bank';
$string['kazkom_settings_url_desc'] = '';
$string['kazkom_settings_urlcontrol'] = 'URL for confirmation, revocation of authorization';
$string['kazkom_settings_urlcontrol_desc'] = '';
$string['kazkom_settings_merchant_certificate_id'] = 'The serial number of the certificate';
$string['kazkom_settings_merchant_certificate_id_desc'] = '';
$string['kazkom_settings_merchant_name'] = 'shop name (website)';
$string['kazkom_settings_merchant_name_desc'] = '';
$string['kazkom_settings_merchant_id'] = 'ID seller in the payment system';
$string['kazkom_settings_merchant_id_desc'] = '';
$string['kazkom_settings_privateuserkey'] = 'Private User Key';
$string['kazkom_settings_privateuserkey_desc'] = '';
$string['kazkom_settings_privateuserkeypassword'] = 'Password to the private user key';
$string['kazkom_settings_privateuserkeypassword_desc'] = '';
$string['kazkom_settings_publicbankkey'] = 'The public key of the bank';
$string['kazkom_settings_publicbankkey_desc'] = '';
$string['kazkom_settings_available_paysystems'] = 'Available paysystems';
$string['kazkom_settings_available_paysystems_desc'] = '';
$string['acquiropay_settings_general'] = 'a payment method settings AcquiroPay';
$string['acquiropay_settings_general_desc'] = '';
$string['acquiropay_settings_mode'] = 'Operating mode';
$string['acquiropay_settings_mode_desc'] = 'If you select a test mode, the settings are ignored and the method of payment to the interplay between the bank made through the settings for the test work';
$string['acquiropay_settings_value_testmode'] = 'test mode';
$string['acquiropay_settings_value_workmode'] = 'Operation';
$string['acquiropay_settings_url'] = 'URL to interact with the bank';
$string['acquiropay_settings_url_desc'] = '';
$string['acquiropay_settings_merchantid'] = 'Merchant_id';
$string['acquiropay_settings_merchantid_desc'] = 'ID provided AcquiroPay system';
$string['acquiropay_settings_productid'] = 'Product_id';
$string['acquiropay_settings_productid_desc'] = 'ID provided AcquiroPay system';
$string['acquiropay_settings_secret'] = 'Secret_word';
$string['acquiropay_settings_secret_desc'] = 'secret word given AcquiroPay system';
$string['acquiropay_settings_available_paysystems'] = 'Available paysystems';
$string['acquiropay_settings_available_paysystems_desc'] = '';
$string['coupon_settings_general'] = 'Settings coupon subscription method';
$string['coupon_settings_general_desc'] = '';
$string['sberbank_settings_general'] = 'Settings Sberbank payment method';
$string['sberbank_settings_general_desc'] = '';
$string['sberbank_settings_requesturl'] = 'Request url';
$string['sberbank_settings_requesturl_desc'] = 'To use the test mode, enter the address https://3dsec.sberbank.ru';
$string['sberbank_settings_login'] = 'Login';
$string['sberbank_settings_login_desc'] = '';
$string['sberbank_settings_password'] = 'Password';
$string['sberbank_settings_password_desc'] = '';
$string['sberbank_settings_payment_authorization_waiting_period'] = 'authorization waiting period of payment from the bank';
$string['sberbank_settings_payment_authorization_waiting_period_desc'] = 'During this period, the system will carry out periodic requests of payments status. As would happen if at the expiration of the period of payment is not confirmed by the bank - it will be canceled. ';
$string['sberbank_settings_available_paysystems'] = 'Available paysystems';
$string['sberbank_settings_available_paysystems_desc'] = '';
$string['yandex_settings_general'] = 'Settings ЮKassa payment method';
$string['yandex_settings_general_desc'] = '';
$string['yandex_settings_connection'] = 'Connection method to ЮKassa';
$string['yandex_settings_connection_desc'] = 'The connection method is displayed in the ЮKassa personal account, in the shop settings';
$string['yandex_settings_connection_api'] = 'API protocol';
$string['yandex_settings_connection_http'] = 'HTTP protocol';
$string['yandex_settings_requesturl'] = 'Request URL';
$string['yandex_settings_requesturl_desc'] = '';
$string['yandex_settings_shopid'] = 'shopId';
$string['yandex_settings_shopid_desc'] = '';
$string['yandex_settings_shoppassword'] = 'Shop password';
$string['yandex_settings_shoppassword_desc'] = '';
$string['yandex_settings_scid'] = 'scid';
$string['yandex_settings_scid_desc'] = '';
$string['yandex_settings_taxsystem'] = 'The system of taxation';
$string['yandex_settings_taxsystem_desc'] = '';
$string['yandex_settings_available_paysystems'] = 'Available paysystems';
$string['yandex_settings_available_paysystems_desc'] = '';
$string['accountgenerate_settings_general'] = 'Account Generation settings';
$string['accountgenerate_settings_general_desc'] = '';

// Типы СНО
$string['yandex_settings_taxsystem_first'] = 'General taxation system';
$string['yandex_settings_taxsystem_second'] = 'Simplified system of taxation (income)';
$string['yandex_settings_taxsystem_third'] = 'Simplified system of taxation (income minus expenses)';
$string['yandex_settings_taxsystem_fourth'] = 'A single tax on imputed income';
$string['yandex_settings_taxsystem_fifth'] = 'Unified agricultural tax';
$string['yandex_settings_taxsystem_sixth'] = 'Patent system of taxation';

// Пользовательские строки
$string['user_enrolment_description'] = 'Enrolment by user {$a->user_fullname} by course {$a->course_fullname}';
$string['user_enrolment_description_couponcodes'] = '{$a}';
$string['sberbank_payform_field_submit'] = 'Pay';

// Ошибки
$string['error_enrol_record'] = 'Enrol to course record doesn\'t exists!';
$string['error_form_process'] = 'During form processing error occurred';
$string['error_form_validation_enrolenddate'] = 'Make sure that the dates have been entered correctly';
$string['kazkom_error_form_validation_cost'] = 'Error in price';
$string['kazkom_error_form_validation_costminamount'] = 'The price should not be less than the minimum payment amount: {$ a}';
$string['acquiropay_error_form_validation_cost'] = 'Error in price';
$string['acquiropay_error_form_validation_costminamount'] = 'The price should not be less than the minimum payment amount: {$ a}';
$string['coupon_error_form_validation_emptycouponcode'] = 'Enter the coupon code';
$string['coupon_error_form_validation_badcouponcode'] = 'The coupon is not valid';
$string['error_provider_acquiropay_form_edit_enrol_validation_currency'] = 'Invalid currency type';
$string['error_provider_kazkom_form_edit_enrol_validation_currency'] = 'Invalid currency type';
$string['error_provider_sberbank_form_edit_enrol_validation_currency'] = 'Invalid currency type';
$string['error_provider_sberbank_action_register'] = 'Error during the registration fee';
$string['error_provider_sberbank_action_register_enrolment_not_found'] = 'Error registration subscription payment for the course through Sberbank. User Subscription not found. ';
$string['error_provider_sberbank_action_register_connection_failed'] = 'Error registration subscription payment for the course through Sberbank. The connection to the API of the Savings Bank is not installed. ';
$string['error_provider_sberbank_action_register_response_errorcode_undefined'] = 'Error registration subscription payment for the course through Sberbank. Unknown error.';
$string['error_provider_sberbank_action_register_response_errorcode_order_already_exist'] = 'Error registration subscription payment for the course through Sberbank. Reference number has already been registered earlier. ';
$string['error_provider_sberbank_action_register_response_errorcode_invalid_currency'] = 'Error registration subscription payment for the course through Sberbank. Invalid \ Unknown currency type {$ a-> currency} .';
$string['error_provider_sberbank_action_register_response_errorcode_required_param_not_found'] = 'Error registration subscription payment for the course through Sberbank. Not Set required parameter query. ';
$string['error_provider_sberbank_action_register_response_errorcode_invalid_required_param'] = 'Error registration subscription payment for the course through Sberbank. A query parameter is not valid. ';
$string['error_provider_sberbank_action_register_response_errorcode_sberbank_api_systemerror'] = 'Error registration subscription payment for the course through Sberbank. Internal sistemanya Sberbank API error';
$string['error_provider_sberbank_action_register_response_errorcode_no_response'] = 'Error registration subscription payment for the course through Sberbank. The response from the API of the Savings Bank is not received. ';
$string['error_provider_sberbank_action_getorderstatus'] = 'An error occurred during the payment status of the request';
$string['error_provider_sberbank_action_getorderstatus_enrolment_not_found'] = 'payment status request Error subscription to the course through the Savings Bank. User Subscription not found. ';
$string['error_provider_sberbank_action_getorderstatus_connection_failed'] = 'payment status request Error subscription to the course through the Savings Bank. The connection to the API of the Savings Bank is not installed. ';
$string['error_provider_sberbank_action_getorderstatus_response_errorcode_undefined'] = 'payment status request Error subscription to the course through the Savings Bank. Unknown error.';
$string['error_provider_sberbank_action_getorderstatus_response_errorcode_invalid_payment_details'] =' payment status request Error subscription to the course through the Savings Bank. Order rejected due to errors in the details of the payment. ';
$string['error_provider_sberbank_action_getorderstatus_response_errorcode_invalid_required_param'] = 'payment status request Error subscription to the course through the Savings Bank. Error value of the parameter query. ';
$string['error_provider_sberbank_action_getorderstatus_response_errorcode_invalid_orderid'] = 'payment status request Error subscription to the course through the Savings Bank. Invalid order number. ';
$string['error_provider_sberbank_action_getorderstatus_response_errorcode_no_response'] = 'payment status request Error subscription to the course through the Savings Bank. The response from the API of the Savings Bank is not received. ';
$string['error_provider_sberbank_action_getorderstatus_response_errorcode_order_not_found'] = 'payment status request Error subscription to the course through the Savings Bank. On the bank of the order was not found. ';
$string['error_provider_sberbank_action_reverse_enrolment_not_found'] = 'Error canceling subscription payment for the course through Sberbank. User Subscription not found. ';
$string['error_provider_sberbank_action_reverse_connection_failed'] = 'Error canceling subscription payment for the course through Sberbank. The connection to the API of the Savings Bank is not installed. ';
$string['error_provider_sberbank_action_reverse_response_errorcode_invalid_required_param'] = 'Error canceling subscription payment for the course through Sberbank. Error value of the parameter query. ';
$string['error_provider_sberbank_action_reverse_response_errorcode_invalid_orderid'] = 'Error canceling subscription payment for the course through Sberbank. Invalid order number. ';
$string['error_provider_sberbank_action_reverse_response_errorcode_system_error'] = 'Error canceling subscription payment for the course through Sberbank. System error.';
$string['error_provider_sberbank_action_reverse_response_errorcode_undefined'] = 'Error canceling subscription payment for the course through Sberbank. Unknown error.';
$string['error_provider_sberbank_init_connection_errorcode_settings_invalid'] = 'Unable to connect to the services of the Savings Bank. The plugin is not set. ';
$string['error_provider_yandex_form_edit_enrol_validation_cost'] = 'Error in price';
$string['error_provider_yandex_form_edit_enrol_validation_costminamount'] = 'The price should not be less than the minimum payment amount: {$a}';
$string['error_provider_yandex_form_edit_enrol_validation_currency'] = 'Invalid currency type';
$string['error_provider_yandex_action_create_payment'] = 'An error occurred while creating the payment. Please try again later or contact technical support if the error persists.';
$string['error_provider_yandex_action_register'] = 'Error during the registration fee';
$string['error_provider_yandex_action_register_enrolment_not_found'] = 'Error registration subscription payment for the course through ЮKassa. User Subscription not found. ';
$string['error_provider_yandex_init_connection_errorcode_settings_invalid'] = 'Unable to connect to the services of the ЮKassa. The plugin is not set. ';
$string['error_provider_accountgenerate_form_edit_enrol_validation_scnerious_doesnt_exists'] = 'Выбранный сценарий отсутствует в системе';
$string['error_provider_accountgenerate_form_edit_enrol_validation_costminamount'] = 'The price should not be less than the minimum payment amount: {$a}';
$string['error_provider_accountgenerate_form_edit_enrol_validation_currency'] = 'Invalid currency type';

// События
$string['eventresponseobtained'] = 'Merchant reply has been received';
$string['event_payment_confirmed'] = 'Payment was confirmed';

// Уведомления
$string['messageprovider:expiry_notification'] = 'Notification of subscription completion';
$string['expirymessageenrolledsubject'] = 'Notification of subscription completion';
$string['expirymessageenrolledbody'] = 'Dear (th) {$a->user},

We inform you that your subscription to the course \'{$a->course}\' end in {$a->timeend}.

If you have questions, please contact your {$a->enroller}.';
$string['expirymessageenrollersubject'] = 'Enrolment completion notificatiob';
$string['expirymessageenrollerbody'] = 'Subscribe to the course \'{$a->course}\' will end during the {$a->threshold} for the following users:

{$a->users}

To renew their subscription, follow this link {$a->extendurl}';
$string['messageprovider:otpay_enrolment'] = 'Enrolmant start notification';
$string['enrolmentnew'] = 'Course enrolment creating notification';
$string['enrolmentnewuser'] = 'User {$a->user} enrolled to the course {$a->course} by OT Pay';

$string['form_create_enrol_save_success'] = 'Request successfully processed!';

// Задания
$string['task_process_stucked_payments'] = 'Handling of busy (blocked user, but not write-off) payment';
$string['send_expiry_notifications'] = 'Sending of subscription completion notification OTPay';
$string['process_expirations'] = 'Processing ending on subscriptions';
$string['task_process_draft_payments'] = 'Processing drafts payments for which no answer came from the bank about making a payment.';

// Панель администрирования
$string['admin_panel'] = 'Applications admin panel';
$string['course_applications'] = 'Course applications';
$string['enrol_applications'] = 'Enrol applications';
$string['enrol_noname'] = 'Payment provider "{$a->paymethod}" ({$a->enrolid})';
$string['admin_panel_date'] = 'Date';
$string['admin_panel_fio'] = 'Fullname';
$string['admin_panel_course'] = 'Coruse';
$string['admin_panel_enroltype'] = 'Enrol type';
$string['admin_panel_enrolname'] = 'Enrol name';
$string['admin_panel_comment'] = 'Comment';
$string['admin_panel_price'] = 'Price';
$string['admin_panel_status'] = 'Status';
$string['apanel_couponcodes'] = 'Used coupons: {$a}';
$string['apanel_vat'] = 'VAT: {$a}';

// Купоны
$string['coupon_system'] = 'Coupon control panel';
$string['coupon_system_link'] = 'Coupon control panel';
$string['coupon_system_tab_couponlist'] = 'Coupons';
$string['coupon_system_tab_categorylist'] = 'Categories';
$string['coupon_system_actions'] = 'Actions';
$string['coupon_system_actions_delete'] = 'Delete';
$string['coupon_system_actions_edit'] = 'Edit';
$string['coupon_system_coupon_view'] = 'Coupon actions history';

// Купоны, системные сообщения
$string['coupon_code_generate_error'] = 'An error occurred while generating the coupon code';
$string['coupon_add_coupon_error'] = 'An error occurred while adding coupons';
$string['coupon_add_coupon_success'] = 'Coupons added';
$string['coupon_add_category_error'] = 'An error occurred while adding the category';
$string['coupon_add_category_success'] = 'Category added';
$string['coupon_delete_coupon_error'] = 'Error deleting coupon';
$string['coupon_delete_coupon_success'] = 'Coupon deleted successfully';
$string['coupon_delete_coupon_message'] = 'Are you sure you want to delete this voucher? ';
$string['coupon_delete_category_error'] = 'Failed to delete the category';
$string['coupon_delete_category_success'] = 'Category deleted successfully';
$string['coupon_delete_category_message'] = 'Do you really want to delete this category? All coupons belonging to this category will also be deleted. ';

// Форма ввода кодов купонов
$string['coupon_codes'] = 'Discount coupons';
$string['coupon_codes_description'] = 'If you have coupons, enter them in the form below';
$string['coupon_codes_insert'] = 'Apply';
$string['coupon_code'] = '';

// Форма просмотра истории купона
$string['coupon_coupon_view_course'] = 'Course';
$string['coupon_coupon_view_user'] = 'User';
$string['coupon_coupon_view_time'] = 'Using time';

// Форма добавления купонов
$string['coupon_add_coupon'] = 'Create coupons';
$string['without_category'] = 'Without category';
$string['coupon_category'] = 'Category';
$string['coupon_for_all_courses'] = 'For all courses';
$string['coupon_hidden_course'] = '*Hidden*';
$string['coupon_course'] = 'Course';
$string['coupon_type'] = 'Coupon type';
$string['coupon_type_single'] = 'Single';
$string['coupon_type_multiple'] = 'Multiple';
$string['coupon_dtype'] = 'Coupon represents';
$string['coupon_dtype_help'] = 'If you would like to enrol with a coupon for a course, then the OTPay enrolment method must be added to the course with an option of enrolment by coupon';
$string['coupon_dtype_percentage'] = 'Persent discount';
$string['coupon_dtype_amount'] = 'Fixed discount';
$string['coupon_dtype_freeaccess'] = 'Enrolment by coupon';
$string['coupon_value'] = 'Markdown';
$string['coupon_lifetime'] = 'Coupon lifetime';
$string['coupon_lifetime_help'] = 'If you specify 0, the code will work without time limits';
$string['coupon_count'] = 'Count of coupons';
$string['coupon_condition'] = 'If the user enters a few coupons, the discount can be summed, provided that the coupons have been with different settings. There must be a different type of (disposable / reusable), or another category of coupons.';
$string['addcouponname'] = 'Create personal coupons';
$string['couponname'] = 'Coupons name';
$string['addcouponname_help'] = 'Allows creation of a coupon with a given name. You can create a coupon with a specific name only 1 time. If you plan to use the coupon more than once, indicate when creating the coupon type "Reusable".';

// Форма добавления купонов, ошибки
$string['coupon_error_invalid_category'] = 'Category not found';
$string['coupon_error_invalid_course'] = 'Course not found';
$string['coupon_error_invalid_type'] = 'An error occurred while determining the type of the coupon';
$string['coupon_error_invalid_dtype'] = 'An error occurred while determining the type of discount coupon';
$string['coupon_error_invalid_value'] = 'Invalid value discount coupon';
$string['coupon_error_invalid_lifetime'] = 'Invalid lifetime of the coupon';
$string['coupon_error_invalid_count'] = 'Invalid number of coupons';
$string['coupon_error_coupon_not_found'] = 'Coupon not found';
$string['coupon_error_invalid_couponname'] = 'Coupons name can not be empty';
$string['coupon_error_couponname_exists'] = 'A coupon with this name already exists';
$string['coupon_with_this_name_already_exists'] = 'An error occurred while creating the coupon. A coupon with the name {$a->code} already exists.';

// Форма удаления купонов
$string['coupon_delete_coupon'] = 'Delete coupon';

// Таблица купонов
$string['coupon_coupon_list_code'] = 'Code';
$string['coupon_coupon_list_category'] = 'Category';
$string['lost_category'] = 'Category not found';
$string['coupon_coupon_list_courseid'] = 'Course';
$string['coupon_coupon_list_type'] = 'Coupon type';
$string['coupon_coupon_list_discounttype'] = 'Discount type';
$string['coupon_coupon_list_value'] = 'Discount';
$string['coupon_coupon_list_createtime'] = 'Created';
$string['coupon_coupon_list_lifetime'] = 'Lifetime';
$string['coupon_coupon_list_lifetime_forever'] = 'In perpetuity';
$string['coupon_coupon_list_status'] = 'Status';
$string['coupon_coupon_list_status_active'] = 'Active';
$string['coupon_coupon_list_status_used'] = 'Used';

// Форма добавления категории купонов
$string['coupon_add_category'] = 'Add category';
$string['coupon_category_name'] = 'Category';
$string['coupon_category_create'] = 'Create category';

// Форма добавления категории купонов, ошибки
$string['coupon_category_error_invalid_name'] = 'Error when specifying the category name';

// Таблица категории купонов
$string['coupon_category_list_name'] = 'Code';
$string['coupon_category_list_count'] = 'Coupons in active status';
$string['coupon_category_list_status'] = 'Status';
$string['coupon_category_list_status_active'] = 'Active';

// Сценарии
$string['scenario_client'] = 'Client\'s scenario';
$string['scenario_simple'] = 'Simple payment through the bank';
$string['scenario_sitecall'] = 'Application for the course';

// Форма удаления категоии купонов
$string['coupon_delete_category'] = 'Delete category';

////////////////////////////////////////
// OT serial
$string['pageheader'] = 'Obtaining a serial number';
$string['otkey'] = 'secret key';
$string['otserial'] = 'serial number';

$string['get_otserial'] = 'Get serial number';
$string['get_otserial_fail'] = 'Attempt to get LMS 3KL serial number failed. Server reported an error: {$a}';
$string['reset_otserial'] = 'Drop serial number';
$string['already_has_otserial'] = 'This copy of plugin already has serial number';
$string['otserial_check_ok'] = 'Serial number verified';
$string['otserial_check_fail'] = 'Serial number not verified. Reason: {$a}.';

// Service
$string['otservice'] = 'Tariff: <u>{$a}</u>';
$string['otservice_send_order'] = 'Send a request';
$string['otservice_renew'] = 'Send a request';
$string['otservice_change_tariff'] = 'Change tariff';

$string['otservice_expired'] = 'The validity of your tariff plan has expired. If you want to extend the deadline, please contact the managers of the "Open Technologies" LLC.';
$string['otservice_active'] = 'The tariff plan is valid until {$a}';
$string['otservice_unlimited'] = 'The tariff plan is valid indefinitely';
$string['otserial_tariff_wrong'] = 'The tariff plan is not available for this product. Please contact technical support.';

$string['Jan'] = 'Jan';
$string['Feb'] = 'Feb';
$string['Mar'] = 'Mar';
$string['Apr'] = 'Apr';
$string['May'] = 'May';
$string['Jun'] = 'Jun';
$string['Jul'] = 'Jul';
$string['Aug'] = 'Aug';
$string['Sep'] = 'Sep';
$string['Oct'] = 'Oct';
$string['Nov'] = 'Nov';
$string['Dec'] = 'Dec';

/**
 * Отображение в витрине неавторизованным
 */
$string['otpay_login'] = "Enrol to course";
$string['guest_should_login'] = 'You are in guest access mode. To enroll in the course, you will need to log in under a regular (non-guest) account.';
$string['unauthorized_should_login'] = 'Authorization is required to enroll in the course. {$a}';
$string['unauthorized_can_signup'] = 'If you don\'t have an account yet, you can register.';


/**
 * Дополнительные языковые строки кастомных сценариев
 */
enrol_otpay_include_custom_scenario_language_strings('en', $string);
