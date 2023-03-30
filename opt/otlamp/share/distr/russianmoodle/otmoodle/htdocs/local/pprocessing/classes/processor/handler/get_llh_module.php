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
use local_learninghistory\local\utilities as llh;

defined('MOODLE_INTERNAL') || die();

/**
 * Получение последней записи о модуле из истории обучения
 *
 * @package    local_pprocessing
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class get_llh_module extends base
{
    /**
     * {@inheritDoc}
     * @see \local_pprocessing\processor\base::validate_parameter()
     */
    protected function validate_parameter($name, $value)
    {
        switch($name)
        {
            case 'cmid':
                return is_number($value);
            case 'active':
                return is_bool($value);
        }
        return false;
    }
    /**
     * {@inheritDoc}
     * @see \local_pprocessing\processor\handler\base::execute()
     */
    protected function execution_process(container $container)
    {
        $cmid = $this->get_required_parameter('cmid');
        $active = $this->get_optional_parameter('active', false);

        $method = 'get_learninghistory_module_snapshot_' . ($active ? 'actual' : 'last');

        return llh::{$method}($cmid);
    }
}

