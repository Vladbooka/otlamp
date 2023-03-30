<?php
////////////////////////////////////////////////////////////////////////////
//                                                                        //
// NOTICE OF COPYRIGHT                                                    //
//                                                                        //
// Dean`s Office for Moodle                                               //
// Электронный деканат                                                    //
// <http://deansoffice.ru/>                                               //
//
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
 * Обмен данных с внешними источниками. Класс маски договоров
 *
 * @package    sync
 * @subpackage transmit
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class dof_modlib_transmit_strategy_participants_mask_contracts extends dof_modlib_transmit_strategy_participants_mask_persons
{
    /**
     * Префиксы для полей (Переопределение префиксов полей персоны)
     *
     * @var array
     */
    protected static $prefixes = [
        'student',
        'parent',
        'seller',
        'curator'
    ];
    
    /**
     * Префиксы для полей текущего класса
     *
     * @var array
     */
    protected static $prefixes_contracts = [
        'student'
    ];
    
    /**
     * Филды маски
     *
     * @var array
     */
    protected static $fields_import_contracts = [
        'person_email',
        'contract_num',
        'contract_num_generate',
        'contract_date',
        'contract_notice',
        'contract_id'
    ];
    
    /**
     * Филды маски на экспорт
     *
     * @var array
     */
    protected static $fields_export_contracts = [
        'contract_id',
        'contract_num',
        'contract_date'
    ];
    
    /**
     * Список хранилищ
     *
     * @var bool
     */
    protected static $dof_customfields_storages = [
        'persons',
        'contracts'
    ];
    
    /**
     * Конфиг маски
     *
     * @var array
     */
    protected $config_contracts = [
        'student_contract_num_override' => null,
        'student_contract_num' => null,
        'student_contract_activate_override' => null,
        'student_contract_activate' => null,
        'student_contract_num_generate_override' => null,
        'student_contract_num_generate' => null,
        'student_contract_date_override' => null,
        'student_contract_date' => null,
        'student_contract_notice_override' => null,
        'student_contract_notice' => null
    ];
    
    /**
     * Поддержка импорта
     *
     * @return bool
     */
    public static function support_import()
    {
        return true;
    }
    
    /**
     * Поддержка экспорта
     *
     * @return bool
     */
    public static function support_export()
    {
        return false;
    }
    
    /**
     * Получить список полей импорта, с которыми работает текущая маска
     *
     * @return array
     */
    public function get_importfields()
    {
        // Получение полного набора полей стратегии
        $strategy = $this->strategy;
        $maskfields = (array)$strategy::$importfields;
        
        // Текущая маска работает только с полями договора
        foreach ( $maskfields as $fieldcode => &$fieldinfo )
        {
            if ( strpos($fieldcode, 'student_') !== 0 &&
                 strpos($fieldcode, 'parent_') !== 0 &&
                 strpos($fieldcode, 'curator_') !== 0 &&
                 strpos($fieldcode, 'seller_') !== 0 &&
                 strpos($fieldcode, 'student_contract_') !== 0 &&
                 strpos($fieldcode, '/customfield_') !== 0 )
            {// Поле не относится к маске
                unset($maskfields[$fieldcode]);
            }
        }
        
        return $maskfields;
    }
    
    /**
     * Конструктор
     *
     * @param dof_control $dof
     * @param dof_storage_logs_queuetype_base $logger
     *
     * @return void
     */
    public function __construct(dof_control $dof, $logger)
    {
        $this->chaining_on();
        parent::__construct($dof, $logger);
    }
    
    /**
     * Заполнить форму дополнительными настройками маски (ИМПОРТ)
     * 
     * @param dof_modlib_transmit_configurator_configform_base $form
     * @param MoodleQuickForm $mform
     *
     * @return void
     */
    protected function configform_definition_import_prepared(dof_modlib_transmit_configurator_configform_base &$form, MoodleQuickForm &$mform)
    {
        // Хидер элементов для студента
        $mform->addElement('html', '<b><div class="dof_modlib_transmit_mask_contracts_header">' . $this->dof->get_string('mask_contracts_header_student_contract', 'transmit', null, 'modlib') . '</div></b>');
        
        // Номер контракта
        $select_contract_num = [
            1 => $this->dof->get_string('default', 'transmit', null, 'modlib'),
            2 => $this->dof->get_string('override', 'transmit', null, 'modlib')
        ];
        $group_contract_num = [];
        $group_contract_num[] = $mform->createElement(
                'select',
                'contract_num',
                '',
                $select_contract_num
                );
        $group_contract_num[] = $mform->createElement(
                'text',
                'contract_num_option',
                $this->dof->get_string('mask_contracts_group_contract_num_input_text', 'transmit', null, 'modlib')
                );
        $mform->addGroup(
                $group_contract_num,
                'contract_num_group',
                $this->dof->get_string('mask_contracts_group_contract_num_input', 'transmit', null, 'modlib'),
                [' ']
                );
        $mform->setType('contract_num_group[contract_num_option]', PARAM_RAW_TRIMMED);
        
        // Флаг активации контракта
        $select_active = [
            0 => $this->dof->get_string('no', 'transmit', null, 'modlib'),
            1 => $this->dof->get_string('yes', 'transmit', null, 'modlib')
        ];
        $group_contract_num = [];
        $group_contract_num[] = $mform->createElement(
                'select',
                'contract_activate',
                '',
                $select_contract_num
                );
        $group_contract_num[] = $mform->createElement(
                'select',
                'contract_activate_option',
                '',
                $select_active
                );
        $mform->addGroup(
                $group_contract_num,
                'contract_activate_group',
                $this->dof->get_string('mask_contracts_group_contract_activate', 'transmit', null, 'modlib'),
                [' ']
                );
        
        // Флаг генерации номера контракта
        $select_contract_num = [
                1 => $this->dof->get_string('default', 'transmit', null, 'modlib'),
                2 => $this->dof->get_string('override', 'transmit', null, 'modlib')
        ];
        $select_active = [
                0 => $this->dof->get_string('no', 'transmit', null, 'modlib'),
                1 => $this->dof->get_string('yes', 'transmit', null, 'modlib')
        ];
        $group_contract_num = [];
        $group_contract_num[] = $mform->createElement(
                'select',
                'contract_num_generate',
                '',
                $select_contract_num
                );
        $group_contract_num[] = $mform->createElement(
                'select',
                'contract_num_generate_option',
                '',
                $select_active
                );
        $mform->addGroup(
                $group_contract_num,
                'contract_num_generate_group',
                $this->dof->get_string('mask_contracts_group_contract_num', 'transmit', null, 'modlib'),
                [' ']
                );
        
        // Дата заключения контракта
        $select_contract_date = [
                1 => $this->dof->get_string('default', 'transmit', null, 'modlib'),
                2 => $this->dof->get_string('override', 'transmit', null, 'modlib')
        ];
        $group_contract_date = [];
        $group_contract_date[] = $mform->createElement(
                'select',
                'contract_date',
                '',
                $select_contract_date
                );
        $group_contract_date[] = $mform->createElement(
                'date_selector', 
                'contract_date_option'
                );
        $mform->addGroup(
                $group_contract_date,
                'contract_date_group',
                $this->dof->get_string('mask_contracts_group_contract_date', 'transmit', null, 'modlib'),
                [' ']
                );
        $mform->setType('contract_date_group[contract_date_option]', PARAM_INT);
        
        // Заметка к контракту
        $select_contract_notice = [
                1 => $this->dof->get_string('default', 'transmit', null, 'modlib'),
                2 => $this->dof->get_string('override', 'transmit', null, 'modlib')
        ];
        $group_contract_notice = [];
        $group_contract_notice[] = $mform->createElement(
                'select',
                'contract_notice',
                '',
                $select_contract_notice
                );
        $group_contract_notice[] = $mform->createElement(
                'textarea',
                'contract_notice_option',
                $this->dof->get_string('mask_contracts_group_contract_notice_text', 'transmit', null, 'modlib')
                );
        $mform->addGroup(
                $group_contract_notice,
                'contract_notice_group',
                $this->dof->get_string('mask_contracts_group_contract_notice', 'transmit', null, 'modlib'),
                [' ']
                );
    }
    
    /**
     * Заполнить форму данными (ИМПОРТ)
     * 
     * @param dof_modlib_transmit_configurator_configform_base $form
     * @param MoodleQuickForm $mform
     *
     * @return void
     */
    protected function configform_definition_after_data_import_prepared(dof_modlib_transmit_configurator_configform_base &$form, MoodleQuickForm &$mform)
    {
        
    }
    
    /**
     * Валидация формы (ИМПОРТ)
     *
     * @param dof_modlib_transmit_configurator_configform_base $form
     * @param MoodleQuickForm $mform
     * @param array $data
     * @param array $files
     *
     * @return void
     */
    protected function configform_validation_import_prepared(dof_modlib_transmit_configurator_configform_base &$form, MoodleQuickForm &$mform, $data, $files)
    {
        return [];
    }
    
    /**
     * Установка конфига
     *
     * @param dof_modlib_transmit_configurator_configform_base $form
     * @param MoodleQuickForm $mform
     * @param stdClass $formdata
     *
     * @return void
     */
    protected function configform_setupconfig_import_prepared(dof_modlib_transmit_configurator_configform_base &$form, MoodleQuickForm &$mform, $formdata)
    {
        $importoptions = $this->get_configitem('importoptions');
        // Земетка к контракту
        if ( $formdata->contract_num_group['contract_num'] == 2 )
        {
            $importoptions['student_contract_num_override'] = true;
            $importoptions['student_contract_num'] = htmlentities($formdata->contract_num_group['contract_num_option']);
        } elseif ( $formdata->contract_num_group['contract_num'] == 1 )
        {
            $importoptions['student_contract_num_override'] = false;
            $importoptions['student_contract_num'] = htmlentities($formdata->contract_num_group['contract_num_option']);
        }
        // Флаг генерации номера контракта
        if ( $formdata->contract_activate_group['contract_activate'] == 2 )
        {
            $importoptions['student_contract_activate_override'] = true;
            $importoptions['student_contract_activate'] = (bool)$formdata->contract_activate_group['contract_activate_option'];
        } elseif ( $formdata->contract_activate_group['contract_activate'] == 1 )
        {
            $importoptions['student_contract_activate_override'] = false;
            $importoptions['student_contract_activate'] = (bool)$formdata->contract_activate_group['contract_activate_option'];
        }
        // Флаг генерации номера контракта
        if ( $formdata->contract_num_generate_group['contract_num_generate'] == 2 )
        {
            $importoptions['student_contract_num_generate_override'] = true;
            $importoptions['student_contract_num_generate'] = (bool)$formdata->contract_num_generate_group['contract_num_generate_option'];
        } elseif ( $formdata->contract_num_generate_group['contract_num_generate'] == 1 )
        {
            $importoptions['student_contract_num_generate_override'] = false;
            $importoptions['student_contract_num_generate'] = (bool)$formdata->contract_num_generate_group['contract_num_generate_option'];
        }
        // Дата заключения договора
        if ( $formdata->contract_date_group['contract_date'] == 2 )
        {
            $importoptions['student_contract_date_override'] = true;
            $importoptions['student_contract_date'] = $formdata->contract_date_group['contract_date_option'] + 3600*12;
        } elseif ( $formdata->contract_date_group['contract_date'] == 1 )
        {
            $importoptions['student_contract_date_override'] = false;
            $importoptions['student_contract_date'] = $formdata->contract_date_group['contract_date_option'] + 3600*12;
        }
        // Земетка к контракту
        if ( $formdata->contract_notice_group['contract_notice'] == 2 )
        {
            $importoptions['student_contract_notice_override'] = true;
            $importoptions['student_contract_notice'] = htmlentities($formdata->contract_notice_group['contract_notice_option']);
        } elseif ( $formdata->contract_notice_group['contract_notice'] == 1 )
        {
            $importoptions['student_contract_notice_override'] = false;
            $importoptions['student_contract_notice'] = htmlentities($formdata->contract_notice_group['contract_notice_option']);
        }
        
        $this->set_configitem('importoptions', $importoptions);
    }
    
    /**
     * Фильтрация процесса импорта
     *
     * Метод для фильтрации пулла данных
     *
     * @return void
     */
    protected function transmit_import_filter(&$data)
    {
        // Базовый сбор полей
        parent::transmit_import_filter($data);
        
        $importoptions = $this->get_configitem('importoptions');
        
        // Номер контракта
        if ( $importoptions['student_contract_num_override'] )
        {// Жесткое переопределение
            $data['student_contract_num'] = $importoptions['student_contract_num'];
        } elseif ( ! isset($data['student_contract_num']) ||
                empty($data['student_contract_num']) )
        {// Мягкое переопределение
            $data['student_contract_num'] = $importoptions['student_contract_num'];
        }
        
        // Флаг генерации номера контракта
        if ( $importoptions['student_contract_activate_override'] )
        {// Жесткое переопределение
            $data['student_contract_activate'] = $importoptions['student_contract_activate'];
        } elseif ( ! isset($data['student_contract_activate']) ||
                empty($data['student_contract_activate']) )
        {// Мягкое переопределение
            $data['student_contract_activate'] = $importoptions['student_contract_activate'];
        }
        
        // Флаг генерации номера контракта
        if ( $importoptions['student_contract_num_generate_override'] )
        {// Жесткое переопределение
            $data['student_contract_num_generate'] = $importoptions['student_contract_num_generate'];
        } elseif ( ! isset($data['student_contract_num_generate']) ||
            empty($data['student_contract_num_generate']) )
        {// Мягкое переопределение
            $data['student_contract_num_generate'] = $importoptions['student_contract_num_generate'];
        }
        
        // Дата заключения контракта
        if ( $importoptions['student_contract_date_override'] )
        {// Жесткое переопределение
            $data['student_contract_date'] = date('d.m.Y', $importoptions['student_contract_date']);
        } elseif ( ! isset($data['student_contract_date']) ||
            empty($data['student_contract_date']) )
        {// Мягкое переопределение
            $data['student_contract_date'] = date('d.m.Y', $importoptions['student_contract_date']);
        }
        
        // Заметка к контракту
        if ( $importoptions['student_contract_notice_override'] )
        {// Жесткое переопределение
            $data['student_contract_notice'] = $importoptions['student_contract_notice'];
        } elseif ( ! isset($data['student_contract_notice']) ||
            empty($data['student_contract_notice']) )
        {// Мягкое переопределение
            $data['student_contract_notice'] = $importoptions['student_contract_notice'];
        }
    }
}