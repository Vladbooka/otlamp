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
 * Панель управления приемной комиссии. Приказ по смене статуса.
 *
 * @package    im
 * @subpackage sel
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

global $DOF;

// Подключение библиотек
require_once($DOF->plugin_path('storage', 'orders', '/baseorder.php'));

class dof_im_sel_order_contracts_status extends dof_storage_orders_baseorder
{
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
		return 'contracts_status';
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
		$status = $order->data->target;
		$statusstring = $this->dof->workflow('contracts')->get_name($status);
		
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
		$reportdata->name = $this->dof->get_string('dof_im_sel_report_contracts_status_title', 'sel');
		$reportdata->contractids = $contractids;
		$reportdata->statusname = $statusstring;
		$reportdata->status = $status;
		
		$data = [];
		foreach ( $contractids as $contractid )
		{
		    $data[$contractid] = new stdClass();
		    $data[$contractid]->id = $contractid;
		    $data[$contractid]->num = '';
		    $data[$contractid]->prevstatus = '';
		    $data[$contractid]->currentstatus = '';
		    $data[$contractid]->message = '';
		    $data[$contractid]->rowclass = 'error';
		    
		    $contract = $this->dof->storage('contracts')->get($contractid);
		    if ( ! $contract )
		    {// Договор не найден
		        $data[$contractid]->message = $this->dof->
		            get_string('dof_im_sel_order_contracts_status_error_contract_not_found', 'sel');
		    } else 
		    {// Договор найден
		        $data[$contractid]->num = $contract->num;
		        $data[$contractid]->prevstatus = $this->dof->workflow('contracts')->get_name($contract->status);
		        $data[$contractid]->currentstatus = $data[$contractid]->prevstatus;
		        
		        // Проверка права доступа на смену статуса
		        $access = $this->dof->workflow('contracts')->is_access(
		            'changestatus:to:'.$status, 
		            $contractid, 
		            $moodleuser->mdluser, 
		            $contract->departmentid
		        );
    		    if( ! $access )
    		    {// Нет права менять статус
        		    $data[$contractid]->message = $this->dof->
		                get_string('dof_im_sel_order_contracts_status_error_status_change_access_denied', 'sel');
    		    } else 
    		    {// Перевод в указанный статус
    		        // Доступные статусы
    		        $availablestatuses = $this->dof->workflow('contracts')->
    		            get_available($contractid);
    		        if ( isset($availablestatuses[$status]) )
    		        {// Доступен переход в указанный статус
    		            if ( $this->dof->workflow('contracts')->change($contract->id, $status) )
    		            {// Статус переведен
    		                $data[$contractid]->message = $this->dof->
		                        get_string('dof_im_sel_order_contracts_status_change_success', 'sel');
		                    $data[$contractid]->currentstatus = $statusstring;
		                    $data[$contractid]->rowclass = 'success';
    		            } else
    		            {// Перевод статуса не удался
    		                $data[$contractid]->message = $this->dof->
		                        get_string('dof_im_sel_order_contracts_status_error_status_change', 'sel');
    		            }
    		        } else
    		        {// Переход недоступен
    		            $data[$contractid]->message = $this->dof->
		                    get_string('dof_im_sel_order_contracts_status_error_status_notavailable', 'sel');
    		        }
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
}
?>