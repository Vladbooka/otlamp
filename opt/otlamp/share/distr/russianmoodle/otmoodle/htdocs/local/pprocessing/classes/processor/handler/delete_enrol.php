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
 * Поиск записей в таблице кастомных полей по коду сущности и доп.параметрам
 *
 * @package     local_pprocessing
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class delete_enrol extends base
{
    /**
     * {@inheritDoc}
     * @see \local_pprocessing\processor\handler\base::execute()
     */
    protected function execution_process(container $container)
    {
        
        $enrolinstance = (object)$this->get_required_parameter('enrol_instance');
        if (!property_exists($enrolinstance, 'enrol'))
        {
            throw new \Exception('Enrol instance should contain \'enrol\'-property');
        }
        
        $enrolplugin = enrol_get_plugin($enrolinstance->enrol);
        if (is_null($enrolplugin))
        {
            throw new \Exception('Enrol plugin not found');
        }
        
        $enrolplugin->delete_instance($enrolinstance);
        
        return true;
    }
}

