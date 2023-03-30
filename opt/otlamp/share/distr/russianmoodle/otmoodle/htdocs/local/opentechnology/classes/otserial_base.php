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

namespace local_opentechnology;

use html_writer;
use stdClass;
use moodle_url;
use Exception;
use cache;
use core\notification;

require_once(dirname(dirname(__FILE__)) . '/../../config.php');
require_once($CFG->dirroot . '/local/opentechnology/locallib.php');

abstract class otserial_base
{
    public static $version = 2013122800;

    protected $clientsurl = 'https://clients.opentechnology.ru/';
    protected $requesturl = 'https://api.opentechnology.ru/';
    protected $otserialuri = 'otserial/index.php';

    protected $corealiases = ['local_opentechnology', 'core', 'moodle', ''];

    /** @var string Код продукта */
    protected $pcode;
    /** @var string Версия продукта */
    protected $pversion;
    /** @var string URL продукта */
    protected $purl;

    // Идентификационные параметры ОТ
    protected $otserial = null;
    protected $otkey = null;

    // Настройки плагина
    // должны быть установлены в классе-наследнике
    public $component;
    public $tariffcodes;

    /**
     * Абстрактный метод, задающий параметры плагина
     */
    protected abstract function setup_plugin_cfg();

    /**
     * Конструктор: задаёт код продукта, версию, url
     * @param string $pcode Код продукта
     * @param string $pversion Версия продукта
     * @param string $purl URL
     * @param array $params Массив дополнительных параметров
     */
    public function __construct($pcode, $pversion, $purl, $params=array())
    {
        // Параметры системы
        global $CFG;

        // Параметры плагина
        $this->pcode = $pcode;
        $this->pversion = $pversion;
        $this->purl = $purl;

        if (isset($CFG->otapiurl))
        {
            $this->requesturl = $CFG->otapiurl;
        }

        if (!empty($params['upgrade']))
        {// Запрос во время обновления
            // Обновление: в конфиге версия ещё не обновилась, и чтобы
            // сообщить серверу новую версию, её приходится читать прямо
            // из файла
            require($CFG->dirroot . '/version.php');
            $this->mversion = $version;
            $this->mrelease = $release;
        }
        else
        {// Регулярный запрос: версию можно спросить у системы
            $this->mversion = $CFG->version;
            $this->mrelease = $CFG->release;
        }

        $this->setup_plugin_cfg();
    }

    /**
     * Получить базовый урл-адрес для запросов
     * @return string
     */
    public function get_requesturl() {
        return $this->requesturl;
    }

    protected function get_plugin_config($configname, $plugin=null)
    {
        global $CFG;

        // если компонент не был передан, используется настройка текущего экземпляра класса
        $plugin = $plugin ?? $this->component;

        // для otkey и otserial предусмотрена возможность переопределения в $CFG
        if (in_array($configname, ['otkey', 'otserial']))
        {
            // проверим наличие конфига для указанного плагина
            if (!empty($CFG->otapi[$plugin][$configname]))
            {
                return $CFG->otapi[$plugin][$configname];
            }

            // в файле конфига серийник/ключ базового продукта допустимо указывать и как core, и как local_opentechnology
            // проверим алиасы
            if (in_array($plugin, $this->corealiases))
            {
                foreach($this->corealiases as $corealias)
                {
                    if (!empty($CFG->otapi[$corealias][$configname]))
                    {
                        return $CFG->otapi[$corealias][$configname];
                    }
                }
            }
        }

        // плагин local_opentechnology - исключение, он всегда хранит свои настройки не в плагине, а в ядре
        $cfgplugin = (in_array($plugin, $this->corealiases) ? '' : $plugin);
        $cfgvalue = get_config($cfgplugin, $configname);
        if ($cfgvalue !== false)
        {
            return $cfgvalue;
        }

        return null;
    }

    protected function set_plugin_config($configname, $value, $plugin=null)
    {
        // если компонент не был передан, используется настройка текущего экземпляра класса
        $plugin = $plugin ?? $this->component;

        // плагин local_opentechnology - исключение, он всегда хранит свои настройки не в плагине, а в ядре
        $cfgplugin = (in_array($plugin, $this->corealiases) ? '' : $plugin);

        return set_config($configname, $value, $cfgplugin);
    }

    protected function unset_plugin_config($configname, $plugin=null)
    {
        // если компонент не был передан, используется настройка текущего экземпляра класса
        $plugin = $plugin ?? $this->component;

        // плагин local_opentechnology - исключение, он всегда хранит свои настройки не в плагине, а в ядре
        $cfgplugin = (in_array($plugin, $this->corealiases) ? '' : $plugin);

        return unset_config($configname, $cfgplugin);
    }

    /**
     * Получение хранящегося в СДО серийника из конфига
     *
     * @param string $plugin - плагин, для которого происходит получение данных
     * @param boolean $useprop - использовать хранение в свойстве класса
     *
     * @return string|NULL
     */
    public function get_config_otserial($plugin=null, $useprop=true)
    {
        if ($useprop && !empty($this->otserial))
        {
            return $this->otserial;
        }

        $otserial = $this->get_plugin_config('otserial', $plugin);

        if ($useprop)
        {
            $this->otserial = $otserial;
        }

        return $otserial;
    }

    /**
     * Получение хранящегося в СДО ключа из конфига
     *
     * @param string $plugin - плагин, для которого происходит получение данных
     * @param boolean $useprop - использовать хранение в свойстве класса
     *
     * @return string|NULL
     */
    public function get_config_otkey($plugin=null, $useprop=true)
    {
        if ($useprop && !empty($this->otkey))
        {
            return $this->otkey;
        }

        $otkey = $this->get_plugin_config('otkey', $plugin);

        if ($useprop)
        {
            $this->otkey = $otkey;
        }

        return $otkey;
    }

    /**
     * Выпуск (создание) серийника
     *
     * @param array $options
     * @throws otserial_base_exception
     */
    public function issue_serial($options=[])
    {
        $otserial = $this->get_config_otserial();
        $otkey = $this->get_config_otkey();

        if (!empty($otserial) and !empty($otkey))
        { // Серийный код уже был получен
            $message = $this->get_string('otserial_exception_already_has_serial', null, $options);
            throw new otserial_base_exception($message, 409);

        } else
        {
            // Запрос серийного кода
            $otdata = $this->otapi_get_otserial();

            if (!empty($otdata->otserial) and !empty($otdata->otkey))
            {
                // Сохранение данных
                $this->set_plugin_config('otserial', $otdata->otserial);
                $this->set_plugin_config('otkey', $otdata->otkey);
                return $otdata;
            } else
            {
                throw new otserial_base_exception($this->get_string('otserial_exception_unknown', null, $options), 520);
            }
        }
    }

    /**
     * Получение строки плагина, с учетом возможных переопределений через опции
     *
     * @param string $identifier - идентификтаор объекта
     * @param object $a - объект, передаваемый в строку
     * @param array $options - опции, переданные в исходный метод, которые могут содержать переопределения
     *        ['plugin_string_identifiers' => [
     *           'identifier' => {string_identifier} - строка, которую необходимо использовать из плагина, наследующегося от класса
     *        ]]
     * @return string
     */
    public function get_string($identifier, $a=null, $options=[])
    {
        $component = 'local_opentechnology';
        if (isset($options['plugin_string_identifiers'][$identifier]))
        {
            $identifier = $options['plugin_string_identifiers'][$identifier];
            $component = $this->component;
        }
        return get_string($identifier, $component, $a);
    }

    public function get_serial_data($options)
    {
        // Проверяем статус
        if ($this->plugin_has_configured_otapi_data()) {
            return $this->otapi_get_otserial_status();
        } else
        {
            throw new otserial_base_exception($this->get_string('otserial_exception_not_configured', null, $options));
        }
    }

    /**
     * Проверить статус. Если серийника не было -- получить его.
     * @return array('status' => true/false, 'messages' => array, 'response' => array)
     */
    public function issue_serial_and_get_data($options=[])
    {
        // Возвращаемые значения
        $message = '';
        $response = null;

        try {

            try {
                // выпуск серийника на случай, если не было ещё
                $this->issue_serial($options);
            } catch (otserial_base_exception $ex)
            {
                // ошибку 409 игнорируем, так как действительно для этого метода допустимо, что реквизиты могли уже существовать
                if ($ex->getCode() != 409)
                {
                    // остальные ошибки пробрасываем дальше
                    throw $ex;
                }
            }

            // получение данных по серийнику
            $response = $this->get_serial_data($options);
            $message = $this->get_string('otserial_notification_otserial_check_ok', null, $options);

        } catch(otserial_base_exception $ex)
        {
            $message = $this->get_string('otserial_error_get_otserial_fail', $ex->getMessage(), $options);
        }

        return [
            'response' => $response,
            'message' => $message
        ];
    }

    /**
     * Получить данные о серийнике из отапи
     *
     * @return object stdClass
     * ->otserial
     * ->otkey
     *
     */
    public function otapi_get_otserial()
    {
        $cache = cache::make('local_opentechnology', 'otserial');
        $cachedresponse = $cache->get($this->pcode);
        if( $cachedresponse !== false )
        {
            return $cachedresponse;
        }


        //время отправки запроса
        $time = 10000*microtime(true);

        //url запроса
        $url = $this->requesturl . $this->otserialuri;
        //параметры запроса
        $params = array(
            'do' => 'get_serial',
            'time' => $time,
        );

        ////////////////////////////////////////
        // Данные для передачи

        // Базовое приложение
        if ($bdata = $this->get_bproduct_data()) {
            //серийник базового приложения
            $bpotserial = $bdata->otserial;
        } else {
            $bpotserial = '';
        }

        //от этих данных берётся хэш
        $data = array(
            'pcode' => $this->pcode,
            'pversion' => $this->pversion,
            'purl' => $this->purl,
            'bpotserial' => $bpotserial,

            'mversion' => $this->mversion,
            'mrelease' => $this->mrelease,
        );

        if (!empty($bdata->otkey))
        {
            // если есть базовое приложение, пользуемся его ключом, чтобы
            // подтвердить аутентичность
            $params['hash'] = $this->calculate_hash($bdata->otkey, $time, $data);
        }

        //отправляем запрос на получение серийника
        try {
            $encodedresponse = $this->request($url, $params+$data);
            $response = json_decode($encodedresponse);
        } catch (Exception $e) {
            throw new otserial_base_exception('Looks like your internet connection is down', 520);
        }

//         if (!property_exists($response, 'status') || $response->status != 'ok')
//         {
//             throw new otserial_base_exception('Bad status in response', 520);
//         }

        $cache->set($this->pcode, $response);
        return $response;
    }

    /**
     * Проверить статус продукта
     * Возвращает полученный ответ
     * @param object $otdata stdClass
     * ->otserial
     * ->otkey
     * @return string $response - статус серийника
     *
     */
    public function otapi_get_otserial_status($otserial=null, $otkey=null)
    {
        if (is_null($otserial) && is_null($otkey))
        {
            $otserial = $this->get_config_otserial();
            $otkey = $this->get_config_otkey();
        }

        if (empty($otserial) || empty($otkey))
        {
            throw new otserial_base_exception('Missing required parameters', 422);
        }


        $cache = cache::make('local_opentechnology', 'otserial_status');
        $cachedresponse = $cache->get($otserial);
        if( $cachedresponse !== false )
        {
            return $cachedresponse;
        }


        //время отправки запроса
        $time = 10000*microtime(true);

        //url запроса
        $url = $this->requesturl . $this->otserialuri;
        //параметры запроса
        $params = array(
            'do'=>'get_status',
            'time' => $time,
        );

        ////////////////////////////////////////
        // Данные для передачи

        // Серийник и секретный ключ
        $this->otserial = $otserial;
        $this->otkey = $otkey;

        // Базовое приложение
        if ($bdata = $this->get_bproduct_data()) {
            //серийник базового приложения
            $bpotserial = $bdata->otserial;
        } else {
            $bpotserial = '';
        }

        //данные для передачи (от них берётся хэш)
        $data = array(
            'pcode' => $this->pcode,
            'pversion' => $this->pversion,
            'purl' => $this->purl,
            'otserial' => $otserial,
            'bpotserial' => $bpotserial,

            'mversion' => $this->mversion,
            'mrelease' => $this->mrelease,
        );

        $params['hash'] = $this->calculate_hash($otkey, $time, $data);

        //отправляем запрос на получение серийника
        try {
            $encodedresponse = $this->request($url, $params+$data);
            $response = json_decode($encodedresponse);
        } catch (Exception $e) {
            throw new otserial_base_exception('Looks like your internet connection is down', 520);
        }

        if (!property_exists($response, 'status') || $response->status != 'ok')
        {
            throw new otserial_base_exception('Bad status in response', 520);
        }

        $cache->set($otserial, $response);
        return $response;
    }

    /**
     * Получить информацию о базовом продукте (moodle otserial)
     */
    protected function get_bproduct_data()
    {
        $data = new stdClass();
        $data->otserial = $this->get_config_otserial('core', false);
        $data->otkey = $this->get_config_otkey('core', false);

        if (!empty($data->otserial) AND !empty($data->otkey)) {
            return $data;
        }

        return false;
    }

    /**
     * Сформировать ссылку и добавить к ней хеш из key, time, otserial
     * @param string $str
     * @param array $params
     * @return moodle_url
     */
    public function url($str, array $params = array(), $prefix='clients')
    {
        $params['time'] = 10000*microtime(true);
        $params['otserial'] = $this->otserial;
        $params['hash'] = $this->calculate_hash($this->otkey, $params['time'], array($this->otserial));
        switch ($prefix)
        {
            case 'clients':
                $baseurl = $this->clientsurl;
                break;
            case 'api':
            default:
                $baseurl = $this->requesturl;
        }
        return new moodle_url($baseurl.$str, $params);
    }

    /**
     * Считает хеш от параметров запроса, ключа продукта OT и метки времени
     * @param string $otkey Ключ продукта ОТ
     * @param int $counter Метка времени
     * @param array $data Параметры запроса
     */
    protected function calculate_hash($otkey, $counter, array $data)
    {
        return sha1("{$otkey}{$counter}" . implode('', $data));
    }
    /**
     * Выполнить запрос по указанному url с указанными параметрами
     *
     * @param string $url
     * @param array $get
     * @param array $post
     */
    protected function request($url, array $getparams=[], $postfields=[], $method='get')
    {
        // GET-параметры
        if (!empty($getparams))
        {
            $url .= (stripos($url, '?') !== false) ? '&' : '?';
            $url .= http_build_query($getparams, '', '&');
        }

        $ch = curl_init($url);

        // Опции cURL
        $options = array(
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false,
            CURLOPT_SSLVERSION => 1,
        );

        // Обработка параметров для передачи в POST, PUT, DELETE
        if (!empty($postfields))
        {
            $options[CURLOPT_POSTFIELDS] = json_encode($postfields);
        }

        switch(strtolower($method)) {
            case 'put':
                $options[CURLOPT_CUSTOMREQUEST] = 'PUT';
                break;
            case 'delete':
                $options[CURLOPT_CUSTOMREQUEST] = 'DELETE';
                break;
            case 'post':
                $options[CURLOPT_POST] = 1;
                break;
            case 'get':
            default:
                $options[CURLOPT_HTTPGET] = 1;
                break;
        }

        curl_setopt_array($ch, $options);

        // Выполняем запрос и получаем результат
        if ( !($rawret = curl_exec($ch)) )
        {// Ошибка
            $error = (string) curl_errno($ch);
            $error .= curl_error($ch);
            throw new Exception($error);
            return false;
        }
        // Завершаем соединеие
        curl_close($ch);

        return $rawret;
    }

    public function delete_otapi_data()
    {
        $this->delete_otapi_caches();

        $this->unset_plugin_config('otserial');
        $this->unset_plugin_config('otkey');
    }

    public function delete_otapi_caches()
    {
        global $CFG;

        $otserialcache = cache::make('local_opentechnology', 'otserial');
        $cachedserial = $otserialcache->get($this->pcode);
        $otserialcache->delete($this->pcode);

        $statuscache = cache::make('local_opentechnology', 'otserial_status');
        if (!empty($cachedserial->otserial))
        {
            $statuscache->delete($cachedserial->otserial);
        }
        if (!empty($CFG->otapi[$this->component]['otserial']))
        {
            $statuscache->delete($CFG->otapi[$this->component]['otserial']);
        }
        if ($cfgotserial = get_config($this->component, 'otserial'))
        {
            $statuscache->delete($cfgotserial);
        }
    }

//     /**
//      * Сброс серийника, на текущий момент не используется
//      * @param array $options
//      */
//     protected function settings_page_action_reset($options=[])
//     {
//         $this->delete_otapi_data();
//     }

    /**
     * Получение серийника
     * @param array $options
     * @throws otserial_base_exception
     */
    public function settings_page_action_issue_otserial($options=[])
    {
        return $this->issue_serial($options);
    }

    public function plugin_has_configured_otapi_data()
    {
        $otserial = $this->get_config_otserial();
        $otkey = $this->get_config_otkey();
        return (!empty($otserial) && !empty($otkey));
    }

    public function get_formatted_serial()
    {
        $serial = $this->get_config_otserial();
        return implode('-', str_split($serial, 4));
    }

    public function settings_make_notification_string($information, $notificationtype=null)
    {
        global $OUTPUT;
        if (!is_null($notificationtype))
        {
            $information = $OUTPUT->notification($information, $notificationtype);
        }
        return $information;
    }

    public function prepare_info_otserial($options=[])
    {
        $info = $this->get_formatted_serial();
        return $this->settings_make_notification_string($info, null);
    }

    public function prepare_info_otserial_check($options=[])
    {
        try {
            $otserialdata = $this->get_serial_data($options);

            // ошибка, если тариф не подходит к продукту
            if (!property_exists($otserialdata, 'tariff') || !in_array($otserialdata->tariff, $this->tariffcodes))
            {
                throw new otserial_base_exception($this->get_string('otserial_error_tariff_wrong', null, $options));
            }

            $info = $this->get_string('otserial_notification_otserial_check_ok', null, $options);
            return [$this->settings_make_notification_string($info, \core\output\notification::NOTIFY_SUCCESS), $otserialdata];

        } catch(otserial_base_exception $ex)
        {
            $info = $this->get_string('otserial_error_otserial_check_fail', $ex->getMessage(), $options);
            return [$this->settings_make_notification_string($info, \core\output\notification::NOTIFY_ERROR), null];
        }
    }

    public function prepare_info_otservice($otserialdata, $options=[])
    {
        $info = $this->get_string('otserial_settingspage_otservice', $otserialdata->tariff ?? 'free', $options);
        return $this->settings_make_notification_string($info, null);
    }

    public function prepare_info_otserial_otservice_expiry_time($otserialdata, $options=[])
    {
        if (property_exists($otserialdata, 'expirytime') &&
            (string)(int)$otserialdata->expirytime == (string)$otserialdata->expirytime)
        {
            $expirytime = (int)$otserialdata->expirytime;

            // было настроеное бессрочное разрешение на использование тарифа
            if ($expirytime == 0)
            {
                $info = $this->get_string('otserial_notification_otservice_unlimited', null, $options);
                return $this->settings_make_notification_string($info, \core\output\notification::NOTIFY_SUCCESS);
            }

            // указанный срок действия тарифа уже прошёл
            if ($expirytime < time())
            {
                $info = $this->get_string('otserial_error_otservice_expired', null, $options);
                return $this->settings_make_notification_string($info, \core\output\notification::NOTIFY_ERROR);
            }

            // указанный срок действия тарифа актуален
            $date = date('Y-m-d H:i', $expirytime);
            $info = $this->get_string('otserial_notification_otservice_active', $date, $options);
            return $this->settings_make_notification_string($info, \core\output\notification::NOTIFY_SUCCESS);

        }

        // если мы до сюда добрались, значит в поле срока действия указано хер пойми что
        $info = $this->get_string('otserial_exception_expirytime_wrong', null, $options);
        return $this->settings_make_notification_string($info, \core\output\notification::NOTIFY_ERROR);
    }

    public function prepare_extenalpage_html($section, $options=[])
    {
        // ответ на запрос получения данных по серийнику
        $result = '';

        $adminroot = admin_get_root(false, false);
        $extpage = $adminroot->locate($section, true);

        // получение параметра, инициализирующего действия
        $action = optional_param($this->component.'_action', null, PARAM_ALPHANUMEXT);
        if (!is_null($action) && method_exists($this, 'settings_page_action_'.$action))
        {
            try {
                // обработка запрошенного действия
                $this->{'settings_page_action_'.$action}($options);
            } catch(otserial_base_exception $ex)
            {
                notification::error($ex->getMessage());
            }
            // перенаправление обратно на страницу управления тарифами
            redirect($extpage->url);
        }

        if ($this->plugin_has_configured_otapi_data())
        {
            // добавление настройки, отображающей серийник в отформатированном виде
            $settingname = $this->get_string('otserial_settingspage_otserial', null, $options);
            $settingvalue = $this->prepare_info_otserial($options);
            $result .= html_writer::div($settingname);
            $result .= html_writer::div($settingvalue);

            // сообщение, что серийник действителен (или нет)
            list($settingvalue, $otserialdata) = $this->prepare_info_otserial_check($options);
            $result .= $settingvalue;

            if (!is_null($otserialdata))
            {
                // тариф, указанный в серийнике
                $settingvalue = $this->prepare_info_otservice($otserialdata, $options);
                $result .= html_writer::div($settingvalue);

                // вывод информации для небесплатного тарифа
                if (property_exists($otserialdata, 'tariff') && $otserialdata->tariff != 'free')
                {
                    // срок действия тарифа
                    $settingvalue = $this->prepare_info_otserial_otservice_expiry_time($otserialdata, $options);
                    $result .= html_writer::div($settingvalue);
                }
            }

        } else
        {
            // название настройки для отображения
            $settingname = $this->get_string('otserial_settingspage_otserial', null, $options);
            // Адрес получения серийного кода для плагина
            $url = new \moodle_url($extpage->url, [$this->component.'_action' => 'issue_otserial']);
            // Ссылка на получение серийного кода для плагина
            $link = html_writer::link($url, $this->get_string('otserial_settingspage_issue_otserial', null, $options));
            // добавление настройки-ссылки для получения серийника
            $result .= html_writer::div($settingname);
            $result .= $link;
        }

        return html_writer::div($result);
    }

    /**
     * Заполнение указанной страницы настроек настройками о серийнике
     * @param object $settingspage
     * @param array $options
     * @return null|object данные о серийнике, если удалось получить
     */
    public function settings_page_fill(&$settingspage, $options=[])
    {
        // ответ на запрос получения данных по серийнику
        $response = null;

        // получение параметра, инициализирующего действия
        $action = optional_param($this->component.'_action', null, PARAM_ALPHANUMEXT);
        if (!is_null($action) && method_exists($this, 'settings_page_action_'.$action))
        {
            try {
                // обработка запрошенного действия
                $this->{'settings_page_action_'.$action}($options);
            } catch(otserial_base_exception $ex)
            {
                notification::error($ex->getMessage());
            }
            // перенаправление обратно на страницу управления тарифами
            redirect(new \moodle_url('/admin/settings.php', ['section' => $settingspage->name]));
        }

        if ($this->plugin_has_configured_otapi_data())
        {
            // добавление настройки, отображающей серийник в отформатированном виде
            $settingcode = $this->component.'/otserial';
            $settingname = $this->get_string('otserial_settingspage_otserial', null, $options);
            $settingvalue = $this->prepare_info_otserial($options);
            $settingspage->add(new \admin_setting_heading($settingcode, $settingname, $settingvalue));

            // сообщение, что серийник действителен (или нет)
            $settingcode = $this->component.'/otserial_check';
            $settingname = '';
            list($settingvalue, $otserialdata) = $this->prepare_info_otserial_check($options);
            $settingspage->add(new \admin_setting_heading($settingcode, $settingname, $settingvalue));
            $response = $otserialdata;

            if (!is_null($otserialdata))
            {
                // тариф, указанный в серийнике
                $settingcode = $this->component.'/otservice';
                $settingname = '';
                $settingvalue = $this->prepare_info_otservice($otserialdata, $options);
                $settingspage->add(new \admin_setting_heading($settingcode, $settingname, $settingvalue));


                // вывод информации для небесплатного тарифа
                if (property_exists($otserialdata, 'tariff') && $otserialdata->tariff != 'free')
                {
                    // срок действия тарифа
                    $settingcode = $this->component.'/otserial_otservice_expiry_time';
                    $settingname = '';
                    $settingvalue = $this->prepare_info_otserial_otservice_expiry_time($otserialdata, $options);
                    $settingspage->add(new \admin_setting_heading($settingcode, $settingname, $settingvalue));
                }
            }

        } else
        {
            // название настройки-ссылки для получения серийника
            $settingcode = $this->component.'/issue_otserial';
            // название настройки для отображения
            $settingname = $this->get_string('otserial_settingspage_otserial', null, $options);
            // Адрес получения серийного кода для плагина
            $url = new \moodle_url('/admin/settings.php', [
                'section' => $settingspage->name,
                $this->component.'_action' => 'issue_otserial'
            ]);
            // Ссылка на получение серийного кода для плагина
            $link = html_writer::link($url, $this->get_string('otserial_settingspage_issue_otserial', null, $options));
            // добавление настройки-ссылки для получения серийника
            $settingspage->add(new \admin_setting_heading($settingcode, $settingname, $link));
        }

        return $response;
    }

    /**
     * Автоматическое создание страницы настроек и добавление в указанную категорию
     *
     * @param object $admincategory
     * @param string $parentcategoryname
     * @param array $options
     *
     * @return null|object страница настроек
     */
    public function settings_page_add(&$admincategory, $parentcategoryname, $options=[])
    {
        global $CFG;
        require_once $CFG->libdir.'/adminlib.php';

        // код страницы с настройками тарифа
        $settingspagename = $options['settings_page_name'] ?? $this->component.'_otserial';
        // отображаемое название страницы с настройками тарифа
        $settingspagevisiblename = $this->get_string('otserial_settingspage_visiblename', null, $options);
        // Создание страницы настроек плагина - Тарифный план
        $settingspage = new \admin_settingpage($settingspagename, $settingspagevisiblename);

        if ($admincategory->fulltree)
        {
            $this->settings_page_fill($settingspage, $options);
        }

        $admincategory->add($parentcategoryname, $settingspage);

        return $settingspage;
    }

}