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
namespace local_pprocessing\processor\handler;
use core_text;
use moodle_exception;
use local_pprocessing\container;
use local_pprocessing\logger;
use local_opentechnology\adodb;
use local_opentechnology\dbconnection;
use local_pprocessing\condition_parser;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir.'/adodb/adodb.inc.php');

/**
 * Обновление записей во внешней базе данных
 *
 * @package     local_pprocessing
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class db_update_records extends base
{
    use adodb;
    /**
     * {@inheritDoc}
     * @see \local_pprocessing\processor\handler\base::execute()
     */
    protected function execution_process(container $container)
    {
        try {
            
            $connection = $this->get_required_parameter('connection');
            if (is_string($connection)) {
                // Передали код подключения
                $dbconnection = new dbconnection($connection);
                $db = $dbconnection->get_connection();
            } elseif (is_array($connection)) {
                // Передали параметры подключения в бд
                $setupsql = $this->get_optional_parameter('setup_sql');
                $db = $this->db_init($connection['type'], $connection['host'], $connection['user'],
                    $connection['pass'], $connection['database'], $setupsql);
            }
            
        } catch(moodle_exception $ex)
        {
            $logdata = [
                'connection' => $connection,
                'setup_sql' => $setupsql,
                'exception' => $ex->getMessage(),
                'exceptionTrace' => $ex->getTrace()
            ];
            logger::write_log('processor', $this->get_full_code(), 'debug', $logdata, 'Connect to database failed');
            return false;
        }
        
        // Массив значений, которые нужно обновить вида field => value
        $values = $this->get_required_parameter('values');
        $tablename = $this->get_required_parameter('table_name');
        $conditions = $this->get_optional_parameter('conditions', []);
        logger::write_log(
            'processor',
            $this->get_type()."__".$this->get_code(),
            'debug',
            [
                'connection' => $connection,
                'values' => $values,
                'tablename' => $tablename,
                'conditions' => $conditions,
            ]
        );
        
        foreach ($values as $field => $value) {
            $set[] = "$field = " . $db->qstr($value);
        }
        $set = implode(', ', $set);
        
        $parser = new condition_parser($conditions, $container, $this->result);
        $parser->set_debugging_level($this->debugging_level+1);
        list($where, $parameters) = $parser->parse();
        
        $sql = "UPDATE $tablename
                   SET $set
                 WHERE $where";
        list($sql, $parameters) = $this->unname_params($sql, $parameters);
        $result = $db->Execute($sql, $parameters);
        
        logger::write_log('processor', $this->get_full_code(), 'debug', 
            ['values' => $values, 'sql' => $sql, 'result' => var_export($result, true)]);
        
        $db->Close();
        return $result;
    }
}

