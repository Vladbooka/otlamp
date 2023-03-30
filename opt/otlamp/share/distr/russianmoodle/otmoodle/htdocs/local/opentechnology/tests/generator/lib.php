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

defined('MOODLE_INTERNAL') || die();

/**
 * local_opentechnology test data generator class
 *
 * @package     local_opentechnology
 * @category    phpunit
 * @copyright   2021 LTD "OPEN TECHNOLOGY"
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class local_opentechnology_generator extends component_generator_base {

    /**
     * Создает подключение по указанным реквизитам
     *
     * @param string $host
     * @param string $type
     * @param string $database
     * @param string $user
     * @param string $pass
     * @param string|NULL $name
     * @param string $setupsql
     * @param string $extencoding
     * @return \local_opentechnology\dbconnection
     */
    public function create_connection($host, $type, $database, $user, $pass, $name=null, $setupsql='SET NAMES \'utf8\'', $extencoding='utf-8') {

        static $index = 0;
        $index++;

        // Создадим подключение к БД
        $dbconname = $name ?? ('testconn'.$index);
        $dbcon = new \local_opentechnology\dbconnection();
        $dbcon->set_name($dbconname);
        $dbcon->set_config_data([
            'host' => $host,
            'type' => $type,
            'database' => $database,
            'user' => $user,
            'pass' => $pass,
            'setupsql' => $setupsql,
            'extencoding' => $extencoding
        ]);
        $dbcon->save_config_data();

        return $dbcon;
    }

    /**
     * Создает подключение к БД СЭО
     * @param string $name - желаемое название подключения или null для автоматической генерации
     * @return \local_opentechnology\dbconnection
     */
    public function create_local_connection(string $name = null) {
        global $CFG;
        return $this->create_connection($CFG->dbhost, $CFG->dbtype, $CFG->dbname, $CFG->dbuser, $CFG->dbpass);
    }

    /**
     * Создает подключение в соответствии с данными, переданными в конфигурационном массиве
     * @return \local_opentechnology\dbconnection
     * @throws \Exception
     */
    public function create_connection_from_config($dbconnconf = null) {
        if (is_null($dbconnconf)) {
            return $this->create_local_connection();
        }
        if (is_array($dbconnconf)) {
            if (array_diff_key(array_flip(['host', 'type', 'database', 'user', 'pass']), $dbconnconf)) {
                throw new \Exception('Отсутствуют обязательные параметры подключения');
            }
            $host        = $dbconnconf['host'];
            $type        = $dbconnconf['type'];
            $database    = $dbconnconf['database'];
            $user        = $dbconnconf['user'];
            $pass        = $dbconnconf['pass'];
            $name        = $dbconnconf['name'] ?? null;
            $setupsql    = $dbconnconf['setupsql'] ?? 'SET NAMES \'utf8\'';
            $extencoding = $dbconnconf['setupsql'] ?? 'utf-8';
            return $this->create_connection($host, $type, $database, $user, $pass, $name, $setupsql, $extencoding);
        }

        throw new \Exception('Не верный формат конфига подключения');
    }
}