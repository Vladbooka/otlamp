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
 * Ведомость оценок по подписке персоны. Классы форм.
 * 
 * @package    im
 * @subpackage journal
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// Подключение базовых функций плагина
require_once('../lib.php');

// Подключение библиотеки форм
$DOF->modlib('widgets')->webform();

class dof_im_journal_personsplans_summary extends dof_modlib_widgets_form
{  
    /**
     * @var dof_control
     */
    protected $dof;
    
    /**
     * @var $addvars - GET параметры для ссылки
     */
    protected $addvars = [];
    
    /**
     * Программы
     * 
     * @var array
     */
    protected $programms = [];
    
    /**
     * Учебный периоды
     * 
     * @var array
     */
    protected $ages = [];
    
    /**
     * Программы
     *
     * @var array
     */
    protected $parallels = [];
    
    /**
     * Данные для формирования отчета
     * 
     * @var $data
     */
    protected $data = [];
    
    /**
     * Результат обработки
     *
     * @var stdClass
     */
    protected $result = null;
    
    /** 
     * Возвращает массив периодов
     * 
     * @return array список периодов, массив(id периода=>название)
     */
    private function get_list_ages()
    {
        // получаем список доступных учебных периодов
        $rez = $this->dof->storage('ages')->get_records(
                [
                    'status' => [
                        'plan',
                        'createstreams',
                        'createsbc',
                        'createschedule',
                        'active',
                        'completed'
                    ]
                ]);
        // преобразуем список записей в нужный для select-элемента формат
        $rez = $this->dof_get_select_values($rez, false, 'id', array('name'));
        // оставим в списке только те объекты, на использование которых есть право
        $permissions = array(array('plugintype'=>'storage', 'plugincode'=>'ages', 'code'=>'use'));
        $rez = $this->dof_get_acl_filtered_list($rez, $permissions);
        $this->ages = $rez;
        return $rez;
    }
    
    /** 
     * Возвращает массив программ
     * 
     * @return void
     */
    private function set_programms_and_parallels()
    {
        $processedprogramms = $processedparallels = [];
        
        // получаем список доступных учебных периодов
        $rez = $this->dof->storage('programms')->get_records(['status' => ['available']]);
        foreach ( $rez as $programm )
        {
            if ( $programm->agenums <= 1 )
            {
                $processedparallels[$programm->id][1] = '1';
            } else
            {
                $arr = array_combine(range(1, $programm->agenums), range(1, $programm->agenums));
                foreach ( $arr as &$val )
                {
                    $val = (string)$val;
                }
                $processedparallels[$programm->id] = $arr;
            }
        }
        // преобразуем список записей в нужный для select-элемента формат
        $rez = $this->dof_get_select_values($rez, false, 'id', ['name', 'code', 'id']);
        
        // оставим в списке только те объекты, на использование которых есть право
        $permissions = [['plugintype' => 'storage', 'plugincode' => 'programms', 'code'=> 'use']];
        $rez = $this->dof_get_acl_filtered_list($rez, $permissions);
        
        $this->programms = $rez;
        $this->parallels = $processedparallels;
    }
    
    /**
     * {@inheritDoc}
     * @see dof_modlib_widgets_form::definition()
     */
    public function definition()
    {
        // Создание ссылки на HTML_QuickForm
        $mform =& $this->_form;
        
        // Добавляем свойства
        $this->dof = $this->_customdata->dof;
        $this->addvars = $this->_customdata->addvars;
        
        // Скрытые поля
        $mform->addElement('hidden', 'sesskey', sesskey());
        $mform->setType('sesskey', PARAM_ALPHANUM);
        $mform->addElement('hidden', 'departmentid', $this->addvars['departmentid']);
        $mform->setType('departmentid', PARAM_INT);
        
        // Поле для вывода сообщений об ошибках скрытых элементов
        $mform->addElement(
            'static',
            'hidden',
            ''
        );
        
        // Учебный период
        $mform->addElement('select', 'ageid', $this->dof->get_string('age', 'journal'), $this->get_list_ages());
        $mform->setType('ageid', PARAM_INT);

        // Программа
        // Установка необходимых данных
        $this->set_programms_and_parallels();
        
        $sel = $mform->addElement('dof_hierselect', 'programmid', $this->dof->get_string('form_select_programm', 'journal'));
        $sel->setOptions([$this->programms, $this->parallels]);
        
        // Временной интервал
        $options = [];
        $usertimezone = $this->dof->storage('persons')->get_usertimezone_as_number();
        $currenttime = time();
        $options['timezone'] = $usertimezone;
        $options['calendartype'] = 'two_calendar';
        $timestart = $currenttime;
        $timeend = $currenttime;
        if ( isset($this->addvars['timestart']) && is_int($this->addvars['timestart']) && $this->addvars['timestart'] >= 0 )
        {// Указан начальный интервал времени
            $timestart = $this->addvars['timestart'];
        }
        if ( isset($this->addvars['timeend']) && is_int($this->addvars['timeend']) )
        {// Указан конечный интервал времени
            if ( $timestart > $this->addvars['timeend'] )
            {// Ошибка интервала
                $this->dof->messages->add($this->dof->get_string('pbcgl_form_sourceselect_notice_timeinterval', 'journal'), 'notice');
                $timestart = $currenttime;
                $timeend = $currenttime;
            } else
            {// Конечный интревал передан верно
                $timeend = $this->addvars['timeend'];
            }
        }
        
        $mform->addElement(
            'dof_calendar',
            'sourceselect_timeinterval',
            $this->dof->get_string('pbcgl_form_sourceselect_timeinterval', 'journal'),
            $options
        );
        
        $group = [];
        $group[] = $mform->createElement(
                'submit',
                'submitbutton',
                $this->dof->get_string('rtreport_personsplans_summary_show_personsplans_summary_report', 'journal')
                );
        $group[] = $mform->createElement(
                'submit',
                'xls',
                $this->dof->get_string('rtreport_personsplans_summary_export_submit', 'journal')
                );
        $obj = $mform->addGroup($group, 'submitgroup', '', [' '], false);
        $mform->closeHeaderBefore('submitttt');
        
        // Применение проверки ко всем элементам
        $mform->applyFilter('__ALL__', 'trim');
    }
    
    /**
     * Задаем проверку корректности введенных значений
     */
    public function validation($data, $files)
    {
        // Cоздаем ссылку на HTML_QuickForm
        $mform =& $this->_form;
                
        // Массив ошибок
        $errors = [];

        // Проверки подразделения
        if ( $data['departmentid'] != $this->addvars['departmentid'] )
        {// Смена подразделения во время получения данных
            $errors['hidden'] = $this->dof->get_string('error_pbcgl_form_sourceselect_department_changed', 'journal');
        }
        
        // Проверки учебного периода
        $exists = $this->dof->storage('ages')->is_exists((int)$data['ageid']);
        if ( empty($exists) )
        {// Идентификатор группы указан неверно
            $errors['ageid'] = $this->dof->get_string('error_age_doesnt_exists', 'journal');
        } else 
        {
            $access = $this->dof->storage('ages')->is_access('use', (int)$data['ageid'], NULL, $this->addvars['departmentid']);
            if ( empty($access) )
            {// Нет доступа к учебному периоду
                $errors['ageid'] = $this->dof->get_string('error_age_doesnt_access', 'journal');
            }
        }
        
        // Проверки программы
        if ( (! array_key_exists($data['programmid'][0], $this->programms)) || 
                (! array_key_exists($data['programmid'][1], $this->parallels[$data['programmid'][0]])) )
        {// Идентификатор группы указан неверно
            $errors['programmid'] = $this->dof->get_string('error_programm_doesnt_exists', 'journal');
        } else
        {
            $access = $this->dof->storage('ages')->is_access('use', (int)$data['programmid'][0], NULL, $this->addvars['departmentid']);
            if ( empty($access) )
            {// Нет доступа к программе
                $errors['programmid'] = $this->dof->get_string('error_programm_doesnt_access', 'journal');
            }
        }
        
	  	// Убираем лишние пробелы со всех полей формы
        $mform->applyFilter('__ALL__', 'trim');
        
        // Возвращаем ошибки, если они есть
        return $errors;
    }
    
    /**
     * Заполнение формы данными
     */
    public function definition_after_data()
    {
        // Cоздаем ссылку на HTML_QuickForm
        $mform =& $this->_form;
    }
    
    /**
     * Обработать пришедшие из формы данные
     *
     * @return bool
     */
    public function process()
    {
        // Cоздаем ссылку на HTML_QuickForm
        $mform =& $this->_form;
        
        if ( $this->is_submitted() && confirm_sesskey() &&
                $this->is_validated() && $formdata = $this->get_data()
                )
        {// Обработка данных формы
            $result = new stdClass();
            $result->ageid = $formdata->ageid;
            $result->programmid = $formdata->programmid[0];
            $result->parallel = $formdata->programmid[1];
            $result->datefrom = $formdata->sourceselect_timeinterval['date_from'];
            $result->todate = $formdata->sourceselect_timeinterval['date_to'];
            $result->programmname = $this->programms[$formdata->programmid[0]];
            $result->agename = $this->ages[$formdata->ageid];
            
            if ( property_exists($formdata, 'xls') )
            {
                // Выгрузка в XLS
                $result->exporter = 'xls';
            }
            
            $this->result = $result;
        }
    }
    
    /**
     * Получение фильтра
     * 
     * @return stdClass
     */
    public function get_filter()
    {
        return $this->result;
    }
}
?>