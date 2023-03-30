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

require_once($CFG->dirroot . '/user/lib.php');
defined('MOODLE_INTERNAL') || die();

/**
 * Базовый класс обработчика
 *
 * @package     local_pprocessing
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class display_notification extends base
{
    /**
     * {@inheritDoc}
     * @see \local_pprocessing\processor\handler\base::execute()
     */
    protected function execution_process(container $container)
    {
        global $PAGE;
        
        // уникальный код сценария
        $scenariocode = $container->read('scenario.code');
        
        if( ! $this->is_precedent_processed($scenariocode, $container) )
        {
            $courseid = $container->read('courseid');
            
            if (!isset($PAGE->context))
            {
                $PAGE->set_context(\context_course::instance($courseid));
            }
            $PAGE->requires->js_call_amd(
                'local_pprocessing/display_notification',
                'init',
                [
                    $this->config['type'] ?? 'alert',
                    $this->config['heading'] ?? '',
                    $this->config['text'] ?? ''
                ]
            );
            
            // сохранение данных обработанного прецедента
            $this->add_processed($scenariocode, $container);
        }
    }
}

