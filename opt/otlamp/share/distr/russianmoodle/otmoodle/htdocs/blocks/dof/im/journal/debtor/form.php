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
 * Форма фильтрации
 *
 * @package    block_dof
 * @subpackage im_journal
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// Подключение базовых функций плагина
require_once('../lib.php');

// Подключение библиотеки форм
$DOF->modlib('widgets')->webform();

class dof_im_journal_form_debtor extends dof_modlib_widgets_form
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
     * Текущее подразделения
     *
     * @var int
     */
    protected $departmentid = 0;
    
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
     * Параллели
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
    protected  function get_list_ages()
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
        $permissions = [
            [
                'plugintype' => 'storage',
                'plugincode' => 'ages',
                'code' => 'use'
            ]
        ];
        $rez = $this->dof_get_acl_filtered_list($rez, $permissions);
        $this->ages = $rez;
        return $rez;
    }
    
    /**
     * Возвращает массив программ
     *
     * @return void
     */
    protected function set_programms_and_parallels()
    {
        // получаем список доступных учебных периодов
        $rez = $this->dof->storage('programms')->get_records(['status' => ['available']]);
        $maxnumparallels = 0;
        foreach ( $rez as $programm )
        {
            if ( $programm->agenums > $maxnumparallels )
            {
                $maxnumparallels = $programm->agenums;
            }
        }
        // преобразуем список записей в нужный для select-элемента формат
        $rez = $this->dof_get_select_values($rez, false, 'id', ['name', 'code', 'id']);
        
        // оставим в списке только те объекты, на использование которых есть право
        $permissions = [
            [
                'plugintype' => 'storage',
                'plugincode' => 'programms',
                'code' => 'use'
            ]
        ];
        $rez = $this->dof_get_acl_filtered_list($rez, $permissions);
        
        $this->programms = $rez;
        $this->parallels = array_combine(range(1, $maxnumparallels), range(1, $maxnumparallels));
    }
    
    /**
     * {@inheritDoc}
     * @see dof_modlib_widgets_form::definition()
     */
    protected function definition()
    {
        $this->departmentid = optional_param('departmentid', 0, PARAM_INT);
        
        // Создание ссылки на HTML_QuickForm
        $mform =& $this->_form;
        
        // Добавляем свойства
        $this->dof = $this->_customdata->dof;
        $this->addvars = $this->_customdata->addvars;
        
        $mform->addElement('hidden', 'departmentid', $this->departmentid);
        $mform->setType('departmentid', PARAM_INT);
        
        // включать в отчет отработчиков из дочерних подразделений
        $mform->addElement(
                'selectyesno',
                'includechildren',
                $this->dof->get_string('form_debtor__includechildren', 'journal')
                );
        $this->add_help('includechildren', 'form_debtor__includechildren', 'journal');
        
        // учебные периоды
        $mform->addElement(
            'autocomplete',
            'ages',
            $this->dof->get_string('form_debtor__ages', 'journal'),
            $this->get_list_ages(),
            [
                'id' => 'debtorages',
                'multiple' => 'multiple',
                'noselectionstring' => $this->dof->get_string('form_debtor__age_all', 'journal')
            ]
        );
        
        // установка программ
        $this->set_programms_and_parallels();
        
        // параллели
        $mform->addElement(
            'autocomplete',
            'parallels',
            $this->dof->get_string('form_debtor__parallels', 'journal'),
            $this->parallels,
            [
                'id' => 'debtorparallels',
                'multiple' => 'multiple',
                'noselectionstring' => $this->dof->get_string('form_debtor__parallels_all', 'journal')
            ]
        );
        
        // программы
        $mform->addElement(
            'autocomplete',
            'programms',
            $this->dof->get_string('form_debtor__programms', 'journal'),
            $this->programms,
            [
                'id' => 'debtorprogramms',
                'multiple' => 'multiple',
                'noselectionstring' => $this->dof->get_string('form_debtor__programms_all', 'journal')
            ]
        );
        
        // группировка
        $mform->addElement(
            'select',
            'grouping',
            $this->dof->get_string('form_debtor__grouping', 'journal'),
            [
                'user' => $this->dof->get_string('form_debtor__grouping__user', 'journal'),
                'group' => $this->dof->get_string('form_debtor__grouping__group', 'journal'),
                'department_with_parallel' => $this->dof->get_string('form_debtor__grouping__department_with_parallel', 'journal'),
            ]
            );
        $this->add_help('grouping', 'form_debtor__grouping', 'journal');
        
        // подписант
        $options = [];
        $options['plugintype'] = "storage";
        $options['plugincode'] = "persons";
        $options['sesskey'] = sesskey();
        $options['type'] = 'autocomplete';
        $options['departmentid'] = $this->departmentid;
        $options['querytype'] = "persons_list";
        
        // используем ajax-autocomplete для ускорения загрузки страницы
        $mform->addElement(
            'dof_autocomplete',
            'signer',
            $this->dof->get_string('form_debtor__signer', 'journal'),
            [],
            $options);
        $this->add_help('signer', 'form_debtor__signer', 'journal');
        
        // дополнительная информация о подписанте
        $mform->addElement(
                'textarea',
                'signerinfo',
                $this->dof->get_string('form_debtor__signerinfo', 'journal')
                );
        $mform->setType('signerinfo', PARAM_RAW);
        $this->add_help('signerinfo', 'form_debtor__signerinfo', 'journal');
        
        // временной интервал
        $options = [];
        $usertimezone = $this->dof->storage('persons')->get_usertimezone_as_number();
        $options['timezone'] = $usertimezone;
        $options['calendartype'] = 'two_calendar';
        $options['alignment'] = 'left';
        
        $mform->addElement(
            'dof_calendar',
            'timeinterval',
            $this->dof->get_string('form_debtor__time_interval', 'journal'),
            $options
        );
        
        $group = [];
        $group[] = $mform->createElement(
                'submit',
                'submitbutton',
                $this->dof->get_string('form_debtor__show', 'journal')
                );
        $group[] = $mform->createElement(
            'submit',
            'pdf',
            $this->dof->get_string('form_debtor__pdf', 'journal')
            );
        
        $group[] = $mform->createElement(
            'submit',
            'docx',
            $this->dof->get_string('form_debtor__docx', 'journal')
            );
        
        $mform->addGroup($group, 'submitgroup', '', [' '], false);
        
        // Применение проверки ко всем элементам
        $mform->applyFilter('__ALL__', 'trim');
    }
    
    /**
     * Обработать пришедшие из формы данные
     *
     * @return bool
     */
    public function process()
    {
        if ( $formdata = $this->get_data() )
        {// обработка данных формы
            $result = new stdClass();
            if ( ! empty($formdata->includechildren) )
            {
                $deps = $this->dof->im('departments')->get_departments_select_options((int)$this->departmentid, ['name_with_code' => true]);
                unset($deps[0]);
                $result->departments = $deps;
            } else
            {
                $result->departments = [$this->departmentid => $this->departmentid];
            }
            $result->ages = ! empty($formdata->ages) ? array_combine($formdata->ages, $formdata->ages) : $this->ages;
            $result->programms = ! empty($formdata->programms) ? array_combine($formdata->programms, $formdata->programms) : $this->programms;
            $result->parallels = ! empty($formdata->parallels) ? $formdata->parallels : $this->parallels;
            $result->grouping = ! empty($formdata->grouping) ? $formdata->grouping : 0;
            $result->fromdate = ! empty($formdata->timeinterval['date_from']) ? $formdata->timeinterval['date_from'] : time();
            $result->todate = ! empty($formdata->timeinterval['date_to']) ? $formdata->timeinterval['date_to'] : time();
            $result->signerid = ! empty($formdata->signer['id']) ? $formdata->signer['id'] : 0;
            $result->signerinfo = ! empty($formdata->signerinfo) ? $formdata->signerinfo : '';
            $result->url = new moodle_url('/blocks/dof/im/journal/debtor/index.php?' . http_build_query($this->_form->_submitValues));
            
            if ( property_exists($formdata, 'pdf') )
            {
                $result->exporter = 'pdf';
            }
            
            if ( property_exists($formdata, 'docx') )
            {
                $result->exporter = 'docx';
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
