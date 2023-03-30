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
 * Менеджер портфолио. Подсистема работы с шаблонами.
 * 
 * @package    modlib
 * @subpackage achievements
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

class dof_modlib_achievements_achievements extends dof_modlib_achievements_basemanager
{
    /**
     * получить активные шаблоны типа прохождение курса
     */
    public function get_coursecompletion_achievements()
    {
        return $this->dof->storage('achievements')
            ->get_records(['type' => 'coursecompletion', 'status' => array_keys($this->dof->workflow('achievements')->get_meta_list('active'))]);
    }
    
    /**
     * получить шаблоны с автоматической фиксацией
     * 
     * @return stdClass[]
     */
    public function get_autocoursecompletion_achievements()
    {
        $processedachievements = [];
        
        $achievements = $this->get_coursecompletion_achievements();
        foreach ( $achievements as $achievement )
        {
            $data = unserialize($achievement->data);
            if ( ! empty($data['coursecompletion_data']['auto_add_achievement']) )
            {
                $processedachievements[$achievement->id] = $achievement;
            }
        }
            
        return $processedachievements;
    }
    
    /**
     * Получить доступные для пользователя шаблоны достижений
     *
     * @param int $achievementcat - Раздел шаблонов достижений
     * @param int|stdClass $person - Текущая персона, для поторой формируется список
     * 
     * @return array - Массив шаблонов достижений
     */
    public function get_available_by_person($achievementcat = 0, $person = null)
    {
        // Получение персоны
        if ( ! is_object($person) )
        {
            $person = $this->dof->storage('persons')->get_bu($person, true);
        }
        
        // Получение списка реальных статусов разделов достижений
        $statuses = $this->dof->workflow('achievementcats')->get_meta_list('real');
        $statuses = array_keys((array)$statuses);
        // Получение списка разделов
        $achievementcats = $this->dof->storage('achievementcats')->get_categories(
            (int)$achievementcat, 
            ['statuses' => $statuses]
        );
        
        // Получение списка реальных статусов шаблонов достижений
        $statuses = $this->dof->workflow('achievements')->get_meta_list('real');
        $statuses = array_keys((array)$statuses);
        // Получение всех шаблонов из указанных разделов
        $achievements = $this->dof->storage('achievements')->get_records(
            [
                'catid' => array_keys($achievementcats),
                'status' => $statuses
            ]
        );
        
        // Фильтрация списка с учетом прав доступа
        foreach ( $achievements as $achievementid => &$achievement )
        {
            $departmentid = $achievementcats[$achievement->catid]->departmentid;
            // Проверка доступа на смену статуса
            $access = $this->dof->storage('achievements')->is_access(
                'use', 
                $achievement->id, 
                $person->mdluser, 
                $departmentid
            );
            if ( ! $access )
            {// Доступ разрешен
                unset($achievementid);
            }
        }
        return $achievements;
    }
}