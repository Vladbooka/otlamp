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
 * Обмен данных с внешними источниками. Класс маски учебных процессов
 *
 * @package    sync
 * @subpackage transmit
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class dof_modlib_transmit_strategy_cstreams_mask_cstreams extends dof_modlib_transmit_strategy_mask_base
{
    /**
     * Статегия маски
     *
     * @var dof_modlib_transmit_strategy_base
     */
    protected $strategy = 'cstreams';
    
    
    private $ages = [];
    
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
     * Получить список полей импорта, с которыми работает текущая маска
     *
     * @return array
     */
    public function get_importfields()
    {
        // Получение полного набора полей стратегии
        $strategy = $this->strategy;
        $maskfields = (array)$strategy::$importfields;
        
        return $maskfields;
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
        $options = $this->get_configitem('importoptions');
        
        $selects = [
            0 => $this->dof->get_string('default', 'transmit', null, 'modlib'),
            1 => $this->dof->get_string('override', 'transmit', null, 'modlib')
        ];
        foreach ( $this->ages as &$age )
        {
            $age = $age->name;
        }
        // Целевой учебный период
        $group = [];
        $group[] = $mform->createElement(
            'select',
            'mode',
            '',
            $selects
        );
        $group[] = $mform->createElement(
            'select',
            'value',
            '',
            $this->ages
        );
        $mform->addGroup(
            $group,
            'option_ageid',
            $this->dof->get_string('mask_cstreams_option_age', 'transmit', null, 'modlib'),
            [' ']
        );
        $mform->setDefault('option_ageid[value]', $options['ageid']);
        $mform->setDefault('option_ageid[mode]', $options['age_option_mode']);
        
        // Разделитель пользователей
        $group = [];
        $group[] = $mform->createElement(
            'select',
            'mode',
            '',
            $selects
        );
        $group[] = $mform->createElement(
            'select',
            'value',
            '',
            [
                'n' => $this->dof->get_string('transmit_delimiter_newline', 'transmit', null, 'modlib'),
                ',' => $this->dof->get_string('transmit_delimiter_comma', 'transmit', null, 'modlib'),
                ' ' => $this->dof->get_string('transmit_delimiter_space', 'transmit', null, 'modlib')
            ]
        );
        $mform->addGroup(
            $group,
            'option_cpassed_list_delimiter',
            $this->dof->get_string('mask_cstreams_option_cpassed_list_delimiter', 'transmit', null, 'modlib'),
            [' ']
        );
        $mform->setDefault('option_cpassed_list_delimiter[value]', $options['cpassed_list_delimiter']);
        $mform->setDefault('option_cpassed_list_delimiter[mode]', $options['cpassed_list_delimiter_mode']);
        
        // Формат ФИО пользователей
        $group = [];
        $group[] = $mform->createElement(
            'select',
            'mode',
            '',
            $selects
        );
        $group[] = $mform->createElement(
            'text',
            'value',
            ''
        );
        $mform->addGroup(
            $group,
            'option_cpassed_fullnameformat',
            $this->dof->get_string('mask_cstreams_option_cpassed_fullnameformat', 'transmit', null, 'modlib'),
            [' ']
        );
        $mform->setType('option_cpassed_fullnameformat[value]', PARAM_RAW_TRIMMED);
        $mform->setDefault('option_cpassed_fullnameformat[value]', $options['fullname_format']);
        $mform->setDefault('option_cpassed_fullnameformat[mode]', $options['fullname_format_mode']);
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
        // Установка конфигурации
        $options = $this->get_configitem('importoptions');
        if ( ! empty($formdata->option_ageid['value']) )
        {
            $options['ageid'] = (int)$formdata->option_ageid['value'];
        }
        $options['age_option_mode'] = (int)$formdata->option_ageid['mode'];
        $options['cpassed_list_delimiter'] = (string)$formdata->option_cpassed_list_delimiter['value'];
        if ( $options['cpassed_list_delimiter'] === 'n' )
        {
            $options['cpassed_list_delimiter'] = "\n";
        }
        $options['cpassed_list_delimiter_mode'] = (int)$formdata->option_cpassed_list_delimiter['mode'];
        $options['fullname_format'] = (string)$formdata->option_cpassed_fullnameformat['value'];
        $options['fullname_format_mode'] = (int)$formdata->option_cpassed_fullnameformat['mode'];
        
        $this->set_configitem('importoptions', $options);
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
        
        // Конфигурация маски
        $options = $this->get_configitem('importoptions');
        
        if ( $options['age_option_mode'] == 1 )
        {// Требуется переопределение входного параметра
            $data['age_id'] = $options['ageid'];
        } else
        {// Установка значения по умолчанию
            if ( ! isset($data['age_id']) )
            {// Требуется добавить значение по умолчанию
                $data['age_id'] = $options['ageid'];
            }
        }
        
        if ( $options['cpassed_list_delimiter_mode'] == 1 )
        {// Требуется переопределение входного параметра
            $data['cpassed_fullname_list_delimiter'] = $options['cpassed_list_delimiter'];
        } else
        {// Установка значения по умолчанию
            if ( ! isset($data['cpassed_fullname_list_delimiter']) )
            {// Требуется добавить значение по умолчанию
                $data['cpassed_fullname_list_delimiter'] = $options['cpassed_list_delimiter'];
            }
        }
        
        if ( $options['cpassed_list_delimiter_mode'] == 1 )
        {// Требуется переопределение входного параметра
            $data['cpassed_fullname_list_fullnameformat'] = $options['fullname_format'];
        } else
        {// Установка значения по умолчанию
            if ( ! isset($data['cpassed_fullname_list_fullnameformat']) )
            {// Требуется добавить значение по умолчанию
                $data['cpassed_fullname_list_fullnameformat'] = $options['fullname_format'];
            }
        }
    }
    
    /**
     * Получение конфигурации по умолчанию для текущей маски
     *
     * @return array
     */
    protected function config_defaults()
    {
        // Базовая конфигурация
        $configdata = parent::config_defaults();

        // Целевой учебный период
        $statuses = array_keys($this->dof->workflow('ages')->get_meta_list('real'));
        $departments = array_keys($this->dof->storage('departments')->
            get_departmentstrace($configdata['departmentid']));

        $this->ages = $this->dof->storage('ages')->get_records([
            'status' => $statuses,
            'departmentid' => $departments + [$configdata['departmentid']]
        ]);
        
        $configdata['importoptions']['ageid'] = (int)key($this->ages);
        $configdata['importoptions']['age_option_mode'] = 0;
        
        // Разделитель подписок в учебный процесс
        $configdata['importoptions']['cpassed_list_delimiter'] = "\n";
        $configdata['importoptions']['cpassed_list_delimiter_mode'] = 0;
        
        // Разбиение FULLNAME персон
        $configdata['importoptions']['fullname_format'] = 'LASTNAME FIRSTNAME MIDDLENAME';
        $configdata['importoptions']['fullname_format_mode'] = 0;

        return $configdata;
    }
}