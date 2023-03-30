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
 * Менеджер портфолио. Подсистема работы с отчетными данными.
 * 
 * @package    modlib
 * @subpackage achievements
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

class dof_modlib_achievements_reports extends dof_modlib_achievements_basemanager
{
    
    /**
     * Получение данных для отчета по подразделениям
     *
     * @param int $departmentid - идентификатор подразделения
     *
     * @return stdClass - объект с данными
     */
    public function get_idp_summary_data($departmentid, $addsubdepartments=false)
    {
        $result = new stdClass();
        $result->rows = [];
        
        if( ( ! $addsubdepartments && $departmentid > 0 ) || ($addsubdepartments && $departmentid >= 0 ) )
        {
            $currentdepartment = [];
            if( $departmentid > 0 )
            {
                $currentdepartment[$departmentid] = $this->dof->storage('departments')->get($departmentid);
            }
            
            $depstatuses = $this->dof->workflow('departments')->get_meta_list('active');
            
            $subdepartments = [];
            if( $addsubdepartments )
            {
                $subdepartments = $this->dof->storage('departments')->get_departments(
                    $departmentid,
                    ['statuses' => array_keys($depstatuses)]
                );
            }
            $departments = array_merge($currentdepartment, $subdepartments);
            
            foreach($departments as $department)
            {
                $resultrow = new stdClass();
                $resultrow->department = $department;
                
                $resultrow->persons = new stdClass();
                $persons = $this->dof->storage('persons')->get_persons_related_to_department($department->id);
                
                if( ! empty($persons) )
                {
                    $resultrow->persons->count = count($persons);
                } else
                {
                    $resultrow->persons->count = 0;
                }
                
                // статистика по текущему подразделению
                $resultrow->stat = $this->get_person_achievements_stat(array_keys($persons));
                
                // так как одна персона может быть в нескольких дочерних подразделениях
                // а учитывать ее необходимо лишь один раз
                // наиболее подходящий из доступных способ собрать данные
                // получение персонализированной статистики по текущему и дочерним подразделениям
                $personalized = $this->get_idp_personalized_data($department->id, true);
                // количество неповторяющихся персон в текущем и дочерних подразделениях
                $resultrow->sumpersons = new stdClass();
                $resultrow->sumpersons->count = count($personalized->rows);
                // суммарная статистика по текущему и дочерним подразделениям
                $resultrow->sumstat = new stdClass();
                $resultrow->sumstat->wait_approve_goals = 0;
                $resultrow->sumstat->approved_goals = 0;
                $resultrow->sumstat->expired_deadlines = 0;
                $resultrow->sumstat->expired_deadlines_users = 0;
                $resultrow->sumstat->wait_approve_achievements = 0;
                $resultrow->sumstat->achievements = 0;
                $resultrow->sumstat->achieved_goals = 0;
                $resultrow->sumstat->achieved_straight = 0;
                foreach($personalized->rows as $personstats)
                {
                    $resultrow->sumstat->wait_approve_goals += $personstats->stat->wait_approve_goals;
                    $resultrow->sumstat->approved_goals += $personstats->stat->approved_goals;
                    $resultrow->sumstat->expired_deadlines += $personstats->stat->expired_deadlines;
                    if( $personstats->stat->expired_deadlines > 0 )
                    {
                        $resultrow->sumstat->expired_deadlines_users++;
                    }
                    $resultrow->sumstat->wait_approve_achievements += $personstats->stat->wait_approve_achievements;
                    $resultrow->sumstat->achievements += $personstats->stat->achievements;
                    $resultrow->sumstat->achieved_goals += $personstats->stat->achieved_goals;
                    $resultrow->sumstat->achieved_straight += $personstats->stat->achieved_straight;
                }
                
                $result->rows[$department->id] = $resultrow;
                
            }
        }
        
        return $result;
    }
    
    /**
     * Получение данных для персонализированного отчета
     *
     * @param int $departmentid - идентификатор подразделения
     *
     * @return stdClass - объект с данными
     */
    public function get_idp_personalized_data($departmentid, $includesubdepartments=false)
    {
        $result = new stdClass();
        $result->rows = [];
        if( ( ! $includesubdepartments && $departmentid > 0 ) || ($includesubdepartments && $departmentid >= 0 ) )
        {
            $currentdepartment = [];
            if( $departmentid > 0 )
            {
                $currentdepartment[] = $departmentid;
            }
            $depstatuses = $this->dof->workflow('departments')->get_meta_list('active');
            $subdepartments = [];
            if( $includesubdepartments )
            {
                $subdepartments = $this->dof->storage('departments')->get_departments(
                    $departmentid,
                    ['statuses' => array_keys($depstatuses)]
                );
            }
            $departmentids = array_merge($currentdepartment, array_keys($subdepartments));
            $personsdata = $this->dof->storage('persons')->get_persons_related_to_department($departmentids, ['detailed' => true]);
            if( ! empty($personsdata) )
            {
                $stat = $this->get_person_achievements_stat(array_keys($personsdata));
                foreach($personsdata as $persondata)
                {
                    $person = $persondata['data'];
                    $resultrow = new stdClass();
                    $resultrow->person = $person;
                    $resultrow->person->fullname = $this->dof->storage('persons')->get_fullname($person);
                    
                    if( ! empty($stat->personalized[$person->id]) )
                    {
                        $resultrow->stat = $stat->personalized[$person->id];
                    } else
                    {
                        $resultrow->stat = new stdClass();
                        $resultrow->stat->wait_approve_goals = 0;
                        $resultrow->stat->approved_goals = 0;
                        $resultrow->stat->expired_deadlines = 0;
                        $resultrow->stat->wait_approve_achievements = 0;
                        $resultrow->stat->achievements = 0;
                        $resultrow->stat->achieved_goals = 0;
                        $resultrow->stat->achieved_straight = 0;
                    }
                    
                    $persondepartmentids = [];
                    foreach($persondata['programmsbcs'] as $psbc)
                    {
                        if( ! in_array($psbc->departmentid, $persondepartmentids) )
                        {
                            $persondepartmentids[] = $psbc->departmentid;
                        }
                    }
                    foreach($persondata['appointments'] as $appointment)
                    {
                        if( ! in_array($appointment->departmentid, $persondepartmentids) )
                        {
                            $persondepartmentids[] = $appointment->departmentid;
                        }
                    }
                    if( ! empty($persondepartmentids) )
                    {
                        $persondepartments = [];
                        $persondepartmentscode = [];
                        $deps = $this->dof->storage('departments')->get_records(['id'=>$persondepartmentids]);
                        foreach($deps as $dep)
                        {
                            $persondepartments[] = $dep->name;
                            $persondepartmentscode[] = $dep->code;
                        }
                        $resultrow->person->departments = implode(', ', $persondepartments);
                        $resultrow->person->departmentscode = implode(', ', $persondepartmentscode);
                    } else
                    {
                        $resultrow->person->departments = '';
                        $resultrow->person->departmentscode = '';
                    }
                    
                    $result->rows[$person->id] = $resultrow;
                }
            }
        }
        
        return $result;
    }
    
    
    /**
     * Формирует объект со статистическими данными по портфолио
     *
     * @param array $personids - идентификаторы персон, по которым необходимо собрать данные
     * @return stdClass - объект статистики
     */
    public function get_person_achievements_stat($personids=[])
    {
        $stat = new stdClass();
        // количество подтвержденных достижений
        $stat->achievements = 0;
        // количество подтвержденных достижений, которые были изначально объявлены как цели
        $stat->achieved_goals = 0;
        // количество подтвержденных достижений, добавленных напрямую
        $stat->achieved_straight = 0;
        // количество одобренных целей
        $stat->approved_goals = 0;
        // количество целей, ожидающих одобрения
        $stat->wait_approve_goals = 0;
        // количество достижений, ожидающих подтверждения
        $stat->wait_approve_achievements = 0;
        // количество просроченных дедлайнов
        $stat->expired_deadlines = 0;
        // количество пользователей с просроченными дедлайнами
        $stat->expired_deadlines_users = 0;
        // массив для персонализированного сбора статистики
        $stat->personalized = [];
        
        if( empty($personids) )
        {
            return $stat;
        }
        
        // получение достижений (целей)
        $achievementins = $this->dof->storage('achievementins')->get_achievementins(0, 0, [
            'personids' => $personids
        ]);
        if( ! empty($achievementins) )
        {
            $curtime = time();
            // массив для сбора пользователей, просрочивших дедлайн
            $deadlinedusers=[];
            foreach($achievementins as $achievementin)
            {
                if( ! array_key_exists($achievementin->userid, $stat->personalized))
                {// по этому пользователю еще ничего не записывали в статистику - инициализируем объект
                    $stat->personalized[$achievementin->userid] = new stdClass();
                    // количество подтвержденных достижений
                    $stat->personalized[$achievementin->userid]->achievements = 0;
                    // количество подтвержденных достижений, которые были изначально объявлены как цели
                    $stat->personalized[$achievementin->userid]->achieved_goals = 0;
                    // количество подтвержденных достижений, добавленных напрямую
                    $stat->personalized[$achievementin->userid]->achieved_straight = 0;
                    // количество одобренных целей
                    $stat->personalized[$achievementin->userid]->approved_goals = 0;
                    // количество целей, ожидающих одобрения
                    $stat->personalized[$achievementin->userid]->wait_approve_goals = 0;
                    // количество достижений, ожидающих подтверждения
                    $stat->personalized[$achievementin->userid]->wait_approve_achievements = 0;
                    // количество просроченных дедлайнов
                    $stat->personalized[$achievementin->userid]->expired_deadlines = 0;
                }
                
                if( $achievementin->status == 'available' )
                {// подтвержденное достижение
                    
                    $stat->achievements++;
                    $stat->personalized[$achievementin->userid]->achievements++;
                    
                    // а было ли достижение целью
                    $wasgoal = $this->dof->storage('statushistory')->has_status(
                        'storage',
                        'achievementins',
                        $achievementin->id,
                        ['wait_approval', 'wait_completion', 'fail_approve']
                        );
                    if( ! empty($wasgoal) )
                    {// цель достигнута и подтверждена
                        $stat->achieved_goals++;
                        $stat->personalized[$achievementin->userid]->achieved_goals++;
                    } else
                    {// достижение добавлено напрямую (без цели) и подтверждено
                        $stat->achieved_straight++;
                        $stat->personalized[$achievementin->userid]->achieved_straight++;
                    }
                }
                
                if( $achievementin->status == 'wait_completion' )
                {// одобренная цель (или цель не требующая одобрения)
                    $stat->approved_goals++;
                    $stat->personalized[$achievementin->userid]->approved_goals++;
                }
                
                if( $achievementin->status == 'wait_approval' )
                {// цель, ожидающая одобрения
                    $stat->wait_approve_goals++;
                    $stat->personalized[$achievementin->userid]->wait_approve_goals++;
                }
                
                if( $achievementin->status == 'notavailable' )
                {// Достижение, требующее подтверждения
                    $stat->wait_approve_achievements++;
                    $stat->personalized[$achievementin->userid]->wait_approve_achievements++;
                }
                
                if( in_array(
                    $achievementin->status,
                    ['wait_approval', 'wait_completion', 'fail_approve']
                    )
                    && ! is_null($achievementin->goaldeadline)
                    && $achievementin->goaldeadline < $curtime )
                {// у цели просрочен дедлайн
                    $stat->expired_deadlines++;
                    $stat->personalized[$achievementin->userid]->expired_deadlines++;
                    
                    if( ! in_array($achievementin->userid, $deadlinedusers) )
                    {
                        $deadlinedusers[] = $achievementin->userid;
                        $stat->expired_deadlines_users++;
                    }
                }
                
            }
        }
        
        return $stat;
    }
}
