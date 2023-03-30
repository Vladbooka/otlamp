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
 * Класс обработчика, останавливающего выполнение сценария
 *
 * @package     local_pprocessing
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class stop_scenario_execution extends base
{

    /**
     * {@inheritDoc}
     * @see \local_pprocessing\processor\handler\base::execute()
     */
    protected function execution_process(container $container)
    {
        $message = 'Scheduled stop of the scenario';
        $description = $this->get_optional_parameter('description', null);
        if (!is_null($description)) {
            $message .= ' with description: '.$description;
        }
        throw new \local_pprocessing\processor\exception($message, 200);
    }
}

