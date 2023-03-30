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
 * Обмен данных с внешними источниками. Базовая форма настроек
 *
 * @package    modlib
 * @subpackage transmit
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

abstract class dof_modlib_transmit_configurator_configform_base 
    extends dof_modlib_widgets_form
{
    /**
     * Контроллер Деканата
     *
     * @var dof_control
     */
    protected $dof = null;
    
    /**
     * GET параметры для ссылки
     *  
     * @var array
     */
    protected $addvars = [];
    
    /**
     * Объект конфигуратора
     *
     * @var dof_modlib_transmit_configurator_base
     */
    protected $configurator = null;
    
    /**
     * URL для возврата после обработки формы
     *
     * @var string
     */
    protected $returnurl = null;
    
    /**
     * Добавление секции источника
     *
     * @var void
     */
    abstract protected function add_section_source();
    
    /**
     * Добавление секции маски
     *
     * @var void
     */
    abstract protected function add_section_mask();
    
    /**
     * Инициализация формы
     *
     */
    protected function definition()
    {
        // Создаем ссылку на HTML_QuickForm
        $mform = &$this->_form;
        
        // Установка класса формы
        $formclass = $mform->getAttribute('class');
        $mform->_attributes['class'] = $formclass.' transmit_configform_form';
        
        $this->dof = $this->_customdata->dof;
        $this->configurator = $this->_customdata->configurator;
        $this->addvars = $this->_customdata->addvars;
        $this->returnurl = $mform->getAttribute('action');
        
        // Добавление раздела настройки источника данных
        $this->add_section_source();
            
        // Добавление раздела настройки маски
        $this->add_section_mask();
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
        
        // Действия
        $actiongroup = [];
        $actiongroup[] = $mform->createElement(
            'submit',
            'action_create_pack',
            $this->dof->get_string('configform_action_create_pack_label', 'transmit', null, 'modlib'),
            ['disabled' => 'disabled']
        );
        $actiongroup[] = $mform->createElement(
            'submit',
            'action_reset',
            $this->dof->get_string('configform_action_reset_label', 'transmit', null, 'modlib')
        );
        $mform->addGroup(
            $actiongroup, 
            'actions'
        );
        $mform->closeHeaderBefore('actions');
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
        // Массив ошибок
        $errors = [];
        
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
            // Получение текущего действия
            $action = key($formdata->actions);
            // Исполнение действия
            if ( method_exists($this, $action) )
            {// Действие найдено
                $this->$action();
            }
        }
    }
    
    /**
     * Возвращает выбранную пользователем маску
     *
     * @return dof_modlib_transmit_strategy_mask_base
     */
    public final function get_mask()
    {
        return $this->configurator->get_current_mask();
    }
    
    /**
     * Возвращает выбранную пользователем маску
     *
     * @return dof_modlib_transmit_source_base
     */
    public final function get_source()
    {
        return $this->configurator->get_current_source();
    }
    
    /**
     * Получение заруженных файлов из указанного поля
     *
     * Прокси-метод для доступа к защищенному методу
     * 
     * @return array
     */
    public final function get_draft_files_shell($element)
    {
        return $this->get_draft_files($element);
    }
    
    /**
     * Возвращает сформированный массив данных
     * 
     * Прокси-метод для доступа к защищенному методу
     * 
     * @return array
     */
    public final function dof_get_select_values_shell($result, $bool = false, $field = '', $fields = [])
    {
        return $this->dof_get_select_values($result, $bool, $field, $fields);
    }
    
    /**
     * Создание пакета настроек обмена
     *
     * @return void
     */
    protected function action_create_pack()
    {
        $this->action_simulate();
        // Запуск процесса синхронизации
        $this->configurator->create_pack();
    }
    
    /**
     * Создание пакета настроек обмена
     *
     * @return void
     */
    protected function action_reset()
    {
        // Сброс настроек
        $this->configurator->config_reset();
        redirect($this->returnurl);
    }
    
    /**
     * Возвращает текущий пакет настроек
     *
     * @param bool $create - создавать ли пакет в случае отсутствия
     *
     * @return dof_modlib_transmit_pack
     */
    public final function get_pack($create=false)
    {
        return $this->configurator->get_current_pack($create);
    }
    
    
    public function get_transmit_type()
    {
        return $this->configurator->get_code();
    }
}
