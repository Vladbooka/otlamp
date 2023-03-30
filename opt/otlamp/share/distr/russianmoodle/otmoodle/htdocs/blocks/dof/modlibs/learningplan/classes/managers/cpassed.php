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
 * Менеджер учебного процесса. Подсистема работы с cpasseds.
 * 
 * @package    modlib
 * @subpackage learningplan
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class dof_modlib_learningplan_cpassed extends dof_modlib_learningplan_basemanager
{
    /**
     * Проверка, является-ли данная подписка заявкой
     * 
     * @param stdClass $cpassed
     */
    public function is_request($cpassed)
    {
        // Получение подписки
        if ( ! is_object($cpassed) )
        {
            $cpassed = $this->dof->storage('cpassed')->get((int)$cpassed);
        }
        if ( ! empty($cpassed) )
        {// Подписка определена
            if ( $cpassed->status == 'request' )
            {
                return true;
            }
            return false;
        }
        return null;
    }
    
    /**
     * Проверка, является ли текущий статус статусом отмены
     *
     * @param stdClass $cpassed
     */
    public function is_request_confirm_state($status)
    {
        if ( $status == 'plan' || $status == 'active' )
        {
            return true;
        }
        return false;
    }
    
    /**
     * Проверка, является ли текущий статус статусом отмены
     *
     * @param stdClass $cpassed
     */
    public function is_request_reject_state($status)
    {
        if ( $status == 'canceled')
        {
            return true;
        }
        return false;
    }
    
    
    /**
     * Получить доступные статусы перевода для подписки на дисциплину
     * 
     * @param int|stdClass $cpassed - Подписка на дисциплину
     * @param int|stdClass $person - Текущая персона, для поторой формируется список
     * 
     */
    public function status_get_available_by_person($cpassed, $person = null)
    {
        // Получение подписки
        if ( ! is_object($cpassed) )
        {
            $cpassed = $this->dof->storage('cpassed')->get((int)$cpassed);
        }
        // Получение персоны
        if ( ! is_object($person) )
        {
            $person = $this->dof->storage('persons')->get_bu($person, true);
        }
        
        $departmentid = $this->dof->storage('cstreams')->get_field($cpassed->cstreamid, 'departmentid');
        
        // Получение возможного набора статусов
        $statuses = (array)$this->dof->workflow('cpassed')->get_available($cpassed->id);
        // Фильтрация списка с учетом прав доступа
        $available = [];
        foreach ( $statuses as $status => $statuslocalized )
        {
            // Проверка доступа на смену статуса
            $access = $this->dof->workflow('cpassed')->
                is_access('changestatus:to:'.$status, $cpassed->id, $person->mdluser, $departmentid);
            if ( $access )
            {// Доступ разрешен
                $available[$status] = $statuslocalized;
            }  
        }
        if ( $this->status_request_can_manage($cpassed, $person) )
        {
            if ( isset($statuses['plan']) )
            {// Перевод из текущего статуса в Запланирован предусмотрен системой
                $available['plan'] = $statuses['plan'];
            }
            if ( isset($statuses['active']) )
            {// Перевод из текущего статуса в Идет предусмотрен системой
                $available['active'] = $statuses['active'];
            }
            if ( isset($statuses['canceled']) )
            {// Перевод из текущего статуса в Отменена предусмотрен системой
                $available['canceled'] = $statuses['canceled'];
            }
        }
        return $available;
    }
        
    /**
     * Проверка возможности подтвердить заявку
     *
     * @param int|stdClass $cpassed - Подписка на дисциплину
     * @param int|stdClass $person - Текущая персона
     * 
     * @return bool
     */
    public function status_request_can_manage($cpassed, $person = null)
    {
        // Получение подписки
        if ( ! is_object($cpassed) )
        {
            $cpassed = $this->dof->storage('cpassed')->get((int)$cpassed);
        }
        // Получение персоны
        if ( ! is_object($person) )
        {
            $person = $this->dof->storage('persons')->get_bu($person, true);
        }
        
        if ( $cpassed->status != 'request' )
        {// Текущая подписка не является заявкой
            return false;
        }

        $departmentid = $this->dof->storage('cstreams')->get_field($cpassed->cstreamid, 'departmentid');
        
        // Проверка доступа на управление заявками
        return $this->dof->workflow('cpassed')->
            is_access('manage_requests', $cpassed->id, $person->mdluser, $departmentid);
    }
    
    
    public function status_change($cpassed, $targetstatus, $person = null, $message = null)
    {
        // Получение подписки
        if ( ! is_object($cpassed) )
        {
            $cpassed = $this->dof->storage('cpassed')->get((int)$cpassed);
        }
        if ( ! empty($cpassed) )
        {
            // Получение доступных статусов перевода
            $available = $this->status_get_available_by_person($cpassed, $person);
            if ( isset($available[$targetstatus]) )
            {// Перевод доступен
                $result = $this->dof->workflow('cpassed')->change($cpassed->id, $targetstatus);
                if ( $result && $message && $cpassed->studentid )
                {
                    $this->dof->sync('messager')->send_email_to_person($cpassed->studentid, $message);
                }
                return $result;
            }
        }
        
        return false;
    }
    
    /**
     * Получение списка подписок на учебный процесс
     *
     * @param stdClass|array|int $cstreamid - объект/массив/значение учебного процесса
     * @param string|array $metastatus - метастатус или массив статусов
     * @param int $departmentid - ID подразделения
     *
     * @return array - массив подписок на учебный процесс
     */
    public function get_cpassed_list_by_cstreamid($cstreamid = null, $metastatus = 'real', $departmentid = 0)
    {
        // Нормализация данных
        if ( is_object($cstreamid) &&
            property_exists($cstreamid, 'id') &&
            ! empty($cstreamid->id) )
        {// Передан объект
            $cstreamid = $cstreamid->id;
        } elseif ( is_array($cstreamid) &&
            isset($cstreamid['id']) &&
            ! empty($cstreamid['id']) )
        {// Передан массив
            $cstreamid = $cstreamid['id'];
        }
    
        if ( empty($cstreamid) )
        {// Данные о учебном процессе отсутствуют
            return false;
        }
    
        // Массив статусов, по которым необходим поиск
        $statuses = [];
    
        if ( is_array($metastatus) )
        {// Получили массив статусов
            $statuses = $metastatus;
        } elseif ( is_string($metastatus) )
        {
            $statuses = array_keys($this->dof->workflow('cpassed')->get_meta_list($metastatus));
        } else
        {// Неподдерживаемый тип
            return false;
        }
    
        $list = $this->dof->storage('cpassed')->get_records(
            [
                'cstreamid' => $cstreamid,
                'status' => $statuses
            ]);
    
        if ( ! empty($list) )
        {// Подписки найдены
            // Сортировка по имени
            usort($list,
                function ($a, $b)
                {
                    return strcmp($this->dof->storage('persons')->get_field($a->studentid, 'sortname'),
                        $this->dof->storage('persons')->get_field($b->studentid, 'sortname'));
            });
        }
    
        // Возвращаем массив подписок на учебный процесс
        return $list;
    }
    
    /**
     * Получение списка подписок на учебный процесс для итоговой ведомости
     *
     * @param stdClass|array|int $cstreamid - объект/массив/значение учебного процесса
     * @param int $departmentid - ID подразделения
     * @param bool $showjunk - показать мусорные подписки
     *
     * @return array - массив подписок на учебный процесс
     */
    public function get_cpassed_list_for_itog_grades($cstreamid = null, $showjunk = false, $departmentid = 0)
    {
        // Подказывать неактивные подписки
        if ( $showjunk )
        {
            $showjunk = $this->dof->storage('config')->get_config_value('showjunkstudents', 'im', 'journal', $departmentid);
        }
    
        // Получим статусы
        $statuses = array_keys($this->dof->workflow('cpassed')->get_register_statuses($showjunk));
    
        // Возвращаем массив подписок на учебный процесс
        return $this->get_cpassed_list_by_cstreamid($cstreamid, $statuses, $departmentid);
    }
    
    /**
     * Получение действующий подписок на учебный процесс
     * 
     * @param int $cstreamid
     * 
     * @return []stdClass
     */
    public function get_active_cpasseds($cstreamid)
    {
        return $this->dof->storage('cpassed')->get_records(
                [
                    'cstreamid' => $cstreamid,
                    'status' => array_keys($this->dof->workflow('cpassed')->get_meta_list('active'))
                ]);
    }
    
    /**
     * Получение данных о занятия, где слушатель должник и где отработал
     *
     * @param stdClass $cpassed
     * @param []dof_lesson $lessons
     * @param int $fromdate - дата, с которой считаем пришедших на отработку
     * @param int $todate - дата, lj которой считаем пришедших на отработку
     *
     * @return []stdClass
     */
    public function get_cpassed_info(stdClass $cpassed, $lessons = null, $fromdate = null, $todate = null)
    {
        if ( is_null($lessons))
        {
            $lessons = $this->dof->modlib('journal')->get_manager('lessonprocess')->get_lessons($cpassed->cstreamid, true)->get_lessons();
        }
        
        // информация об отработка по подписке на учебный процесс
        $info = new stdClass();
        $info->amountdebts = 0;
        $info->amountdebtsbyskippedlesson = 0;
        $info->amountdebtsbybadgrade = 0;
        $info->amountworked = 0;
        
        foreach ($lessons as $lesson)
        {
            if ( ! $lesson->plan_exists() )
            {
                continue;
            }
            if ( $lesson->get_startdate() < $cpassed->begindate || (! empty($cpassed->enddate && ($lesson->get_startdate() > $cpassed->enddate))) )
            {
                continue;
            }
            $isdebtor = $this->dof->modlib('journal')->get_manager('lessonprocess')->is_debtor($lesson, $cpassed);
            if ( $isdebtor )
            {
                $info->amountdebts++;
                if ( $isdebtor === 1 )
                {
                    $info->amountdebtsbyskippedlesson++;
                } elseif ( $isdebtor === 2 )
                {
                    $info->amountdebtsbybadgrade++;
                }
            }
            $gradedata = $lesson->get_listener_gradedata($cpassed->id);
            foreach ($gradedata->grades as $grade)
            {
                if ( empty($grade->order) )
                {
                    continue;
                }
                if ( $grade->item->workingoff )
                {
                    if ( ! is_null($fromdate) && ! is_null($todate) )
                    {
                        if ( ($fromdate <= $grade->order->exdate) && ($grade->order->exdate <= $todate) )
                        {
                            $info->amountworked++;
                        }
                    } else
                    {
                        $info->amountworked++;
                    }
                }
            }
        }
        
        return $info;
    }
}