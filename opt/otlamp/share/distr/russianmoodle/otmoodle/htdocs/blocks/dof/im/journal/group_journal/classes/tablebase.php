<?php
////////////////////////////////////////////////////////////////////////////
//                                                                        //
// NOTICE OF COPYRIGHT                                                    //
//                                                                        //
// Dean`s Office for Moodle                                               //
// Электронный деканат                                                    //
// <http://sourceforge.net/projects/freedeansoffice/>                     //
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
 * Журнал предмето-класса. Базовый класс таблиц.
 * 
 * @package    im
 * @subpackage journal
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class dof_im_journal_tablebase
{
    /**
     * Cсылка на контроллер деканата для доступа к общим методам
     * 
     * @var dof_control
     */
    protected $dof;
    
    /**
     * Идентификатор учебного процесса
     * 
     * @var integer
     */
    var $csid;

    /** 
     * Конструктор - определяет с каким учебным потоком будет вестись работа
     * 
     * @param dof_control - глобальный объект Деканата $DOF 
     * @param int $csid - ID учебного процесса(предмето-класса)
     */

    function __construct(dof_control $dof, $csid)
    {
        $this->dof  = $dof;
        $this->csid = (int)$csid;
    }

    /** 
     * Получить ID учебного процесса(предмето-класса)
     * 
     * @return integer
     */
    protected function get_cstreamid()
    {
        return $this->csid;
    }

    /** 
     * Получить список доступных статусов планов
     * 
     * @return array  
     */
    protected function get_planstatuses()
    {
        // @TODO - Сформировать связь с workflow
        return ['active', 'fixed', 'checked', 'completed'];
    }

    /** 
     * Получить список статусов с которыми будут извлекаться события из таблицы schevents
     * 
     * @return array|NULL - Массив статусов или NULL, если не требуется фильтрация по статусам
     */
    protected function get_eventstatuses()
    {
        return NULL;
    }

    /** 
     * Получить контрольные точки учебного процесса
     * 
     * Получить массив объектов, содержащие связь между тематическим планом и событием
     * 
     * @param bool - 
     * @return array - Массив контрольных точек учебного процесса
     */
    protected function get_checkpoints($emevent = true)
    {
        if ( ! $this->csid )
        {// Идентификатор учебного процесса не получен
            return FALSE;
        }
        
        // Получить список статусов для фильтрации тематического плана
        $planstatuses  = $this->get_planstatuses();
        // Получить список статусов для фильтрации событий
        $eventstatuses = $this->get_eventstatuses();
        // Получение контрольных точек учебного процесса
        $checkpoints = $this->dof->storage('schevents')->
            get_mass_date($this->csid, $eventstatuses, $planstatuses, $emevent);
        
        // Вернуть контрольные точки
        return $checkpoints;
    }

    /** 
     * Получить все подписки на учебный процесс
     * 
     * @return array|bool - Массив подписок на учебный процесс или false
     */
    protected function get_cpassed($showjunk = false)
    {
        $list = $this->dof->storage('cpassed')->get_records([
            'cstreamid' => $this->csid, 
            'status' => array_keys($this->dof->workflow('cpassed')->get_register_statuses($showjunk))
        ]);
        if ( ! $list  )
        {// Подписки не найдены
            return FALSE;
        }
        
        // Сортировка по имени
        usort($list, array('dof_im_journal_tablebase', 'sortapp_by_sortname2'));
        
        return $list;
    }

    /**
     * Получить все подписки на учебный процесс для указанной подписки на программу
     * 
     * @return array массив записей из таблицы cpassed или false
     */
    protected function get_cpassed_programmsbc($programmsbcid, $showjunk = false)
    {
        $params = [
            'cstreamid' => $this->csid,
            'programmsbcid' => $programmsbcid,
            'status' => array_keys($this->dof->workflow('cpassed')->get_register_statuses($showjunk))
        ];
        $list = $this->dof->storage('cpassed')->get_records($params, 'begindate ASC');
        if ( ! $list )
        {// Подписки на учебный процесс не найдены
            return FALSE;
        }
        return $list;
    }

    /** 
     * Получить всех студентов указанного учебного потока
     * 
     * @return array массив записей из таблицы persons или false
     */
    protected function get_students()
    {
        $studentids  = [];
        
        // Получаем подписки на учебный процесс
        $listcpassed = $this->get_cpassed();
        if ( ! $listcpassed )
        {// Подписок не найдено
            return false;
        }
        // Перебор всех подписок и создание из них строки для запроса
        foreach ( $listcpassed as $cpassed )
        {
            $studentids[] = $cpassed->studentid;
        }
        
        return $this->dof->storage('persons')->get_records(['id' => $studentids], 'lastname');
    }

    /** 
     * Получить имя указанной контрольной точки
     * 
     * @param int $planid - ID записи в таблице plans или false
     * 
     * @return string, если имя есть, или false, если оно не указано
     */
    protected function get_checkpoint_name($planid)
    {
        return '';
    }

    /** 
     * Получить ID преподавателя учебного процесса
     * 
     * @return int - ID преподавателя или false
     */
    protected function get_teacherid()
    {
        $cstream = $this->dof->storage('cstreams')->get($this->csid);
        if ( ! $cstream )
        {// Учебный процесс не найден
            return FALSE;
        }
        
        return $cstream->teacherid;
    }

    /**
     * Функция сравнения двух объектов из таблицы persons по полю sortname
     * 
     * @param object $person1 - запись из таблицы persons
     * @param object $person2 - другая запись из таблицы persons
     * 
     * @return -1, 0, 1 в зависимости от результата сравнения
     */
    public function sortapp_by_sortname2($person1, $person2)
    {
        return strnatcmp($this->dof->storage('persons')->get_field($person1->studentid, 'sortname'),
                         $this->dof->storage('persons')->get_field($person2->studentid, 'sortname'));
    }
}