<?php
////////////////////////////////////////////////////////////////////////////
//                                                                        //
// NOTICE OF COPYRIGHT                                                    //
//                                                                        //
// Dean`s Office for Moodle                                               //
// Электронный деканат                                                    //
// <http://deansoffice.ru/>                                               //
//                                                                        //
// This program is free software: you can redistribute it and/or modify   //
// it under the terms of the GNU General Public License as published by   //
// the Free Software Foundation, either version 3 of the Licensen.        //
//                                                                        //
// This program is distributed in the hope that it will be useful,        //
// but WITHOUT ANY WARRANTY; without even the implied warranty of         //
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the          //
// GNU General Public License for more details.                           //
//                                                                        //
// You should have received a copy of the GNU General Public License      //
// along with this program.  If not, see <http://www.gnu.org/licenses/>.  //
//                                                                        //
////////////////////////////////////////////////////////////////////////////

/**
 * Задача по обслуживанию Деканата. Исполнение задач по обмену данными
 *
 * @package    block_dof
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace block_dof\task;

use core\task\scheduled_task;

class execute_transmit extends scheduled_task
{
    /**
     * Получить локализованное имя задачи
     *
     * @return string
     */
    public function get_name()
    {
        return get_string('execute_transmit', 'block_dof');
    }

    /**
     * Исполнение задачи
     */
    public function execute()
    {
        // Подключение Деканата
        require_once(dirname(realpath(__FILE__)).'/../../locallib.php');
        
        global $DOF;
        
        $result = true;
        
        // Логирование старта задачи
        dof_mtrace(1, 'Load transmit packs');
        
        $packrecords = $DOF->storage('transmitpacks')->get_records(
            ['status' => array_keys($DOF->workflow('transmitpacks')->get_meta_list('active'))],
            '-sortorder DESC');
        
        if (!empty($packrecords))
        {
            foreach($packrecords as $packrecord)
            {
                /**
                 * @var \dof_modlib_transmit_pack $pack
                 */
                $pack = $DOF->modlib('transmit')->get_pack($packrecord);
                dof_mtrace(2, '...transmit pack ['.$pack->get_id() .'] "'.$pack->get_name().'"');
                // запуск процесса синхронизации
                $DOF->modlib('transmit')->transmit_from_pack($pack);
            }
            dof_mtrace(1, "[Done]");
        } else
        {
            dof_mtrace(1, "[Nothing to do]");
        }        
        
        return $result;
    }
}
