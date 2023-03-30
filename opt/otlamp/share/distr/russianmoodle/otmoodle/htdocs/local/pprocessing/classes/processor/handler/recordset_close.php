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
use moodle_recordset;

defined('MOODLE_INTERNAL') || die();

/**
 * Закрытие итератора
 *
 * @package     local_pprocessing
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class recordset_close extends base
{
    /**
     * {@inheritDoc}
     * @see \local_pprocessing\processor\handler\base::execute()
     */
    protected function execution_process(container $container)
    {
        $rs = $this->get_required_parameter('rs');
        if (!empty($rs) && $rs instanceof moodle_recordset) {
            $rs->close();
        } else {
            $this->debugging('required parameter rs is empty or not instanceof moodle_recordset', [
                'rs' => var_export($rs, true),
                'scenario_code' => var_export($container->read('scenario.code'), true),
                
            ]);
        }
    }
}

