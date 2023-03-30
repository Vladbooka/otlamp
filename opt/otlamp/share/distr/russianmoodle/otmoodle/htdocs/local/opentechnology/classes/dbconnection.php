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

use ADOConnection;
use local_opentechnology\adodb;

class dbconnection {

    use adodb;

    private $name;
    private $configcode;
    private $config = [
        'host' => null,
        'type' => null,
        'database' => null,
        'user' => null,
        'pass' => null,
        'setupsql' => null,
        'extencoding' => null
    ];
    /** @var ADOConnection - соединение с базой данных */
    private $db = null;

    public function __construct(string $code=null) {
        $this->configcode = $code;
        $this->fill_config_data();
        $this->fill_config_name();
    }

    public function set_name(string $name) {

        if (empty($name)) {
            throw new \Exception('Name should be not empty string');
        }
        $this->name = $name;
    }

    public function get_name() {
        return $this->name;
    }

    /**
     * Получение порядкового номера конфигурации
     * @param boolean $refresh
     * @return number|unknown
     */
    public function get_config_code($refresh=false) {
        if (!isset($this->configcode) || $refresh) {
            $this->fill_config_code();
        }
        return $this->configcode;
    }

    private function fill_config_name() {

        $listconfigs = self::get_list_configs();
        if (array_key_exists($this->configcode, $listconfigs)) {
            $this->name = $listconfigs[$this->configcode];
        }
    }

    /**
     * Заполнение свойства класса configcode (короткий код конфигурации с порядковым номером)
     * в случае, если это новая конфигурация (еще не имеет своего номера), то будет записан следующий по порядку номер
     */
    private function fill_config_code() {

        if (!empty($this->configcode)) {
            // Всё уже заполнено
            return;
        }

        $listconfigs = self::get_list_configs();

        if (empty($listconfigs)) {
            // еще не было ни одного конфига сохранено - мы делаем это впервые
            $this->configcode = 'c0';
            return;
        }

        // получение последнего порядкового номера, имеющегося среди сохраненных значений
        $lastkey = max(array_map(function($key){
            return (int)substr($key, 1);
        }, array_keys($listconfigs)));
        // создание нового кода из следующего порядкового номера с префиксом для нового конфига
        $this->configcode = 'c'.($lastkey+1);
    }

    /**
     * Получить название для свойства конфига соединения, которое следует использовать для сохранения в конфиг плагина
     * @param string $prop - название свойства соединения
     * @throws \Exception
     * @return string
     */
    public function get_config_name($prop) {
        if (!array_key_exists($prop, $this->config)) {
            throw new \Exception('Unknown config property');
        }
        return 'dbc_'.$this->get_config_code().'_'.$prop;
    }

    /**
     * Получить список сохраненных конфигов соединений
     * @return array
     */
    public static function get_list_configs() {

        $listconfigs = get_config('local_opentechnology', 'dbc_configs');

        if ($listconfigs === false) {
            return [];
        }

        $listconfigs = json_decode($listconfigs, true);
        if (is_null($listconfigs)) {
            return [];
        }

        return $listconfigs;
    }

    /**
     * Заполнить конфиг для данного экземпляра класса из ранее сохраненных данных, соответствующих названию конфига
     */
    private function fill_config_data() {

        $configplugin = ($this->configcode == 'auth_db' ? 'auth_db' : 'local_opentechnology');

        // массив для формирования конфига из ранее сохраненных данных
        $config = [];
        foreach (array_keys($this->config) as $prop) {

            if ($this->configcode == 'auth_db') {
                $configname = ($prop == 'database' ? 'name' : $prop);
            } else {
                $configname = $this->get_config_name($prop);
            }

            $value = get_config($configplugin, $configname);
            if ($value !== false) {
                $config[$prop] = $value;
            }
        }
        // установка конфига для данного экземпляра класса
        $this->set_config_data($config);
    }

    /**
     * Установка конфига для данного экземпляра класса
     * @param array $config
     */
    public function set_config_data(array $config) {

        foreach(array_keys($this->config) as $prop) {
            if (array_key_exists($prop, $config)) {
                $this->config[$prop] = $config[$prop];
            }
        }

    }

    /**
     * Получить конфиг коннекшена
     * @return [] - массив со свойствами соединения: host, type, database, user, pass, setupsql, extencoding
     */
    public function get_config_data() {
        return $this->config;
    }

    /**
     * Удаление конфига коннекшена
     */
    public function delete_config() {
        $listconfigs = self::get_list_configs();
        $configcode = $this->get_config_code(true);
        if (array_key_exists($configcode, $listconfigs)) {
            unset($listconfigs[$configcode]);
            $this->save_list_configs($listconfigs);

            foreach(array_keys($this->config) as $prop) {
                unset_config('dbc_'.$configcode.'_'.$prop, 'local_opentechnology');
            }
        }
    }

    private function save_list_configs($listconfigs) {
        set_config('dbc_configs', json_encode($listconfigs), 'local_opentechnology');
    }

    /**
     * Сохранение конфига коннекшена
     * @throws \Exception
     */
    public function save_config_data() {

        if ($this->get_config_code() == 'auth_db') {
            throw new \Exception('auth_db config should be modified with auth_db settings interface');
        }

        // сохранение конфигурации в список конфигураций
        $listconfigs = self::get_list_configs();
        $configcode = $this->get_config_code(true);
        if (!array_key_exists($configcode, $listconfigs) && !isset($this->name)) {
            throw new \Exception('the name should be specified for new connections');
        }
        if (isset($this->name)) {
            $listconfigs[$configcode] = $this->name;
            $this->save_list_configs($listconfigs);
            $this->configcode = $configcode;
        }

        // сохранение всех свойств коннекшена в конфигурацию
        foreach($this->config as $prop => $val) {
            set_config($this->get_config_name($prop), $val, 'local_opentechnology');
        }
    }

    /**
     * Получить соединение с базой данных для дальнейшей работы
     * @return ADOConnection
     */
    public function get_connection() {
        if (!is_null($this->db)) {
            return $this->db;
        }
        $this->db = $this->db_init($this->config['type'], $this->config['host'], $this->config['user'],
            $this->config['pass'], $this->config['database'], $this->config['setupsql']);
        return $this->db;
    }

    /**
     * Проверить соединение с базой данных
     * @return boolean
     */
    public function check_connection() {
        if (!is_null($this->db)) {
            return $this->db->IsConnected();
        }
        return false;
    }
    
    /**
     * Get server version info 
     * @return array An array with 2 elements: $arr['string'] is the description string,
			and $arr[version] is the version (also a string).
     */
    public function get_server_info() {
        if (!is_null($this->db)) {
            return $this->db->ServerInfo();
        }
        return '';
    }
    
    /**
     * the last error message
     * @return string
     */
    public function get_error_message() {
        if (!is_null($this->db)) {
            return $this->db->ErrorMsg();
        }
        return '';
    }
}
