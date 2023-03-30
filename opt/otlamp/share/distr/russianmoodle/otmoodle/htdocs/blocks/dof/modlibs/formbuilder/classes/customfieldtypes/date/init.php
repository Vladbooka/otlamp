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
 * Класс дополнительного поля типа date, выбор даты
 *
 * @package    modlib
 * @subpackage formbuilder
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class dof_customfields_date extends dof_customfields_base
{
    /**
     * Добавление дополнительного поля на форму
     *
     * @param MoodleQuickForm $mform - объект формы
     * @param int $objectid - идентификатор редактируемого объекта
     * @param array $options - массив с опциями
     *            ['prefix'] - префикс, который необходимо добавить к наименованию элемента формы
     *            
     * @return string|null $fieldname - Код добавленного дополнительного поля или NULL
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
        
        // Получение опций поля выбора даты
        $dateoptions = $this->get_dateoptions();
        
        // Инициализация поля
        if ( empty($beforeelement) ) 
        {
            $element = $mform->addElement('dof_date_selector', $elementname, $customfield->name, $dateoptions);
        } else 
        {
            $element = $mform->createElement('dof_date_selector', $elementname, $customfield->name, $dateoptions);
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
        
        } catch ( dof_storage_customfields_exception $e )
        {// Пользователь не имеет прав на редактирование данных в поле
            // Поле заблокировано
            $mform->freeze($elementname);
        }
        
        if ( isset($customfield->defaultvalue) )
        {
            $mform->setDefault($elementname, $customfield->defaultvalue);
        }
        
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
        
        // Получение опций поля выбора даты
        $dateoptions = $this->get_dateoptions();
        
        // Инициализация поля
        if ( empty($beforeelement) ) 
        {
            $element = $mform->addElement(
                'static', 
                $elementname, 
                $customfield->name, 
                $this->render_data($objectid, $dateoptions)
            );
        } else 
        {
            $element = $mform->createElement(
                'static', 
                $elementname, 
                $customfield->name, 
                $this->render_data($objectid, $dateoptions)
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
        
        if ( $this->is_required() && (int)$this->make_value($formvalue) <= 0 )
        {
            $errors[] = $this->dof->
                get_string('validation_error_empty_in_required', 'formbuilder', null, 'modlib');
        }
        
        // Текущие временные интервалы календаря
        $startdate = 0;
        $enddate = 0;
        // Получение опций формирования календаря
        $dateoptions = $this->get_options()['dateoptions'];
        
        // Конечная дата
        if ( isset($dateoptions['stopyear']) )
        {// Указан конечный год временного интервала календаря
            // Получение timestamp последней секунды текущего года
            $departmentid = $this->get_customfield()->departmentid;
            $departmenttimestamp = $this->dof->
                storage('departments')->get_timezone($departmentid);
            $enddate = dof_make_timestamp((int)$dateoptions['stopyear'] + 1, 1, 1, 0, 0, -1, $departmenttimestamp);
            if ( $dateoptions['stopyear'] === 'currentyear' )
            {// Конечный год - текущий
                
                // Получение timestamp последней секунды текущего года
                $enddate = dof_make_timestamp(date('Y') + 1, 1, 1, 0, 0, -1, $departmenttimestamp);
                
                if ( ! empty($dateoptions['offset_stopyear']) )
                {// Указано дополнительное смещение конечной даты
                    $enddate = $enddate + $dateoptions['offset_stopyear'];
                }
            }
        }
        
        // Начальная дата
        if ( isset($dateoptions['startyear']) )
        {// Указан конечный год временного интервала календаря
            // Получение timestamp последней секунды текущего года
            $departmentid = $this->get_customfield()->departmentid;
            $departmenttimestamp = $this->dof->
                storage('departments')->get_timezone($departmentid);
            $startdate = dof_make_timestamp((int)$dateoptions['startyear'], 1, 1, 0, 0, 0, $departmenttimestamp);
            if ( $dateoptions['startyear'] === 'currentyear' )
            {// Конечный год - текущий
                $startdate = dof_make_timestamp(date('Y'), 1, 1, 0, 0, 0, $departmenttimestamp);
        
                if ( ! empty($dateoptions['offset_startyear']) )
                {// Указано дополнительное смещение конечной даты
                    $startdate = $startdate + $dateoptions['offset_startyear'];
                }
            }
        }

        if ( isset($formvalue['timestamp']) )
        {// Валидация выбранной даты
            if ( ! empty($startdate) && $startdate > $formvalue['timestamp'] )
            {// Выбранная дата выходит за лимиты начальной даты
                $errors[] = $this->dof->get_string('error_incorrect_date', 'formbuilder', null, 'modlib');
                return $errors;
            }
            if ( ! empty($enddate) && $enddate < $formvalue['timestamp'] )
            {// Выбранная дата выходит за лимиты конечной даты
                $errors[] = $this->dof->get_string('error_incorrect_date', 'formbuilder', null, 'modlib');
                return $errors;
            }
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
        return (string)dof_make_timestamp(
            $formvalue['year'], $formvalue['month'], $formvalue['day'], 
            $formvalue['hours'], $formvalue['minutes'], $formvalue['seconds'], 
            $formvalue['timezone']
        );
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
            
            $timestamp = $this->get_data($objectid, 'value', $options);
            if( ! empty($options['format']) )
            {
                $format = $options['format'];
            } else
            {
                $format = '';
            }
            return userdate($timestamp, $format);
        } catch ( dof_storage_customfields_exception $e )
        {// Доступ закрыт
            return '';
        }
    }

    /**
     * Получение опций инициализации поля выбора даты
     * 
     * @return array
     */
    private function get_dateoptions()
    {
        $dateoptions = [];
        if ( ! empty($this->get_options()['dateoptions']) )
        {// Определены параметры формирования календаря
            
            $dateoptions = (array)$this->get_options()['dateoptions'];
            if ( $this->is_required() )
            {// Обязательное поле запрещено отключать
                unset($dateoptions['optional']);
            }
        }
        return $dateoptions;
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
        // Базовое определение формы
        parent::saveform_definition($form, $mform);
        
        // Значение по умолчанию
        $dafaultgroup = [];
        $select = [
            0 => $this->dof->get_string('saveform_default_mode_currenttimestamp_title', 'formbuilder', null, 'modlib'),
            1 => $this->dof->get_string('saveform_default_mode_date_title', 'formbuilder', null, 'modlib')
        ];
        $dafaultgroup[] = $mform->createElement(
            'select',
            'default_mode',
            $this->dof->get_string('saveform_default_mode_title', 'formbuilder', null, 'modlib'),
            $select
        );
        $dafaultgroup[] = $mform->createElement(
            'dof_date_selector',
            'default_date',
            $this->dof->get_string('saveform_default_date_title', 'formbuilder', null, 'modlib')
        );
        $mform->disabledIf(
            'default[default_date]',
            'default[default_mode]',
            'eq',
            '0'
        );
        $mform->addGroup(
            $dafaultgroup,
            'default',
            $this->dof->get_string('saveform_default_title', 'formbuilder', null, 'modlib'),
            '',
            true
        );
        
        // Начальный год
        $select = [
            0 => $this->dof->get_string('saveform_option_startyear_mode_disabled_title', 'formbuilder', null, 'modlib'),
            1 => $this->dof->get_string('saveform_option_startyear_mode_current_title', 'formbuilder', null, 'modlib'),
            2 => $this->dof->get_string('saveform_option_startyear_mode_date_title', 'formbuilder', null, 'modlib')
        ];
        $mform->addElement(
            'select',
            'option_startyear_mode',
            $this->dof->get_string('saveform_option_startyear_mode_title', 'formbuilder', null, 'modlib'),
            $select
        );
        $mform->addElement(
            'text',
            'option_startyear_date',
            $this->dof->get_string('saveform_option_startyear_value_title', 'formbuilder', null, 'modlib')
        );
        $mform->addElement(
            'text',
            'option_startyear_offset',
            $this->dof->get_string('saveform_option_startyear_offset_title', 'formbuilder', null, 'modlib')
        );
        $mform->disabledIf(
            'option_startyear_date',
            'option_startyear_mode',
            'neq',
            '2'
        );
        $mform->disabledIf(
            'option_startyear_offset',
            'option_startyear_mode',
            'neq',
            '1'
        );
        $mform->setType('option_startyear_date', PARAM_INT);
        $mform->setType('option_startyear_offset', PARAM_INT);
        
        // Конечный год
        $select = [
            0 => $this->dof->get_string('saveform_option_stopyear_mode_disabled_title', 'formbuilder', null, 'modlib'),
            1 => $this->dof->get_string('saveform_option_stopyear_mode_current_title', 'formbuilder', null, 'modlib'),
            2 => $this->dof->get_string('saveform_option_stopyear_mode_date_title', 'formbuilder', null, 'modlib')
        ];
        $mform->addElement(
            'select',
            'option_stopyear_mode',
            $this->dof->get_string('saveform_option_stopyear_mode_title', 'formbuilder', null, 'modlib'),
            $select
        );
        $mform->addElement(
            'text',
            'option_stopyear_date',
            $this->dof->get_string('saveform_option_stopyear_value_title', 'formbuilder', null, 'modlib')
        );
        $mform->addElement(
            'text',
            'option_stopyear_offset',
            $this->dof->get_string('saveform_option_stopyear_offset_title', 'formbuilder', null, 'modlib')
        );
        $mform->disabledIf(
            'option_stopyear_date',
            'option_stopyear_mode',
            'neq',
            '2'
        );
        $mform->disabledIf(
            'option_stopyear_offset',
            'option_stopyear_mode',
            'neq',
            '1'
        );
        $mform->setType('option_stopyear_date', PARAM_INT);
        $mform->setType('option_stopyear_offset', PARAM_INT);
        
        $customfield = $this->get_customfield();
        if ( $customfield )
        {// Шаблон определен
            if ( $customfield->defaultvalue )
            {// Указано значение по умолчанию
                $mform->setDefault('groupdescription[default_mode]', 1);
                $mform->setDefault('groupdescription[default_date]', $customfield->defaultvalue);
            } else 
            {// Указана текущая дата
                $mform->setDefault('groupdescription[default_mode]', 0);
            }
            
            // Базовые значений конфигурации
            $mform->setDefault('option_startyear_mode', 0);
            $mform->setDefault('option_stopyear_mode', 0);
            
            // Получение опций формирования календаря
            $dateoptions = $this->get_dateoptions();
            
            // Начальная дата
            if ( isset($dateoptions['startyear']) )
            {
                if ( $dateoptions['startyear'] === 'currentyear' )
                {// Текущий год
                    $mform->setDefault('option_startyear_mode', 1);
                    
                    if ( ! empty($dateoptions['offset_startyear']) )
                    {
                        $mform->setDefault('option_startyear_offset', (int)$dateoptions['offset_startyear']);
                    }
                } else
                {
                    $mform->setDefault('option_startyear_mode', 2);
                    $mform->setDefault('option_startyear_date', (int)$dateoptions['startyear']);
                }
            }
            
            // Конечная дата
            if ( isset($dateoptions['stopyear']) )
            {
                if ( $dateoptions['stopyear'] === 'currentyear' )
                {// Текущий год
                    $mform->setDefault('option_stopyear_mode', 1);
                    
                    if ( ! empty($dateoptions['offset_stopyear']) )
                    {
                        $mform->setDefault('option_stopyear_offset', (int)$dateoptions['offset_stopyear']);
                    }
                } else
                {
                    $mform->setDefault('option_stopyear_mode', 2);
                    $mform->setDefault('option_stopyear_date', (int)$dateoptions['stopyear']);
                }
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
     * @return void
     */
    public function saveform_subprocess(dof_modlib_widgets_form &$form, MoodleQuickForm &$mform, $formdata)
    {
        parent::saveform_subprocess($form, $mform, $formdata);
        
        // Значение по умолчанию
        $this->customfield->defaultvalue = '';
        if ( $formdata->default['default_mode'] == 1 )
        {// Указана конкретная дата
            $datefield = $formdata->default['default_date'];
            $this->customfield->defaultvalue = $datefield['timestamp'];
        }
        
        // Начальный год
        $dateoptions = [];
        if ( $formdata->option_startyear_mode == 1 )
        {// Текущий год
            $dateoptions['startyear'] = 'currentyear';
            
            if ( ! empty($formdata->option_startyear_offset) )
            {// Смещение
                $dateoptions['offset_startyear'] = $formdata->option_startyear_offset;
            }
        } elseif ( $formdata->option_startyear_mode == 2 )
        {// Фиксированный год
            if ( ! empty($formdata->option_startyear_date) )
            {// Указан год
                $dateoptions['startyear'] = $formdata->option_startyear_date;
            }
        }
        
        if ( $formdata->option_stopyear_mode == 1 )
        {// Текущий год
            $dateoptions['stopyear'] = 'currentyear';
            
            if ( ! empty($formdata->option_stopyear_offset) )
            {// Смещение
                $dateoptions['offset_stopyear'] = $formdata->option_stopyear_offset;
            }
        } elseif ( $formdata->option_stopyear_mode == 2 )
        {// Фиксированный год
            if ( ! empty($formdata->option_stopyear_date) )
            {// Указан год
                $dateoptions['stopyear'] = $formdata->option_stopyear_date;
            }
        }
        
        $this->customfield->options['dateoptions'] = $dateoptions;
    }
}