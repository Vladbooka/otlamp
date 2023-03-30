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

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir.'/adodb/adodb.inc.php');

/**
 * Базовый класс обработчика
 *
 * @package     local_pprocessing
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class db_insert_record extends base
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

        $tablename = $this->get_required_parameter('table_name');
        $field_values = $this->get_required_parameter('field_values');
        $result = false;
        
        $result = $db->autoExecute($tablename, $field_values);

        logger::write_log('processor', $this->get_full_code(), 'debug', 
            ['field_values' => $field_values, 'tablename' => $tablename, 'result' => $result]);

        $db->Close();
        return $result;
    }
}

