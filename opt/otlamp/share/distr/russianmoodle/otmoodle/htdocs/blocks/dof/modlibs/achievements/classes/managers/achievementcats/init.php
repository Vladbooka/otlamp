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
 * Менеджер портфолио. Подсистема работы с разделами.
 * 
 * @package    modlib
 * @subpackage achievements
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

class dof_modlib_achievements_achievementcats extends dof_modlib_achievements_basemanager
{
    /**
     * Получить доступные статусы перевода для раздела достижений
     *
     * @param int|stdClass $achievementcat - Раздел достижений
     * @param int|stdClass $person - Текущая персона, для поторой формируется список
     * 
     * @return array - Массив статусов, в которые текущая персона может перевести раздел
     */
    public function status_get_available_by_person($achievementcat, $person = null)
    {
        // Получение раздела
        if ( ! is_object($achievementcat) )
        {
            $achievementcat = $this->dof->storage('achievementcats')->get((int)$achievementcat);
        }
        // Получение персоны
        if ( ! is_object($person) )
        {
            $person = $this->dof->storage('persons')->get_bu($person, true);
        }
        
        $departmentid = $achievementcat->departmentid;
        
        // Получение возможного набора статусов
        $statuses = (array)$this->dof->workflow('achievementcats')->get_available($achievementcat->id);
        
        // Фильтрация списка с учетом прав доступа
        $available = [];
        foreach ( $statuses as $status => $statuslocalized )
        {
            // Проверка доступа на смену статуса
            $access = $this->dof->workflow('achievementcats')->is_access(
                'changestatus:to:'.$status, 
                $achievementcat->id, 
                $person->mdluser, 
                $departmentid
            );
            if ( $access )
            {// Доступ разрешен
                $available[$status] = $statuslocalized;
            }
        }
        return $available;
    }
    
    /**
     * Сменить статус раздела
     * 
     * @param int/stdClass $achievementcat - Раздел достижений
     * @param int|null $personid - ID текущей персоны
     * 
     * @return bool
     */
    public function status_change($achievementcat, $targetstatus, $person = null)
    {
        // Получение раздела
        if ( ! is_object($achievementcat) )
        {
            $achievementcat = $this->dof->storage('achievementcats')->get((int)$achievementcat);
        }
        if ( $achievementcat )
        {
            // Получение доступных статусов перевода
            $available = $this->status_get_available_by_person($achievementcat, $person);
            if ( isset($available[$targetstatus]) )
            {// Перевод доступен
                $result = $this->dof->workflow('achievementcats')->change($achievementcat->id, $targetstatus);
                return $result;
            }
        }
        
        return false;
    }
}