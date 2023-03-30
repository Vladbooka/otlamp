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
 * Менеджер учебного процесса. Базовый класс подсистем.
 * 
 * @package    modlib
 * @subpackage learningplan
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

class dof_modlib_learningplan_selfenrol extends dof_modlib_learningplan_basemanager
{
    /**
     * Получить учебные процессы преподавателя,
     * в которых включена самостоятельная запись
     *
     * @param int $teacherid - ID персоны
     *
     * @return array - Массив учебных процессов, сгруппированных по дисциплине
     */
    public function get_teacher_cstreams_by_teacherid($teacherid)
    {
        $teachercstreams = [];
        
        // Определение статусов объектов
        $cstreamsrealstatuses = $this->dof->workflow('cstreams')->
            get_meta_list('real');
        
        $cstreams = (array)$this->dof->storage('cstreams')->get_listing([
            'status' => array_keys($cstreamsrealstatuses),
            'teacherid' => $teacherid
        ]);
            
        // Фильтрация учебных процессов с закрытой самозаписью
        foreach ( $cstreams as $cstreamid => &$cstream )
        {
            // Проверка доступности самозаписи
            if ( ! $this->cstream_is_available($cstream) )
            {// Учебный процесс не поддерживает самостоятельную запись
                unset($cstreams[$cstreamid]);
            } else 
            {
                $teachercstreams[$cstream->programmitemid][$cstream->id] = $cstream;
            }
        }
            
        return $teachercstreams;
    }
    
    /**
     * Получить учебные процессы с фильтрацией по персоне,
     * в которых включена самостоятельная запись
     *
     * Метод для получения общего списка учебных процессов
     * без учета возможности записаться в них
     *
     * @param int $personid - ID персоны
     *
     * @return array - Массив учебных процессов, сгруппированных по подписке на программу
     */
    public function get_programmsbc_cstreams_by_person($personid)
    {
        $programmsbcscstreams = [];
    
        // Получение договоров персоны
        $contracts = (array)$this->dof->storage('contracts')->
        get_contracts_for_person((int)$personid);
    
        // Определение статусов объектов
        $contractactivestatuses = $this->dof->workflow('contracts')->
        get_meta_list('active');
        $programmsbcactivestatuses = $this->dof->workflow('programmsbcs')->
        get_meta_list('active');
    
        foreach ( $contracts as $contract )
        {// Получение подписок на программу по договору
    
            if ( ! isset($contractactivestatuses[$contract->status]) )
            {// Договор в неактивном статусе
                continue;
            }
    
            // Получение подписок на программу
            $programmsbcs = (array)$this->dof->storage('programmsbcs')->
            get_programmsbcs_by_contractid($contract->id);
    
            foreach ( $programmsbcs as $programmsbcid => $programmsbc )
            {// Получение учебных процессов, на которые может записаться данная подписка на программу
    
                if ( ! isset($programmsbcactivestatuses[$programmsbc->status]) )
                {// Подписка в неактивном статусе
                    continue;
                }
    
                // Получение дисциплин программы
                $programmitems = (array)$this->dof->storage('programmitems')->get_pitems_list(
                    $programmsbc->programmid,
                    $programmsbc->agenum
                    );
    
                foreach ( $programmitems as $programmitemid => $programmitem )
                {
                    // Получение учебных процессов дисциплины
                    $cstreams = $this->get_programmitem_cstreams_by_programmitem($programmitemid);
                    foreach ( $cstreams as $cstream )
                    {
                        $programmsbcscstreams[$programmsbcid][$cstream->id] = $cstream;
                    }
                }
            }
        }
    
        return $programmsbcscstreams;
    }
    
    /**
     * Получение списка подписок персоны на дисциплину
     *
     * @param stdClass|int $cstream - Учебный процесс, или ID
     * @param stdClass|int $programmsbc - Подписка на программу, или ID
     *
     * @return stdClass[]|null - Экземпляр пользовательской подписки или null
     * в случае ее отсутствия
     */
    public function get_cpassed_programmitem($cstream, $programmsbc)
    {
        // Получение учебного процесса
        if ( ! is_object($cstream) )
        {// Получение учебного процесса
            $cstream = $this->dof->storage('cstreams')->get((int)$cstream);
        }
        // Получение подписки на программу
        if ( ! is_object($programmsbc) )
        {
            $programmsbc = $this->dof->storage('programmsbcs')->get((int)$programmsbc);
        }
    
        if ( ! $cstream || ! $programmsbc )
        {// Данные не получены
            return null;
        }
    
        // Получение набора учебных процессов дисциплины
        $cstreams = $this->get_programmitem_cstreams_by_cstream($cstream->id);
        if ( empty($cstreams) )
        {// Учебные процессы не найдены
            return null;
        }
    
        // Получение подписок по дисциплине
        $statuses = $this->dof->workflow('cpassed')->get_meta_list('actual');
        return $this->dof->storage('cpassed')->get_cpasseds_by_options(
            [
                'statuses' => array_keys($statuses),
                'cstreamids' => array_keys($cstreams),
                'programmbcids' => $programmsbc->id
            ]
            );
    }
    
    /**
     * Получение набора учебных процессов дисциплины c открытой самозаписью
     *
     * @param int $cstreamid - ID учебного процесса
     *
     * @return array - Массив учебных процессов указанной дисциплины
     */
    public function get_programmitem_cstreams_by_cstream($cstreamid)
    {
        // Определение дисциплины учебного процесса
        $programmitemid = $this->dof->storage('cstreams')->get_field((int)$cstreamid, 'programmitemid');
        if ( empty($programmitemid) )
        {// Дисциплина не найдена
            return [];
        }
    
        // Получение массива учебных процессов
        return $this->get_programmitem_cstreams_by_programmitem($programmitemid);
    }
    
    /**
     * Получение набора учебных процессов дисциплины c открытой самозаписью
     *
     * @param int $programmitemid - ID дисциплины
     *
     * @return array - Массив учебных процессов указанной дисциплины
     */
    public function get_programmitem_cstreams_by_programmitem($programmitemid)
    {
        // Получение запланированных учебных процессов дисциплины
        $cstreams = (array)$this->dof->storage('cstreams')->
        get_programmitem_cstream((int)$programmitemid, 'plan');
    
        // Фильтрация учебных процессов с закрытой самозаписью
        foreach ( $cstreams as $cstreamid => &$cstream )
        {
            // Проверка доступности самозаписи
            if ( ! $this->cstream_is_available($cstream) )
            {// Учебный процесс не поддерживает самостоятельную запись
                unset($cstreams[$cstreamid]);
            }
        }
    
        return $cstreams;
    }
    
    /**
     * Проверка доступности самостоятельной записи в учебный процесс
     *
     * @param stdClass|int $cstream - Учебный процесс, или ID учебного процесса
     * @param bool $ignorelimits - Игнорировать лимиты записи в учебный процесс
     *
     * @return bool
     */
    public function cstream_is_available($cstream, $ignorelimits = true)
    {
        // Получение учебного процесса
        if ( ! is_object($cstream) )
        {// Получение учебного процесса
            $cstream = $this->dof->storage('cstreams')->get((int)$cstream);
        }
    
        if ( $cstream )
        {// Получение данных по учебному процессу
    
            $selfenrol = $this->dof->storage('cstreams')->get_selfenrol($cstream);
            if ( $selfenrol == 2 || $selfenrol == 1 )
            {// Самостоятельная запись в учебный процесс открыта
    
                if ( ! $ignorelimits )
                {// Требуется проверка по лимитам
    
                    // Получение оставшегося количества оставшихся мест в учебном процессе
                    $enrolslotscounts = $this->cstream_count_enrols_left($cstream);
                    if ( $enrolslotscounts === 0 )
                    {// Все места заняты
                        return false;
                    }
                }
    
                return true;
            }
        }
        return false;
    }
    
    /**
     * Проверка доступности самоподписки в учебный процесс через заявку
     *
     * @param stdClass|int $cstream - Учебный процесс, или ID учебного процесса
     *
     * @return bool
     */
    public function cstream_is_request_available($cstream)
    {
        // Получение учебного процесса
        if ( ! is_object($cstream) )
        {// Получение учебного процесса
            $cstream = $this->dof->storage('cstreams')->get((int)$cstream);
        }
    
        if ( $cstream )
        {// Получение данных по учебному процессу
    
            $selfenrol = $this->dof->storage('cstreams')->get_selfenrol($cstream);
            if ( $selfenrol == 2  )
            {// Самостоятельная запись c заявками в учебный процесс открыта
                return true;
            }
        }
        return false;
    }
    
    /**
     * Получение количества мест в учебном процессе
     *
     * @param stdClass|int $cstream - Учебный процесс, или ID учебного процесса
     *
     * @return int - Количество заявок
     */
    public function cstream_count_requests($cstream)
    {
        // Получение учебного процесса
        if ( ! is_object($cstream) )
        {// Получение учебного процесса
            $cstream = $this->dof->storage('cstreams')->get((int)$cstream);
        }
    
        if ( $cstream )
        {// Получение данных по учебному процессу
    
            // Получение текущего количества обучающихся в учебном процессе студентов
            $cpasseds = $this->dof->storage('cpassed')->get_cpasseds_by_options(
                [
                    'statuses' => ['request'],
                    'cstreamids' => (int)$cstream->id
                ]
            );
            return count($cpasseds);
        }
        return 0;
    }
    
    /**
     * Получение количества оставшихся мест в учебном процессе
     *
     * @param stdClass|int $cstream - Учебный процесс, или ID учебного процесса
     *
     * @return int|false - Число оставшихся мест или false, если неограниченно
     */
    public function cstream_count_enrols_left($cstream)
    {
        // Получение учебного процесса
        if ( ! is_object($cstream) )
        {// Получение учебного процесса
            $cstream = $this->dof->storage('cstreams')->get((int)$cstream);
        }
    
        if ( $cstream )
        {// Получение данных по учебному процессу
    
            // Проверка возможности записи по лимиту
            $limit = $this->dof->storage('cstreams')->get_studentslimit($cstream);
            if ( empty($limit) )
            {// Лимиты обучающихся не установлены
                return false;
            }
    
            // Получение текущего количества обучающихся в учебном процессе студентов
            $statuses = $this->dof->workflow('cpassed')->get_meta_list('actual');
            $cpasseds = $this->dof->storage('cpassed')->get_cpasseds_by_options(
                [
                    'statuses' => array_keys($statuses),
                    'cstreamids' => (int)$cstream->id
                ]
                );
            if ( $limit > count($cpasseds) )
            {// Найдены свободные места
                return $limit - count($cpasseds);
            }
        }
    
        return 0;
    }
    
    /**
     * Проверка доступности самостоятельной записи подписки на программу
     *
     * @param stdClass|int $cstream - Учебный процесс, или ID
     * @param stdClass|int $programmsbc - Подписка на программу, или ID
     *
     * @return bool
     */
    public function cstream_sbcenrol_is_available($cstream, $programmsbc)
    {
        // Получение учебного процесса
        if ( ! is_object($cstream) )
        {// Получение учебного процесса
            $cstream = $this->dof->storage('cstreams')->get((int)$cstream);
        }
        // Получение подписки на программу
        if ( ! is_object($programmsbc) )
        {
            $programmsbc = $this->dof->storage('programmsbcs')->get((int)$programmsbc);
        }
    
        if ( ! $cstream || ! $programmsbc )
        {// Данные не получены
            return false;
        }
    
        // Проверка общей доступности самозаписи в учебном процессе
        if ( ! $this->cstream_is_available($cstream, false) )
        {// Учебный процесс закрыт для самостоятеьной записи
            return false;
        }
    
        // Проверка отсутствия записей во всех учебных процессах этой дисциплины
        $programmitemcstreams = $this->get_programmitem_cstreams_by_cstream($cstream->id);
        if ( empty($programmitemcstreams) )
        {// Ошибка получения списка учебных процессов
            return false;
        }
        $statuses = $this->dof->workflow('cpassed')->get_meta_list('actual');
        $cpasseds = $this->dof->storage('cpassed')->get_records(
            [
                'programmsbcid' => $programmsbc->id,
                'cstreamid' => array_keys($programmitemcstreams),
                'status' => array_keys($statuses)
            ]
            );
    
        if ( ! empty($cpasseds) )
        {// Запись на дисциплину уже присутствует, самостоятельная запись запрещена
            return false;
        }
    
        return true;
    }
    
    /**
     * Cамостоятельная запись подписки на программу в учебный процесс
     *
     * @param stdClass|int $cstream - Учебный процесс, или ID
     * @param stdClass|int $programmsbc - Подписка на программу, или ID
     *
     * @return int|bool - ID подписки или false
     */
    public function cstream_sbcenrol_enrol($cstream, $programmsbc)
    {
        // Получение учебного процесса
        if ( ! is_object($cstream) )
        {// Получение учебного процесса
            $cstream = $this->dof->storage('cstreams')->get((int)$cstream);
        }
        // Получение подписки на программу
        if ( ! is_object($programmsbc) )
        {
            $programmsbc = $this->dof->storage('programmsbcs')->get((int)$programmsbc);
        }
    
        if ( ! $cstream || ! $programmsbc )
        {// Данные не получены
            return false;
        }
    
        // Проверка возможности записать подписку в учебный процесс
        if ( ! $this->cstream_sbcenrol_is_available($cstream, $programmsbc) )
        {// Самостоятельная запись запрещена
            return false;
        }
    
        // Определение статуса, в котором будет создана подписка на учебный процесс
        $cpassedstatus = 'plan';
        $selfenrol = $this->dof->storage('cstreams')->get_selfenrol($cstream);
        if ( $selfenrol == 2 )
        {// Статус "Заявка"
            $cpassedstatus = 'request';
        }
    
        return $this->dof->storage('cpassed')->
            sign_student_on_cstream($cstream->id, $programmsbc->id, null, $cpassedstatus);
    }
    
    /**
     * Получение комментария по записи указанной подписки в учебный процесс
     *
     * @param stdClass|int $cstream - Учебный процесс, или ID
     * @param stdClass|int $programmsbc - Подписка на программу, или ID
     *
     * @return string|false
     */
    public function cstream_sbcenrol_note($cstream, $programmsbc)
    {
        // Получение учебного процесса
        if ( ! is_object($cstream) )
        {// Получение учебного процесса
            $cstream = $this->dof->storage('cstreams')->get((int)$cstream);
        }
        // Получение подписки на программу
        if ( ! is_object($programmsbc) )
        {
            $programmsbc = $this->dof->storage('programmsbcs')->get((int)$programmsbc);
        }
    
        if ( ! $cstream || ! $programmsbc )
        {// Данные не получены
            return false;
        }
    
        // Проверка общей доступности самозаписи в учебном процессе
        if ( ! $this->cstream_is_available($cstream, true) )
        {// Учебный процесс закрыт для самостоятеьной записи
            return $this->dof->get_string('selfenrol_comment_cstream_closed_for_selfenrol', 'learningplan', null, 'modlib');
        }
    
        // Поиск отмененных заявок
        $cpasseds = $this->dof->storage('cpassed')->get_records(
            [
                'programmsbcid' => $programmsbc->id,
                'cstreamid' => $cstream->id,
                'status' => 'canceled'
            ]
        );
        if ( $cpasseds )
        {// Поиск среди отмененных подписок, тех, что ранее были заявками
            $hascanceled = $this->dof->storage('statushistory')->get_records(
                [
                    'plugintype' => 'storage',
                    'plugincode' => 'cpassed',
                    'objectid' => array_keys($cpasseds),
                    'prevstatus' => 'request',
                    'status' => 'canceled'
                ]
            );
            if ( ! empty($hascanceled) )
            {// Пользовательская заявка была отменена
                return dof_html_writer::div(
                    $this->dof->get_string(
                        'selfenrol_comment_cstream_request_canceled', 
                        'learningplan', 
                        null, 
                        'modlib'
                    ),
                    'alert alert-danger'
                );
            }
        }
    
        // Проверка наличия пользовательской записи в учебный процесс дисциплины
        $cpasseds = $this->get_cpassed_programmitem($cstream, $programmsbc);
        if ( ! empty($cpasseds) )
        {// Найдены подписки по дисциплине учебного процесса, самозапить запрещена
            // Поиск подписки для текущей учебной программы
            foreach ( $cpasseds as $cpassed )
            {
                if ( $cpassed->cstreamid == $cstream->id )
                {// Найдена подписка на текущий учебный процесс
    
                    // Подписка подтверждена
                    $message = $this->dof->get_string('selfenrol_comment_cpassed_confirmed', 'learningplan', null, 'modlib');
                    if ( $cpassed->status == 'request' )
                    {// Заявка отправлена
                        $message = $this->dof->get_string('selfenrol_comment_cpassed_request', 'learningplan', null, 'modlib');
                    }
                    return dof_html_writer::div(
                        $message,
                        'alert alert-success'
                    );
                }
            }
        }
    
        // Проверка возможности записи
        if ( ! $this->cstream_sbcenrol_is_available($cstream, $programmsbc) )
        {// Учебный процесс закрыт для самостоятеьной записи
            return dof_html_writer::div(
                $this->dof->get_string(
                    'selfenrol_comment_selfenrol_unavailable', 
                    'learningplan', 
                    null, 
                    'modlib'
                ),
                'alert alert-danger'
            );
        }
    
        return $this->dof->get_string('selfenrol_comment_selfenrol_available', 'learningplan', null, 'modlib');
    }
}