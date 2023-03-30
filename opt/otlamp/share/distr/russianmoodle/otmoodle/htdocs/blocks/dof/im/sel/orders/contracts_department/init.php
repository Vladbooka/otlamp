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
 * Панель управления приемной комиссии. Приказ по смене подразделения.
 *
 * @package    im
 * @subpackage sel
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

global $DOF;

// Подключение библиотек
require_once($DOF->plugin_path('storage', 'orders', '/baseorder.php'));

class dof_im_sel_order_contracts_department extends dof_storage_orders_baseorder
{
    /**
     * Буфер имен подразделений
     * 
     * @var array
     */
    private $departmentnames = [];
    
    public function plugintype()
    {
        return 'im';
    }

    public function plugincode()
    {
        return 'sel';
    }

    public function code()
    {
        return 'contracts_department';
    }
    
    /**
     * Выполнения действий после подписания приказа
     *
     * @see dof_storage_orders_baseorder::execute_actions()
     */
    protected function execute_actions($order)
    {
        // Получаем данные из приказа
        if ( empty($order->data->contractids) && empty($order->data->target) )
        {// Данных не найдено
            return false;
        }
    
        // ID договоров
        $contractids = (array)explode(';', $order->data->contractids);
        $targetdepartmentid = $order->data->target;
        $additionaloption = $order->data->option;
        
        // Подключение генератора отчета
		$reportobj = $this->dof->storage('reports')->report(
		    $this->plugintype(), 
		    $this->plugincode(), 
		    $this->code()
		);
        if ( ! $reportobj )
        {// Генератор не инициализирован
            $this->log_string(date('d.m.Y H:i:s',time())."\n");
            $this->log_string('Report is not loaded'."\n\n");
            return false;
        }
        $this->log_string(date('d.m.Y H:i:s',time())."\n");
        $this->log_string('Executing order'.'\n\n');
    
        // ID пользователя
		$moodleuser = $this->dof->storage('persons')->get($order->signerid);
		
		$reportdata = new stdClass();
    
        // Базовые данные отчета
        $reportdata->name = $this->dof->get_string('dof_im_sel_report_contracts_deprtment_title', 'sel');
        $reportdata->contractids = $contractids;
        $reportdata->targetdepartmentid = $targetdepartmentid;
        $reportdata->additionaloption = $additionaloption;
        $reportdata->personid = $order->signerid;
        $reportdata->departmentid = $order->departmentid;
        $reportdata->objectid = $order->departmentid;
        
        // Буфер имен подразделений
        $departmentsnamebuffer = [];
        
        $data = [];
        foreach ( $contractids as $contractid )
        {
            $data[$contractid] = new stdClass();
            $data[$contractid]->id = $contractid;
            $data[$contractid]->num = '';
            $data[$contractid]->prevdepartment = '';
            $data[$contractid]->currentdepartment = '';
            $data[$contractid]->message = '';
            $data[$contractid]->additionalactions = '';
            $data[$contractid]->rowclass = 'error';
    
            $contract = $this->dof->storage('contracts')->get($contractid);
            if ( ! $contract )
            {// Договор не найден
                $data[$contractid]->message = $this->dof->
                   get_string('dof_im_sel_order_contracts_department_error_contract_not_found', 'sel');
            } else
            {// Договор найден
                
                $data[$contractid]->num = $contract->num;
                $data[$contractid]->prevdepartment = $this->get_department_name($contract->departmentid);
                
                $statuses = $this->dof->workflow('departments')->
                   get_meta_list('real');
                $statuses = array_keys((array)$statuses);
                if ( ! $this->dof->storage('departments')->get_record(['id' => $targetdepartmentid, 'status' => $statuses]) )
                {// Подразделение не найдено
                    $data[$contractid]->currentdepartment = $data[$contractid]->prevdepartment;
                    $data[$contractid]->message = $this->dof->
                        get_string('dof_im_sel_order_contracts_department_error_department_not_found', 'sel');
                    continue;
                }
                
                $accesscreate = $this->dof->storage('contracts')->
                   is_access('create', null, null, $targetdepartmentid);
                $accessdelete = $this->dof->storage('contracts')->
                   is_access('edit', $contractid, null, $contract->departmentid);
                
                if ( ! $accesscreate || ! $accessdelete )
                {// Права доступа не позволяют перемещание
                    $data[$contractid]->currentdepartment = $data[$contractid]->prevdepartment;
                    $data[$contractid]->message = $this->dof->
                        get_string('dof_im_sel_order_contracts_department_error_access_denied', 'sel');
                    continue;
                }
               
                // Начало транзакции
                $transaction = $this->dof->storage('contracts')->begin_transaction();
               
                // Смена подразделения у договора
                $update = new stdClass();
                $update->id = $contractid;
                $update->departmentid = $targetdepartmentid;
               
                try 
                {
                    // Сохранение договора
                    $this->dof->storage('contracts')->save($update);
                } catch ( dof_exception_dml $e )
                {// Ошибка сохранения
                    $this->dof->storage('contracts')->rollback_transaction($transaction);
                    $data[$contractid]->message = $e->getMessage();
                    continue;
                }
               
                // Исполнение дополнительных действий
                switch ( $additionaloption )
                {
                    // Дополнительно перенести персону
                    case 'person' :
                        
                        $result = $this->move_person($contract->studentid, $targetdepartmentid);
                        $errors = $result['errors'];
                        $messages = $result['messages'];
                        break;
                    // Дополнительно перенести подписку на программу
                    case 'programmsbcs' :
                        $result = $this->move_programmsbcs($contract->id, $targetdepartmentid);
                        $errors = $result['errors'];
                        $messages = $result['messages'];
                        break;
                   // Дополнительно перенести персону и подписки на программу
                   case 'personandprogrammsbcs' :
                       $resultperson = $this->move_person($contract->studentid, $targetdepartmentid);
                       $resultprogrammsbcs = $this->move_programmsbcs($contract->id, $targetdepartmentid);
                       
                       $errors = array_merge($resultperson['errors'], $resultprogrammsbcs['errors']);
                       $messages = array_merge($resultperson['messages'], $resultprogrammsbcs['messages']);
                       break;
                   default :
                       $errors = [];
                       $messages = [];
                       break;
                }
                
                if ( empty($errors) )
                {// Обработка дополнительных действий прошла без ошибок
                    $this->dof->storage('contracts')->commit_transaction($transaction);
                    
                    $data[$contractid]->currentdepartment = $this->get_department_name($targetdepartmentid);
                    $data[$contractid]->message = $this->dof->
                        get_string('dof_im_sel_order_contracts_department_success', 'sel');
                    $data[$contractid]->rowclass = 'success';
                    $data[$contractid]->additionalactions .= implode('<br/>', $messages);
                } else 
                {// Во время исполнения дополнительных действий произошли ошибки
                    $this->dof->storage('contracts')->rollback_transaction($transaction);
                    
                    $data[$contractid]->additionalactions = implode('<br/>', $errors);
                    $data[$contractid]->message = $this->dof->
                        get_string('dof_im_sel_order_contracts_department_error_additionalactions_error', 'sel');
                }
            }
        }
        
        $reportdata->report = $data;
        
        $report = new stdClass();
        $report->data = $reportdata;
        $report->personid = $order->signerid;
        $report->departmentid = $order->departmentid;
        $report->objectid = $order->departmentid;

        // Сохранение отчета
        $reportid = $reportobj->save($report);
        if ( $reportid )
        {// Отчет создан
            $order = new stdClass();
            $order->id = $this->get_id();
            $order->data = new stdClass();
            $order->data->reportid = $reportid;
            $this->save($order);
            
            // Генерация отчета
            $result = $reportobj->generate();
            
            return $result;
        }
		
		return false;
    }
    
    /**
     * Получить имя подразделения
     * 
     * @param int $departmentid - ID подразделения
     * 
     * @return string
     */
    private function get_department_name($departmentid)
    {
        // Нормализация идентификатора подразделения
        $departmentid = (int)$departmentid;
        
        if ( ! isset($this->departmentnames[$departmentid]) )
        {// Подразделение не найдено в буфере
            
            $department = $this->dof->storage('departments')->get($departmentid);
            if ( empty($department) )
            {// Подразделение не найдено
                if ( $departmentid == 0 )
                {// Подразделение не установлено
                    $this->departmentnames[$departmentid] = $this->dof->
                        get_string('dof_im_sel_order_contracts_department_department_not_set', 'sel');
                } else 
                {// Подразделение не найдено
                    $this->departmentnames[$departmentid] = $this->dof->
                        get_string('dof_im_sel_order_contracts_department_department_not_found', 'sel');
                }
            } else 
            {// Подразделение найдено
                 $this->departmentnames[$departmentid] = $department->name.' ['.$department->code.']';
            }
        }
        
        return $this->departmentnames[$departmentid];
    }
    
    /**
     * Подзадача смены подразделения подписок на программы
     *
     * @param int $studentid - ID договора
     * @param int $targetdepartmentid - ID подразделения
     *
     * @return array
     */
    private function move_programmsbcs($contractid, $targetdepartmentid)
    {
        // Нормализация идентификаторов
        $contractid = (int)$contractid;
        $targetdepartmentid = (int)$targetdepartmentid;
        
        $result = ['errors' => [], 'messages' => []];
    
        $statuses = $this->dof->workflow('programmsbcs')->get_meta_list('real');
        $statuses = array_keys((array)$statuses);
        $programmsbcs = $this->dof->storage('programmsbcs')->
            get_records(['contractid' => $contractid, 'status' => $statuses]);
        
        if ( $programmsbcs )
        {// Подписки на программы найдены
            
            foreach ( $programmsbcs as $programmsbc )
            {
                $accesscreate = $this->dof->storage('programmsbcs')->
                    is_access('create', null, null, $targetdepartmentid);
                $accessdelete = $this->dof->storage('programmsbcs')->
                    is_access('edit', $programmsbc->id, null, $programmsbc->departmentid);
            
                if ( ! $accesscreate || ! $accessdelete )
                {// Права доступа не позволяют перемещание
                    $a = new stdClass();
                    $a->name = $this->dof->storage('programms')->get_field($programmsbc->programmid, 'name');
                    $a->id = $programmsbc->id;
                    $result['errors'][] = $this->dof->
                        get_string('dof_im_sel_order_contracts_department_programmsbcs_access_denied', 'sel', $a);
                } else 
                {
                    $update = new stdClass();
                    $update->id = $programmsbc->id;
                    $update->departmentid = $targetdepartmentid;
                    
                    try
                    {
                        // Сохранение персоны
                        $this->dof->storage('programmsbcs')->save($update);
                        $a = new stdClass();
                        $a->name = $this->dof->storage('programms')->get_field($programmsbc->programmid, 'name');
                        $a->id = $programmsbc->id;
                        $result['messages'][] = $this->dof->
                            get_string('dof_im_sel_order_contracts_department_programmsbcs_success', 'sel', $a);
                    } catch ( dof_exception_dml $e )
                    {// Ошибка сохранения
                        $a = new stdClass();
                        $a->name = $this->dof->storage('programms')->get_field($programmsbc->programmid, 'name');
                        $a->id = $programmsbc->id;
                        $a->error = $e->errorcode;
                        $result['errors'][] = $this->dof->
                            get_string('dof_im_sel_order_contracts_department_programmsbcs_error', 'sel', $a);
                    }
                }
            }
        } else 
        {// Подписки на программы не найдены
            $result['messages'][] = $this->dof->
                get_string('dof_im_sel_order_contracts_department_programmsbcs_not_found', 'sel');
        }
        return $result;
    }
    
    /**
     * Подзадача смены подразделения студента
     *
     * @param int $contractid - ID договора
     * @param int $targetdepartmentid - ID подразделения
     *
     * @return array
     */
    private function move_person($studentid, $targetdepartmentid)
    {
        // Нормализация идентификаторов
        $studentid = (int)$studentid;
        $targetdepartmentid = (int)$targetdepartmentid;
    
        $result = ['errors' => [], 'messages' => []];
    
        $statuses = $this->dof->workflow('persons')->get_meta_list('real');
        $statuses = array_keys((array)$statuses);
        $exists = $this->dof->storage('persons')->get_record(['id' => $studentid, 'status' => $statuses]);
    
        if ( $exists )
        {// Персона найдена
    
            $accesscreate = $this->dof->storage('persons')->
                is_access('create', null, null, $targetdepartmentid);
            $accessdelete = $this->dof->storage('persons')->
                is_access('edit', $studentid, null, null);
    
            if ( ! $accesscreate || ! $accessdelete )
            {// Права доступа не позволяют перемещание
                $a = new stdClass();
                $a->fullname = $this->dof->storage('persons')->get_fullname($studentid);
                $a->id = $studentid;
                $result['errors'][] = $this->dof->
                    get_string('dof_im_sel_order_contracts_department_student_access_denied', 'sel', $a);
            } else
            {
                $update = new stdClass();
                $update->id = $studentid;
                $update->departmentid = $targetdepartmentid;
    
                try
                {
                    // Сохранение персоны
                    $this->dof->storage('persons')->save($update);
                    $a = new stdClass();
                    $a->fullname = $this->dof->storage('persons')->get_fullname($studentid);
                    $a->id = $studentid;
                    $result['messages'][] = $this->dof->
                        get_string('dof_im_sel_order_contracts_department_student_success', 'sel', $a);
                } catch ( dof_exception_dml $e )
                {// Ошибка сохранения
                    $a = new stdClass();
                    $a->fullname = $this->dof->storage('persons')->get_fullname($studentid);
                    $a->id = $studentid;
                    $a->error = $e->errorcode;
                    $result['errors'][] = $this->dof->
                        get_string('dof_im_sel_order_contracts_department_student_error', 'sel', $a);
                }
            }
        } else
        {// Персона не найдена
            $a = new stdClass();
            $a->id = $studentid;
            $result['errors'][] = $this->dof->
                get_string('dof_im_sel_order_contracts_department_student_not_found', 'sel', $a);
        }
        
        return $result;
    }
}
?>