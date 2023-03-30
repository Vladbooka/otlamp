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
 * Класс дополнительного поля типа file, загрузка файла
 *
 * @package    modlib
 * @subpackage formbuilder
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class dof_customfields_file extends dof_customfields_base
{
    /**
     * Добавление дополнительного поля на форму
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
        
        // Формирование имени файловой зоны
        $filearea = 'modlib_formbuilder_'.$customfield->departmentid.'_'.$customfield->code;
        
        // Получение опций для файл-менеджера
        $filemanageroptions = $this->get_filemanageroptions();        

        // По умолчанию идентификатор записи файл-менеджера приравнивается к null для создания
        $itemid = null;
        if ((int)$objectid > 0)
        {// Объект редактируется
            try
            {
                // Получение идентификатора записи файл-менеджера из cov
                $itemid = $this->get_data((int)$objectid);
            } catch (dof_storage_customfields_exception $e)
            {
            }
        }
        
        // Инициализация поля
        if ( empty($beforeelement) )
        {
            $element = $mform->addElement(
                'filemanager', $elementname, $customfield->name, null, $filemanageroptions
            );
        } else
        {
            $element = $mform->createElement(
                'filemanager', $elementname, $customfield->name, null, $filemanageroptions
            );
            $mform->insertElementBefore($element, $beforeelement);
        }

        // Добавление поля с данными в форму
        try
        {
            // Проверка прав доступа к полю
            $this->check_access('editdata', $objectid, 0, $customfield->departmentid);
        
            // Дополнительные данные по полю
            if ( $customfield->required )
            {
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
        
        // Получение значения файл-менеджера для установки в элемент формы
        $filemanager = $this->dof->modlib('filestorage')->definion_filemanager($elementname.'_filemanager', $itemid, $filearea, [
            'filemanageroptions' => $filemanageroptions
        ]);
        $mform->setDefault($elementname, $filemanager);
        
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
    protected function make_value($formvalue, $objectid=0, $options = [])
    {
        // Получение данных шалона дополнительного поля
        $customfield = $this->get_customfield();
        
        // Формирование имени элемента формы
        // По умолчанию префикс пустой
        $objectprefix = "";
        if (! empty($options['prefix']))
        {// В качестве опции метод инициализации формы передал префикс
            // Добавление префикса со знаком подчеркивания
            $objectprefix = (string) $options['prefix'] . "_";
        }
        // Формирование имени элемента формы
        $elementname = $objectprefix . $customfield->code;
        

        // По умолчанию идентификатор записи файл-менеджера приравнивается к null для создания
        $itemid = null;
        if ((int) $objectid > 0)
        {// Объект редактируется
            try
            {
                // Получение идентификатора записи файл-менеджера из cov
                $itemid = $this->get_data((int) $objectid);
            } catch (dof_storage_customfields_exception $e)
            {
            }
        }

        // Формирование имени файловой зоны
        $filearea = 'modlib_formbuilder_' . $customfield->departmentid . '_' . $customfield->code;
        
        // Получение опций для файл-менеджера
        $filemanageroptions = $this->get_filemanageroptions();
        
        // Обработчик файлового менеджера
        $processedformvalue = $this->dof->modlib('filestorage')->process_filemanager($elementname . '_filemanager', $formvalue, $itemid, $filearea, [
            'filemanageroptions' => $filemanageroptions
        ]);
        // Значение файл-менеджера для записи в БД
        return (string)$processedformvalue;
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
        
        // Получение данных шалона дополнительного поля
        $customfield = $this->get_customfield();
        
        // Формирование имени файловой зоны
        $filearea = 'modlib_formbuilder_' . $customfield->departmentid . '_' . $customfield->code;
        
        $itemid = $this->get_data($objectid, 'value', $options);
        
        $links = $this->dof->modlib('filestorage')->link_files($itemid, $filearea, [
            'return_array' => true
        ]);
        $html = '';
        foreach($links as $link)
        {
            $html .= dof_html_writer::div($link);
        }
        return $html;
    }
    
    /**
     * Получение списка опций файлового менеджера
     * 
     * @return number[]|boolean[]
     */
    private function get_filemanageroptions()
    {
        $customfield = $this->get_customfield();
        
        $filemanageroptions = ['maxfiles' => 1, 'subdirs' => 0];
        
        $options = $this->get_options();
        if ( isset($options['filemanageroptions']) )
        {// Указаны опции файлменеджера
            $filemanageroptions = $options['filemanageroptions'];
        }
        
        return $filemanageroptions;
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
        
        $fileoptions = [];
        
        // Поддержка подкатегорий
        $mform->addElement(
            'selectyesno',
            'subdirs',
            $this->dof->get_string('saveform_validation_file_subdirs_title', 'formbuilder', null, 'modlib')
        );
        $mform->setDefault('subdirs', 0);
        
        // Максимальное количество файлов
        $mform->addElement(
            'text',
            'maxfiles',
            $this->dof->get_string('saveform_validation_file_maxfiles_title', 'formbuilder', null, 'modlib')
        );
        $mform->setDefault('maxfiles', '1');
        $mform->setType('maxfiles', PARAM_INT);
        $mform->addRule(
            'maxfiles',
            $this->dof->get_string('saveform_validation_file_error_required', 'formbuilder', null, 'modlib'),
            'required'
        );
        
        // Типы файлов
        $filetypes = [
        ];
        // Сбор массива групп типов
        $groups = [
            '' => []
        ];
        foreach ( get_mimetypes_array() as $mimetype => $info )
        {
            if ( empty($info['groups']) )
            {
                $groups[''][$mimetype] = $mimetype;
            } else 
            {
                foreach ( $info['groups'] as $group )
                {
                    $groups[$group][$mimetype] = $mimetype;
                }
            }
        }
        foreach ( $groups as $type => $formats )
        {
            $filetypes[$type] = $type;
        }
        $mform->addElement(
            'dof_multiselect',
            'accepted_types',
            $this->dof->get_string('saveform_validation_file_types_title', 'formbuilder', null, 'modlib'),
            $filetypes
        );
        $mform->setDefault('accepted_types', '');
        
        $customfield = $this->get_customfield();
        if ( $customfield )
        {// Шаблон определен
            $filemanageroptions = $this->get_filemanageroptions();
            
            $mform->setDefault('subdirs', (int)$filemanageroptions['subdirs']);
            $mform->setDefault('maxfiles', (int)$filemanageroptions['maxfiles']);
            if ( ! empty($filemanageroptions['accepted_types']) )
            {
                $mform->setDefault('accepted_types', $filemanageroptions['accepted_types']);
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
        
        $filemanageroptions = [
            'maxfiles' => (int)$formdata->maxfiles, 
            'subdirs' => (int)$formdata->subdirs
        ];
        if ( $filemanageroptions['maxfiles'] < 1 )
        {
            $filemanageroptions['maxfiles'] = 1;
        }
        
        if ( ! empty($formdata->accepted_types) )
        {
            $filemanageroptions['accepted_types'] = $formdata->accepted_types;
        }
        
        $this->customfield->options['filemanageroptions'] = $filemanageroptions;
    }
    
    /**
     * Сопоставление ID сохраненного значения с ID владельца данных
     *
     * В общем случае данные, сохраненные в поле, хранятся вместе с идентификатором владельца
     * (например, текстовые дополнительные поля договора хранятся непосредственно с идентификатором договора)
     * Для ряда полей требуется организовать расшифровку этого идентификатора
     * Например - для файловых полей персоны требуется производить
     * соответствие ITEMID файловой зоны -> ID персоны
     *
     * @param int $itemid - ID хранимого значения
     *
     * @return int ID владельца поля
     */
    public function get_objectid_from_itemid($itemid)
    {
        $customfield = $this->get_customfield();
        if ( ! $customfield )
        {
            return null;
        }
        
        // Поиск владельца, файлы которого находятся в указанной файловой подзоне
        $conditions = [
            'plugintype' => 'storage',
            'plugincode' => $customfield->linkpcode,
            'value' => $itemid,
            'substorage' => 'value',
            'code' => $customfield->code
        ];
        $covs = $this->dof->storage('cov')->get_records_sql(
            'SELECT id, objectid
             FROM {block_dof_s_cov}
             WHERE
                plugintype = :plugintype AND
                plugincode = :plugincode AND
                substorage = :substorage AND
                code = :code AND
                '.$this->dof->storage('cov')->sql_compare_text('value'). ' = ' . $this->dof->storage('cov')->sql_compare_text(':value'),
            $conditions
        );
        // Получение значения поля
        $cov = array_shift($covs);
       
        if ( ! $cov )
        {// Данные не найдены
            return null;
        }
        
        return $cov->objectid;
    }
}