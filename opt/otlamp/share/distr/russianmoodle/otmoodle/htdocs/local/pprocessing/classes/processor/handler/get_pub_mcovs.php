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
class get_pub_mcovs extends base
{
    /**
     * {@inheritDoc}
     * @see \local_pprocessing\processor\handler\base::execute()
     */
    protected function execution_process(container $container)
    {
        global $DB;

        $entity = $this->get_required_parameter('entity');
        $sqlconds = (array)$this->get_optional_parameter('sqlconds', []);

        $result = [];

        if (class_exists('\\local_mcov\\entity'))
        {
            $entity = new \local_mcov\entity($entity);

            // через универсальный хэндлер разрешено получать только публичные свойства хранимые в mcov
            $sql = $DB->sql_like('prop', ':proppub');
            $params = ['proppub' => 'pub_%'];

            if (array_key_exists('conditions', $sqlconds))
            {
                $sql .= ' AND '.$sqlconds['conditions'];
            }
            if (array_key_exists('parameters', $sqlconds))
            {
                $params = array_merge($params, $sqlconds['parameters']);
            }

            $result = $entity->get_mcovs_select($sql, $params);
        }

        return $result;
    }
}

