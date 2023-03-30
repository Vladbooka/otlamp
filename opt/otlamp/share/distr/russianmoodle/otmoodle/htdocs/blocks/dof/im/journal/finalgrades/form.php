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
 * Класс формы
 * 
 * @package    im
 * @subpackage journal
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// Подключение базовых функций плагина
require_once('../lib.php');

// Подключение библиотеки форм
$DOF->modlib('widgets')->webform();

class dof_im_journal_finalgrades extends dof_modlib_widgets_form
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
     * Тип экспорта
     *
     * @var string
     */
    protected $export = null;
    
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
        $this->dof = $this->_customdata['dof'];
        $this->addvars = $this->_customdata['addvars'];
        $this->export = $this->_customdata['export'];
        
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
        if ( isset($this->addvars['ageid']) )
        {
            $mform->setDefault('ageid', $this->addvars['ageid']);
        }

        // Программа
        // Установка необходимых данных
        $this->set_programms_and_parallels();
        
        $sel = $mform->addElement('dof_hierselect', 'programm_parallel', $this->dof->get_string('form_select_programm', 'journal'));
        $sel->setOptions([$this->programms, $this->parallels]);
        if ( isset($this->addvars['programmid']) && isset($this->addvars['parallel']) )
        {
            $mform->setDefault(
                'programm_parallel', 
                [
                    $this->addvars['programmid'],
                    $this->addvars['parallel']
                ]
            );
        }
        
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
        if ( (! array_key_exists($data['programm_parallel'][0], $this->programms)) || 
                (! array_key_exists($data['programm_parallel'][1], $this->parallels[$data['programm_parallel'][0]])) )
        {// Идентификатор группы указан неверно
            $errors['programm_parallel'] = $this->dof->get_string('error_programm_doesnt_exists', 'journal');
        } else
        {
            $access = $this->dof->storage('ages')->is_access('use', (int)$data['programm_parallel'][0], NULL, $this->addvars['departmentid']);
            if ( empty($access) )
            {// Нет доступа к программе
                $errors['programm_parallel'] = $this->dof->get_string('error_programm_doesnt_access', 'journal');
            }
        }
        
	  	// Убираем лишние пробелы со всех полей формы
        $mform->applyFilter('__ALL__', 'trim');
        
        // Возвращаем ошибки, если они есть
        return $errors;
    }
    
    /**
     * Обработать пришедшие из формы данные
     *
     * @return bool
     */
    public function process()
    {
        $html = '';
        
        if (    $this->is_submitted() && confirm_sesskey() &&
                $this->is_validated() && $formdata = $this->get_data()  )
        {// форма с новыми значениями фильтров была отправлена
            // формирование редиректа с нужными гет-параметрами
            $redirectaddvars = $this->addvars;
            $redirectaddvars['ageid'] = $formdata->ageid;
            $redirectaddvars['programmid'] = $formdata->programm_parallel[0];
            $redirectaddvars['parallel'] = $formdata->programm_parallel[1];
            if ( property_exists($formdata, 'xls') )
            {
                $redirectaddvars['export'] = 'xls';
            }
            unset($redirectaddvars['programm_parallel']);
            redirect(
                $this->dof->url_im('journal', '/finalgrades/index.php', $redirectaddvars)
            );
        } else
        {// новые фильтры не задавались
            // формирование отчета
            
            $tab = $this->addvars['tab'];
            
            // инициализация отчета
            if ( $rtreport = $this->dof->modlib('rtreport')->get_rtreport('im', 'journal', $tab) )
            {
                // исходные данные для формирования отчета
                $inputdata = new stdClass();
                $inputdata->ageid = $this->addvars['ageid'];
                $inputdata->programmid = $this->addvars['programmid'];
                $inputdata->parallel = $this->addvars['parallel'];
                
                // передача данных в отчет
                $rtreport->set_data(['input' => $inputdata]);
                $rtreport->set_exporter($this->export);
                // сбор отчета
                if ( $table = $rtreport->run() )
                {
                    $table->attributes['class'] .= ' '.$tab;
                    // Заголовок отчета
                    $html .= $rtreport->get_header();
            
                    // Вывод отчета
                    $html .= dof_html_writer::div(
                        dof_html_writer::table($table),
                        $tab.'_table'
                    );
                } else
                {
                    // Нехватка данных
                    $html .= $this->dof->get_string('not_enough_data', 'rtreport');
                }
            } else
            {
                // Переданы неверные данные
                $html .= $this->dof->get_string('invalid_data', 'rtreport');
            }
        }
        
        return $html;
    }
}
