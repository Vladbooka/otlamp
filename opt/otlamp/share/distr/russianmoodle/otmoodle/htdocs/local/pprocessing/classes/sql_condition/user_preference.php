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

namespace local_pprocessing\sql_condition;

defined('MOODLE_INTERNAL') || die();

class user_preference extends base
{
    /**
     * Алиас таблицы для поля
     * @var string
     */
    protected function get_table_alias()
    {
        return 'up';
    }
    
    /**
     * Префикс для формирования списка параметров
     * @var string
     */
    protected function get_param_prefix()
    {
        return 'up';
    }
    
    public function get_select_pieces($field, $operator, $value, $prefix)
    {
        list($sqlpart, $params) = $this->prepare_sql_part($field, $operator, $value, $prefix);
        
        $table = '';
        if (!is_null($this->get_table_alias()))
        {
            $table = $this->get_table_alias() . '.';
        }
        
        return [$table . 'name = \'' . $field . '\' AND up.value ' . $sqlpart, $params];
    }
}