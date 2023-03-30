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
 * Источник - внешняя база данных
 *
 * @package    auth
 * @subpackage dof
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace auth_dof\sourcetype;

use HTML_QuickForm;
use local_opentechnology\dbconnection;
use auth_dof\sourcetype_base;


class db extends sourcetype_base
{
    /**
     * 
     * {@inheritDoc}
     * @see \auth_dof\sourcetype_base::definition()
     */
    public function definition(HTML_QuickForm $mform) {
        $group = [];
        // Получить список сохраненных конфигов соединений из local_opentechnology\dbconnection
        $configs = dbconnection::get_list_configs();
        $group[] = $mform->createElement(
            'html',
            get_string('db_connection_configs_list_desc', 'auth_dof')
            );
        $group[] = $mform->createElement(
            'select', 
            'db_connection_configs_list', 
            get_string('db_connection_configs_list', 'auth_dof'), 
            $configs
            );
        
        $group[] = $mform->createElement(
            'text', 
            'db_table', 
            get_string('db_table', 'auth_dof')
            );
        $mform->setType('db_table', PARAM_TEXT);
        return $group;
    }
    
    /**
     * 
     * {@inheritDoc}
     * @see \auth_dof\sourcetype_base::process()
     */
    public function process($data) {
        return [$data['db_connection_configs_list'], $data['db_table']];
    }
    
    /**
     * 
     * {@inheritDoc}
     * @see \auth_dof\sourcetype_base::get_external_fields()
     */
    public function get_external_fields(string $connection, string $table) {
        $dbconnection = new dbconnection($connection);
        $db = $dbconnection->get_connection();
        if (!$dbconnection->check_connection()) {
            print_error('src_connection_error',  'auth_dof', '', $this->get_cofig_name($connection));
        }
        // Такой вариант сработает не со всеми типами баз, но может получить поля пустых таблиц
        // должен работать с mysql, mssql и postgres, а вообще можно использовать такой вариант
        $cfgdat = $dbconnection->get_config_data();
        $sql = "SELECT column_name FROM information_schema.columns";
        $sql .= ' WHERE table_name="' . $table . '" AND table_schema="' . $cfgdat['database'] .'"';
        $result = $db->Execute($sql);
        $dbfields = [];
        if (! empty($result)) {
            if ($result !== false) {
                while($row = $result->fetchRow()) {
                    $dbfields[$row['column_name']] = $row['column_name'];
                }
            }
        } else {
            // Вариант 2 на случай если первый не сработал
            $result = $db->Execute('SELECT * FROM ' . $table . ' LIMIT 1');
            if (! empty($result)) {
                foreach ($result->fields as $key => $val) {
                    $dbfields[$key] = $key;
                }
            }
        }
        $db->Close();
        return $dbfields;
    }
    
    /**
     * 
     * {@inheritDoc}
     * @see \auth_dof\sourcetype_base::get_external_fields_data()
     */
    public function get_external_fields_data(string $connection, string $table, array $conditions) {
        $conditionsdat = [];
        $parameters = [];
        $result = [];
        $connconfigname = $this->get_cofig_name($connection) . '(' . $table . ')';
        foreach ($conditions as $name => $value) {
            $conditionsdat[] = "{$name}=?";
            $parameters[] = $value;
        }
        $dbconnection = new dbconnection($connection);
        $db = $dbconnection->get_connection();
        if (!$dbconnection->check_connection()) {
            print_error('src_connection_error',  'auth_dof', '', $connconfigname);
        }
        $sql = "SELECT * FROM " . $table . " WHERE " . implode(' AND ', $conditionsdat);
        // пробовал использовать: $this->unname_params($sql, $conditions); это точно нормальный код?
        if (! $queryresult = $db->selectLimit($sql, 2, -1, $parameters)) {
            $db->Close();
            print_error('src_no_queryresult',  'auth_dof', '', $connconfigname);
        }
        while ($r = $queryresult->fetchRow()) {
            if (! empty($result)) {
                $db->Close();
                print_error('src_many_entries_by_conditions', 'auth_dof', '', $connconfigname);
            }
            $result = $r;
        }
        if (empty($result)) {
            $db->Close();
            print_error('src_no_entries_by_conditions', 'auth_dof', '', $connconfigname);
        }
        $db->Close();
        return $result;
    }
    
    public static function get_name_string() {
        return get_string('src_db', 'auth_dof');
    }
    
    public static function get_cofig_name(string $connection) {
        $configs = dbconnection::get_list_configs();
        return $configs[$connection] ?? '';
    }
    
    /**
     * 
     * {@inheritDoc}
     * @see \auth_dof\sourcetype_base::validation()
     */
    public function validation() {
        $errors = [];
        $err = false;
        try {
            $connection = required_param('db_connection_configs_list', PARAM_TEXT);
            $table = required_param('db_table', PARAM_TEXT);
            if (empty($this->get_external_fields($connection, $table))) {
                $errors['db_connection_configs_list'] = get_string(
                    'error_get_src_fields', 'auth_dof');
            }
        } catch (\Exception $e) {
            $errors['db_connection_configs_list'] = $e->getMessage();
        }
        return $errors;
    }

}