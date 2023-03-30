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
 * Интерфейс управления отчетами. Библиотека форм.
 *
 * @package    im
 * @subpackage reports
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// Подключение базовых функций плагина
require_once('lib.php');

// Подключение библиотеки форм
global $DOF;
$DOF->modlib('widgets')->webform();

/**
 * Форма заказа нового отчета
 */
class dof_im_journal_report_form extends dof_modlib_widgets_form
{
    /**
     * @var dof_control
     */
    protected $dof;
    
    /**
     * GET параметры для ссылки
     *
     * @var array
     */
    public $addvars = [];
    
    /**
     * URL для возврата
     *
     * @var string
     */
    protected $returnurl = null;
    
    /**
     * Тип плагина - владельца отчета
     *
     * @var string
     */
    public $plugintype = null;
    
    /**
     * Код плагина - владельца отчета
     *
     * @var string
     */
    public $plugincode = null;
    
    /**
     * Код отчета
     *
     * @var string
     */
    public $code = null;
    
    /**
     * Получение объекта конструктора формы
     * 
     * @return MoodleQuickForm
     */
    public function get_mform()
    {
        return $this->_form;
    }
    
    /**
     * Обьявление полей формы
     *
     * @see dof_modlib_widgets_form::definition()
     */
    public function definition()
    {    
        // Создание ссылки на HTML_QuickForm
        $mform =& $this->_form;
        
         // Добавление свойств
        $this->dof = $this->_customdata->dof;
        $this->addvars = $this->_customdata->addvars;
        $this->plugintype = $this->_customdata->plugintype;
        $this->plugincode = $this->_customdata->plugincode;
        $this->code = $this->_customdata->code;
        if ( isset($this->_customdata->returnurl) && ! empty($this->_customdata->returnurl) )
        {// Передан url возврата
            $this->returnurl = $this->_customdata->returnurl;
        } else
        {// Установка url возврата на страницу обработчика
            $this->returnurl = $mform->getAttribute('action');
        }
        
        // Определение часовой зоны текущего пользователя
        $usertimezone = $this->dof->storage('persons')->
            get_usertimezone_as_number();
        
        // Скрытые поля
        $mform->addElement('hidden', 'sesskey', sesskey());
        $mform->setType('sesskey', PARAM_ALPHANUM);
        $mform->addElement('hidden', 'departmentid', $this->addvars['departmentid']);
        $mform->setType('departmentid', PARAM_INT);
        $mform->addElement('hidden', 'plugintype', $this->plugintype);
        $mform->setType('plugintype', PARAM_TEXT);
        $mform->addElement('hidden', 'plugincode', $this->plugincode);
        $mform->setType('plugincode', PARAM_TEXT);
        $mform->addElement('hidden', 'code', $this->code);
        $mform->setType('code', PARAM_TEXT);
        
        // Поле для вывода сообщений об ошибках скрытых элементов
        $mform->addElement(
            'static',
            'hidden',
            ''
        );
        
        // Заголовок формы
        $mform->addElement(
            'header',
            'formtitle', 
            $this->dof->get_string('report_form_header_title', 'reports')
        );
        
        $dateday = dof_usergetdate(time(), $usertimezone);
        
        $group = [];
        // Начальная граница интервала отчета
        $opts = [];
        $opts['timezone'] = $usertimezone;
        $opts['startyear'] = dof_userdate(time()-10*365*24*3600, '%Y', $usertimezone);
        $opts['stopyear'] = dof_userdate(time()+10*365*24*3600, '%Y', $usertimezone);
        $opts['optional'] = false;
        $opts['onlytimestamp'] = true;
        $opts['hours'] = 00;
        $opts['minutes'] = 00;
        
        $group[] = $mform->createElement(
            'dof_date_selector',
            'begindate',
            $this->dof->get_string('report_form_begindate_title', 'reports'),
            $opts
        );
        $mform->setType('begindate', PARAM_INT);
        $mform->setDefault('begindate', mktime(12, 0, 0, $dateday['mon'], 1, $dateday['year']));
        
        // Конечная граница интервала отчета
        $opts = [];
        $opts['timezone'] = $usertimezone;
        $opts['startyear'] = dof_userdate(time()-10*365*24*3600, '%Y', $usertimezone);
        $opts['stopyear'] = dof_userdate(time()+10*365*24*3600, '%Y', $usertimezone);
        $opts['optional'] = false;
        $opts['onlytimestamp'] = true;
        $opts['hours'] = 23;
        $opts['minutes'] = 55;
        $group[] = $mform->createElement(
            'dof_date_selector',
            'enddate',
            $this->dof->get_string('report_form_enddate_title', 'reports'),
            $opts
        );
        $mform->setType('enddate', PARAM_INT);
        $mform->setDefault('enddate', mktime(12, 0, 0, $dateday['mon'] + 1, 0, $dateday['year']));
        
        $mform->addGroup(
            $group, 
            'dateinterval', 
            $this->dof->get_string('report_form_dateinderval_title', 'reports'), 
            ' — ', 
            false
        );
        
        // Дата сбора отчета
        $opts = [];
        $opts['timezone'] = $usertimezone;
        $opts['startyear'] = dof_userdate(time(), '%Y', $usertimezone);
        $opts['stopyear'] = dof_userdate(time()+10*365*24*3600, '%Y', $usertimezone);
        $opts['optional'] = false;
        $opts['onlytimestamp'] = true;
        
        $mform->addElement(
            'dof_date_selector',
            'crondate',
            $this->dof->get_string('report_form_crondate_title', 'reports'),
            $opts
        );
        $mform->setType('crondate', PARAM_INT);
        
		// Дополнительные поля заказа отчета
		// @TODO - Избавиться от допусловий и перенести все в классы отчетов
		$reportfullname = $this->plugintype.'_'.$this->plugincode.'_'.$this->code;
		switch ( $reportfullname )
		{
		    // Отчет нагрузки преподавателей
		    case 'im_journal_loadteachers' :
		        $mform->addElement('checkbox', 'forecast', '', $this->dof->get_string('forecast','journal'));
		        $mform->setType('forecast', PARAM_BOOL);
		        $mform->disabledIf('forecast', 'begindate', 'eq', 'new');
		        
		        $params = new stdClass;
		        $params->departmentid = optional_param('departmentid', null, PARAM_INT);
		        $params->plugintype = 'im';
		        $params->plugincode = 'journal';
		        $params->code       = 'loadteachers';
		        $params->status     = 'completed';
		        // получаем список доступных учебных периодов
		        $select = $this->dof->storage('reports')->get_select_listing($params);
		        $rez = $this->dof->storage('reports')->get_records_select($select);
		        // преобразуем список записей в нужный для select-элемента формат
		        $rez = $this->dof_get_select_values($rez, array(0=>$this->dof->get_string('out_correction', 'journal')), 'id', array('name'));
		        // оставим в списке только те объекты, на использование которых есть право
		        $permissions = array(array('plugintype'=>'storage', 'plugincode'=>'reports', 'code'=>'im_journal_loadteachers'));
		        $list = $this->dof_get_acl_filtered_list($rez, $permissions);
		        
		        $mform->addElement('select', 'reportid', $this->dof->get_string('age','cstreams').':', $list);
		        $mform->setType('reportid', PARAM_INT);
		        break;
		    // Отчет по смене подразделения договоров
		    case 'im_sel_contracts_department':
		    // Отчет по смене подразделения договоров
		    case 'im_sel_contracts_status':
		        $mform->removeElement('formtitle');
		        $mform->removeElement('dateinterval');
		        $mform->removeElement('crondate');
		        return;
		        break;
		    default:
		        // Подключение генератора отчета
		        $reportobj = $this->dof->storage('reports')->report(
		            $this->plugintype,
		            $this->plugincode,
		            $this->code
		        );
		        // Дополнительные поля формы заказа отчета
		        $reportobj->reportcreate_form_definition($this);
		        break;
		}
        
        // Кнопки действий
        $group = [];
        $group[] = $mform->createElement(
            'submit', 
            'submit', 
            $this->dof->get_string('report_form_submit_title', 'reports')
        );
        $mform->addGroup($group, 'buttons', '', '', false);
        
        // применение проверки ко всем элементам
        $mform->applyFilter('__ALL__', 'trim');
    }
    
    public function validation($data, $files) 
    {
        // Базовая валидация
        $errors = parent::validation($data, $files);

        // Подключение генератора отчета
        $reportobj = $this->dof->storage('reports')->report(
            $this->plugintype,
            $this->plugincode,
            $this->code
        );
        
        // Получение аналогичных отчетов
        $reports = $this->dof->storage('reports')->get_records(
            [
                'departmentid' => $this->addvars['departmentid'],
                'plugintype' => $this->plugintype,
                'plugincode' => $this->plugincode,
                'code' => $this->code,
                'status' => 'requested',
                'crondate' => $data['crondate'],
                'begindate' => $data['begindate'],
                'enddate' => $data['enddate']
            ]
        );
        
        $addvars = [
            'plugintype' => $this->plugintype,
            'plugincode' => $this->plugincode,
            'code' => $this->code
        ];
        // Дополнительные проверки отчета
        // @TODO - Избавиться от допусловий и перенести все в классы отчетов
        $reportfullname = $this->plugintype.'_'.$this->plugincode.'_'.$this->code;
        switch ( $reportfullname )
        {
            case 'im_journal_loadteachers': 
                if ( ! empty($data['forecast']) )
                {
                    $begintime = dof_usergetdate($data['begindate']);
                    $endtime = dof_usergetdate($data['enddate']);
                    if ( $begintime['mday'] != 1 OR
                         $begintime['mon'] != $endtime['mon'] OR 
                         $begintime['year'] != $endtime['year'])
                    {
                        $errors['forecast'] = $this->dof->get_string('report_form_loadteachers_forecast', 'reports');
                    }
                }
                break;
            default:
                // Дополнительные поля формы заказа отчета
                $reportobj->reportcreate_form_validation($this, $data, $files, $errors);
                break;
            
        }       
        if ( ! empty($reports) )
        {// Аналогичный отчет уже находится на сборе
            $errors['begindate'] = $this->dof->get_string('report_form_already_exists', 'reports');
        }
        
        return $errors;
    }
    
    /**
     * Обработчик формы
     */
    public function process()
    {
        if ( $this->is_submitted() && confirm_sesskey() &&
             $this->is_validated() && $formdata = $this->get_data() )
        {// Форма подтверждена и данные получены
            
            // Подключение генератора отчета
            $reportobj = $this->dof->storage('reports')->report(
                $this->plugintype,
                $this->plugincode,
                $this->code
            );
            
            // Формирование данных отчета
            $reportdata = new stdClass();
            $reportdata->data = new stdClass();
            $reportdata->begindate    = $formdata->begindate;
            $reportdata->enddate      = $formdata->enddate;
            $reportdata->crondate     = $formdata->crondate;
            $reportdata->personid     = $this->dof->storage('persons')->get_bu(null, true)->id;
            $reportdata->departmentid = $formdata->departmentid;
            $reportdata->objectid     = $formdata->departmentid;
            
            // Дополнительная сборка данных отчета
            // @TODO - Избавиться от допусловий и перенести все в классы отчетов
            $reportfullname = $this->plugintype.'_'.$this->plugincode.'_'.$this->code;
            switch ( $reportfullname )
            {
                case 'im_journal_loadteachers':
                    $forecast = false;
                    if ( isset($formdata->forecast) AND $formdata->forecast )
                    {
                        $forecast = true;
                    }
                    $reportdata->data->forecast = $forecast;
                    $reportdata->data->reportid = $formdata->reportid;
                    break;
                default:
                    // Дополнительные поля формы заказа отчета
                    $reportobj->reportcreate_form_process($this, $formdata, $reportdata);
                    break;
            }
            
            $result = (bool)$reportobj->save($reportdata);
    
            redirect(
                dof_build_url(
                    $this->returnurl, 
                    ['reportcreate_complete' => $result]
                )
            );
        }
    } 
}
?>