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

namespace local_pprocessing\processor\filter;

use local_pprocessing\container;
use local_pprocessing\constants;

defined('MOODLE_INTERNAL') || die();

/**
 * Фильтрация назначений ролей
 *
 * @package    local_pprocessing
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class role_assignment extends base
{
    /**
     * {@inheritDoc}
     * @see \local_pprocessing\processor\filter\base::execute()
     */
    protected function execution_process(container $container)
    {
        global $CFG, $DB;

        $result = new \stdClass();
        $conditions = $ctxparams = [];
        $ctxsql = '';

        // формирование условий по основным полям профиля
        if( isset($this->config) && is_array($this->config))
        {
            foreach($this->config as $fieldname => $condition)
            {
                if( isset($condition['value']) && isset($condition['operator'])
                    && in_array($condition['operator'], constants::sql_comparison_operators) )
                {
                    $conditions[] = 'ra.'.$fieldname.' '.$condition['operator']. ' :mf_'.$fieldname;
                    $result->parameters['mf_'.$fieldname] = $condition['value'];
                }
            }
        }
        
        $contexts = $container->read('contexts', null, false);
        if( ! is_null($contexts) && ! empty($contexts) )
        {
            list($ctxsql, $ctxparams) = $DB->get_in_or_equal(array_keys($contexts), SQL_PARAMS_NAMED, 'mf_');
        }
        $conditions[] = 'ra.contextid ' . $ctxsql;
        $result->parameters = array_merge($result->parameters, $ctxparams);
        
        $result->conditions = implode(' AND ',$conditions);
        $container->write('role_assignmentfilter', $result);
    }

}

