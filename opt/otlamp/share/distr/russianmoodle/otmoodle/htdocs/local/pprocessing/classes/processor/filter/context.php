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
 * Фильтрация контекстов
 *
 * @package    local_pprocessing
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class context extends base
{
    /**
     * {@inheritDoc}
     * @see \local_pprocessing\processor\filter\base::execute()
     */
    protected function execution_process(container $container)
    {
        global $CFG;

        $result = new \stdClass();
        $conditions = [];

        // формирование условий по основным полям профиля
        if( isset($this->config) && is_array($this->config))
        {
            foreach($this->config as $fieldname => $condition)
            {
                if( isset($condition['value']) && isset($condition['operator'])
                    && in_array($condition['operator'], constants::sql_comparison_operators) )
                {
                    $conditions[] = 'ctx.'.$fieldname.' '.$condition['operator']. ' :mf_'.$fieldname;
                    $result->parameters['mf_'.$fieldname] = $condition['value'];
                }
            }
        }

        $result->conditions = implode(' AND ',$conditions);
        $container->write('contextfilter', $result);
    }

}

