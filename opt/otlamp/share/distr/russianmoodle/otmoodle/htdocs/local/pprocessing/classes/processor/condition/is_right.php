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

namespace local_pprocessing\processor\condition;

use local_pprocessing\container;
use local_pprocessing\condition;

defined('MOODLE_INTERNAL') || die();

/**
 * Условие - проверка, что результат сравнения настроенных значений соответствует ожидаемому
 *
 * @package    local_pprocessing
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class is_right extends base
{

    /**
     * {@inheritDoc}
     * @see \local_pprocessing\processor\condition\base::execute()
     */
    protected function execution_process(container $container)
    {
        $check = $this->get_required_parameter('check');
        $operator = $this->get_required_parameter('operator');
        $value = $this->get_required_parameter('value');

        $config = [
            'comparison_value' => $check,
            'operator' => $operator,
            'value' => $value,
            'target' => 'comparison_result'
        ];

        try {

            $cond = condition::construct_from_config($container, $this->result, $config, null, ($this->debugging_level+1));

            $comparisonresult = $cond->get_comparison_result();

            $this->debugging('comparison explanation', [
                'comparison_value' => $cond->get_comparison_value(),
                'operator' => $cond->get_operator(),
                'value' => $cond->get_value(),
                'comparison_result' => $comparisonresult
            ]);

            return $comparisonresult;

        } catch(\Exception $ex)
        {
            $this->debugging('comparison exception', ['exMessage' => $ex->getMessage(), 'config' => $config]);
            return false;
        }

    }
}

