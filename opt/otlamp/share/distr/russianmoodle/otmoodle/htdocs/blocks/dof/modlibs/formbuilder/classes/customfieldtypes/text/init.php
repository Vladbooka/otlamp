<?php
////////////////////////////////////////////////////////////////////////////
//                                                                        //
// NOTICE OF COPYRIGHT                                                    //
//                                                                        //
// Dean`s Office for Moodle                                               //
// Электронный деканат                                                    //
// <http://sourceforge.net/projects/freedeansoffice/>                     //
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
 * Класс дополнительного поля типа text, текстовое поле
 *
 * Список дополнительных параметров поля:
 * fieldoptions['validation']['regex'] - Регулярное выражение, которое будет учитываться в ходе валидации поля
 *
 * @package    modlib
 * @subpackage formbuilder
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class dof_customfields_text extends dof_customfields_base
{
    /**
     * Добавление дополнительного поля на форму
     *
     * @param MoodleQuickForm $mform - объект формы
     * @param int $objectid - идентификатор редактируемого объекта
     * @param array $options - массив с опциями
     *            ['prefix'] - префикс, который необходимо добавить к наименованию элемента формы
     *            ['type']   - Тип данных
     *            
     * @return string $fieldname - Код добавленного дополнительного поля
     */
    public function create_element(&$mform, $objectid, $beforeelement = null, $options = [])
    {
        // Получение данных по дополнительному полю
        $customfield = $this->get_customfield();
        
        try
        {
            // Проверка прав доступа на просмотр данных
            $this->check_access('viewdata', $objectid, 0, $customfield->departmentid);
        } catch ( dof_storage_customfields_exception $e )
        {// Пользователь не имеет прав на просмотр данных в поле
            return null;
        }
        
        // Получение имени поля формы
        $prefix = '';
        if ( ! empty($options['prefix']) )
        {
            $prefix = (string)$options['prefix'].'_';
        }
        $elementname = $prefix.$customfield->code;
        
        // Получение типа данных поля
        $elementtype = PARAM_RAW;
        if ( ! empty($options['type']) )
        {
            $elementtype = $options['type'];
        }

        // Получение опций текстового поля
        $textoptions = $this->get_textoptions();
        
        // Элемент - обычное текстовое поле
        $elementcode = 'text';
        $elementattrs = '';
        if( ! empty($textoptions['multiline']) )
        {
            $elementcode = 'textarea';
            if( ! empty($textoptions['cols']) )
            {
                $elementattrs .= ' cols="'.(int)$textoptions['cols'].'"';
            }
            if( ! empty($textoptions['rows']) )
            {
                $elementattrs .= ' rows="'.(int)$textoptions['rows'].'"';
            }
        } else
        {
            if( ! empty($textoptions['cols']) )
            {
                $elementattrs .= ' size="'.(int)$textoptions['cols'].'"';
            }
        }
        // Инициализация поля
        if ( empty($beforeelement) )
        {
            $element = $mform->addElement($elementcode, $elementname, $customfield->name, $elementattrs);
        } else
        {
            $element = $mform->createElement($elementcode, $elementname, $customfield->name, $elementattrs);
            $mform->insertElementBefore($element, $beforeelement);
        }
        
        // Добавление поля с данными в форму
        try 
        {
            // Проверка прав доступа к полю
            $this->check_access('editdata', $objectid, 0, $customfield->departmentid);
            
            // Дополнительные данные по полю
            if ( $customfield->required )
            {// Поле является обязательным
                $mform->addRule(
                    $elementname,
                    $this->dof->get_string('validation_error_empty_in_required', 'formbuilder', null, 'modlib'),
                    'required'
                );
            }
            
            // Получение правил валидации поля
            if ( ! empty($this->get_options()['validation']['regex']) )
            {// Указано решуляроне выражение для валидации поля
                
                // Добавление валидации по регулярному выражению
                $mform->addRule(
                    $elementname,
                    $this->dof->get_string('validation_error_text_invalid_regex', 'formbuilder', null, 'modlib'),
                    'regex',
                    (string)$this->get_options()['validation']['regex'],
                    'server'
                );
            }
        } catch ( dof_storage_customfields_exception $e )
        {// Пользователь не имеет прав на редактирование данных в поле
            // Поле заблокировано
            $mform->freeze($elementname);
        }
        
        // Установка типа данных поля
        $mform->setType($elementname, $elementtype);
        
        // Установка текущего значения поля
        if ( (int)$objectid > 0 )
        {
            try
            {
                // Получение текущего значения поля для указанного объекта
                $covvalue = $this->get_data((int)$objectid);
                $mform->setDefault($elementname, $covvalue);
            } catch (dof_storage_customfields_exception $e)
            {// Значение не найдено
                // Установка значения по умолчанию
                if ( isset($customfield->defaultvalue) )
                {
                    $mform->setDefault($elementname, $customfield->defaultvalue);
                }
            }
        }
        
        return $element->getName();
    }

    /**
     * Получение опций инициализации текстового поля
     * 
     * @return array
     */
    private function get_textoptions()
    {
        $result = [];
        $options = $this->get_options();
        if ( ! empty($options['textoptions']) )
        {// Определены параметры отображения текстового поля
            $result = (array)$options['textoptions'];
        }
        return $result;
    }
    
    /**
     * Добавление дополнительного поля на форму в режиме просмотра
     *
     * @param MoodleQuickForm $mform - объект формы
     * @param int $objectid - идентификатор редактируемого объекта
     * @param array $options - массив с опциями
     *            ['prefix'] - префикс, который необходимо добавить к наименованию элемента формы
     *            
     * @return string|null $fieldname - Код добавленного дополнительного поля или NULL
     */
    public function render_element(&$mform, $objectid, $beforeelement = null, $options = [])
    {
        // Получение данных по дополнительному полю
        $customfield = $this->get_customfield();
        try
        {
            // Проверка прав доступа на просмотр данных
            $this->check_access('viewdata', $objectid, 0, $customfield->departmentid);
        } catch ( dof_storage_customfields_exception $e )
        {// Пользователь не имеет прав на просмотр данных в поле
            return null;
        }
        
        // Получение имени поля формы
        $prefix = '';
        if ( ! empty($options['prefix']) )
        {
            $prefix = (string)$options['prefix'].'_';
        }
        $elementname = $prefix.$customfield->code;
        
        // Инициализация поля
        if ( empty($beforeelement) ) 
        {
            $element = $mform->addElement(
                'static', 
                $elementname, 
                $customfield->name, 
                $this->render_data($objectid)
            );
        } else 
        {
            $element = $mform->createElement(
                'static', 
                $elementname, 
                $customfield->name, 
                $this->render_data($objectid)
            );
            $mform->insertElementBefore($element, $beforeelement);
        }
        
        return $element->getName();
    }
    
    /**
     * Валидация данных, пришедших из формы, которую необходимо выполнять перед сохранением
     *
     * @param mixed $formvalue - Значение, полученное из формы
     *            
     * @return $errors - Массив ошибок, полученных при валидации поля
     */
    public function validate_data($formvalue, $objectid)
    {
        // Базовая валидация
        $errors = parent::validate_data($formvalue, $objectid);
        
        try
        {
            // Проверка прав доступа к полю
            $this->check_access('editdata', $objectid, 0, $this->customfield->departmentid);
        } catch ( dof_storage_customfields_exception $e )
        {// Доступ закрыт
            $errors[] = (string)$e->getMessage();
        }
        
        // Валидация обязательного заполнения поля
        $customfield = $this->get_customfield();
        if ( $this->is_required() && trim((string)$formvalue) == '' )
        {// Поле не заполнено
            $errors[] = $this->dof->get_string('validation_error_empty_in_required', 'formbuilder', null, 'modlib');
        }
        
        return $errors;
    }

    /**
     * Преобразование значения, возвращаемого формой в значение для хранения в БД
     *
     * @param mixed $formvalue - значение, возвращаемое формой
     * @param int $objectid - идентифиатор объекта
     * @param array $options - дополнительные опции
     *
     * @return string значение для хранения в БД
     */
    protected function make_value($formvalue, $objectid = 0, $options = [])
    {
        return (string)$formvalue;
    }
    
    /**
     * Преобразование значения, хранящегося в БД в значение для отображения в форме
     *
     * @param int $objectid - идентифиатор объекта
     * @param array $options - дополнительные опции
     *
     * @return string значение для хранения в БД
     */
    public function render_data($objectid, $options = [])
    {
        try
        {
            // Проверка прав доступа к полю
            $this->check_access('viewdata', $objectid);
            
        } catch ( dof_storage_customfields_exception $e )
        {// Доступ закрыт
            return '';
        }
        
        // Получение текущего значения допполя
        $text = (string)$this->get_data($objectid, 'value', $options);
        return nl2br($text);
    }
    
    /**
     * Заполнение формы сохранения дополнительного поля базовыми данными
     *
     * @param dof_modlib_widgets_form $form - Форма сохранения поля
     * @param MoodleQuickForm $mform - Контроллер формы
     *
     * @return void
     */
    public function saveform_definition(dof_modlib_widgets_form &$form, MoodleQuickForm &$mform)
    {
        // Базовое заполнение формы
        parent::saveform_definition($form, $mform);
        
        // Значение по умолчанию
        $mform->addElement(
            'text',
            'default',
            $this->dof->get_string('saveform_default_text_title', 'formbuilder', null, 'modlib')
        );
        $mform->setType('default', PARAM_RAW_TRIMMED);
        
        // Ширина поля в символах
        $mform->addElement(
            'text',
            'cols',
            $this->dof->get_string('saveform_cols', 'formbuilder', null, 'modlib')
        );
        $mform->setType('cols', PARAM_INT);
        
        // Отображение поля в многострочном режиме
        $mform->addElement(
            'checkbox',
            'multiline',
            $this->dof->get_string('saveform_multiline', 'formbuilder', null, 'modlib'),
            ''
        );
        $mform->setType('multiline', PARAM_BOOL);
        
        
        // Высота поля в строках
        $mform->addElement(
            'text',
            'rows',
            $this->dof->get_string('saveform_rows', 'formbuilder', null, 'modlib')
        );
        $mform->setType('rows', PARAM_INT);
        $mform->disabledIf(
            'rows',
            'multiline',
            'notchecked'
        );
        
        // Тип данных в поле
        $validationgroup = [];
        $datatype = [
            '' => $this->dof->get_string('saveform_validation_text_disabled_title', 'formbuilder', null, 'modlib'),
            '/^\d+$/' => $this->dof->get_string('saveform_validation_text_onlydigits_title', 'formbuilder', null, 'modlib'),
            'custom' => $this->dof->get_string('saveform_validation_text_customregex_title', 'formbuilder', null, 'modlib')
        ];
        $validationgroup[] = $mform->createElement(
            'select',
            'mode',
            $this->dof->get_string('saveform_validation_text_mode_title', 'formbuilder', null, 'modlib'),
            $datatype
        );
        $validationgroup[] = $mform->createElement(
            'text',
            'custom',
            $this->dof->get_string('saveform_validation_text_custom_title', 'formbuilder', null, 'modlib')
        );
        $mform->addGroup(
            $validationgroup,
            'validation',
            $this->dof->get_string('saveform_validation_text_title', 'formbuilder', null, 'modlib'),
            '',
            true
        );
        $mform->disabledIf(
            'validation[custom]',
            'validation[mode]',
            'neq',
            'custom'
        );
        $mform->setType('validation[custom]', PARAM_RAW_TRIMMED);
        
        $customfield = $this->get_customfield();
        if ( $customfield )
        {// Шаблон определен
            // Значение по умолчанию
            $mform->setDefault('default', $customfield->defaultvalue);
           
            // Тип данных в поле
            $options = $this->get_options();
            $mform->setDefault('validation[mode]', '');
            $mform->setDefault('validation[custom]', '');
            if ( ! empty($options['validation']['regex']) )
            {// Указано регуляроне выражение
                
                if ( ! empty($datatype[$options['validation']['regex']]) )
                {// Стандартное регулярное выражение 
                    $mform->setDefault('validation[mode]', $options['validation']['regex']);
                } else 
                {// Нестандартное регулярное выражение
                    $mform->setDefault('validation[mode]', 'custom');
                    $mform->setDefault('validation[custom]', $options['validation']['regex']);
                }  
            }
        }

        // Получение опций формирования календаря
        $textoptions = $this->get_textoptions();
        $mform->setDefault('cols', 20);
        $mform->setDefault('multiline', false);
        $mform->setDefault('rows', 2);
        if( isset($textoptions['cols']) && (int)$textoptions['cols'] >= 20 )
        {
            $mform->setDefault('cols', (int)$textoptions['cols']);
        }
        if( ! empty($textoptions['multiline']) )
        {
            $mform->setDefault('multiline', true);
            if( isset($textoptions['rows']) && (int)$textoptions['rows'] >= 2 )
            {
                $mform->setDefault('rows', (int)$textoptions['rows']);
            }
        }
    }
    
    /**
     * Сохранение данных формы
     *
     * @param dof_modlib_widgets_form $form - Форма настройки
     * @param MoodleQuickForm $mform - Контроллер формы
     * @param stdClass $formdata - Данные формы
     *
     * @return int - ID дополнительного поля
     */
    public function saveform_subprocess(dof_modlib_widgets_form &$form, MoodleQuickForm &$mform, $formdata)
    {
        parent::saveform_subprocess($form, $mform, $formdata);
        
        // Значение по умолчанию
        $this->customfield->defaultvalue = (string)$formdata->default;
        
        // Валидация
        if ( $formdata->validation['mode'] )
        {// Указано регулярное выражение
            $this->customfield->options['validation']['regex'] = $formdata->validation['mode'];
        }
        if ( $formdata->validation['mode'] == 'custom' )
        {
            if ( ! empty($formdata->validation['custom']) )
            {
                $this->customfield->options['validation']['regex'] = $formdata->validation['custom'];
            }
        }
        
        $textoptions = [
            'multiline' => false,
            'cols' => 20,
            'rows' => 2
        ]; 

        if ( (int)$formdata->cols >= 20 )
        {
            $textoptions['cols'] = (int)$formdata->cols;
        }
        if ( ! empty($formdata->multiline) )
        {
            $textoptions['multiline'] = true;
            if ( (int)$formdata->rows >= 2 )
            {
                $textoptions['rows'] = (int)$formdata->rows;
            }
        }
        $this->customfield->options['textoptions'] = $textoptions;
    }
}