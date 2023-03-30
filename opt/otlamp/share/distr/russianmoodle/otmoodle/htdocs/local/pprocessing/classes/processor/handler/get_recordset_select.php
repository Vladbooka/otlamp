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

defined('MOODLE_INTERNAL') || die();

/**
 * Итеративное получение записей из базы данных через метод get_recordset_select
 *
 * @package     local_pprocessing
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class get_recordset_select extends base
{
    /**
     * {@inheritDoc}
     * @see \local_pprocessing\processor\handler\base::execute()
     */
    protected function execution_process(container $container)
    {
        global $DB;
        
        $table = (string)$this->get_required_parameter('table');
        $sqlconds = (array)$this->get_required_parameter('sqlconds');
        
        $sql = '';
        if (array_key_exists('conditions', $sqlconds)) {
            $sql = $sqlconds['conditions'];
        }
        
        $params = [];
        if (array_key_exists('parameters', $sqlconds)) {
            $params = $sqlconds['parameters'];
        }
        
        $sort = $this->get_optional_parameter('sort', '');
        $fields = $this->get_optional_parameter('fields', '*');
        $limitfrom = $this->get_optional_parameter('limitfrom', 0);
        $limitnum = $this->get_optional_parameter('limitnum', 0);
        
        $rs = $DB->get_recordset_select($table, $sql, $params, $sort, $fields, $limitfrom, $limitnum);
        if ($rs->valid()) {
            $this->debugging('rs', ['rs' => var_export($rs, true)]);
            return $rs;
        } else {
            return [];
        }
    }
}

