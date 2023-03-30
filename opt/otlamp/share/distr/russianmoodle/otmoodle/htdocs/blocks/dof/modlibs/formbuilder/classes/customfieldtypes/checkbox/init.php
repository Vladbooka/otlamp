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
 * Класс дополнительного поля типа checkbox
 *
 * @package    modlib
 * @subpackage formbuilder
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class dof_customfields_checkbox extends dof_customfields_base
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
        try {
            // Проверка прав доступа на просмотр данных
            $this->check_access('viewdata', $objectid, 0, $customfield->departmentid);
        } catch ( dof_storage_customfields_exception $e ) {
            // Пользователь не имеет прав на просмотр данных в поле
            return null;
        }
        // Получение имени поля формы
        $prefix = '';
        if (!empty($options['prefix'])) {
            $prefix = (string)$options['prefix'].'_';
        }
        $elementname = $prefix.$customfield->code;
        // текст чекбокса
        if (!isset($customfield->text)) {
            $customfield->text = '';
        }
        // Инициализация поля
        if (empty($beforeelement)) {
            $element = $mform->addElement(
                'advcheckbox', $elementname, $customfield->name, $customfield->text
                );
        } else {
            $element = $mform->createElement(
                'advcheckbox', $elementname, $customfield->name, $customfield->text
                );
            $mform->insertElementBefore($element, $beforeelement);
        }
        // Добавление поля с данными в форму
        try {
            // Проверка прав доступа к полю
            $this->check_access('editdata', $objectid, 0, $customfield->departmentid);

        } catch (dof_storage_customfields_exception $e) {
            // Пользователь не имеет прав на редактирование данных в поле
            // Поле заблокировано
            $mform->freeze($elementname);
        }
        // Установка текущего значения поля
        if ((int)$objectid > 0) {
            try {
                // Получение текущего значения поля для указанного объекта
                $covvalue = $this->get_data((int)$objectid);
                $mform->setDefault($elementname, $covvalue);
            } catch (dof_storage_customfields_exception $e) {
                // Значение не найдено
                // Установка значения по умолчанию
                if (isset($customfield->defaultvalue)) {
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
        try {
            // Проверка прав доступа на просмотр данных
            $this->check_access('viewdata', $objectid, 0, $customfield->departmentid);
        } catch (dof_storage_customfields_exception $e) {
            // Пользователь не имеет прав на просмотр данных в поле
            return null;
        }
        // Получение имени поля формы
        $prefix = '';
        if (!empty($options['prefix'])) {
            $prefix = (string)$options['prefix'].'_';
        }
        $elementname = $prefix.$customfield->code;
        // текст чекбокса
        if (!isset($customfield->text)) {
            $customfield->text = '';
        }
        // Инициализация поля
        if (empty($beforeelement)) {
            $element = $mform->addElement(
                'advcheckbox', $elementname, $customfield->name, $customfield->text
                );      
        } else {
            $element = $mform->createElement(
                'advcheckbox', $elementname, $customfield->name, $customfield->text
                );
            $mform->insertElementBefore($element, $beforeelement); 
        }
        $mform->setDefault($elementname, $this->render_data($objectid));
        $mform->freeze($elementname);
        
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
        try {
            // Проверка прав доступа к полю
            $this->check_access('editdata', $objectid, 0, $this->customfield->departmentid);
        } catch (dof_storage_customfields_exception $e) {
            // Доступ закрыт
            $errors[] = (string)$e->getMessage();
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
        return $formvalue;
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
        try {
            // Проверка прав доступа к полю
            $this->check_access('viewdata', $objectid);
        } catch ( dof_storage_customfields_exception $e ) {
            // Доступ закрыт
            return '';
        }
        // Получение текущего значения допполя
        return $this->get_data($objectid, 'value', $options);
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
        $customfield = $this->get_customfield();
        $mform->addElement(
            'select',
            'default',
            $this->dof->get_string('saveform_default_title', 'formbuilder', null, 'modlib'),
            [0,1]
            );
        // Установка значения по умолчанию
        if (isset($customfield->defaultvalue)) {
            $mform->setDefault('default', $customfield->defaultvalue);
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
        $this->customfield->defaultvalue = $formdata->default;
    }
}