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
use local_pprocessing\logger;

defined('MOODLE_INTERNAL') || die();

/**
 * Обрабатывает массив с помощью указанного колбека.
 * В качестве колбека используется хендлер.
 *
 * @package local
 * @subpackage pprocessing
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class array_map extends base
{
    /**
     * {@inheritDoc}
     * @see \local_pprocessing\processor\handler\base::execute()
     */
    protected function execution_process(container $container)
    {
        global $CFG;
        $array = (array)$this->get_required_parameter('array');
        $callback = $this->get_required_parameter('callback');
        $itemparamname = $this->get_required_parameter('itemparamname');
        $callbackparams = $this->get_optional_parameter('callbackparams', []);
        $config = $this->get_optional_parameter('config', []);
        
        $handler = $CFG->dirroot . '/local_pprocessing/classes/processor/handler/' . $callback . '.php';
        if (file_exists($handler)) {
            require_once($handler);
        }
        $class = "\\local_pprocessing\\processor\\handler\\" . $callback;
        if (class_exists($class)) {
            $processorconfig = [];
            if (is_array($config)) {
                $processorconfig = $config;
            }
            $lastresult = null;
            $processor = new $class($processorconfig);
            foreach ($array as $k => $v) {
                $processorparams = array_merge($callbackparams, [$itemparamname => $v]);
                $processor->set_params($processorparams);
                $processor->result = $lastresult;
                $array[$k] = $lastresult = $processor->execute($container);
            }
        }
        return $array;
    }
}

