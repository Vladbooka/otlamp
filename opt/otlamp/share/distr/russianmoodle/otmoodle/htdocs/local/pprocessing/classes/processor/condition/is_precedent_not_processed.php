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

use local_pprocessing\composite_key;
use local_pprocessing\container;
use local_pprocessing\logger;

defined('MOODLE_INTERNAL') || die();

/**
 * Условие - является ли прецедент еще не обработанным (если уже обработан - останавливаемся)
 *
 * @package    local_pprocessing
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class is_precedent_not_processed extends base
{
    use composite_key;
    
    /**
     * {@inheritDoc}
     * @see \local_pprocessing\processor\condition\base::execute()
     */
    protected function execution_process(container $container)
    {
        // уникальный код сценария
        $scenariocode = $container->read('scenario.code');
        $handlercode = null;
        if (array_key_exists('handlercode', $this->config))
        {
            $handlercode = $this->config['handlercode'];
        }
        
        if ($this->is_precedent_processed($scenariocode, $container, $handlercode))
        {
            return false;
        }
        
        
        return $this->result;
    }
}

