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
 * Обмен данных с внешними источниками. Класс настроек экспорта
 *
 * @package    modlib
 * @subpackage transmit
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class dof_modlib_transmit_configurator_export_configform 
    extends dof_modlib_transmit_configurator_configform_base
{
    /**
     * Раздел источника данных для экспорта
     *
     * @return void
     */
    protected function add_section_source()
    {
        // Cоздаем ссылку на HTML_QuickForm
        $mform = &$this->_form;
        
        // Заголовок раздела настроек
        $mform->addElement(
            'header',
            'header_configform_source',
            $this->dof->get_string('header_configform_source_export', 'transmit', null, 'modlib')
        );
        $mform->setExpanded('header_configform_source', true);
        
        // Передача управления в источник
        $this->get_source()->configform_definition_export($this, $mform);
    }
    
    /**
     * Раздел маски для импорта
     *
     * @return void
     */
    protected function add_section_mask()
    {
        // Cоздаем ссылку на HTML_QuickForm
        $mform = &$this->_form;
        
        // Заголовок раздела настроек
        $mform->addElement(
            'header',
            'header_configform_mask',
            $this->dof->get_string('header_configform_mask_export', 'transmit', null, 'modlib')
        );
        $mform->setExpanded('header_configform_mask', false);
        
        // Передача управления в маску
        $this->get_mask()->configform_definition_export($this, $mform);
    }
    
    /**
     * Заполнение только что созданную форму данными
     *
     * @return void
     */
    public function definition_after_data()
    {
        // Cоздаем ссылку на HTML_QuickForm
        $mform = &$this->_form;
        
        // Заполнение полей источника
        $this->get_source()->configform_definition_after_data_export($this, $mform);
        
        // Заполнение полей маски
        $this->get_mask()->configform_definition_after_data_export($this, $mform);
        
        parent::definition_after_data();
        
        // Получение группы кнопок
        $actiongroup = $mform->getElement('actions')->getElements();
        
        $additional = [];
        // Добавление кнопки исполнения
        $labelcode = 'configform_'.$this->configurator->get_code().'_execute_label';
        $additional[] = $mform->createElement(
            'submit',
            'action_execute',
            $this->dof->get_string($labelcode, 'transmit', null, 'modlib')
        );
        
        $actiongroup = array_merge($additional, $actiongroup);
        $mform->getElement('actions')->setElements($actiongroup);
    }
    
    /**
     * Валидация формы
     *
     * @param array $data - Данные формы
     * @param array $files - Файлы формы
     *
     * @return array $errors - Массив ошибок
     */
    public function validation($data, $files)
    {
        // Cоздаем ссылку на HTML_QuickForm
        $mform = &$this->_form;
        
        // Массив ошибок
        $errors = parent::validation($data, $files);
        
        // Валидация полей источника
        $errors = array_merge(
            $this->get_source()->configform_validation_export($this, $mform, $data, $files),
            $errors
        );
        
        // Валидация полей маски
        $errors = array_merge(
            $this->get_mask()->configform_validation_export($this, $mform, $data, $files),
            $errors
        );
        
        // Вернуть ошибки валидации элементов
        return $errors;
    }
    
    /**
     * Обработать пришедшие из формы данные
     *
     * @return bool
     */
    public function process()
    {
        if ( $this->is_submitted() && $this->is_validated() &&
            $formdata = $this->get_data() )
        {
            // MoodleQuick форма
            $mform = &$this->_form;
            
            // Процессинг полей источника
            $this->get_source()->configform_setupconfig_export($this, $mform, $formdata);
            
            // Процессинг полей маски
            $this->get_mask()->configform_setupconfig_export($this, $mform, $formdata);
            
            parent::process();
        }
    }
    
    /**
     * Исполнение
     *
     * @return void
     */
    protected function action_execute()
    {
        $this->configurator->transmit();
    }
}
