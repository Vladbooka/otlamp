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

namespace local_pprocessing;

defined('MOODLE_INTERNAL') || die();

/**
 * Класс сценария
 *
 * @package    local_pprocessing
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class scenario
{

    protected $processors = [];

    /**
     * Добавление обработчика в сценарий
     * @param unknown $type
     * @param unknown $code
     * @param array $config
     * @param unknown $compositekeyfields
     * @param array $input
     * @param string $resvar
     * @param array $preconditions
     */
    public function add_processor($type, $code, $config=[], $compositekeyfields=null, array $input=null, string $resvar=null,
        array $preconditions = null, string $description=null)
    {
        $class = "\\local_pprocessing\\processor\\" . $type . "\\" . $code;
        if( class_exists($class) )
        {
            $processorconfig = [];
            if( is_array($config) )
            {
                $processorconfig = $config;
            }

            $processor = new $class($processorconfig);

            if( is_array($compositekeyfields) && count($compositekeyfields) > 0
                && is_callable([$processor, 'set_composite_key_fields']) )
            {
                $processor->set_composite_key_fields($compositekeyfields);
            }

            if (!is_null($resvar) && is_string($resvar))
            {
                $processor->set_result_variable($resvar);
            }

//             logger::write_log('scenario', $type.'_'.$code, 'debug', $input, 'input');
            if (!is_null($input) && is_array($input))
            {
                $processor->set_params($input);
            }

            if (!is_null($preconditions) && is_array($preconditions))
            {
                $processor->set_preconditions($preconditions);
            }

            if (!is_null($description)) {
                $processor->set_description($description);
            }

            $this->processors[] = $processor;

        }
    }

    public function get_processors()
    {
        return $this->processors;
    }

    /**
     * установка переменных
     *
     * @param string $varname
     * @param mixed $value
     *
     * @return void
     */
    public function __set($varname, $value)
    {
        if ( property_exists($this, $varname) && empty($this->{$varname}) )
        {
            $this->{$varname} = $value;
        }
    }

    /**
     * получение переменных
     *
     * @param string $varname
     * @param mixed $value
     *
     * @return void
     */
    public function __get($varname)
    {
        if ( property_exists($this, $varname) )
        {
            return $this->{$varname};
        }
    }
}

