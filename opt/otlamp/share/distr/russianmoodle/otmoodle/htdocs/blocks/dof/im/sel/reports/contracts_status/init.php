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
 * Отчет о смене статуса договоров
 */
class dof_im_sel_report_contracts_status extends dof_storage_reports_basereport
{
    // Параметры для работы с шаблоном
    protected $templatertype = 'im';
    protected $templatercode = 'sel';
    protected $templatertemplatename = 'contracts_status';
    
    /**
     * Получить код отчета
     * 
     * @return string
     */
    public function code()
    {
        return 'contracts_status';
    }
    
    /**
     * Получить локализованное имя отчета
     * 
     * @return string
     */
    public function name()
    {
        return $this->dof->
            get_string('dof_im_sel_report_contracts_status_title','sel');
    }
    
    /**
     * Получить тип плагина - владельца
     * 
     * @return string
     */
    public function plugintype()
    {
        return 'im';
    }
    
    /**
     * Получить код плагина - владельца
     * 
     * @return string
     */
    public function plugincode()
    {
        return 'sel';
    }

    /**
     * Добавление CSS для стилизации HTML-формата отчета
     *
     * @return void
     */
    public function templater_html_additional_css()
    {
        $this->dof->modlib('nvg')->
            add_css('im', 'sel', '/reports/contracts_status/styles.css');
    }
    
    /** 
     * Метод записывает в отчет все данные по изменению статуса договоров
     * 
     * @param object $report - отчет, который формируется
     * 
     * @return object $report - сформированный объект отчета 
     */
    protected function generate_data($report)
    {
    	// Название подразделения
    	if ( $report->departmentid )
    	{// Отчет по подразделению
	    	$department = $this->dof->storage('departments')->get($report->departmentid);
	    	$report->data->department_name = $department->name.' ['.$department->code.']';
    	} else
    	{// Общий отчет
    		$report->data->department_name = $this->dof->get_string('dof_im_sel_report_contracts_status_all_departments', 'sel');
    	}
    	
    	$report->data->reportcomplete_title = $this->dof->get_string('dof_im_sel_report_contracts_status_reportcomplete_title', 'sel');
    	$report->data->department_title = $this->dof->get_string('dof_im_sel_report_contracts_status_department_title', 'sel');
    	$report->data->author_title = $this->dof->get_string('dof_im_sel_report_contracts_status_author_title', 'sel');
    	$report->data->author_fullname = $this->dof->storage('persons')->get_fullname($report->personid);
    	$report->data->contractprevstatus_title = $this->dof->get_string('dof_im_sel_report_contracts_status_contractprevstatus_title', 'sel');
    	$report->data->contractid_title = $this->dof->get_string('dof_im_sel_report_contracts_status_contractid_title', 'sel');
    	$report->data->contractnum_title = $this->dof->get_string('dof_im_sel_report_contracts_status_contractnum_title', 'sel');
    	$report->data->contractstatus_title = $this->dof->get_string('dof_im_sel_report_contracts_status_contractstatus_title', 'sel');
    	$report->data->contractmessage_title = $this->dof->get_string('dof_im_sel_report_contracts_status_contractmessage_title', 'sel');
    	$report->data->status_title = $this->dof->get_string('dof_im_sel_report_contracts_status_status_title', 'sel');
        return $report;
    }
    
    /** 
     * Отобразить отчет в формате HTML
     */
    public function show_report_html($addvars = null)
    {
    	if ( ! $this->is_generate($this->load()) )
    	{// Отчет еще не сгенерирован
    	    $this->dof->messages->add(
    	        $this->dof->get_string('dof_im_sel_report_contracts_status_error_not_generated_yet','sel'),
    	        'error'
    	    );
    	} else
    	{// Загрузка шаблона
    	    
    	    // Получение данных отчета
	    	$reportdata = $this->load_file();
	    	
	    	// Базовая проверка полей
	    	if ( ! empty($reportdata->contractids) && ! empty($reportdata->status) && ! empty($reportdata->report) )
	    	{
	    	    // Получение шаблонизатора
	    		$templater = $this->template();
	    		
	    		if ( ! $templater )
	    		{// Шаблонизатор не подключен
	    		    $this->dof->messages->add(
	    		        $this->dof->get_string('dof_im_sel_report_contracts_status_error_templater_not_found','sel'),
	    		        'error'
	    		    );
	    		} else
	    		{
	    		    // Генерация HTML-представления отчета
	    		    $reporthtml = $templater->get_file('html');
	    		    if ( ! $reporthtml )
	    		    {// Представление не сформировано
	    		        $this->dof->messages->add(
	    		            $this->dof->get_string('dof_im_sel_report_contracts_status_error_getting_data','sel'),
	    		            'error'
	    		        );
	    		    } else 
	    		    {
	    		        print($reporthtml);
	    		    }
	    		}
	    	} else
	    	{
	    	    $this->dof->messages->add(
	    	        $this->dof->get_string('dof_im_sel_report_contracts_status_error_empty_data','sel'),
	    	        'error'
	    	    );
	    	}
    	}
    }
}
?>