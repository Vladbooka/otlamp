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
include_once $CFG->dirroot.'/user/profile/lib.php';

defined('MOODLE_INTERNAL') || die();

/**
 * Получение списка глобальных групп
 *
 * @package    local
 * @subpackage pprocessing
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class get_cohorts extends base
{
    /**
     * @var array - массив, поддерживаемых обработчиком префильтров
     *              (будет учитываться для отображения настройки фильтра в интерфейсе)
     */
    const supports_filters = ['cohort'];

    /**
     * {@inheritDoc}
     * @see \local_pprocessing\processor\handler\base::execute()
     */
    protected function execution_process(container $container)
    {
        global $DB;

        $conditions = '1=1';
        $parameters = [];

        $cohortfilter = $container->export('cohortfilter');

        if ( ! empty($cohortfilter->conditions) )
        {
            $conditions = $cohortfilter->conditions;
        }
        if ( ! empty($cohortfilter->parameters) )
        {
            $parameters = $cohortfilter->parameters;
        }

        $sql = "SELECT coh.*
                  FROM {cohort} coh
                 WHERE ". $conditions . "
              GROUP BY coh.id";
        $this->debugging('get cohorts query', ['sql' => $sql, 'parameters' => $parameters]);
        $cohorts = $DB->get_records_sql($sql, $parameters);

        // кладем пользователя в пулл
        $container->delete('cohorts');
        $container->write('cohorts', $cohorts, false);
    }
}

