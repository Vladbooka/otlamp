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
 * Класс дополнительного поля типа select, выпадающий список
 *
 * @package    modlib
 * @subpackage formbuilder
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class dof_customfields_select extends dof_customfields_base
{
    /**
     * Создание элемента для формы
     *
     * @param MoodleQuickForm $mform - объект формы
     * @param int $objectid - идентификатор редактируемого объекта
     * @param array $options - массив с опциями
     *            ['prefix'] - префикс, который необходимо добавить к наименованию элемента формы
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

        // Получение доступных значений выпадающего списка
        $selectoptions = $this->get_selectoptions();
        $additionaloptions = $this->get_additional_options();
        $selectattrs = [];
        $multiple = ! empty($additionaloptions['multiple']);
        if( $multiple )
        {
            $selectattrs['multiple'] = true;
        }

        // Инициализация поля
        if ( empty($beforeelement) )
        {
            $element = $mform->addElement('select', $elementname, $customfield->name, $selectoptions, $selectattrs);
        } else
        {
            $element = $mform->createElement('select', $elementname, $customfield->name, $selectoptions, $selectattrs);
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
                    $this->dof->get_string('validation_error_empty_in_required', 'customfields', null, 'storage'),
                    'required'
                );
            }
        } catch ( dof_storage_customfields_exception $e )
        {// Пользователь не имеет прав на редактирование данных в поле
            // Поле заблокировано
            $mform->freeze($elementname);
        }

        // Установка текущего значения поля
        if ( (int)$objectid > 0 )
        {
            try
            {
                // Получение текущего значения поля для указанного объекта
                $saveddata = $this->get_data((int)$objectid);
                $this->set_default($mform, $elementname, $saveddata, $multiple);
            } catch (dof_storage_customfields_exception $e)
            {// Значение не найдено
                // Установка значения по умолчанию
                if ( isset($customfield->defaultvalue) )
                {
                    $this->set_default($mform, $elementname, $customfield->defaultvalue, $multiple);
                }
            }
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

        // Получение списка доступных значений
        $selectoptions = $this->get_selectoptions();
        if( is_array($formvalue) )
        {
            $selectedvalues = $formvalue;
        } else
        {
            $selectedvalues = [$formvalue];
        }
        foreach($selectedvalues as $selectedvalue)
        {
            if ( ! in_array($selectedvalue, array_keys($selectoptions)) )
            {// Выбранное значение не найдено среди возможных
                $errors[] = $this->dof->
                    get_string('validation_error_value_not_in_list', 'customfields',
                    implode(',', $selectoptions), 'storage'
                );
                break;
            }
        }

        $fieldoptions = $this->get_options();
        $selectoptions = $this->get_selectoptions();
        if ( isset($fieldoptions['emptyvalue']) && isset($selectoptions[$fieldoptions['emptyvalue']]) )
        {// Среди вариантов выпадающего списка есть пустое значение
            if ( $this->is_required() && (string)$formvalue == $fieldoptions['emptyvalue'] )
            {// Выбрано пустое значение выпадающего списка
                $errors[] = $this->dof->
                    get_string('validation_error_empty_in_required', 'customfields', null, 'storage');
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
        if ( is_array($formvalue) )
        {
            return serialize($formvalue);
        } else
        {
            return (string)$formvalue;
        }
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

        $options = $this->get_selectoptions();
        $saveddata = $this->get_data($objectid, 'value', $options);
        if ( $saveddata == serialize(false) || @unserialize($saveddata) !== false )
        {
            $selectedvalues = unserialize($saveddata);
            if( is_array($selectedvalues) )
            {
                $result = [];
                foreach($selectedvalues as $selectedvalue)
                {
                    $result[] = $options[$selectedvalue];
                }
                return implode(', ', $result);
            }
        } else
        {
            if ( array_key_exists($saveddata, $options) )
            {
                return $options[$saveddata];
            }
        }
        return '';
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
     * Получение списка доступных пунктов выпадающего списка
     *
     * @return array
     */
    private function get_selectoptions()
    {
        $selectoptions = [];
        if ( ! empty($this->get_options()['selectoptions']) )
        {// Определены пункты выпадающего списка
            $selectoptions = (array)$this->get_options()['selectoptions'];
        }
        return $selectoptions;
    }

    /**
     * Получение дополнительных настроек
     *
     * @return array
     */
    protected function get_additional_options()
    {
        $result = [];
        $options = $this->get_options();
        if ( ! empty($options['additional_options']) )
        {// Определены параметры отображения текстового поля
            $result = (array)$options['additional_options'];
        }
        return $result;
    }

    /**
     * Получение значения опции выпадающего списка по ее тексту
     *
     * @param string $optiontext - Текст опции
     *
     * @return null|string - Значение опции
     */
    public function get_selectoption($optiontext)
    {
        // Получение опций выпадающего списка
        $selectoptions = $this->get_selectoptions();

        $opton = array_search((string)$optiontext, (array)$selectoptions);
        if ( $opton === false )
        {// Значение не найдено
            return null;
        }
        return $opton;
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

        $customfield = $this->get_customfield();

        // Опции выпадающего списка
        $mform->addElement(
            'textarea',
            'selectoptions',
            $this->dof->get_string('saveform_selectoptions_title', 'formbuilder', null, 'modlib')
        );
        $mform->setType('selectoptions', PARAM_RAW_TRIMMED);
        $form->add_help('selectoptions', 'saveform_selectoptions_help', 'formbuilder', 'modlib');

        // Возможность выбора нескольких значений
        $mform->addElement(
            'checkbox',
            'multiple',
            $this->dof->get_string('saveform_multiple', 'formbuilder', null, 'modlib'),
            ''
        );
        $mform->setType('multiple', PARAM_BOOL);

        // Значение по умолчанию
        $selectoptions = $this->get_selectoptions();
        $additionaloptions = $this->get_additional_options();
        $multiple = ! empty($additionaloptions['multiple']);
        $selectattrs = [];
        if( ! empty($additionaloptions['multiple']) )
        {
            $selectattrs['multiple'] = true;
        }
        $mform->addElement(
            'select',
            'default',
            $this->dof->get_string('saveform_default_title', 'formbuilder', null, 'modlib'),
            $selectoptions,
            $selectattrs
        );

        if ( $customfield )
        {// Шаблон определен

            // Установка значения по умолчанию
            if ( isset($customfield->defaultvalue) )
            {
                $this->set_default($mform, 'default', $customfield->defaultvalue, $multiple);
            }

            // Установка значения по умолчанию
            if ( $multiple )
            {
                $mform->setDefault('multiple', true);
            }

            // Список доступных элементов
            $options = $this->get_options();
            if ( ! empty($options['selectoptions']) )
            {// Указан список
                $textoptions = (string)implode("\n", $options['selectoptions']);
                $mform->setDefault('selectoptions', $textoptions);
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
        if ( isset($formdata->default) )
        {
            if( is_array($formdata->default) )
            {
                $this->customfield->defaultvalue = serialize($formdata->default);
            } else
            {
                $this->customfield->defaultvalue = (string)$formdata->default;
            }
        }

        $additionaloptions = [];
        if ( ! empty($formdata->multiple) )
        {
            $additionaloptions['multiple'] = true;
        }
        // Список доступных элементов
        $this->customfield->options['additional_options'] = $additionaloptions;

        // Список доступных элементов
        $this->customfield->options['selectoptions'] =
            explode("\n", $formdata->selectoptions);
    }

    protected function set_default(&$mform, $elementname, $saveddata, $multiple)
    {

        $selectedvalues = [];

        if ( $saveddata == serialize(false) || @unserialize($saveddata) !== false )
        {
            $savedvalues = unserialize($saveddata);
            if( is_array($savedvalues) )
            {
                $selectedvalues = $savedvalues;
            }
        } else
        {
            $selectedvalues = [$saveddata];
        }

        if( $multiple )
        {
            $mform->setDefault($elementname, $selectedvalues);
        } else
        {
            $mform->setDefault($elementname, array_shift($selectedvalues));
        }
    }
}