<?php
////////////////////////////////////////////////////////////////////////////
//                                                                        //
// NOTICE OF COPYRIGHT                                                    //
//                                                                        //
// Dean`s Office for Moodle                                               //
// Электронный деканат                                                    //
// <http://sourceforge.net/projects/freedeansoffice/>                     //
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
 * Менеджер учебного процесса. Подсистема работы с причинами отсутствия.
 * 
 * @package    modlib
 * @subpackage journal
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

class dof_modlib_journal_schabsenteeism extends dof_modlib_journal_basemanager
{
    /**
     * Получение списка причин отсутствия
     * 
     * @param int $personid - ID персоны
     * @param int $departmentid - ID подразделения
     * 
     * @return array - Массив реальных причин с учетом прав доступа
     */
    public function get_list($personid, $departmentid = 0)
    {
        // Получение связи персоны с пользоваетлем Moodle
        $mdluserid = $this->dof->storage('persons')->get_field((int)$personid, 'mdluser');
        if ( empty($mdluserid) )
        {// Связи нет, проверка прав недоступна
            return [];
        }
        
        // Получение всех причин
        $statuses = array_keys($this->dof->workflow('schabsenteeism')->get_meta_list('real'));
        $schabsenteeisms = (array)$this->dof->storage('schabsenteeism')->
            get_records(['status' => $statuses]);
        
        // Фильтрация списка с учетом прав доступа
        $permission = [
            'plugintype'=>'storage',
            'plugincode'=>'schabsenteeism',
            'code' => 'viewdesk',
            'departmentid' => $departmentid,
            'userid' => $mdluserid
        ];
        $schabsenteeisms = $this->dof->storage('acl')->
            get_acl_filtered_list($schabsenteeisms, $permission);
        
        return $schabsenteeisms;
    }
    
    /**
     * Проверка возможности редактирования указанной причины
     *
     * @param int|stdClass $schabsenteeismid - ID причины
     *
     * @return bool - Результат проверки
     */
    public function can_edit($schabsenteeism)
    {
        // Получение объекта
        if ( ! is_object($schabsenteeism) )
        {
            $schabsenteeism = $this->dof->storage('schabsenteeism')->get((int)$schabsenteeism);
            if ( empty($schabsenteeism) )
            {// Объект не найден
                return null;
            }
        }
        
        // Редактирование удаленных записей запрещено
        $realstatuses = $this->dof->workflow('schabsenteeism')->get_meta_list('real');
        if ( ! isset($realstatuses[$schabsenteeism->status]) )
        {
            return false;
        }
        
        if ( ! $this->dof->storage('schabsenteeism')->is_access('edit', $schabsenteeism->id) )
        {// Доступ запрещен
            return false;
        }
        return true;
    }
    
    /**
     * Проверка возможности удаления указанной причины
     *
     * @param int|stdClass $schabsenteeism - ID причины
     *
     * @return bool - Результат проверки
     */
    public function can_delete($schabsenteeism)
    {
        // Получение объекта
        if ( ! is_object($schabsenteeism) )
        {
            $schabsenteeism = $this->dof->storage('schabsenteeism')->get((int)$schabsenteeism);
            if ( empty($schabsenteeism) )
            {// Объект не найден
                return null;
            }
        }
        
        // Редактирование удаленных записей запрещено
        $realstatuses = $this->dof->workflow('schabsenteeism')->get_meta_list('real');
        if ( ! isset($realstatuses[$schabsenteeism->status]) )
        {
            return false;
        }
        
        if ( ! $this->dof->workflow('schabsenteeism')->is_access('changestatus:to:deleted', $schabsenteeism->id) )
        {// Доступ запрещен
            return false;
        }
        return true;
    }
    
    /**
     * Проверка возможности удаления указанной причины
     *
     * @param int $schabsenteeismid - ID причины
     *
     * @return bool - Результат удаления
     */
    public function delete($schabsenteeism)
    {
        // Получение объекта
        if ( ! is_object($schabsenteeism) )
        {
            $schabsenteeism = $this->dof->storage('schabsenteeism')->get((int)$schabsenteeism);
            if ( empty($schabsenteeism) )
            {// Объект не найден
                return null;
            }
        }
    
        // Проверка прав доступа
        if ( ! $this->can_delete($schabsenteeism) )
        {
            return false;
        }
        
        // Смена статуса
        $this->dof->workflow('schabsenteeism')->change($schabsenteeism->id, 'deleted');
    }
}