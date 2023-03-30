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
use moodle_exception;
use local_pprocessing\container;
use local_pprocessing\logger;
use local_pprocessing\condition_parser;
use local_opentechnology\adodb;
defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir.'/adodb/adodb.inc.php');

/**
 * Базовый класс обработчика
 *
 * @package     local_pprocessing
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class db_get_record extends base
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
            $setupsql = $this->get_optional_parameter('setup_sql');
            $db = $this->db_init($connection['type'], $connection['host'], $connection['user'],
                                 $connection['pass'], $connection['database'], $setupsql);

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

        $structure = $this->get_required_parameter('conditions');
        $parser = new condition_parser($structure, $container, $this->result);
        $parser->set_debugging_level($this->debugging_level+1);
        list($conditions, $parameters) = $parser->parse();

        $fields = $this->get_optional_parameter('fields', '*');
        if (is_array($fields))
        {
            $fields = implode(', ', $fields);
        }

        $nrows = $this->get_optional_parameter('nrows', -1);
        // @TODO: проверить не дичь ли тут ниже написана
        if (is_array($nrows))
        {
            $nrows = (int)$nrows;
        }

        $offset = $this->get_optional_parameter('offset', -1);
        // @TODO: проверить не дичь ли тут ниже написана
        if (is_array($offset))
        {
            $offset = (int)$offset;
        }

        $tablename = $this->get_required_parameter('table_name');
        $sql = "SELECT ".$fields."
                  FROM " . $tablename . "
                 WHERE " . $conditions;

        list($sql, $parameters) = $this->unname_params($sql, $parameters);

        $results = [];
        $queryresult = $db->selectLimit($sql, $nrows, $offset, $parameters);
        while ($r = $queryresult->fetchRow())
        {
            $results[] = $r;
        }

        $db->Close();

        return $results;
    }

}

