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

require_once($CFG->libdir . '/accesslib.php');
require_once($CFG->dirroot . '/local/pprocessing/locallib.php');
defined('MOODLE_INTERNAL') || die();

/**
 * Класс обработчика назначения роли
 *
 * @package    local_pprocessing
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class role_assign extends base
{
    /**
     * {@inheritDoc}
     * @see \local_pprocessing\processor\handler\base::execute()
     */
    protected function execution_process(container $container)
    {
        // получим входные параметры
        $roleassignment = [];
        $roleassignment['roleid'] =    $this->get_required_parameter('roleid');
        $roleassignment['userid'] =    $this->get_required_parameter('userid');
        $roleassignment['contextid'] = $this->get_required_parameter('contextid');
        $roleassignment['component'] = $this->get_optional_parameter('component', '');
        $roleassignment['itemid'] =    $this->get_optional_parameter('itemid', 0);
        // Назначим роль
        role_assign(
            $roleassignment['roleid'],
            $roleassignment['userid'],
            $roleassignment['contextid'],
            $roleassignment['component'],
            $roleassignment['itemid']
        );
        // уникальный код сценария
        $scenariocode = $container->read('scenario.code');
        if ( empty(local_pprocessing_get_role_assignment(
                    $roleassignment['roleid'], 
                    $roleassignment['userid'], 
                    $roleassignment['contextid']
                  )) )
        {// Если назначения нет отправим в лог ошибку
            // запись в лог
            logger::write_log(
                'processor',
                $this->get_type()."__".$this->get_code(),
                'error',
                [
                    'scenariocode' => $scenariocode,
                    'roleid' => $roleassignment['roleid'],
                    'userid' => $roleassignment['userid'],
                    'contextid' => $roleassignment['contextid']
                ]
            );
        } else {
            // Все отлично так и запишем)
            // запись в лог
            logger::write_log(
                'processor',
                $this->get_type()."__".$this->get_code(),
                'success',
                [   
                    'scenariocode' => $scenariocode,
                    'roleid' => $roleassignment['roleid'],
                    'userid' => $roleassignment['userid'],
                    'contextid' => $roleassignment['contextid']
                ]
            );
        }  
    }
}

