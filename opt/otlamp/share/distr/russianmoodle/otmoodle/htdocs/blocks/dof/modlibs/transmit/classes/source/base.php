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
 * Обмен данных с внешними источниками. Базовый класс источников данных.
 *
 * @package    modlib
 * @subpackage transmit
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
abstract class dof_modlib_transmit_source_base 
{
    /**
     * Контроллер ЭД
     *
     * @var dof_control
     */
    protected $dof;
    
    /**
     * Массив полей
     *
     * @var array
     */
    protected $datafields = [];
    
    /**
     * Массив полей маски
     *
     * @var array
     */
    protected $maskimportfields = [];
    
    /**
     * Конфигурация источника
     *
     * @var array
     */
    protected $config = [];
    
    /** ПОЛЯ ДЛЯ РАБОТЫ С ПАКЕТОМ **/
    
    /**
     * Объект пака
     * 
     * @var dof_modlib_transmit_pack
     */
    protected $pack = null;
    
    /**
     * Флаг режима пакета (работает совместно с фреймворком синхронизаций storage/sync)
     * 
     * @var bool
     */
    protected $pack_mode = false;
    
    /**
     * Объект фремворка синхронизаций
     *
     * @var dof_storage_sync_connect
     */
    protected $connection = null;
    
    /**
     * Поддержка импорта текущим источником
     *
     * @return bool
     */
    public static function support_import()
    {
        return false;
    }
    
    /**
     * Поддержка экспорта текущим источником
     *
     * @return bool
     */
    public static function support_export()
    {
        return false;
    }
    
    /**
     * Получение кода источника
     *
     * @return string
     */
    public static final function get_code()
    {
        return str_replace('dof_modlib_transmit_source_', '', static::class);
    }
    
    /**
     * Получить локализованное имя источника
     *
     * @return string
     */
    public static function get_name_localized()
    {
        global $DOF;
        return $DOF->get_string('source_'.static::get_code().'_name', 'transmit', null, 'modlib');
    }
    
    /**
     * Получить локализованное описание источника
     *
     * @return string
     */
    public static function get_description_localized()
    {
        global $DOF;
        return $DOF->get_string('source_'.static::get_code().'_description', 'transmit', null, 'modlib');
    }
    
    /**
     * Крон
     *
     * @return void
     */
    public static function cron()
    {
    }
    
    /**
     * Получение объекта соединения с фреймворком синхронизаций
     * 
     * @return dof_storage_sync_connect
     */
    protected function get_sync_connection()
    {
        if ( is_null($this->connection) )
        {
            // получение конфигов пака
            $packconfig = $this->get_configitem('pack_config');
            
            $this->connection = $this->dof->storage('sync')->createConnect(
                    $packconfig['downtype'],
                    $packconfig['downcode'],
                    $packconfig['downsubstorage'],
                    'modlib',
                    'transmit',
                    $this->pack->get_id()
                    );
        }
        
        return $this->connection;
    }
    
    /**
     * Конструктор источника
     *
     * @param dof_control $dof
     *
     * @return void
     */
    public function __construct(dof_control $dof)
    {
        $this->dof = $dof;
        
        // Заполнение конфигурации значениями по умолчанию
        $this->config = $this->config_defaults();
    }
    
    /**
     * Установка очереди логирования, с которой будет работать текущий источник
     *
     * @param dof_storage_logs_queuetype_base $logger
     *
     * @return void
     */
    public function set_logger(dof_storage_logs_queuetype_base $logger)
    {
        $this->set_configitem('logger', $logger);
    }
    
    /**
     * Установка пака
     * 
     * @param dof_modlib_transmit_pack $pack
     */
    public function set_pack(dof_modlib_transmit_pack $pack)
    {
        $this->pack = $pack;
    }
    
    /** РАБОТА С ДАННЫМИ ДЛЯ ОБМЕНА **/
    
    /**
     * Получить итератор с данными для обмена
     *
     * @return Iterator
     */
    public abstract function get_dataiterator();
    
    /**
     * Запуск процесса экспорта данных
     *
     * @return void
     */
    public function export_start_process()
    {
    }
    
    /**
     * Процесс экспорта данных одного элемента
     *
     * @param $fields - Поля экспорта
     * @param $data - Данные экспорта
     *
     * @return void
     */
    public function export(array $fields, array $data)
    {
    }
    
    /**
     * Завершение процесса экспорта данных
     *
     * @return void
     */
    public function export_finish_process()
    {
    }
    
    
    
    /**
     * Получить поля обмениваемых данных
     *
     * @return array
     */
    public function get_datafields_list()
    {
        return (array)$this->datafields;
    }
    
    /**
     * Установить поле
     *
     * @param string $fieldname
     * @param string $value
     *
     * @return void
     */
    public function add_datafield($fieldname, $value)
    {
        $this->datafields[$fieldname] = $value;
    }
    
    /**
     * Проверка доступности файла
     *
     * @param string $fileidentifier - Индентификатор файла
     *
     * @return bool
     */
    public function file_exists($fileidentifier)
    {
        // Работа с файлами не поддерживается
        return false;
    }
    
    /**
     * Копирование файла из внешенго источника в указанную зону
     *
     * @param string $fileidentifier - Индентификатор файла в источнике
     * @param string $filearea - Файловая зона, в которую требуется скопировать файл
     * @param string $filepath - Путь до файла внутри зоны
     * @param number $itemid - Идентификатор подзоны
     *
     * @return stored_file|null - Скопированный файл или null
     */
    public function file_copy($fileidentifier, $filearea, $filepath, $itemid = 0)
    {
        // Работа с файлами не поддерживается
        return null;
    }
    
    /** РАБОТА С ФОРМАМИ НАСТРОЙКИ ОБМЕНА **/
    
    /**
     * Заполнить форму дополнительными настройками источника
     *
     * @param dof_modlib_transmit_configurator_configform_base $form - Форма настройки
     * @param MoodleQuickForm $mform - Контроллер формы
     *
     * @return void
     */
    public function configform_definition_import(dof_modlib_transmit_configurator_configform_base &$form, MoodleQuickForm &$mform)
    {
    }
    
    /**
     * Заполнить форму данными
     *
     * @param dof_modlib_transmit_configurator_configform_base $form - Форма настройки
     * @param MoodleQuickForm $mform - Контроллер формы
     *
     * @return void
     */
    public function configform_definition_after_data_import(dof_modlib_transmit_configurator_configform_base &$form, MoodleQuickForm &$mform)
    {
    }
    
    /**
     * Валидация формы
     *
     * @param dof_modlib_transmit_configurator_configform_base $form - Форма настройки
     * @param MoodleQuickForm $mform - Контроллер формы
     * @param array $data - Данные формы
     * @param array $files - Загруженные в форму файлы
     *
     * @return array
     */
    public function configform_validation_import(dof_modlib_transmit_configurator_configform_base &$form, MoodleQuickForm &$mform, $data, $files)
    {
        return [];
    }
    
    /**
     * Установка конфигурации источника данными из формы
     *
     * @param dof_modlib_transmit_configurator_configform_base $form - Форма настройки
     * @param MoodleQuickForm $mform - Контроллер формы
     * @param stdClass $formdata - Данные формы
     *
     * @return void
     */
    public function configform_setupconfig_import(dof_modlib_transmit_configurator_configform_base &$form, MoodleQuickForm &$mform, $formdata)
    {
    }
    
    /**
     * Заполнить форму дополнительными настройками
     *
     * @param dof_modlib_transmit_configurator_configform_base $form - Форма настройки
     * @param MoodleQuickForm $mform - Контроллер формы
     *
     * @return void
     */
    public function configform_definition_export(dof_modlib_transmit_configurator_configform_base &$form, MoodleQuickForm &$mform)
    {
    }
    
    /**
     * Заполнить форму данными
     *
     * @param dof_modlib_transmit_configurator_configform_base $form - Форма настройки
     * @param MoodleQuickForm $mform - Контроллер формы
     *
     * @return void
     */
    public function configform_definition_after_data_export(dof_modlib_transmit_configurator_configform_base &$form, MoodleQuickForm &$mform)
    {
    }
    
    /**
     * Валидация формы
     *
     * @param dof_modlib_transmit_configurator_configform_base $form - Форма настройки
     * @param MoodleQuickForm $mform - Контроллер формы
     * @param array $data - Данные формы
     * @param array $files - Загруженные в форму файлы
     *
     * @return array
     */
    public function configform_validation_export(dof_modlib_transmit_configurator_configform_base &$form, MoodleQuickForm &$mform, $data, $files)
    {
        return [];
    }
    
    /**
     * Установка конфигурации источника данными из формы
     *
     * @param dof_modlib_transmit_configurator_configform_base $form - Форма настройки
     * @param MoodleQuickForm $mform - Контроллер формы
     * @param stdClass $formdata - Данные формы
     *
     * @return void
     */
    public function configform_setupconfig_export(dof_modlib_transmit_configurator_configform_base &$form, MoodleQuickForm &$mform, $formdata)
    {
    }

    /** РАБОТА С КОНФИГУРАЦИЕЙ ИСТОЧНИКА **/
    
    /**
     * Получить полную конфигурацию источника
     *
     * @return array
     */
    public function get_configitems()
    {
        return (array)$this->config;
    }
    
    /**
     * Получить элемент конфигурации источника
     *
     * @param string $configname - Код элемента
     *
     * @return mixed
     */
    public function get_configitem($configname)
    {
        if ( ! isset($this->config[(string)$configname]) )
        {
            return false;
        }
        
        return $this->config[$configname];
    }
    
    /**
     * Установить элемент конфигурации источника
     *
     * @param string $configcode - Код элемента конфигурации
     * @param mixed $configvalue - Значение элемента конфигурации
     *
     * @return $configvalue - Сохраненное значение
     * 
     * @throws dof_modlib_transmit_exception - В случае ошибки добавления элемента
     */
    public function set_configitem($configcode, $configvalue)
    {
        if ( ! array_key_exists((string)$configcode, $this->config) )
        {// Код конфигурации не найден
            $stringdata = new stdClass();
            $stringdata->configcode = $configcode;
            throw new dof_modlib_transmit_exception(
                'exception_error_invalid_configdata', 'modlib_transmit', '', $stringdata);
        }
        
        // Добавление элемента конфигурации
        $this->config[(string)$configcode] = $configvalue;
        
        return $this->get_configitem((string)$configcode);
    }
    
    /**
     * Получить полную конфигурацию источника в запакованном виде
     * 
     * Требуется для формировани пакета настройки периодического обмена
     *
     * @return string - Запакованные данные 
     */
    public function get_configdata()
    {
        $configitems = $this->get_configitems();
        return serialize($configitems);
    }
    
    /**
     * Установить полную конфигурацию источника из запакованного формата
     *
     * @param string $configdata - Запакованные данные источника
     *
     * @return void
     * 
     * @throws dof_modlib_transmit_exception - В случае если формат настроек не валиден
     */
    public function set_configdata($configdata)
    {
        $configitems = unserialize((string)$configdata);
        if ( is_array($configitems) == false )
        {// Ошибка распаковки настроек
            throw new dof_modlib_transmit_exception('exception_error_invalid_configdata', 'modlib_transmit');
        }
        
        // Установка конфигурации
        foreach ( (array)$configitems as $configcode => $configvalue )
        {
            $this->set_configitem((string)$configcode, $configvalue);
        }
    }
    
    /**
     * Получение конфигурации по умолчанию для текущего источника
     * 
     * @return array
     */
    protected function config_defaults()
    {
        // конфигурация для базового источника
        $configdata = [];
        
        // хранилище логов для сохранения процесса работы источника
        $configdata['logger'] = null;
        
        // фильтры для источника данных
        $configdata['filters'] = [];
        
        // конфиги пакета
        $configdata['pack_config'] = [
            // название поля внешнего идентификатора
            'upfieldname' => null,
            // поля, по которым вычисляется внешний хеш
            'uphashfields' => [],
            // поля, по которым вычисляется внутренний хеш
            'downhashfields' => [],
            // тип плагина
            'downtype' => '',
            // код плагина
            'downcode' => '',
            // внутренний код (опциональный)
            'downsubstorage' => '',
            // флаг полной синхронизации
            'fullsync' => false
        ];
        
        return $configdata;
    }
    
    /**
     * Сброс конфигурации источника
     *
     * @return array
     */
    public function config_reset()
    {
        $this->config = $this->config_defaults();
    }
    
    /**
     * Установка полей, о которых знает маска
     * 
     * @param array $maskfields
     */
    public function set_mask_import_fields($maskimportfields)
    {
        $this->maskimportfields = $maskimportfields;
    }

    /**
     * Уведомление источнику о том, что запись обработана
     * Используется для обновления записи в фреймворке синхронизаций
     *
     * @param array $item
     * @param int $downid - внутренний идентификатор
     *
     * @return void
     */
    public function record_processed($item = [], $downid = null)
    {
    }
    
    /**
     * Включение режима пакета
     *
     * @return void
     */
    public function pack_mode_on()
    {
        $this->pack_mode = true;
    }
    
    /**
     * Выключение режима пакета
     * 
     * @return void
     */
    public function pack_mode_off()
    {
        $this->pack_mode = false;
    }
    
    /**
     * Операции сравнения для фильтра
     * 
     * @return array
     */
    public static function get_filter_operators()
    {
        global $DOF;
        
        $sqlcomparisonoperators = [
            '=' => $DOF->get_string('sql_comparison_operator_equal_to', 'transmit', null, 'modlib'),
            '>' => $DOF->get_string('sql_comparison_operator_greater_than', 'transmit', null, 'modlib'),
            '<' => $DOF->get_string('sql_comparison_operator_less_than', 'transmit', null, 'modlib'),
            '>=' => $DOF->get_string('sql_comparison_operator_greater_than_or_equal_to', 'transmit', null, 'modlib'),
            '<=' => $DOF->get_string('sql_comparison_operator_less_than_or_equal_to', 'transmit', null, 'modlib'),
            '<>' => $DOF->get_string('sql_comparison_operator_not_equal_to', 'transmit', null, 'modlib')
        ];
        
        return [
            $DOF->get_string('source_filterform_dont_filter', 'transmit', null, 'modlib'),
        ] + $sqlcomparisonoperators;
    }
    
    /**
     * Установка фильтров источника
     * 
     * @param MoodleQuickForm $mform
     * 
     * @return void
     */
    public function filterform_definition_after_data_import(MoodleQuickForm &$mform)
    {
        if(method_exists($this, 'get_fields'))
        {
            $fields = $this->get_fields($mform);
            
            if( ! empty($fields) )
            {
                // Фильтрация. Заголовок
                $mform->addElement(
                    'header',
                    'header_filterform',
                    $this->dof->get_string('source_filterform_header', 'transmit', null, 'modlib'));
                $mform->setExpanded('header_filterform', false);
                
                // @TODO: реализовать возможность добавлять неограниченное количество правил фильтрации и в каждом выбирать поле
                // функциональность доступна для moodleform, но у нас MoodleQuickForm :( 
                
                // поэтому пока так:
                // добавление инструментов настройки фильтрации (сравнения) для каждого из полей
                foreach($fields as $fieldcode => $fieldname)
                {
                    $mform->addElement(
                        'group',
                        'filter__' . $fieldcode,
                        $fieldname,
                        [
                            $mform->createElement('select', 'comparison_operator', '', self::get_filter_operators()),
                            $mform->createElement('text', 'comparison_value')
                        ]);
                    
                    $mform->disabledIf(
                        'filter__' . $fieldcode . '[comparison_value]',
                        'filter__' . $fieldcode . '[comparison_operator]',
                        'eq',
                        '0');
                }
            }
        }
        //@TODO: реализовать возможность самостоятельно указывать названия полей, если их нельзя подтянуть из источника
        // либо реализовать чтение заголовков в csv, от чего ранее отказались для упрощения пользовательского интерфейса
    }
    
    
    /**
     * Валидация фильтров
     * 
     * @param MoodleQuickForm $mform
     * @param array $data
     * @param array $files
     * 
     * @return string[]|mixed[]
     */
    public function filterform_validation_import(MoodleQuickForm &$mform, $data, $files)
    {
        $errors = [];
        
        if(method_exists($this, 'get_fields'))
        {
            foreach($this->get_fields($mform) as $fieldcode => $fieldname)
            {
                if( ! empty($data['filter__'.$fieldcode]['comparison_operator']) )
                {
                    if( ! isset($data['filter__'.$fieldcode]['comparison_value']) || 
                        $data['filter__'.$fieldcode]['comparison_value'] == '')
                    {
                        $errors['filter__'.$fieldcode] = $this->dof->get_string(
                            'source_filterform_missed_filter_value', 'transmit', null, 'modlib');
                    }
                }
            }
        }
        
        return $errors;
    }
    
    /**
     * Установка фильтров
     * 
     * @param MoodleQuickForm $mform
     * @param stdClass $formdata
     * 
     * @return void
     */
    public function prepare_filters(MoodleQuickForm &$mform, $formdata) 
    {
        
        $filters = [];
        if( method_exists($this, 'get_fields') )
        {
            foreach($this->get_fields($mform) as $fieldcode => $fieldname)
            {
                if( ! empty($formdata->{'filter__'.$fieldcode}['comparison_operator']) )
                {
                    if (isset($formdata->{'filter__'.$fieldcode}['comparison_value']) &&
                        $formdata->{'filter__'.$fieldcode}['comparison_value'] != '')
                    {
                        $filter = new stdClass();
                        $filter->fieldname = $fieldcode;
                        $filter->operator = $formdata->{'filter__'.$fieldcode}['comparison_operator'];
                        $filter->value = $formdata->{'filter__'.$fieldcode}['comparison_value'];
                        $filters[] = $filter;
                    }
                }
            }
        }
        if( ! empty($filters) )
        {
            $this->set_configitem('filters', $filters);
        }
    }
}