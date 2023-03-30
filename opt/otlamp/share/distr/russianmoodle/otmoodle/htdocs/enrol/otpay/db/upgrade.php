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
 * Плагин записи на курс OTPAY. Скрипт обновления плагина.
 *
 * @package    enrol
 * @subpackage otpay
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

function xmldb_enrol_otpay_upgrade($oldversion)
{
    global $DB;
    // Получение менеджера работы с таблицами
    $dbman = $DB->get_manager();

    // Обновления в зависимости от установленной версии
    if ( $oldversion < 2016111400 )
    {
        // Перенос настроек с AcquiroPay
        foreach(get_config('enrol_otpay') as $k=>$v)
        {
            if( strpos($k, 'acuiropay') !== false )
            {
                set_config(str_replace('acuiropay', 'acquiropay', $k), $v, 'enrol_otpay');
            }
        }
    }
    if ( $oldversion <= 2016111401 )
    {
        // Добавление поля внешнего идентификатора платежа
        $table = new xmldb_table('enrol_otpay');
        $field = new xmldb_field('externalpaymentid', XMLDB_TYPE_CHAR, 255, null, true);

        if ( ! $dbman->field_exists($table, $field) )
        {
            $dbman->add_field($table, $field);
        }
        upgrade_plugin_savepoint(true, 2016120100, 'enrol', 'otpay');
    }
    if ( $oldversion < 2016122700 )
    {
        // Базовая инициализация
        $plugin = enrol_get_plugin('otpay');
        $providers = $plugin->get_providers();

        // Замена строковых идентификаторов валют на числовые в экземплярах подписки
        $instances = (array)$DB->get_records('enrol', ['enrol' => 'otpay']);
        foreach ( $instances as $instance )
        {
            // Провайдер способа записи
            $provider = $instance->customchar1;
            if ( isset($providers[$provider]) )
            {// Провайдер найден

                // Получение настроек плагина
                $providerconfig = $providers[$provider]->otpay_config();

                if ( ! empty($providerconfig->currencycodes) )
                {// Провайдер поддерживает валюты
                    $code = array_search($instance->currency, (array)$providerconfig->currencycodes);
                    if ( $code )
                    {// Код найден

                        // Замена буквенного кода на строковый
                        $update = new stdClass();
                        $update->currency = $code;
                        $update->id = $instance->id;
                        $DB->update_record('enrol', $update);
                    }
                }
            }
        }

        // Замена строковых идентификаторов валют на числовые в пользовательских подписках
        $userpayments = (array)$DB->get_records('enrol_otpay');
        foreach ( $userpayments as $userpayment )
        {
            // Провайдер подписки
            $provider = $userpayment->paymethod;
            if ( isset($providers[$provider]) )
            {// Провайдер найден

                // Получение настроек плагина
                $providerconfig = $providers[$provider]->otpay_config();
                if ( ! empty($providerconfig->currencycodes) )
                {// Провайдер поддерживает валюты
                    $code = array_search($userpayment->currency, (array)$providerconfig->currencycodes);
                    if ( $code )
                    {// Код найден

                        // Замена буквенного кода на строковый
                        $update = new stdClass();
                        $update->currency = $code;
                        $update->id = $userpayment->id;
                        $DB->update_record('enrol_otpay', $update);
                    }
                }
            }
        }

        upgrade_plugin_savepoint(true, 2016122600, 'enrol', 'otpay');
    }
    if ( $oldversion <= 2017021600 )
    {
        // Добавление поля внешнего идентификатора платежа
        $table = new xmldb_table('enrol_otpay');
        $DB->set_field('enrol', 'customint6', '1', [
            'enrol' => 'otpay'
        ]);
    }
    if ( $oldversion <= 2017030312 )
    {
        $dbman = $DB->get_manager();
        $table = new xmldb_table('enrol_otpay_coupons');
        $field = new xmldb_field('code', XMLDB_TYPE_CHAR, '255', null, true, null, null, 'catid');

        // Меняем размер поля 'code' с 8 до 255 символов
        $index = new xmldb_index('icode', XMLDB_INDEX_NOTUNIQUE, ['code']);
        if ( !$dbman->index_exists($table, $index) )
        {
            $dbman->change_field_precision($table, $field);
            $dbman->add_index($table, $index);
        }
        else
        {// Фикс случая, если вдруг в таблице магическим образом оказался индекс на поле code (если есть индекс, менять размер нельзя)
            $dbman->drop_index($table, $index);
            $dbman->change_field_precision($table, $field);
            $dbman->add_index($table, $index);
        }

        $table = new xmldb_table('enrol_otpay_coupon_cat');
        $index = new xmldb_index('iname', XMLDB_INDEX_NOTUNIQUE, ['name']);
        if ( !$dbman->index_exists($table, $index) )
        {
            $dbman->add_index($table, $index);
        }

        $table = new xmldb_table('enrol_otpay_coupon_log');
        $index = new xmldb_index('icouponid', XMLDB_INDEX_NOTUNIQUE, ['couponid']);
        if ( !$dbman->index_exists($table, $index) )
        {
            $dbman->add_index($table, $index);
        }
    }

    if ($oldversion < 2020072100)
    {
        // добавляем дефолтный конфиг способа подключения к яндекс.кассе для старых инсталляций
        // раньше у всех был способ подключения - http
        // теперь будет настройка, которая у старых инсталляций должна остаться в значении http
        // хотя по умолчанию для новых теперь будет значение api

        $config = new stdClass();
        $config->plugin = 'enrol_otpay';
        $config->name = 'yandex_connection';
        $config->value = 'http';
        $DB->insert_record('config_plugins', $config);
    }

    if ($oldversion <= 2020123001)
    {
        // изменение адреса платежных запросов для интеграции по устаревшему протоколу HTTP в ex-яндекс.касса (yookassa)
        $requesturl = get_config('enrol_otpay', 'yandex_requesturl');
        if ($requesturl !== false)
        {
            $oldhost = parse_url($requesturl, PHP_URL_HOST);
            if ($oldhost == 'money.yandex.ru')
            {
                set_config('yandex_requesturl', 'https://yoomoney.ru/eshop.xml', 'enrol_otpay');
            }
        }
    }

    if ($oldversion < 2021020801)
    {
        // Провайдеры, в которых поддерживается настройка поддерживаемых платежных систем
        $providers = ['acquiropay', 'kazkom', 'sberbank', 'yandex'];
        foreach ($providers as $provider) {
            // настройка с последовательностью поддерживаемых платежных систем
            $settingname = $provider.'_available_paysystems';
            $paysystems = get_config('enrol_otpay', $settingname);
            if ($paysystems === false)
            {
                continue;
            }
            $paysystems = explode(',', $paysystems);

            foreach ($paysystems as $p => $paysystem)
            {
                // если среди платежных систем имеется яндекс - заменяем на юмани
                if (trim($paysystem) == 'yad')
                {
                    $paysystems[$p] = 'yoomoney';
                    // сохраняем изменения
                    set_config($settingname, implode(',', $paysystems), 'enrol_otpay');
                    // и сразу переходим к следующему провайдеру, так как замена у нас может быть только одна
                    continue 2;
                }
            }
        }
    }

    if ($oldversion < 2021071900) {
        // до сего момента поле "customtext3" не использовалось
        // в новых инстансах мы хотим сделать значением по умолчанию "Отображать в витрине неавторизованным" - "Да"
        // но сломать привычное поведение пользователям, которые ранее настроили otpay мы не хотим
        // поэтому здесь старые способы записи настраиваем по умолчанию так, чтобы не отображать ничего неавторизованным
        $customtext3 = '{"display_unauthorized":false,"availability":{"conditions":[],"hide_unavailable":false}}';
        $DB->set_field('enrol', 'customtext3', $customtext3, ['enrol' => 'otpay']);
    }

    return true;
}
