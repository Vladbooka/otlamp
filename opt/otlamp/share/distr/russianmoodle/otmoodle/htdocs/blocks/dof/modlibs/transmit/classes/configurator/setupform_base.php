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
 * Обмен данных с внешними источниками. Базовая форма выбора маски и источника
 * 
 * Форма для первичной настройки конфигуратора
 * 
 * @package    modlib
 * @subpackage transmit
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class dof_modlib_transmit_configurator_setupform_base extends dof_modlib_widgets_form
{
    /**
     * Контроллер Деканата
     *
     * @var dof_control
     */
    protected $dof = null;

    /**
     * Настраиваемый формой конфигуратор
     *
     * @var dof_modlib_transmit_configurator_base
     */
    protected $configurator = null;
    
    /**
     * Массив GET-параметров текущей страницы
     *
     * @var array
     */
    protected $addvars = [];
    
    /**
     * Код маски
     *
     * @var string
     */
    protected $maskcode = null;
    
    /**
     * Код источника
     *
     * @var string
     */
    protected $sourcecode = null;
    
    /**
     * Список доступных масок
     *
     * @var dof_modlib_transmit_strategy_mask_base[]
     */
    protected $masks_available = [];
    
    /**
     * Список доступных источников
     *
     * @var dof_modlib_transmit_source_base[]
     */
    protected $sources_available = [];
    
    /**
     * Получить код текущей выбранной маски
     *
     * @return string
     */
    public function get_maskcode()
    {
        return $this->maskcode;
    }
    
    /**
     * Получить код текущего выбранного источника
     *
     * @return string
     */
    public function get_sourcecode()
    {
        return $this->sourcecode;
    }
    
    /**
     * Дополнительная инициализация формы
     * 
     * @return void
     */
    protected function definition_inner()
    {
    }
    
    /**
     * Инициализация формы
     */
    protected function definition()
    {
        // Создание ссылки на HTML_QuickForm
        $mform =& $this->_form;
        
        // Добавление класса формы
        $formclass = $mform->getAttribute('class');
        $mform->_attributes['class'] = $formclass.' transmit_setupform_form';
        
        // Добавление свойств
        $this->dof = $this->_customdata->dof;
        $this->addvars = $this->_customdata->addvars;
        $this->sources_available = $this->_customdata->sources_available;
        $this->masks_available = $this->_customdata->masks_available;
        $this->maskcode = $this->_customdata->mask;
        $this->sourcecode = $this->_customdata->source;
        $this->configurator = $this->_customdata->configurator;
        
        // Заголовок формы
        $header = [
            $mform->createElement('header', 'header', $this->dof->get_string('setupform_header', 'transmit', null, 'modlib'))
        ];
        $mform->addGroup($header, 'header_group', null, [' ']);
        
        // Выбор маски стратегии для обмена данных
        $strategies = [];
        $masks = [];
        foreach ( $this->masks_available as $code => $maskclass )
        {
            // Определение стратегии
            $strategy = $maskclass::get_strategy_code();
            $strategyclass =  $maskclass::get_strategy_class();
            $strategies[$strategy] = $strategyclass::get_name_localized();
            // Определение маски
            $mask = $maskclass::get_fullcode();
            $masks[$strategy][$mask] = $maskclass::get_name_localized();
        }
        $selectmask = $mform->addElement(
            'dof_hierselect', 
            'maskcode', 
            $this->dof->get_string('setupform_select_mask_label', 'transmit', null, 'modlib'),
            '',
            null,
            ''
        );
        $selectmask->setOptions([$strategies, $masks]);
        if ( ! empty($this->masks_available[$this->maskcode]) )
        {
            $maskclass = $this->masks_available[$this->maskcode];
            $mform->setDefault('maskcode', [$maskclass::get_strategy_code() ,$this->maskcode]);
        }
        
        // Выбор источника данных для обмена данных
        $sources = [];
        foreach ( $this->sources_available as $code => $classname )
        {
            // Получение локализованного имени типа источника
            $parentclass = get_parent_class($classname);
            $parentname = $parentclass::get_name_localized();
            // Получение локализованного имени источника
            $sourcename = $classname::get_name_localized();
            $sources[$parentname][$code] = $sourcename;
        }
        $mform->addElement(
            'selectgroups', 
            'sourcecode', 
            $this->dof->get_string('setupform_select_source_label', 'transmit', null, 'modlib'), 
            $sources
        );
        $mform->setDefault('sourcecode', $this->sourcecode);
        
        // Подтверждение выбора
        $mform->addElement(
            'submit', 
            'submit_button', 
            $this->dof->get_string('setupform_submit_label', 'transmit', null, 'modlib')
        );
        
        // Дополнительная инициализация формы
        $this->definition_inner();
    }
    
    /**
     * Валидация формы
     * 
     * @param $data array
     * @param $fields array
     * 
     * @return array
     */
    public function validation($data, $files)
    {
        // Массив ошибок
        $errors = [];
        
        if ( empty($data['maskcode'][0]) || empty($data['maskcode'][1]) )
        {// Маска не выбрана
            $errors['maskcode'] = $this->dof->
                get_string('setupform_error_empty_mask', 'transmit', null, 'modlib');
        }
        
        if ( ! isset($data['sourcecode']) || empty($data['sourcecode']) )
        {// Источник не выбран
            $errors['sourcecode'] = $this->dof->
                get_string('setupform_error_empty_sourcecode', 'transmit', null, 'modlib');
        }
        
        return $errors;
    }
    
    /**
     * Установить конфигуратор с учетом переданных данных
     *
     * @return bool
     */
    public function process_setup_configurator()
    {
        if ( $this->is_submitted() && confirm_sesskey() && 
             $this->is_validated() && $formdata = $this->get_data() )
        {
            // Установка данных маски и источника
            $this->maskcode = $formdata->maskcode[1];
            $this->sourcecode = $formdata->sourcecode;
            
            // Установка конфигуратора
            $config = [
                'mask' => $this->maskcode,
                'source' => $this->sourcecode
            ];
            
            // Тип интерфейса
            $type = $this->configurator->get_code();
            
            // Редирект на страницу
            redirect($this->dof->url_im('transmit', '/' . $type . '/index.php', array_merge($this->addvars, $config)));
        }
    }
}