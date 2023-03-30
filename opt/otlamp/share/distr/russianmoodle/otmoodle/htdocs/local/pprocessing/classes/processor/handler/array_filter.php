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
use local_pprocessing\condition;
use local_pprocessing\logger;

defined('MOODLE_INTERNAL') || die();

/**
 * Фильтрация массива по заданным критериям
 *
 * @package local
 * @subpackage pprocessing
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class array_filter extends base
{
    
    /**
     * {@inheritDoc}
     * @see \local_pprocessing\processor\handler\base::execute()
     */
    protected function execution_process(container $container)
    {
        $array = (array)$this->get_required_parameter('array');
        $keycondoperator = $this->get_optional_parameter('key_cond_operator', null);
        $keycondvalue = $this->get_optional_parameter('key_cond_value', null);
        $valuecondoperator = $this->get_optional_parameter('value_cond_operator', null);
        $valuecondvalue = $this->get_optional_parameter('value_cond_value', null);
        
        $lastresult = $this->result;
        $debugginglevel = $this->debugging_level+1;
        
        logger::write_log(
            'processor',
            $this->get_type()."__".$this->get_code(),
            'debug',
            [
                'array' => var_export($array, true),
                'is_array' => is_array($array),
            ]
        );
        
        return array_filter($array, function($v, $k) use ($keycondoperator, $keycondvalue, $valuecondoperator, $valuecondvalue, $container, $lastresult, $debugginglevel) {
            $filterresult = true;
            if (!is_null($keycondoperator) && !is_null($keycondvalue)) {
                $config = [
                    'comparison_value' => $k,
                    'operator' => $keycondoperator,
                    'value' => $keycondvalue,
                    'target' => 'comparison_result'
                ];
                logger::write_log(
                    'processor',
                    $this->get_type()."__".$this->get_code(),
                    'debug',
                    [
                        'config' => $config,
                    ],
                    'k_config'
                );
                $cond = condition::construct_from_config($container, $lastresult, $config, null, $debugginglevel);
                $filterresult = $filterresult && $cond->get_comparison_result();
            }
            if (!is_null($valuecondoperator) && !is_null($valuecondvalue)) {
                $config = [
                    'comparison_value' => $v,
                    'operator' => $valuecondoperator,
                    'value' => $valuecondvalue,
                    'target' => 'comparison_result'
                ];
                logger::write_log(
                    'processor',
                    $this->get_type()."__".$this->get_code(),
                    'debug',
                    [
                        'config' => $config,
                    ],
                    'v_config'
                );
                $cond = condition::construct_from_config($container, $lastresult, $config, null, $debugginglevel);
                $filterresult = $filterresult && $cond->get_comparison_result();
            }
            return $filterresult;
        }, ARRAY_FILTER_USE_BOTH);
    }
}

