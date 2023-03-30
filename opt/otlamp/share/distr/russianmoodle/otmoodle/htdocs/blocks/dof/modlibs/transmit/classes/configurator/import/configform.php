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
 * Обмен данных с внешними источниками. Класс настроек импорта
 *
 * @package    modlib
 * @subpackage transmit
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class dof_modlib_transmit_configurator_import_configform 
    extends dof_modlib_transmit_configurator_configform_base
{
    /**
     * Раздел источника данных для импорта
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
            $this->dof->get_string('header_configform_source_import', 'transmit', null, 'modlib')
        );
        $mform->setExpanded('header_configform_source', true);
        
        // Передача управления в источник
        $this->get_source()->configform_definition_import($this, $mform);
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
        
        $title = $this->dof->modlib('ig')->icon('help_question').' '.
            $this->dof->get_string('configform_mask_import_fields_header', 'transmit', null, 'modlib');
        // Информация об импортируемых полях
        $infobutton = dof_html_writer::div(
            $title,
            'btn button dof_button'
        );
        $html = $this->dof->modlib('widgets')->modal(
            $infobutton,
            $this->get_mask()->get_importfields_infoblock(),
            $this->dof->get_string('configform_mask_import_fields_header', 'transmit', null, 'modlib')
        );
        $mform->addElement('html', $html);
        
        // Заголовок раздела настроек
        $mform->addElement(
            'header', 
            'header_configform_mask', 
            $this->dof->get_string('header_configform_mask_import', 'transmit', null, 'modlib')
        );
        $mform->setExpanded('header_configform_mask', false);
        
        // Передача управления в маску
        $this->get_mask()->configform_definition_import($this, $mform);
    }
    
    /**
     * Добавить секцию отчета
     *
     * @return void
     */
    protected function add_section_report(dof_storage_logs_queuetype_base $logger)
    {
        // Cоздаем ссылку на HTML_QuickForm
        $mform = &$this->_form;
        $configform = &$this;
        
        // Заголовок
        $header = [
            $mform->createElement(
                'header', 
                'header_configform_report', 
                $this->dof->get_string('header_configform_report', 'transmit', null, 'modlib')
            )
        ];
        $mform->addGroup($header, 'header_group_source', null, [' ']);
        
        // идентификатор записи лога в БД
        $logid = $logger->get_id();
        
        // Запросим html отчет у интерфейса логов
        $table = $this->dof->im('logs')->get_logreport($logid, 'html');
        
        // Добавим в отчет в форму
        $mform->addElement('html', $table);
        
        // Включение кнопки импорта и сохранения
        $actions = $mform->getElement('actions')->getElements();
        foreach ( $actions as $action )
        {
            if ( $action->getName() == 'action_execute' )
            {
                $attributes = $action->getAttributes();
                unset($attributes['disabled']);
                $action->setAttributes($attributes);
            }
        }
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
        $this->get_source()->configform_definition_after_data_import($this, $mform);
        
        // Заполнение полей фильтрации
        $this->get_source()->filterform_definition_after_data_import($mform);
        
        // Заполнение полей маски
        $this->get_mask()->configform_definition_after_data_import($this, $mform);
        
        // Заполнение полей пакета
        $this->get_pack(true)->configform_definition_after_data_import($this, $mform);
        
        parent::definition_after_data();
        
        
        // Получение группы кнопок
        $actiongroup = $mform->getElement('actions')->getElements();
        
        $additional = [];
        // Добавление кнопки симуляции
        $labelcode = 'configform_'.$this->configurator->get_code().'_simulate_label';
        $additional[] = $mform->createElement(
            'submit',
            'action_simulate',
            $this->dof->get_string($labelcode, 'transmit', null, 'modlib')
        );
        // Добавление кнопки исполнения
        $labelcode = 'configform_'.$this->configurator->get_code().'_execute_label';
        $additional[] = $mform->createElement(
            'submit',
            'action_execute',
            $this->dof->get_string($labelcode, 'transmit', null, 'modlib'),
            ['disabled' => 'disabled']
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
            $this->get_source()->configform_validation_import($this, $mform, $data, $files),
            $errors
        );
        
        // Валидация полей фильтрации
        $errors = array_merge(
            $this->get_source()->filterform_validation_import($mform, $data, $files),
            $errors
            );
        
        // Валидация полей маски
        $errors = array_merge(
            $this->get_mask()->configform_validation_import($this, $mform, $data, $files),
            $errors
        );
        
        // Валидация полей пакета
        $errors = array_merge(
            $this->get_pack()->configform_validation_import($this, $mform, $data, $files),
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
            $this->get_source()->configform_setupconfig_import($this, $mform, $formdata);
            
            // Процессинг полей маски
            $this->get_mask()->configform_setupconfig_import($this, $mform, $formdata);
            
            // Процессинг полей пакета
            $this->get_pack(true)->configform_setupconfig_import($this, $mform, $formdata);
            
            
            parent::process();
        }
    }
    
    /**
     * Процесс симуляции импорта
     *
     * @return void
     */
    protected function action_simulate()
    {
        // Cоздаем ссылку на HTML_QuickForm
        $mform = &$this->_form;
        
        // Включить режим симуляции
        $this->configurator->simulation_on();
        
        // Запуск обмена
        $this->configurator->transmit();
        
        // Добавление раздела отчета по обмену
        $this->add_section_report($this->configurator->get_logger());
        
        // Активация кнопки сохранения пакета
        $this->activate_pack_button();
        
        // Удалание записи лога
        $this->configurator->get_logger()->delete();
    }
    
    /**
     * Процесс импорта
     *
     * @return void
     */
    protected function action_execute()
    {
        // Запуск процесса синхронизации
        $this->configurator->transmit();
        
        // Редирект на страницу лога
        $this->addvars['id'] = $this->configurator->get_logger()->get_id();
        redirect($this->dof->url_im('logs', '/view.php', $this->addvars));
    }
    
    protected function action_create_pack()
    {
        // Активация кнопки сохранения пакета
        $this->activate_pack_button();
        $this->configurator->create_pack();
    }
    
    /**
     * Добавить секцию отчета
     *
     * @return void
     */
    protected function activate_pack_button()
    {
        // Cоздаем ссылку на HTML_QuickForm
        $mform = &$this->_form;
        
        // Включение кнопки импорта и сохранения
        $actions = $mform->getElement('actions')->getElements();
        foreach ( $actions as $action )
        {
            if ( $action->getName() == 'action_create_pack' )
            {
                $attributes = $action->getAttributes();
                unset($attributes['disabled']);
                $action->setAttributes($attributes);
            }
        }
    }
}
