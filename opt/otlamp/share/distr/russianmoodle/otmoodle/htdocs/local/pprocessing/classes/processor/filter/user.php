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

namespace local_pprocessing\processor\filter;

use local_pprocessing\container;
use local_pprocessing\constants;
use local_pprocessing\condition_parser;

defined('MOODLE_INTERNAL') || die();

/**
 * Фильтрация пользователей
 *
 * @package    local_pprocessing
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
//@TODO: лучше переименовать в userfields
class user extends base
{
    /**
     * {@inheritDoc}
     * @see \local_pprocessing\processor\filter\base::execute()
     */
    protected function execution_process(container $container)
    {
        global $CFG;
        
        $result = new \stdClass();
        $conditions = [];
        
        // формирование условий по основным полям профиля
        if( isset($this->config['conditions']) && is_array($this->config['conditions']))
        {
            $parser = new condition_parser($this->config['conditions'], $container, $this->result);
            $parser->set_debugging_level($this->debugging_level+1);
            list($sql, $params) = $parser->parse();
            $conditions[] = $sql;
            $result->parameters = $params;
            
            // Гость не должен попадать в выборку, если об этом явно не попросили отдельной опцией
            if( empty($this->config['including_guest']) )
            {
                $conditions[] = 'u.id <> :mf_guestid';
                $result->parameters['mf_guestid'] = $CFG->siteguest;
            }
            
            // по умолчанию выбираем только подтвержденных пользователей, если не указано явно другого
            if( strpos($sql, 'u.confirmed') === false )
            {
                $conditions[] = 'u.confirmed = 1';
            }
            
            // по умолчанию выбираем только неудаленных пользователей, если не указано явно другого
            if( strpos($sql, 'u.deleted') === false )
            {
                $conditions[] = 'u.deleted = 0';
            }
        }
        
        $result->conditions = implode(' AND ',$conditions);
        $container->write('userfilter', $result);
    }
    
}

