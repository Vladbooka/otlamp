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
use local_pprocessing\container;
use local_pprocessing\condition_parser;

require_once($CFG->dirroot . '/user/lib.php');
defined('MOODLE_INTERNAL') || die();

/**
 * Формирование подстроки запроса из конфигурационного массива с условиями
 *
 * @package     local_pprocessing
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class compose_sql_conditions extends base
{
    /**
     * {@inheritDoc}
     * @see \local_pprocessing\processor\handler\base::execute()
     */
    protected function execution_process(container $container)
    {
        $conditionsconf = $this->get_required_parameter('conditions');
        
        $parser = new condition_parser($conditionsconf, $container, $this->result);
        $parser->set_debugging_level($this->debugging_level+1);
        list($sql, $params) = $parser->parse();
        
        $result = new \stdClass();
        $result->conditions = $sql;
        $result->parameters = $params;
        
        return $result;
    }
}

