<?php
////////////////////////////////////////////////////////////////////////////
//                                                                        //
// NOTICE OF COPYRIGHT                                                    //
//                                                                        //
// Dean`s Office for Moodle                                               //
// Электронный деканат                                                    //
// <http://deansoffice.ru/>                                               //
//                                                                        //
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
 * Логирование Деканата
 * 
 * Подсистема логирования создае очереди логов для конкретных процессов системы.
 * Каждая очередь сама выбирает. как и где хранить свои логи и как их отображать пользователю.
 *
 * @package    modlib
 * @subpackage transmit
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class dof_modlib_logs extends dof_modlib_base
{
    /**
     * Возвращает версию установленного плагина
     * 
     * @return int - Версия плагина
     */
    public function version()
    {
		return 2017092500;
    }
    
    /**
     * Создание очереди логов
     *
     * @param string $ptype - Тип плагина
     * @param string $pcode - Код плагина
     * @param string $subcode - Сабкод плагина
     * @param string $objectid - Идентификатор объекта
     * @param string $config - Конфигурация очереди
     * @param string $logtype - Тип очереди логов
     *
     * @return dof_storage_logs_queuetype_base
     */
    public function create_queue($ptype, $pcode, $subcode, $objectid = null, $logtype = null, $config = null)
    {
        // Инициализация очереди логов
        return $this->dof->storage('logs')->create_queue($ptype, $pcode, $subcode, $objectid, $logtype, $config);
    }
    
    /**
     * Завершение очереди логов
     *
     * @return bool
     */
    public function finish_queue($queueid)
    {
        $queue = $this->dof->storage('logs')->get((int)$queueid);
        if ( ! $queue )
        {// Очередь не найдена
            return false;
        }
        $queue->timeend = time();
        
        $this->dof->storage('logs')->save($queue);
        
        // Смена статуса
        $this->dof->workflow('logs')->change($queueid, 'finished');
        
        return true;
    }
}
?>