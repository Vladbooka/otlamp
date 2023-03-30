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

namespace local_pprocessing\processor\handler;

use local_pprocessing\container;
use local_pprocessing\logger;

defined('MOODLE_INTERNAL') || die();

/**
 * Получение данных курса по идентификатору
 *
 * @package    local_pprocessing
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class get_pubcourses_in_progress extends base
{
    /**
     * {@inheritDoc}
     * @see \local_pprocessing\processor\handler\base::execute()
     */
    protected function execution_process(container $container)
    {
        $epcourses = \block_mastercourse\helper::get_courses_in_progress();
        
        // уникальный код сценария
        $scenariocode = $container->read('scenario.code');
        
        // запись в лог успешного результата
        logger::write_log(
            'processor',
            $this->get_type()."__".$this->get_code(),
            'success',
            [
                'scenariocode' => $scenariocode,
                'epcourses' => $epcourses
            ]
        );
        $container->write('pubcourses_in_progress', $epcourses);
    }
}

