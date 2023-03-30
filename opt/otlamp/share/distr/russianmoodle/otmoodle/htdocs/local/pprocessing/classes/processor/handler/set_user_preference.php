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
use local_pprocessing\logger;
defined('MOODLE_INTERNAL') || die();

/**
 * Класс обработчика установки user_preferences
 *
 * @package local
 * @subpackage pprocessing
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class set_user_preference extends base
{
    /**
     * {@inheritDoc}
     * @see \local_pprocessing\processor\handler\base::execute()
     */
    protected function execution_process(container $container)
    {
        // уникальный код сценария
        $scenariocode = $container->read('scenario.code');
        $userid = $container->read('user.id');

        // проверка конфигов
        if ( empty($userid) )
        {
            // данных недостаточно для выполнения операции
            // запись в лог
            logger::write_log(
                'processor',
                $this->get_type()."__".$this->get_code(),
                'error',
                ['userid' => $userid],
                'user not found'
                );
            return false;
        }
        
        $property['value'] = $this->get_required_parameter('value');
        $property['name'] = $this->get_required_parameter('name');
       
        try
        {
            set_user_preference($property['name'], $property['value'], $userid);
            // запись в лог
            logger::write_log(
                'processor',
                $this->get_type()."__".$this->get_code(),
                'success',
                [
                    'scenariocode' => $scenariocode,
                    'userid' => $userid,
                    'name' => $property['name'],
                    'value' => $property['value']
                ]
            );
            return true;
            
        } catch(\Exception $e)
        {
            // запись в лог
            logger::write_log(
                'processor',
                $this->get_type()."__".$this->get_code(),
                'error',
                [
                    'scenariocode' => $scenariocode,
                    'userid' => $userid,
                    'name' => $property['name'],
                    'value' => $property['value'],
                    'error'        => $e->getMessage()
                ]
            );
            return false;
            
        }
    }
}

