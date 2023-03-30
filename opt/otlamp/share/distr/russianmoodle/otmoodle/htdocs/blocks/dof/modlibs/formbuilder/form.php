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
 * Менеджер построения форм
 * 
 * Класс формы 
 *
 * @package    modlib
 * @subpackage formbuilder
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

global $DOF;
// Подключаем библиотеку форм
$DOF->modlib('widgets')->webform();

class dof_modlib_formbuilder_form extends dof_modlib_widgets_form
{
    /**
     * @var dof_control
     */
    protected $dof;
    
    /**
     * Список зарегистрированных разделов
     * 
     * @var array
     */
    protected $sections = [];
    
    /**
     * Список групп разделов
     * 
     * @var array
     */
    protected $sectiongroups = [];
    
    /**
     * Привязка элементов к разделу
     * 
     * @var array
     */
    protected $sectionelements = [];
    
    /**
     * Состояние разделов
     * 
     * @var array
     */
    protected $sectionstate = [];
    
    /**
     * Реестр добавленное поле - класс поля
     *
     * @var array
     */
    protected $customfields = [];
    
    /**
     * Текущая активная группа разделов
     * 
     * @var string
     */
    private $activesectiongroup = null;
    
    /**
     * Получение идентификатора формы
     */
    protected function get_form_identifier() {
        
        return preg_replace('/[^a-z0-9_]/i', '_', $this->_customdata->formname);
    }
    
    /**
     * Получение группы раздела по имени раздела
     */
    protected function get_sectiongroup($sectionname)
    {
        foreach ( $this->sectiongroups as $groupcode => $sectioncodes )
        {
            if ( isset($sectioncodes[$sectionname]) )
            {
                return $groupcode;
            }
        }
        return  null;
    }
    
    /**
     * Метод добавления базовых полей в инициализированный раздел
     * 
     * @param string $sectioncode - Код раздела
     * 
     * @return void
     */
    protected function section_basefields($sectioncode)
    {
    }
    
    /**
     * Расширенная валидация дополнительного поля
     * @param unknown $elementcode
     * @param unknown $customfield
     * @param unknown $formvalue
     * @param unknown $fielderrors
     */
    protected function additionalvalidation_customfield($elementcode, $customfield, $formvalue, &$fielderrors)
    {
    }
    
    public function definition()
    {
        // Cоздаем ссылку на HTML_QuickForm
        $mform =& $this->_form;
        
        // Добавление атрибутов формы
        $formattributes = $mform->getAttributes();
        $formattributes['class'] = $formattributes['class'].' dof_formbuilder';
        $mform->setAttributes($formattributes);
        
        // Идентификатор текущей формы
        $formid = $mform->getAttribute('id');
        
        // Добавление свойств
        $this->dof = $this->_customdata->dof;
        // Добавление таблицы стилей плагина
        $this->dof->modlib('nvg')->add_css('modlib', 'formbuilder', '/style.css');
        // Добавление JS поддержки
        $this->dof->modlib('nvg')->add_js('modlib', 'formbuilder', '/script.js');
        
        // Добавление якоря для конечного формирования панели вкладок
        $mform->addElement('hidden', '__anchor_tabs', null);
        $mform->setType('__anchor_tabs', PARAM_BOOL);
        
        // Скрытые поля
        $mform->addElement('hidden', 'sesskey', sesskey());
        $mform->setType('sesskey', PARAM_ALPHANUM);
        
        // Багфикс рендера формы
        $mform->addElement(
            'text',
            '__fixanchor',
            '',
            ['form' => $formid, 'class' => 'formanchor']
        );
        $mform->setType('__fixanchor', PARAM_BOOL);
    }
    
    /**
     * Обработать пришедшие из формы данные
     *
     * @return bool
     */
    public function validation($data, $files)
    {
        // Cоздаем ссылку на HTML_QuickForm
        $mform =& $this->_form;
        
        // Базовая валидация
        $errors = parent::validation($data, $files);
        
        // Формирование списка элементов для валидации
        $submittedelements = [];
        foreach ( $this->sections as $sectioncode => $sectionname )
        {
            $submitname = 'submit__'.$sectioncode;
            if ( isset($data[$submitname]) )
            {// Найдена подтвержденная вкладка
                    
                // Получение группы раздела, в котором была отправлена кнопка
                $sectiongroup = $this->get_sectiongroup($sectioncode);
                // Получение всех разделов группы
                $groupsections = (array)$this->sectiongroups[$sectiongroup];
                    
                // Формирование списка элементов целевой группы разделов
                foreach ( $groupsections as $sectioncode )
                {
                    $submittedelements = array_merge($submittedelements, $this->sectionelements[$sectioncode]);
                }
            }
        }
        
        // Валидация элементов целевой группы разделов
        foreach ( $submittedelements as $elementcode )
        {
            if ( ! $mform->isElementFrozen($elementcode) )
            {
                if ( isset($this->customfields[$elementcode]) && isset($data[$elementcode]) )
                {// Текущее поле является дополнительным
                
                    // Значение дополнительного поля
                    $formvalue = $data[$elementcode];
                    
                    // Получение дополнительного поля
                    $customfield = $this->customfields[$elementcode];
                    // Получение ID объекта дополнительного поля
                    $exploded = explode('_', $elementcode, 3);
                    $objectid = $exploded[1];
                    // Валидация отправленного значения
                    $fielderrors = (array)$customfield->validate_data($formvalue, $objectid);
                    
                    $this->additionalvalidation_customfield($elementcode, $customfield, $formvalue, $fielderrors);
                    
                    if ( ! empty($fielderrors) )
                    {
                        // Сохранение найденных ошибок валидации
                        $errors[$elementcode] = implode(', ' ,$fielderrors);
                    }
                }
            }
            
        }
        
        return $errors;
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
        
        if ( $this->is_submitted() && confirm_sesskey() )
        {// Форма отправлена
            
            // Сохранение текущего набора правил валидации
            $rules = $mform->_rules;
            
            // Формирование списка элементов для валидации
            $submittedelements = [];
            foreach ( $this->sections as $sectioncode => $sectionname )
            {
                $submitname = 'submit__'.$sectioncode;
                if ( isset($_POST[$submitname]) )
                {// Найдена подтвержденная вкладка
                    
                    // Получение группы раздела, в котором была отправлена кнопка
                    $sectiongroup = $this->get_sectiongroup($sectioncode);
                    $this->activesectiongroup = $sectiongroup;
                    // Получение всех разделов группы
                    $groupsections = (array)$this->sectiongroups[$sectiongroup];
                    
                    // Формирование списка элементов целевой группы разделов
                    foreach ( $groupsections as $sectioncode )
                    {
                        $submittedelements = array_merge($submittedelements, $this->sectionelements[$sectioncode]);
                    }
                    
                    // Сброс всех правил валидации для элементов, которые находятся вне указанной группы разделов
                    foreach ( $mform->_rules as $elementcode => &$rules )
                    {
                        if ( ! isset($submittedelements[$elementcode]) )
                        {// Элемент не находится в активной группе вкладок
                            unset($mform->_rules[$elementcode]);
                        }
                    }
                }
            }
            
            // Валидация данных элементов указанной группы разделов
            if ( $this->is_validated() && $formdata = $this->get_data() )
            {// Валидация пройдена
                
                // Обработка данных элементов указанной группы разделов
                $this->process_sectiongroup($sectiongroup, $submittedelements, $formdata);
            }
            
            // Восстановление полного набора правил валидации
            $mform->_rules = $rules;
        }
    }
    
    /**
     * Обработка элементов активной группы разделов
     * 
     * @param stdClass $formdata
     * @param array $submittedelements
     */
    protected function process_sectiongroup($activegroup, $submittedelements, $formdata)
    {
        // Cоздаем ссылку на HTML_QuickForm
        $mform =& $this->_form;
        
        // Обработка полей в целевой группе разделов
        foreach ( $submittedelements as $elementcode )
        {
            if ( isset($formdata->$elementcode) )
            {// Обработка отправленных данных из указанного поля
                
                if ( ! $mform->isElementFrozen($elementcode) )
                {
                    if ( isset($this->customfields[$elementcode]) )
                    {// Текущее поле является дополнительным
                    
                        // Полцчение дополнительного поля, для которого были получены данные
                        $customfield = $this->customfields[$elementcode];
                        
                        // Получение ID объекта дополнительного поля
                        $exploded = explode('_', $elementcode, 3);
                        $objectid = $exploded[1];
                        
                        // Сохранение данных
                        $customfield->save_data($objectid, $formdata->$elementcode);
                    }
                }
                
            }
        }
    }
    
    /**
     * Рендеринг формы
     */
    public function render()
    {
        // Ссылка на HTML_QuickForm
        $mform =& $this->_form;
        // Идентификатор текущей формы
        $formid = $mform->getAttribute('id');
        
        if ( ! empty($this->sections) )
        {// Найдены вкладки
            
            if ( count($this->sections) > 1 )
            {// Формирование вкладок формы
                $sectionshtml = '';
                
                // Обертка вкладок
                $sectionshtml .= '<ul id="form_list_tabs'.$formid.'" class="nav nav-tabs">';
                
                // Определение активного раздела
                $activesection = null;
                if ( isset($this->activesectiongroup) )
                {// Активный раздел - первый в активной группе
                    reset($this->sectiongroups[$this->activesectiongroup]);
                    $activesection = key($this->sectiongroups[$this->activesectiongroup]);
                } else 
                {// Активный раздел - первый
                    reset($this->sections);
                    $activesection = key($this->sections);
                }
                // Переключатели
                foreach ($this->sections as $sectioncode => $sectionname )
                {
                    // Генерация переключателя
                    $class = '';
                    if ( isset($activesection) && $sectioncode == $activesection )
                    {// Текущий раздел активен
                        $class .= 'active';
                    }
                    
                    $sectionshtml .= '<li class="'.$class.'"><a>'.$sectionname.'<label for="form_list_tab_'.$formid.$sectioncode.'"></label></a></li>';
                }
                
                $sectionshtml .= '</ul>';
                $sectiontrigger = $mform->createElement('html', $sectionshtml);
                
                // Инициализация вкладок
                $mform->insertElementBefore($sectiontrigger, '__anchor_tabs');
            } else 
            {// Установка заголовка формы
                $sectioncode = key($this->sections);
                $sectionname = array_shift($this->sections);
                // Заголовок формы
                $header = $mform->createElement(
                    'header',
                    '__'.$sectioncode,
                    $sectionname
                );
                
                $mform->insertElementBefore($header, '__anchor_tabs');
            }
        }
        
        return parent::render();
    }
    
    /**
     * Добавление нового раздела в форму
     * 
     * @param string $sectionname - Локализованное имя раздела
     * 
     * @return string $sectioncode - Код созданного раздела
     */
    public function add_section($sectionname, $sectiongroup)
    {
        // Ссылка на HTML_QuickForm
        $mform =& $this->_form;
        // Идентификатор текущей формы
        $formid = $mform->getAttribute('id');
        
        // Формирование кода для раздела
        $index = (int)count($this->sections); 
        $sectioncode = 'section_'.$index;
        
        // Флаг первого раздела в форме
        $isfirstsection = ! (bool)count($this->sections);
        
        // Регистрация раздела
        $this->sections[$sectioncode] = $sectionname;
        $this->sectiongroups[(string)$sectiongroup][$sectioncode] = $sectioncode;
        
        // Добавление триггера раздела
        $triggerstate = '';
        if ( $isfirstsection )
        {// Первый раздел в форме
            $triggerstate = 'checked = "checked"';
        }
        $trigger = '<input type="radio" '.$triggerstate.' class="sectiontrigger" name="form_'.$formid.'_trigger" id="form_list_tab_'.$formid.$sectioncode.'" />';
        $sectionwrapper = dof_html_writer::start_div($sectioncode.'_sectionwrapper sectionwrapper');
        
        $mform->addElement('html', $trigger.$sectionwrapper);
        
        // Префикс для якорей текущего раздела
        $prefix = '__anchor_section_'.$sectioncode;
        
        // Добавление начальных рамок раздела
        $mform->addElement('hidden', $prefix.'_start', null);
        $mform->setType($sectioncode.'_start', PARAM_BOOL);
        
        // Багфикс отображения формы
        $mform->addElement(
            'text',
            '__fixanchor'.$sectioncode,
            '',
            ['form' => $formid, 'class' => 'formanchor']
        );
        $mform->setType('__fixanchor'.$sectioncode, PARAM_BOOL);
        
        // Добавление дополнительных полей в раздел
        $this->section_basefields($sectioncode);
        
        // Добавление конечных рамок раздела
        $mform->addElement('hidden', $prefix.'_end', null);
        $mform->setType($sectioncode.'_end', PARAM_BOOL);

        if( empty($this->_customdata->viewonly) )
        {
            // Кнопка подтверждения
            $mform->addElement(
                'submit',
                'submit__'.$sectioncode,
                $this->dof->get_string('formbuilder_save_data', 'formbuilder', null, 'modlib'),
                ['form' => $formid]
            );
        } elseif( ! empty($this->_customdata->editurl) )
        {
            $mform->addElement('static', 'edit_link__'.$sectioncode, '', 
                dof_html_writer::link(
                    $this->_customdata->editurl,
                    $this->dof->get_string('formbuilder_edit_data', 'formbuilder', null, 'modlib')
                ) 
            );
        }
        
        $mform->addElement('html', '</div>');
        
        return $sectioncode;
    }
    
    /**
     * Добавление нового дополнительного поля в раздел формы
     * @param unknown $sectioncode
     * @param unknown $customfield
     * @param unknown $objectid
     */
    public function add_customfield($sectioncode, $customfield, $objectid)
    {
        // Ссылка на HTML_QuickForm
        $mform =& $this->_form;
        
        // Якорь раздела, в который будет добавлено указанное поле
        $anchor = '__anchor_section_'.$sectioncode.'_end';
        
        // Добавление поля в форму
        $cfpcode = $customfield->get_customfield()->linkpcode;
        $options['prefix'] = $cfpcode.'_'.$objectid.'_';
        if ( ! empty($this->_customdata->viewonly) )
        {
            $fieldname = $customfield->render_element($mform, $objectid, $anchor, $options);
        } else
        {
            $fieldname = $customfield->create_element($mform, $objectid, $anchor, $options);
        }
        if ( $fieldname )
        {// Поле создано
            // Регистрация добавленного дополнительного поля в форме
            $this->customfields[$fieldname] = $customfield;
            $this->sectionelements[$sectioncode][$fieldname] = $fieldname;
        }
        
        return $fieldname;
    }
}
?>