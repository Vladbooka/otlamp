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
use local_opentechnology\dbconnection;

defined('MOODLE_INTERNAL') || die();

/**
 * Обработчик получения настроек подключения из плагина аутентификации Внешняя база данных
 *
 * @package     local_pprocessing
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class get_db_connection_config extends base
{
    /**
     * {@inheritDoc}
     * @see \local_pprocessing\processor\handler\base::execute()
     */
    protected function execution_process(container $container)
    {
        $code = $this->get_required_parameter('code');
        $dbconnection = new dbconnection($code);
        $settings = $dbconnection->get_config_data();
        return $settings;

//         $connection = [];
//         $connection['type'] = get_config('auth_db', 'type') ?? '';
//         $connection['host'] = get_config('auth_db', 'host') ?? '';
//         $connection['user'] = get_config('auth_db', 'user') ?? '';
//         $connection['pass'] = get_config('auth_db', 'pass') ?? '';
//         $connection['database'] = get_config('auth_db', 'name') ?? '';
//         $adbsettings = [];
//         $adbsettings['connection'] = $connection;
//         $adbsettings['setup_sql'] = get_config('auth_db', 'setupsql') ?? '';

//         $adbsettings['extencoding'] = get_config('auth_db', 'extencoding') ?? '';
//         $adbsettings['table_name'] = get_config('auth_db', 'table') ?? '';

//         $adbsettings['passtype'] = get_config('auth_db', 'passtype') ?? '';
//         $adbsettings['userpass'] = get_config('auth_db', 'fieldpass') ?? '';
//         $adbsettings['username'] = get_config('auth_db', 'fielduser') ?? '';

//         return $adbsettings;
    }
}

