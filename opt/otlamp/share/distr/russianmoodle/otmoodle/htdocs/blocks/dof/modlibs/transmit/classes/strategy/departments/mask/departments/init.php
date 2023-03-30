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
 * Обмен данных с внешними источниками. Класс маски подразделений
 *
 * @package    sync
 * @subpackage transmit
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

class dof_modlib_transmit_strategy_departments_mask_departments extends dof_modlib_transmit_strategy_mask_base
{
    /**
     * Статегия маски
     *
     * @var dof_modlib_transmit_strategy_base
     */
    protected $strategy = 'departments';
    
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
        
        // Текущая маска работает только с полями персоны
        foreach ( $maskfields as $fieldcode => &$fieldinfo )
        {
            if ( strpos($fieldcode, 'department_') !== 0 )
            {// Поле не относится к подразделению
                unset($maskfields[$fieldcode]);
            }
        }
        
        return $maskfields;
    }
    
    /**
     * Изменение формы настройки импорта
     *
     * @param dof_modlib_transmit_configurator_configform_base $form - Форма настройки обмена данными
     * @param MoodleQuickForm $mform
     *
     * @return void
     */
    protected function configform_definition_import_prepared(dof_modlib_transmit_configurator_configform_base &$form, MoodleQuickForm &$mform)
    {
        $options = $this->get_configitem('importoptions');
        
        // Секция настроек подразделения
        $headertext = dof_html_writer::tag(
            'b', 
            $this->dof->get_string('dof_modlib_transmit_mask_departments_header', 'transmit', null, 'modlib')
        );
        $header = dof_html_writer::div(
            $headertext,
            'dof_modlib_transmit_mask_departments_header'
        );
        $mform->addElement('html', $header);
        
            
            $selects = [
                0 => $this->dof->get_string('default', 'transmit', null, 'modlib'),
                1 => $this->dof->get_string('override', 'transmit', null, 'modlib')
            ];
        
            // Активация подразделения при импорте
            $group = [];
            $group[] = $mform->createElement(
                'select',
                'value',
                '',
                $selects
            );
            $group[] = $mform->createElement(
                'selectyesno',
                'optmode',
                ''
            );
            $mform->addGroup(
                $group,
                'department_activate',
                $this->dof->get_string('mask_departments_choose_activate', 'transmit', null, 'modlib'),
                [' ']
            );
            
            $mform->setDefault('department_activate[value]', $options['department_activate_value']);
            $mform->setDefault('department_activate[optmode]', $options['department_activate_optmode']);
            
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
        // Массив ошибок
        $errors = [];
        
        return $errors;
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
        
        $options['department_activate_value'] = (int)$formdata->department_activate['value'];
        $options['department_activate_optmode'] = (int)$formdata->department_activate['optmode'];
        
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
        
        if ( $options['department_activate_optmode'] == 1 )
        {// Требуется переопределение входного параметра
            $data['department_activate'] = $options['department_activate_value'];
        } else
        {// Установка значения по умолчанию
            if ( ! isset($data['department_activate']) )
            {// Требуется добавить значение по умолчанию
                $data['department_activate'] = $options['department_activate_value'];
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
        // Конфигурация для базовой маски
        $configdata = parent::config_defaults();
        
        // Опции импорта
        $configdata['importoptions'] = [
            // Активация подразделения
            'department_activate_value' => 0,
            // Режим установки опции
            'department_activate_optmode' => 0
        ];
        
        // Процесс симуляции
        $configdata['simulation'] = false;
        
        // Целевое подразделение
        $configdata['departmentid'] = 0;
        
        // Сравнение экспортных полей
        $exportfields = array_keys($this->get_exportfields());
        $configdata['exportfields'] = array_combine($exportfields, $exportfields);
        
        return $configdata;
    }
    
}
