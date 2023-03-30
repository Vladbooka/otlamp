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

use local_pprocessing\container;
use local_pprocessing\logger;
use local_opentechnology\dbconnection;

defined('MOODLE_INTERNAL') || die();

/**
 * Условие - есть ли соединение с базой данных
 *
 * @package    local_pprocessing
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class is_connected extends base
{
    /**
     * {@inheritDoc}
     * @see \local_pprocessing\processor\condition\base::execute()
     */
    protected function execution_process(container $container)
    {
        $connection = $this->get_required_parameter('connection');
        if (is_string($connection)) {
            // Передали код подключения
            $dbconnection = new dbconnection($connection);
            $db = $dbconnection->get_connection();
        } elseif (is_array($connection)) {
            // Передали параметры подключения в бд
            $setupsql = $this->get_optional_parameter('setup_sql');
            $db = $this->db_init($connection['type'], $connection['host'], $connection['user'],
                $connection['pass'], $connection['database'], $setupsql);
        }
        logger::write_log(
            'processor',
            $this->get_type()."__".$this->get_code(),
            'debug',
            [
                'connection' => var_export($connection, true)
            ]
        );
        return $db->IsConnected();
    }
}

