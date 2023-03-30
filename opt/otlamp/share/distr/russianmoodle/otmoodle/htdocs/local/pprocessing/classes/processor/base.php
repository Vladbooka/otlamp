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

namespace local_pprocessing\processor;

use local_pprocessing\container;
use local_pprocessing\executor;
use local_pprocessing\logger;
use local_pprocessing\scenario;
use local_pprocessing\stub;
use local_pprocessing\input_parameter;

defined('MOODLE_INTERNAL') || die();

/**
 * Базовый класс обработчика
 *
 * @package    local_pprocessing
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
abstract class base
{
    private $result_variable = null;
    private $params_config = null;
    private $preconditions_scenario = null;
    protected $debugging_level = 1;
    protected $params = null;
    protected $description = null;
    protected $config = [];


    public function __construct($config = [])
    {
        if( is_array($config) )
        {
            $this->config = $config;
        }
    }

    /**
     * Выполнение обработчика
     *
     * @param container $container - Контейнер переменных
     *
     * @return mixed
     */
    public function execute(container $container)
    {
        $curlevel = $this->debugging_level;

        $scenariocode = $container->read('scenario.code');

        $this->preparing_execution($container);
        $debugdata = [
            'description' => $this->description,
            'scenariocode' => $scenariocode,
            'params' => $this->params,
            'config' => $this->config
        ];
        if (property_exists($this, 'composite_key_fields'))
        {
            $debugdata['composite_key_fields'] = $this->composite_key_fields;
        }
        $this->debugging($this->get_type().' execution started', $debugdata, true);

        $this->execute_preconditions($container);

        $this->set_debugging_level($curlevel + 1);
        $result = $this->execution_process($container);
        $this->set_debugging_level($curlevel);

        $result = $this->processing_execution_results($container, $result);
        $debugdata = [
            'scenariocode' => $scenariocode,
            'result' => $result
        ];
        $this->debugging($this->get_type().' execution ended', $debugdata, false);

        return $result;
    }

    public function get_full_code()
    {
        return $this->get_type().'_'.$this->get_code();
    }

    public function set_debugging_level($level)
    {
        $this->debugging_level = $level;
    }

    protected function execute_preconditions(container $container)
    {
        if (!is_null($this->preconditions_scenario))
        {
            $scenariocode = $container->read('scenario.code');
            $fullcode = $this->get_full_code();
            // запуск обработки предусловий
            $preconditionsresult = executor::preconditions(
                $scenariocode.'_'.$fullcode.'_preconditions',
                $this->preconditions_scenario,
                $container,
                $this->result
                );
            if (!$preconditionsresult)
            {
                throw new exception('Preconditions failed', 412);
            }
        }
    }

    protected function preparing_execution(container $container)
    {
        // формирование входных параметров перед запуском самого обработчика
        $this->params = [];
        if (!empty($this->params_config) && is_array($this->params_config))
        {
            foreach($this->params_config as $parameter => $paramconfig)
            {
                $inputparam = new input_parameter($paramconfig, $container, $this->result);
                $this->params[$parameter] = $inputparam->get_value();
            }
        }
    }

    public function debugging($comment, $data, $ends=null)
    {
        $pad = (is_null($ends) ? '' : ($ends ? '>' : '<'));
        $padtype = ($ends === true ? STR_PAD_LEFT : STR_PAD_RIGHT);
        $prefix = str_pad($pad, $this->debugging_level*2, '-', $padtype);
        logger::write_log('processor', $prefix . ' ' . $this->get_full_code(), 'debug', $data, $comment);
    }

    protected function processing_execution_results(container $container, $result)
    {
        if ($this->get_type() == 'condition' && !empty($this->config['invert_result']))
        {
            $result = !$result;
        }

        if (!is_null($this->result_variable) && is_string($this->result_variable))
        {
            $container->write($this->result_variable, $result);
        }

        return $result;
    }

    public function set_result_variable(string $resvar)
    {
        $this->result_variable = $resvar;
    }

    public function set_description(string $description=null) {
        $this->description = $description;
    }

    public function set_params(array $input)
    {
        $this->params_config = [];
        foreach($input as $parameter => $paramconfig)
        {
            $this->params_config[$parameter] = $paramconfig;
        }
    }

    public function set_preconditions($preconditions)
    {
        if (is_array($preconditions) && count($preconditions) > 0)
        {
            $preconditionscenario = new scenario();

//             $this->debugging('preconditions', $preconditions);
            foreach($preconditions as $precondition)
            {
                $precondition['type'] = 'condition';
                $precondition['preconditions'] = null;
                stub::add_processor($preconditionscenario, $precondition);
            }
            $this->preconditions_scenario = $preconditionscenario;
        }
    }

    protected function validate_parameter($name, $value)
    {
        return true;
    }

    protected function get_required_parameter($name)
    {
        if ($this->isset_parameter($name))
        {
            if ($this->validate_parameter($name, $this->params[$name]))
            {
                return $this->params[$name];

            } else
            {
                throw new \local_pprocessing\processor\exception('Invalid parameter "'.$name.'" value', 422);
            }
        } else
        {
            throw new \local_pprocessing\processor\exception('Missing required parameter "'.$name.'"', 422);
        }
    }

    protected function get_optional_parameter($name, $default=null)
    {
        if ($this->isset_parameter($name))
        {
            if ($this->validate_parameter($name, $this->params[$name]))
            {
                return $this->params[$name];

            } else
            {
                throw new \local_pprocessing\processor\exception('Invalid parameter "'.$name.'" value', 422);
            }
        }
        return $default;
    }

    protected function isset_parameter($name)
    {
        if (is_array($this->params) && array_key_exists($name, $this->params))
        {
            return true;
        }
        return false;
    }

    public function get_code()
    {
        $class = get_called_class();
        return substr(strrchr($class, "\\"), 1);
    }

    abstract public function get_type();
}

