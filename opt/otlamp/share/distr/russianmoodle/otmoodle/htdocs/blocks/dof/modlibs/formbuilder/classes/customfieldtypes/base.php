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
 * Абстрактный класс дополнительных полей Деканата
 *
 * @package    modlib
 * @subpackage formbuilder
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
abstract class dof_customfields_base
{
    /**
     * Объект контроллера Деканата
     *
     * @var dof_control
     */
    protected $dof;

    /**
     * Объект дополнительного поля из Справочника
     *
     * @var stdClass
     */
    protected $customfield = null;

    /**
     * Дополнительные опции
     *
     * @var array
     */
    protected $options = [];

    /**
     * Получение типа дополнительного поля
     *
     * @return string - Тип дополнительного поля
     */
    public static function type()
    {
        $classname = get_called_class();
        $classname = str_replace('dof_customfields_', '', $classname);
        return $classname;
    }

    /**
     * Получить локализованное название типа дополнительного поля
     *
     * @return string
     */
    public static function get_localized_type()
    {
        global $DOF;
        return $DOF->get_string(get_called_class(), 'customfields', null, 'storage');
    }

    /**
     * Конструктор класса
     *
     * @param dof_control $dof - Объект контроллера Деканата
     * @param null|stdClass $customfield - Объект дополнительного поля
     */
    public function __construct(dof_control $dof, $customfield = null)
    {
        // Сохранение ссылки на DOF
        $this->dof = $dof;

        if ( $customfield !== null )
        {// Связь с указанным дополнительным полем

            try
            {
                // Нормализация переданных данных
                $this->customfield = $customfield;
            } catch ( dof_exception_dml $e )
            {// Ошибка во время нормализации данных
                throw new dof_storage_customfields_exception($e->errorcode);
            }
        }
    }

    /**
     * Получить связанное дополнительное поле Справочника
     *
     * @return null|stdClass - Данные дополнительного поля
     */
    public function get_customfield()
    {
        return $this->customfield;
    }

    /**
     * Получить состояния обязательности заполнения поля
     *
     * @return bool
     */
    public function is_required()
    {
        // Получение данных поля
        $customfield = $this->get_customfield();
        if ( ! empty($customfield->required) )
        {// Поле обязательно
            return true;
        }
        return false;
    }

    /**
     * Получить конфигурацию дополнительного поля
     *
     * @return array - Массив с данными конфигурации
     */
    public function get_options()
    {
        // Получение данных поля
        $customfield = $this->get_customfield();
        if ( ! empty($customfield->options) )
        {// Определены дополнительные данные по полю
            // Десериализация данных
            return (array)unserialize((string)$customfield->options);
        }
        return [];
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
        // Код поля
        $mform->addElement(
            'text',
            'code',
            $this->dof->get_string('saveform_code_title', 'formbuilder', null, 'modlib')
        );
        $mform->setType('code', PARAM_ALPHANUM);

        // Имя поля
        $mform->addElement(
            'text',
            'name',
            $this->dof->get_string('saveform_name_title', 'formbuilder', null, 'modlib')
        );
        $mform->setType('name', PARAM_RAW_TRIMMED);

        // Прилинкованный справочник
        $storages = (array)$this->dof->plugin_list('storage');
        foreach ( $storages as $code => &$storage )
        {// Локализация имен справочников
            if ( $this->dof->plugin_files_exists('storages', $code) )
            {
                $storage = $this->dof->get_string('title', $code, null, 'storage');
            } else
            {
                $storage = $code;
            }
        }
        $mform->addElement(
            'select',
            'linkpcode',
            $this->dof->get_string('saveform_linkpcode_title', 'formbuilder', null, 'modlib'),
            $storages
        );

        // Описание поля
        $mform->addElement(
            'editor',
            'description',
            $this->dof->get_string('saveform_description_title', 'formbuilder', null, 'modlib')
        );
        $mform->setType('description', PARAM_RAW_TRIMMED);

        // Флаг обязательности поля
        $mform->addElement(
            'selectyesno',
            'required',
            $this->dof->get_string('saveform_required_title', 'formbuilder', null, 'modlib'),
            0
        );

        // Флаг модерируемости поля
        $mform->addElement(
            'selectyesno',
            'moderation',
            $this->dof->get_string('saveform_moderation_title', 'formbuilder', null, 'modlib'),
            0
        );

        // Заполнение формы
        $customfield = $this->get_customfield();
        if ( $customfield )
        {
            // Заполнение полей данными
            $mform->setDefault('code', $customfield->code);
            $mform->setDefault('name', $customfield->name);
            $mform->setDefault('description', [
                'format' => FORMAT_HTML,
                'text' => $customfield->description
            ]);
            $mform->setDefault('linkpcode', $customfield->linkpcode);
            $mform->setDefault('required', (int)$customfield->required);
            $mform->setDefault('moderation', (int)$customfield->moderation);
        }
    }

    /**
     * Валидация формы сохранения дополнительного поля
     *
     * @param dof_modlib_widgets_form $form - Форма сохранения поля
     * @param MoodleQuickForm $mform - Контроллер формы
     * @param array $errors - Имеющиеся ошибки валидации
     * @param array $data - Данные формы
     * @param array $files - Загруженные в форму файлы
     *
     * @return void
     */
    public function saveform_validation(dof_modlib_widgets_form &$form, MoodleQuickForm &$mform, &$errors, $data, $files)
    {
        // Код
        $code = trim($data['code']);
        if ( $code == '' )
        {// Код поля не указан
            $errors['code'] = $this->dof->get_string('saveform_code_error_empty', 'formbuilder', null, 'modlib');
        }

        // Проверка уникальности кода
        $statuses = $this->dof->workflow('customfields')->get_meta_list('real');
        $customfields = (array)$this->dof->storage('customfields')->get_records([
            'departmentid' => $data['departmentid'],
            'code' => $code,
            'status' => array_keys($statuses)
        ]);
        unset($customfields[(string)$data['id']]);
        if ( ! empty($customfields) )
        {// Поле не уникально
            $errors['code'] = $this->dof->get_string('saveform_code_error_notunique', 'formbuilder', null, 'modlib');
        }

        // Имя
        $name = trim($data['name']);
        if ( $name == '' )
        {// Имя поля не указано
            $errors['name'] = $this->dof->get_string('saveform_name_error_empty', 'formbuilder', null, 'modlib');
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
    public final function saveform_process(dof_modlib_widgets_form &$form, MoodleQuickForm &$mform, $formdata)
    {
        if ( $this->customfield == null )
        {// Инициализация базовых данных
            $this->customfield = new stdClass();
        }

        // Общие данные дополнительного поля
        $this->customfield->name = $formdata->name;
        $this->customfield->code = $formdata->code;
        $this->customfield->type = $this->type();
        $this->customfield->departmentid = $formdata->departmentid;
        $this->customfield->linkpcode = $formdata->linkpcode;
        $this->customfield->description = $formdata->description['text'];
        $this->customfield->required = $formdata->required;
        $this->customfield->moderation = $formdata->moderation;

        // Данные, определяемые отдельным типом поля
        $this->saveform_subprocess($form, $mform, $formdata);

        // Запаковка конфигурации поля
        $this->customfield->options = serialize($this->customfield->options);

        // Сохранение дополнительного поля
        $result = $this->save();
        if ( is_int($result) )
        {// Добавление идентификатора поля
            $this->customfield->id = $result;
        }
        return $this->customfield->id;
    }

    /**
     * Дозаполнение объекта для сохранения данных формы
     *
     * @param dof_modlib_widgets_form $form - Форма настройки
     * @param MoodleQuickForm $mform - Контроллер формы
     * @param stdClass $formdata - Данные формы
     */
    public function saveform_subprocess(dof_modlib_widgets_form &$form, MoodleQuickForm &$mform, $formdata)
    {
        $this->customfield->defaultvalue = '';
        $this->customfield->options = [];
    }

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
        return null;
    }

    /**
     * Добавление дополнительного поля на форму в режиме просмотра
     *
     * @param MoodleQuickForm $mform - объект формы
     * @param int $objectid - идентификатор редактируемого объекта
     * @param array $options - массив с опциями
     *            ['prefix'] - префикс, который необходимо добавить к наименованию элемента формы
     *
     * @return string $fieldname - Код добавленного дополнительного поля
     */
    public function render_element(&$mform, $objectid, $beforeelement = null, $options = [])
    {
        return null;
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
        return [];
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
        return '';
    }

    /**
     * Формирование отображения данных, хранимых в cov
     *
     * @param int $objectid - идентифиатор объекта
     * @param array $options - дополнительные опции
     *
     * @return string значение для хранения в БД
     */
    public function render_data($objectid, $options = [])
    {
        return '';
    }

    /**
     * Сохранение шаблона дополнительных полей
     *
     * @param array $options - Дополнительные опции сохранения
     *
     * @return bool|int - True при обновлении, ID при добавлении
     *
     * @throws dof_storage_customfields_exception
     */
    public function save($options = [])
    {
        // Копия данных для сохранения
        $customfield = fullclone($this->customfield);
        // Управление статусами производится через Workflow
        unset($customfield->status);


        if ( isset($customfield->id) && $customfield->id > 0 )
        {// Обновление записи
            if ( ! $this->dof->storage('customfields')->is_access('edit', $customfield->id))
            {// Доступ для обновления закрыт
                throw new dof_storage_customfields_exception(
                    $this->dof->get_string('access_error_edit', 'customfields', null, 'storage'));
            }

            // Получим запись из БД
            $result = $this->dof->storage('customfields')->save($customfield);
            if ( empty($result) )
            {// Обновление не удалось
                throw new dof_storage_customfields_exception(
                    $this->dof->get_string('error_customfield_record_cannot_be_updated',
                        'customfields', $customfield->id, 'storage'));
            } else
            { // Обновление удалось
                return $customfield->id;
            }
        } else
        {// Создание записи
            if( ! $this->dof->storage('customfields')->is_access('create'))
            {
                throw new dof_storage_customfields_exception(
                    $this->dof->get_string('access_error_create', 'customfields', null, 'storage'));
            }

            if( ! isset($customfield->sortorder) && isset($customfield->departmentid) && isset($customfield->linkpcode) )
            {
                $customfields = $this->dof->storage('customfields')->get_customfields(
                    $customfield->departmentid,
                    ['linkpcode' => $customfield->linkpcode]
                );
                $customfield->sortorder = count($customfields);
            }
            $result = $this->dof->storage('customfields')->save($customfield);
            if (empty($result))
            { // Добавление не удалось
                throw new dof_storage_customfields_exception(
                    $this->dof->get_string('error_customfield_record_cannot_be_inserted',
                        'customfields', $customfield->code, 'storage'));
            } else
            { // Добавление удалось
                return $result;
            }
        }
    }

    /**
     * Сохранение значения дополнительного поля для целевого ID объекта в связанном хранилище
     *
     * @param int $objectid - ID объекта. для которого сохраняются данные
     * @param string $value - Сохраняемое значение
     * @param array $options - Дополнительные опции
     *
     * @return void
     *
     * @throws dof_storage_customfields_exception - В случае ошибок
     */
    public function save_data($objectid, $value, $options = [])
    {
        // Получение данных дополнительного поля
        $customfield = $this->get_customfield();
        if ( $customfield )
        {
            try
            {
                // Проверка прав доступа к полю
                $this->check_access('editdata', $objectid, 0, $this->customfield->departmentid);
            } catch ( dof_storage_customfields_exception $e )
            {// Доступ закрыт
                throw new dof_storage_customfields_exception(
                    $this->dof->get_string('access_error_editdata', 'customfields', null, 'storage')
                    );
            }

            // Подготовка значения для сохранения
            $value = $this->make_value($value, $objectid, $options);

            // Получение текущего значения допполя
            $options = ['notexist_return' => false, 'return' => ['id', 'value']];
            $currentvalue = $this->get_data($objectid, 'value', $options);

            $valuechanged = false;
            if ( $currentvalue === false )
            {// Значение не найдено
                $cov = new stdClass();
                $cov->plugintype = 'storage';
                $cov->plugincode = $customfield->linkpcode;
                $cov->objectid = $objectid;
                $cov->substorage = 'value';
                $cov->code = $customfield->code;
                $cov->value = $value;
                $valuechanged = (bool)$this->dof->storage('cov')->insert($cov);
            } else
            {// Значение найдено
                if ( $currentvalue['value'] != $value )
                {// Обновление значения
                    $record = new stdClass();
                    $record->id = $currentvalue['id'];
                    $record->value = $value;

                    $valuechanged = (bool)$this->dof->storage('cov')->update($record);
                } else
                {
                    $valuechanged = true;
                }
            }

            if ( ! empty($customfield->moderation) && $valuechanged )
            {// Включена модерация для поля и значение было изменено
                // Требуется повторная модерация поля

                // Получение текущего значения флага модерации
                $options = ['notexist_return' => false, 'return' => ['id', 'value']];
                $ismoderated = $this->get_data($objectid, 'moderated', $options);

                if ( ! empty($ismoderated) )
                {// Флаг модерации найден
                    if ( $ismoderated['value'] != 0)
                    {// Сброс флага модерации

                        $record = new stdClass();
                        $record->id = $ismoderated['id'];
                        $record->value = 0;

                        $success = (bool)$this->dof->storage('cov')->update($record);
                        if ( ! $success )
                        {// Обновление флага модерации завершилось с ошибкой
                            throw new dof_storage_customfields_exception(
                                $this->dof->get_string('error_moderation_reset_failed',
                                    'customfields', null, 'storage'));
                        }
                    }
                }
            }

            if ( !$valuechanged )
            {
                throw new dof_storage_customfields_exception('save_data_error');
            }
        }
    }

    /**
     * Получение данных дополнительного поля, сохраненных для ID объекта
     * из связанного с полем хранилища
     *
     * @param int $objectid - ID объекта в привязанном к полю хранилище
     * @param string $name - Тип данных, которые требуется получить
     *                       По умолчанию - сохраненное значение поля value
     * @param array $options - Дополнительные опции
     *          mixed ['notexist_return'] - Значение, которое будет возвращено
     *              в случае, если данные не найдены
     *          array ['return'] - Массив требуемых полей
     *
     * @return mixed - Запрашиваемые данные
     */
    protected function get_data($objectid, $name = 'value', $options = [])
    {
        // Получение данных дополнительного поля
        $customfield = $this->get_customfield();
        if ( $customfield === null )
        {
            return null;
        }

        // Поиск сохраненных данных в COV
        $conditions = [
            'plugintype' => 'storage',
            'plugincode' => $customfield->linkpcode,
            'objectid' => $objectid,
            'substorage' => (string)$name,
            'code' => $customfield->code
        ];
        $data = $this->dof->storage('cov')->get_record($conditions, '*', IGNORE_MULTIPLE);

        if ( ! empty($data) )
        {// Значение найдено
            if ( isset($options['return']) )
            {// Требуется вернуть указанные данные
                $return = [];
                foreach ( (array)$options['return'] as $field )
                {
                    if (  isset($data->{$field} ) )
                    {
                        $return[$field] = $data->{$field};
                    } else
                    {
                        //throw new dof_exception_coding('Field '.$field.' not found in cov table');
                        $return[$field] = $data->{$field};
                    }
                }
                return $return;
            }
            // Возврат значения поля
            return $data->value;
        } else
        {// Значение не найдено

            if ( isset($options['notexist_return']) )
            {// Указано требуемое значение
                return $options['notexist_return'];
            }

            // Установка значения по умолчанию
            return $customfield->defaultvalue;
        }
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
        return $itemid;
    }

    /**
     * Проверка доступа к данным дополнительного поля
     *
     * @param string $do - Проверяемое право
     * @param number $objectid - ID объекта, доступ к значению которого проверяется
     *
     * @return void
     *
     * @throws dof_storage_customfields_exception
     */
    public function check_access($do, $objectid = 0, $personid = 0, $departmentid = 0)
    {
        // Нормализация идентификатора объекта
        if ( $objectid < 1 )
        {
            $objectid = 0;
        }
        // Нормализация идентификатора персоны
        if ( $personid < 1 )
        {
            $personid = $this->dof->storage('persons')->get_by_moodleid_id();
        }
        // Нормализация подразделения
        if ( $departmentid < 1 )
        {
            $departmentid = 0;
        }

        // Получение данных о шаблоне
        $fielddata = $this->get_customfield();
        if ( $fielddata )
        {// Шаблон найден

            $fieldid = null;
            if( ! empty($fielddata->id) )
            {// ID шаблона указан
                $fieldid = $fielddata->id;
            }

            // Право доступа к собственным данным
            $accessowner = false;
            if( (int)$objectid > 0 )
            {// Указан объект, для данных которого требуется проверка прав
                if( method_exists($this->dof->storage($fielddata->linkpcode), 'is_owner') )
                {// Метод проверки владельца объекта найден
                    if( $this->dof->storage($fielddata->linkpcode)->is_owner((int)$objectid, $personid) )
                    {// Текущая персона является владельцем указанного объекта
                        // Право доступа к собственным данным по дополнительному полю
                        $accessowner = $this->dof->storage('customfields')->is_access($do.'/owner', $fieldid, null, $departmentid);
                    }
                }
            }

            if ( $accessowner || $this->dof->storage('customfields')->is_access($do, $fieldid, null, $departmentid) )
            {// Доступ открыт
                return null;
            }
        }

        throw new dof_storage_customfields_exception(
            $this->dof->get_string('access_error_'.$do, 'customfields', null, 'storage')
        );
    }

    public function set_sortorder($sortorder)
    {
        $this->customfield->sortorder = (int)$sortorder;
    }
}
