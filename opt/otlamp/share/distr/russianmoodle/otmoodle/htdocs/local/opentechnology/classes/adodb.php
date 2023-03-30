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

require_once($CFG->libdir . '/adodb/adodb.inc.php');
use moodle_exception;
use ADOConnection;


defined('MOODLE_INTERNAL') || die();

trait adodb {

    protected $dbtypes = ["access", "ado_access", "ado", "ado_mssql", "borland_ibase", "csv", "db2",
        "fbsql", "firebird", "ibase", "informix72", "informix", "mssql", "mssql_n", "mssqlnative",
        "mysql", "mysqli", "mysqlt", "oci805", "oci8", "oci8po", "odbc", "odbc_mssql", "odbc_oracle",
        "oracle", "pdo", "postgres64", "postgres7", "postgres", "proxy", "sqlanywhere", "sybase", "vfp"];



    /**
     * Add slashes, we can not use placeholders or system functions.
     *
     * @param string $text
     * @return string
     */
    protected function ext_addslashes($text, $sybasequoting=false) {
        if (empty($sybasequoting)) {
            $text = str_replace('\\', '\\\\', $text);
            $text = str_replace(['\'', '"', "\0"], ['\\\'', '\\"', '\\0'], $text);
        } else {
            $text = str_replace("'", "''", $text);
        }
        return $text;
    }

    /**
     * Connect to external database.
     *
     * @return ADOConnection
     * @throws moodle_exception
     */
    protected function db_init($type, $host, $user, $pass, $database, $setupsql) {

        if (!in_array($type, $this->dbtypes))
        {
            throw new moodle_exception('Wrong db type');
        }

        // Connect to the external database (forcing new connection).
        $db = ADONewConnection($type);
        $db->Connect($host, $user, $pass, $database, true);
        $db->SetFetchMode(ADODB_FETCH_ASSOC);

        if (!empty($setupsql))
        {
            $db->Execute($setupsql);
        }

        return $db;
    }


    /**
     * Переработать запрос, превратив именованные параметры в неименованные параметры вида "?"
     *
     * @param string $sql - sql-запрос
     * @param array $params - массив именованных параметров
     *
     * @return array - массив, состоящий из двух элементов: строка запроса, массив параметров, выстроенных по порядку встречающихся подстановок в запросе
     */
    protected function unname_params($sql, $params)
    {
        $newparams = [];
        $sql = preg_replace_callback('/(?<!:):[a-z][a-z0-9_]*/', function($matches) use ($sql, $params, &$newparams){
            $newparams[] = $params[substr($matches[0],1)];
            return '?';
        }, $sql);
        return [$sql, $newparams];
    }


}