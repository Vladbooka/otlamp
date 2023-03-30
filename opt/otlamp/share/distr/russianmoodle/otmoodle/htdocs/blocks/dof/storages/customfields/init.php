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

require_once $DOF->plugin_path('storage', 'config', '/config_default.php');

/**
 * Справочник дополнительных полей
 *
 * @package    storage
 * @subpackage customfields
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class dof_storage_customfields extends dof_storage implements dof_storage_config_interface
{

    /**
     * Объект деканата для доступа к общим методам
     * 
     * @var dof_control
     */
    protected $dof;
    
    // **********************************************
    //   Методы, предусмотренные интерфейсом plugin
    // **********************************************
    
    /**
     * Метод, реализующий инсталяцию плагина в систему
     * Создает или модифицирует существующие таблицы в БД
     * и заполняет их начальными значениями
     *
     * @return boolean
     */
    public function install()
    {
        if (! parent::install())
        {
            return false;
        }
        return $this->dof->storage('acl')->save_roles($this->type(), $this->code(), 
            $this->acldefault());
    }

    /**
     * Метод, реализующий обновление плагина в системе.
     * Создает или модифицирует существующие таблицы в БД
     *
     * @param string $old_version
     *            - Версия установленного в системе плагина
     *            
     * @return boolean
     */
    public function upgrade($oldversion)
    {
        global $CFG, $DB;
        $result = true;
        // Методы для установки таблиц из xml
        require_once ($CFG->libdir . '/ddllib.php');
        
        $manager = $DB->get_manager();
        $table = new xmldb_table($this->tablename());
        if ( $oldversion < 2017021719 )
        {// Удаляем 
            // Удаление индекс с уникальным департаментом
            $index = new xmldb_index('departmentid', XMLDB_INDEX_UNIQUE, ['departmentid']);
            if ( $manager->index_exists($table, $index))
            {// Индекс есть, удаляем
                $manager->drop_index($table, $index);
            }
        }
        if ( $oldversion < 2017090400 )
        {
            // Замена уникального индекса
            $index = new xmldb_index('fullcode', XMLDB_INDEX_UNIQUE, ['code', 'departmentid']);
            if ( $manager->index_exists($table, $index))
            {// Индекс есть, удаляемs
                $manager->drop_index($table, $index);
            }
            $index = new xmldb_index('fullcode', XMLDB_INDEX_NOTUNIQUE, ['code', 'departmentid']);
            if ( ! $manager->index_exists($table, $index))
            {
                $manager->add_index($table, $index);
            }
        }
        if ( $oldversion < 2017091900 )
        {
            $sortorder = new xmldb_field('sortorder', XMLDB_TYPE_INTEGER, 10, true);
            if( ! $manager->field_exists($table, $sortorder) )
            {
                $manager->add_field($table, $sortorder);
            }
            $index = new xmldb_index('sortorder', XMLDB_INDEX_NOTUNIQUE, ['sortorder']);
            if ( ! $manager->index_exists($table, $index))
            {
                $manager->add_index($table, $index);
            }
        }
        
        return $result &&
             $this->dof->storage('acl')->save_roles($this->type(), $this->code(), 
                $this->acldefault());
    }

    /**
     * Возвращает версию установленного плагина
     *
     * @return int - Версия плагина
     */
    public function version()
    {
        return 2017091900;
    }

    /**
     * Возвращает версии интерфейса Деканата, с которыми этот плагин может работать
     *
     * @return string
     */
    public function compat_dof()
    {
        return 'aquarium';
    }

    /**
     * Возвращает версии стандарта плагина этого типа, которым этот плагин соответствует
     *
     * @return string
     */
    public function compat()
    {
        return 'paradusefish';
    }

    /**
     * Возвращает тип плагина
     *
     * @return string
     */
    public function type()
    {
        return 'storage';
    }

    /**
     * Возвращает короткое имя плагина
     *
     * Оно должно быть уникально среди плагинов этого типа
     *
     * @return string
     */
    public function code()
    {
        return 'customfields';
    }

    /**
     * Возвращает список плагинов, без которых этот плагин работать не может
     *
     * @return array
     */
    public function need_plugins()
    {
        return [
            'storage' => [
                'acl'    => 2016071500,
                'config' => 2012042500
            ]
        ];
    }

    /**
     * Определить, возможна ли установка плагина в текущий момент
     * Эта функция одинакова абсолютно для всех плагинов и не содержит в себе каких-либо зависимостей
     * 
     * @todo УДАЛИТЬ эту функцию при рефакторинге. Вместо нее использовать наследование
     *       от класса dof_modlib_base_plugin
     *      
     * @see dof_modlib_base_plugin::is_setup_possible()
     * @param int $oldversion[optional]
     *            - старая версия плагина в базе (если плагин обновляется)
     *            или 0 если плагин устанавливается
     *            
     * @return bool true - если плагин можно устанавливать
     *         false - если плагин устанавливать нельзя
     */
    public function is_setup_possible($oldversion = 0)
    {
        return dof_is_plugin_setup_possible($this, $oldversion);
    }

    /**
     * Получить список плагинов, которые уже должны быть установлены в системе,
     * и без которых начать установку или обновление невозможно
     *
     * @param int $oldversion[optional]
     *            - старая версия плагина в базе (если плагин обновляется)
     *            или 0 если плагин устанавливается
     *            
     * @return array массив плагинов, необходимых для установки
     *         Формат: array('plugintype'=>array('plugincode' => YYYYMMDD00));
     */
    public function is_setup_possible_list($oldversion = 0)
    {
        return [
            'storage' => [
                'acl'    => 2016071500,
                'config' => 2012042500
            ]
        ];
    }

    /**
     * Список обрабатываемых плагином событий
     *
     * @return array - array(array('plugintype'=>..,'plugincode'=>..,'eventcode'=>..),...)
     */
    public function list_catch_events()
    {
        // Пока событий не обрабатываем
        return [];
    }

    /**
     * Требуется ли запуск cron в плагине
     *
     * @return bool
     */
    public function is_cron()
    {
        // Запуск не требуется
        return false;
    }

    /**
     * Проверяет полномочия на совершение действий
     *
     * @param string $do
     *            - идентификатор действия, которое должно быть совершено
     * @param int $objid
     *            - идентификатор экземпляра объекта,
     *            по отношению к которому это действие должно быть применено
     * @param int $userid
     *            - идентификатор пользователя Moodle, полномочия которого проверяются
     *            
     * @return bool true - можно выполнить указанное действие по
     *         отношению к выбранному объекту
     *         false - доступ запрещен
     */
    public function is_access($do, $objid = NULL, $userid = NULL, $depid = null)
    {
        if ($this->dof->is_access('datamanage') or $this->dof->is_access('admin') or
             $this->dof->is_access('manage'))
        { // Открыть доступ для менеджеров
            return true;
        }
        
        // Получаем ID персоны, с которой связан данный пользователь
        $personid = $this->dof->storage('persons')->get_by_moodleid_id($userid);
        
        // Дополнительные проверки прав
        switch ($do)
        {
            default:
                break;
        }
        
        // Формируем параметры для проверки прав
        $acldata = $this->get_access_parametrs($do, $objid, $personid, $depid);
        // Производим проверку
        if ($this->acl_check_access_parameters($acldata))
        { // Право есть
            return true;
        }
        return false;
    }

    /**
     * Требует наличия полномочия на совершение действий
     *
     * @param string $do
     *            - идентификатор действия, которое должно быть совершено
     * @param int $objid
     *            - идентификатор экземпляра объекта,
     *            по отношению к которому это действие должно быть применено
     * @param int $userid
     *            - идентификатор пользователя Moodle, полномочия которого проверяются
     *            
     * @return bool true - можно выполнить указанное действие по
     *         отношению к выбранному объекту
     *         false - доступ запрещен
     */
    public function require_access($do, $objid = NULL, $userid = NULL, $depid = null)
    {
        if (! $this->is_access($do, $objid, $userid, $depid))
        {
            $notice = "{$this->code()}/{$do} (block/dof/{$this->type()}/{$this->code()}: {$do})";
            if ($objid)
            {
                $notice .= " id={$objid}";
            }
            $this->dof->print_error('nopermissions', '', $notice);
        }
    }

    /**
     * Обработать событие
     *
     * @param string $gentype
     *            - тип модуля, сгенерировавшего событие
     * @param string $gencode
     *            - код модуля, сгенерировавшего событие
     * @param string $eventcode
     *            - код задания
     * @param int $intvar
     *            - дополнительный параметр
     * @param mixed $mixedvar
     *            - дополнительные параметры
     *            
     * @return bool - true в случае выполнения без ошибок
     */
    public function catch_event($gentype, $gencode, $eventcode, $intvar, $mixedvar)
    {
        // Ничего не делаем, но отчитаемся об "успехе"
        return true;
    }

    /**
     * Запустить обработку периодических процессов
     *
     * @param int $loan
     *            - нагрузка (
     *            1 - только срочные,
     *            2 - нормальный режим,
     *            3 - ресурсоемкие операции
     *            )
     * @param int $messages
     *            - количество отображаемых сообщений (
     *            0 - не выводить,
     *            1 - статистика,
     *            2 - индикатор,
     *            3 - детальная диагностика
     *            )
     *            
     * @return bool - true в случае выполнения без ошибок
     */
    public function cron($loan, $messages)
    {
        return true;
    }

    /**
     * Обработать задание, отложенное ранее в связи с его длительностью
     *
     * @param string $code
     *            - код задания
     * @param int $intvar
     *            - дополнительный параметр
     * @param mixed $mixedvar
     *            - дополнительные параметры
     *            
     * @return bool - true в случае выполнения без ошибок
     */
    public function todo($code, $intvar, $mixedvar)
    {
        return true;
    }

    /**
     * Конструктор
     *
     * @param dof_control $dof
     *            - объект с методами ядра деканата
     */
    public function __construct($dof)
    {
        // Сохраняем ссылку на DOF, чтоб вызывать его через $this->dof
        $this->dof = $dof;
    }

    /**
     * Возвращает название таблицы без префикса (mdl_)
     * 
     * @return text
     * @access public
     */
    public function tablename()
    {
        // Имя таблицы, с которой работаем
        return 'block_dof_s_customfields';
    }
    
    // **********************************************
    //       Методы для работы с полномочиями
    // **********************************************
    
    /**
     * Получить список параметров для фунции has_right()
     *
     * @param string $action
     *            - совершаемое действие
     * @param int $objectid
     *            - id объекта над которым совершается действие
     * @param int $personid           
     * 
     * @return stdClass - список параметров для фунции has_right()
     */
    protected function get_access_parametrs($action, $objectid, $personid, $depid = null)
    {
        $result = new stdClass();
        $result->plugintype = $this->type();
        $result->plugincode = $this->code();
        $result->code = $action;
        $result->personid = $personid;
        $result->departmentid = $depid;
        $result->objectid = $objectid;
        
        if (is_null($depid))
        { // Подразделение не задано - ищем в GET/POST
            $result->departmentid = optional_param('departmentid', 0, PARAM_INT);
        }
        if (! $objectid)
        { // Если objectid не указан - установим туда 0 чтобы не было проблем с sql-запросами
            $result->objectid = 0;
        }
        
        return $result;
    }

    /**
     * Проверить права через плагин acl.
     *
     * Функция вынесена сюда, чтобы постоянно не писать
     * длинный вызов и не перечислять все аргументы
     *
     * @param stdClass $acldata
     *            - объект с данными для функции storage/acl->has_right()
     *            
     * @return bool
     */
    protected function acl_check_access_parameters($acldata)
    {
        return $this->dof->storage('acl')->has_right(
            $acldata->plugintype, 
            $acldata->plugincode, 
            $acldata->code, 
            $acldata->personid, 
            $acldata->departmentid, 
            $acldata->objectid
        );
    }

    /**
     * Задаем права доступа для объектов этого хранилища
     *
     * @return array
     */
    public function acldefault()
    {
        $a = [];
        
        $a['create'] = [
            'roles' => [
                'manager'
            ]
        ];
        $a['edit'] = [
            'roles' => [
                'manager'
            ]
        ];
        $a['view'] = [
            'roles' => [
                'manager',
                'methodist',
                'teacher',
                'parent',
                'student'
            ]
        ];
        $a['editdata'] = [
            'roles' => [
                'manager'
            ]
        ];
        $a['editdata/owner'] = [
            'roles' => [
                'manager'
            ]
        ];
        $a['viewdata'] = [
            'roles' => [
                'manager',
                'methodist',
                'teacher',
                'parent',
                'student'
            ]
        ];
        $a['viewdata/owner'] = [
            'roles' => [
                'manager',
                'methodist',
                'teacher',
                'parent',
                'student'
            ]
        ];
        
        return $a;
    }

    /**
     * Функция получения настроек для плагина
     */
    public function config_default($code = null)
    {
        $config = [];
        return $config;
    }

    // **********************************************
    //              Собственные методы
    // **********************************************
    
    /**
     * Получить список плагинов, для которых зарегистрированы дополнительные поля
     *
     * @param array $conditions - Условия отбора элементов
     *
     * @return array - Список кодов плагинов
     */
    public function get_list_linkpcodes($conditions)
    {
        $fields = $this->get_records($conditions, '', 'id, linkpcode');
        $plugincodes = [];
        foreach ( $fields as $field )
        {
            $plugincodes[$field->linkpcode] = 'dof_storage_'.$field->linkpcode;
        }
        unset($fields);
        return $plugincodes;
    }
    
    /**
     * Получить название дополнительного поля
     *
     * @param stdClass|int $item - Дополнительное поле, или ID поля
     *
     * @return string|null
     */
    public function get_name($item)
    {
        if ( isset($item->name) )
        {// Передан объект
            return format_string((string)$item->name);
        }
        
        // Получение данных по ID поля
        $name = $this->get_field((int)$item, 'name');
        if ( $name === false )
        {// Ошибка получения имени
            return null;
        }
        return format_string((string)$name);
    }
    
    /**
     * Получить описание дополнительного поля
     *
     * @param stdClass|int $item - Дополнительное поле, или ID поля
     *
     * @return string|null
     */
    public function get_description($item)
    {
        if ( isset($item->description) )
        {// Передан объект
            return format_text((string)$item->description);
        }
        
        // Получение данных по ID поля
        $description = $this->get_field((int)$item, 'description');
        if ( $description === false )
        {// Ошибка получения имени
            return null;
        }
        return format_text((string)$description);
    }
    
    /**
     * Нормализация данных дополнительного поля
     * 
     * Формирует объект дополнительного поля на основе переданных данных. В случае критической ошибки 
     * или же если данных недостаточно, выбрасывает исключение.
     * 
     * @param stdClass|array $customfielddata - Данные дополнительного поля
     * @param array $options - Опции работы
     * 
     * @return stdClass - Нормализовализованный Объект дополнительного поля
     * 
     * @throws dof_exception_dml - Исключение в случае критической ошибки или же недостаточности данных
     */
    public function normalize($data, $options = [])
    {
        // Нормализация входных данных
        if ( is_object($data) || is_array($data) )
        {// Комплексные данные
            $data = (object)$data;
        } else
        {// Данные не определены
            throw new dof_exception_dml('invalid_data');
        }
       
        // Начало построения дополнительного поля
        $normalized = new stdClass();
        
        // Идентификатор дополнительного поля
        if ( ! empty($data->id) && (int)$data->id > 0 )
        {// Обновление поля
            
            // Проверка валидности идентификатора
            $exists = $this->dof->storage('customfields')->
                is_exists((int)$data->id);
            if ( $exists == true )
            {// Идентификатор валиден
                $normalized->id = (int)$data->id;
            } else
            {// Идентификатор не найден
                throw new dof_storage_customfields_exception_dml('item_not_found', '', '', $data);
            }
        } else 
        {// Создание нового поля
            
            // Шаблонный объект
            $normalized->code = '';
            $normalized->name = '';
            $normalized->description = '';
            $normalized->departmentid = $this->dof->storage('departments')->get_default_id();
            $normalized->linkpcode = '';
            $normalized->type = '';
            $normalized->defaultvalue = '';
            $normalized->required = 0;
            $normalized->moderation = 0;
            $normalized->options = '';
            $normalized->sortorder = 0;
            $normalized->status = '';
        }
            
        // НОРМАЛИЗАЦИЯ ПОЛЕЙ
        // Код дополнительного поля
        if ( isset($data->code) )
        {
            // Фильтрация символов
            $normalized->code = clean_param((string)$data->code, PARAM_ALPHANUM);
            // Корректировка длины
            $normalized->code = substr($normalized->code, 0, 255);
        }
        // Название дополнительного поля
        if ( isset($data->name) )
        {
            // Фильтрация символов
            $normalized->name = clean_param($data->name, PARAM_RAW_TRIMMED);
            // Корректировка длины
            $normalized->name = substr($normalized->name, 0, 255);
        }
        // Описание дополнительного поля
        if ( isset($data->description) )
        {
            $normalized->description = clean_param($data->description, PARAM_RAW_TRIMMED);
        }
        // Подразделение дополнительного поля
        if ( isset($data->departmentid) )
        {
            $normalized->departmentid = (int)$data->departmentid;
        }
        // Справочник дополнительного поля
        if ( isset($data->linkpcode) )
        {
            // Фильтрация символов
            $normalized->linkpcode = clean_param($data->linkpcode, PARAM_ALPHANUM);
            // Корректировка длины
            $normalized->linkpcode = substr($normalized->linkpcode, 0, 255);
        }
        // Тип дополнительного поля
        if ( isset($data->type) )
        {
            // Фильтрация символов
            $normalized->type = clean_param($data->type, PARAM_ALPHA);
            // Корректировка длины
            $normalized->type = substr($normalized->type, 0, 255);
        }
        // Значение по умолчанию дополнительного поля
        if ( isset($data->defaultvalue) )
        {
            // Фильтрация символов
            $normalized->defaultvalue = clean_param((string)$data->defaultvalue, PARAM_RAW_TRIMMED);
        }
        // Значение по умолчанию дополнительного поля
        if ( isset($data->required) )
        {
            $normalized->required = (int)(bool)$data->required;
        }
        // Модерация дополнительного поля
        if ( isset($data->moderation) )
        {
            $normalized->moderation = (int)(bool)$data->moderation;
        }
        // Опции дополнительного поля
        if ( isset($data->options) )
        {
            $normalized->options = trim((string)$data->options);
        }
        // Порядок сортировки
        if ( isset($data->sortorder) )
        {
            $normalized->sortorder = (int)$data->sortorder;
        }
        
        // ВАЛИДАЦИЯ ДАННЫХ
        if ( isset($normalized->code) )
        {// Проверка кода
            
            if ( $normalized->code == '' )
            {// Код не может быть пуст
                throw new dof_storage_customfields_exception_dml('code_empty', '', '', $normalized);
            }
        }
        
        if ( isset($normalized->name) )
        {// Проверка имени
            if ( $normalized->name == '' )
            {// Имя не может быть пустым
                throw new dof_storage_customfields_exception_dml('name_empty', '', '', $normalized);
            }
        }
        
        if ( isset($normalized->departmentid) )
        {// Проверка подразделения
            if ( ! $this->dof->storage('departments')->is_exists((int)$normalized->departmentid) )
            {// Подразделение не найдено
                throw new dof_storage_customfields_exception_dml('department_not_found', '', '', $normalized);
            }
        }
        
        if ( isset($normalized->linkpcode) )
        {// Проверка Справочника
            if ( ! $this->dof->plugin_exists('storage', $normalized->linkpcode) )
            {// Справочник не найден
                throw new dof_storage_customfields_exception_dml('storage_not_found', '', '', $normalized);
            }
        }
        
        if ( isset($normalized->type) )
        {// Проверка типа поля
            if ( $normalized->type == '' )
            {// Тип поля не может быть пустым
                throw new dof_storage_customfields_exception_dml('type_empty', '', '', $normalized);
            }
        }

        return $normalized;
    }
    
    /**
     * Сохранение шаблона дополнительного поля
     * 
     * @param stdClass $customfielddata - Объект с данными шаблона дополнительного поля
     * @param array $options - Массив дополнительных параметров
     * 
     * @return int|bool - ID сохраненного шаблона поля при добавлении, 
     *                    true при успешном обновлении
     * 
     * @throws dof_exception_dml - В случае ошибки
     */
    public function save($customfielddata, $options = [])
    {
        // Нормализация данных
        try {
            $normalized_data = $this->normalize($customfielddata, $options);
        } catch ( dof_exception_dml $e )
        {
            throw new dof_exception_dml('error_save_'.$e->errorcode);
        }
        
        if ( isset($customfielddata->id) && $customfielddata->id > 0 )
        {// Обновление записи
            if( ! $this->dof->storage('customfields')->is_access('edit', $customfielddata->id))
            {// Доступ для обновления закрыт
                throw new dof_storage_customfields_exception(
                    $this->dof->get_string('access_error_edit', 'customfields', null, 'storage'));
            }
        
            // Получим запись из БД
            $oldcustomfield = $this->dof->storage('customfields')->get($customfielddata->id);
            if (empty($oldcustomfield))
            {// Запись не найдена
                throw new dof_storage_customfields_exception(
                    $this->dof->get_string('error_customfield_record_not_exist',
                        'customfields', $customfielddata->id, 'storage'));
            }
        
            // Обновляем запись
            $updateresult = $this->dof->storage('customfields')->update($normalized_data);
            if ( empty($updateresult) )
            {// Обновление не удалось
                throw new dof_storage_customfields_exception(
                    $this->dof->get_string('error_customfield_record_cannot_be_updated',
                        'customfields', $customfielddata->id, 'storage'));
            } else
            {// Обновление удалось
                return $customfielddata->id;
            }
        } else
        {// Создание записи
            if( ! $this->dof->storage('customfields')->is_access('create'))
            {
                throw new dof_storage_customfields_exception(
                    $this->dof->get_string('access_error_create', 'customfields', null, 'storage'));
            }
        
            // Убираем автоматически генерируемые поля
            unset($customfielddata->id);
        
            // Добавляем запись
            $insertresult = $this->dof->storage('customfields')->insert($customfielddata);
            if (empty($insertresult))
            {// Добавление не удалось
                throw new dof_storage_customfields_exception(
                    $this->dof->get_string('error_customfield_record_cannot_be_inserted',
                        'customfields', $customfielddata->code, 'storage'));
            } else
            { // Добавление удалось
                return $insertresult;
            }
        }
    }
    
    /**
     * Получение массива шаблонов дополнительных полей c учетом условий
     * 
     * Итоговый список формируется с учетом дополнительных полей вышестоящего подразделения
     * 
     * @param int $departmentid - ID подразделения, для которого требуется найти поля
     * @param array $options - Массив опций
     *                          ['code'] - Код поля
     *                          ['linkpcode'] - Фильтрация шаблонов по коду хранилища
     *                          ['status'] - Фильтрация по массиву статусов
     *                          
     * @return array - Массив инициализированных экземпляров шаблонов дополнительных полей
     */
    public function get_customfields($departmentid = 0, $options = [])
    {
        // Список дополнительных полей
        $customfields = [];
        
        // Получение ID родительского подразделения
        $currentdepartment = $this->dof->storage('departments')->get((int)$departmentid);
        if ( isset($currentdepartment->leaddepid) )
        {// Найдено вышестоящее подразделение
            // Получение дополнительных полей вышестоящего подразделения
            $customfields = $this->get_customfields($currentdepartment->leaddepid, $options);
        }
       
        // Условия фильтрации
        $conditions = [];
        
        // Фильтрация по подразделению
        $conditions['departmentid'] = (int)$departmentid;
        
        if ( ! empty($options['linkpcode'] ) &&
             in_array((string) $options['linkpcode'], array_keys($this->dof->plugin_list('storage'))))
        {// Фильтрация по коду хранилища
            $conditions['linkpcode'] = (string)$options['linkpcode'];
        }
        
        if ( ! empty($options['code']) )
        {// Фильтрация по коду
            $conditions['code'] = (string)$options['code'];
        }
        
        if ( ! empty($options['status']) && is_array($options['status']) )
        {// Фильтрация по статусам
            $conditions['status'] = $options['status'];
        } else
        {// Фильтрация по умолчанию
            $activemetalist = $this->dof->workflow('customfields')->get_meta_list('active');
            $conditions['status'] = array_keys($activemetalist);
        }

        // Получение дополнительных полей из БД
        $customfieldsrecords = $this->get_records($conditions, 'departmentid, linkpcode, sortorder ASC, id ASC');
        foreach ( $customfieldsrecords as $customfieldsrecord )
        {
            $customfields[$customfieldsrecord->code] = $customfieldsrecord;
        }
        
        return $customfields;
    }
}