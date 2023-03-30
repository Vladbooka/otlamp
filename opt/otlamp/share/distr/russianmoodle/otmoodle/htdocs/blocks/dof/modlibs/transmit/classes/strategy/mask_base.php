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
 * Импорт/экспорт данных. Базовый класс масок
 *
 * @package    sync
 * @subpackage transmit
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
abstract class dof_modlib_transmit_strategy_mask_base
{
    /**
     * Конфиг маски
     *
     * @var array
     */
    protected $config = [];
    
    /**
     * Контроллер Деканата
     *
     * @var dof_control
     */
    protected $dof = null;
    
    /**
     * Симуляция
     *
     * @var bool
     */
    protected $simulation = false;
    
    /**
     * Статегия маски
     *
     * @var dof_modlib_transmit_strategy_base
     */
    protected $strategy = null;
    
    /**
     * Логгер
     *
     * @var dof_storage_logs_queuetype_base
     */
    protected $logger = null;
    
    /**
     * Префиксы для полей
     *
     * @var array
     */
    protected $prefix = [];
    
    /**
     * Использование цепочки импорта
     *
     * @var bool
     */
    protected $chaining = false;
    
    /**
     * Список классов цепочки импорта
     *
     * @var array
     */
    protected $classes = [];
    
    /**
     * Поддержка импорта
     *
     * @return bool
     */
    public static function support_import()
    {
        return false;
    }
    
    /**
     * Поддержка экспорта
     *
     * @return bool
     */
    public static function support_export()
    {
        return false;
    }
    
    /**
     * Получение кода стратегии, на которой основывается маска
     *
     * @return string
     */
    public static final function get_strategy_code()
    {
        $maskcode = str_replace('dof_modlib_transmit_strategy_', '', static::class);
        return substr($maskcode, 0, strpos($maskcode, '_mask'));
    }
    
    /**
     * Получение класса стратегии, на которой основывается маска
     *
     * @return string
     */
    public static final function get_strategy_class()
    {
        return 'dof_modlib_transmit_strategy_'.static::get_strategy_code();
    }
    
    /**
     * Получение полного кода маски с учетом стратегии
     *
     * @return string
     */
    public static final function get_fullcode()
    {
        return static::get_strategy_code().'_'.static::get_code();
    }
    
    /**
     * Получение кода маски
     *
     * @return string
     */
    public static final function get_code()
    {
        return str_replace('dof_modlib_transmit_strategy_'.static::get_strategy_code().'_mask_', '', static::class);
    }
    
    /**
     * Получить локализованное имя стратегии
     *
     * @return string
     */
    public static function get_name_localized()
    {
        global $DOF;
        return $DOF->get_string('mask_'.static::get_fullcode().'_name', 'transmit', null, 'modlib');
    }
    
    /**
     * Получить локализованное описание стратегии
     *
     * @return string
     */
    public static function get_description_localized()
    {
        global $DOF;
        return $DOF->get_string('mask_'.static::get_fullcode().'_description', 'transmit', null, 'modlib');
    }
    
    /**
     * Получение текущих префиксов
     *
     * @return array
     */
    public static function get_current_prefixes()
    {
        $property = 'prefixes_' . static::get_code();
        if ( property_exists(static::class, $property) )
        {
            return static::$$property;
        } else
        {
            return [];
        }
    }
    
    /**
    * Получение текущих префиксов
    *
    * @return array
    */
    public static function get_fields_export()
    {
        $property = 'fields_export_' . static::get_code();
        if ( property_exists(static::class, $property) )
        {
            return static::$$property;
        } else
        {
            return [];
        }
    }
    
    /** ОБЪЕКТНЫЕ МЕТОДЫ **/
    
    /**
     * Заполнить форму дополнительными настройками маски (ИМПОРТ)
     *
     * @return void
     */
    protected function configform_definition_import_prepared(dof_modlib_transmit_configurator_configform_base &$form, MoodleQuickForm &$mform)
    {
    }
    
    /**
     * Валидация формы (ИМПОРТ)
     *
     * @return void
     */
    protected function configform_validation_import_prepared(dof_modlib_transmit_configurator_configform_base &$form, MoodleQuickForm &$mform, $data, $files)
    {
        return [];
    }
    
    /**
     * Заполнить формы данными (ИМПОРТ)
     *
     * @return void
     */
    protected function configform_definition_after_data_import_prepared(dof_modlib_transmit_configurator_configform_base &$form, MoodleQuickForm &$mform)
    {
    }
    
    /**
     * Установка данных маски из настроек формы (ИМПОРТ)
     *
     * @return void
     */
    protected function configform_setupconfig_import_prepared(dof_modlib_transmit_configurator_configform_base &$form, MoodleQuickForm &$mform, $formdata)
    {
    }
    
    /**
     * Конструктор
     *
     * @param dof_control $dof
     * @param dof_storage_logs_queuetype_base $logger
     *
     * @return void
     */
    public function __construct(dof_control $dof, $logger)
    {
        $this->dof = $dof;
        
        // Привязка маски к стратегии обмена
        $strategyclass = static::get_strategy_class();
        $this->strategy = new $strategyclass($this->dof, $logger);
        
        // Заполнение конфигурации значениями по умолчанию
        $this->config = $this->config_defaults();
        
        $this->set_configitem('logger', $logger);
        
        // Установка департамента
        $this->set_department();
    }
    
    /**
     * Установка менеджера файлов
     *
     * @param dof_modlib_transmit_source_filemanager $filemanager
     */
    public function set_filemanager(dof_modlib_transmit_source_filemanager $filemanager)
    {
        // Установка менеджера файлов
        $this->strategy->set_configitem('filemanager', $filemanager);
    }
    
    /**
     * Задать идентификатор подразделения
     *
     * @return void
     */
    public final function set_department($id = null)
    {
        if ( empty($id) )
        {
            $this->set_configitem(
                'departmentid',
                optional_param('departmentid', 0, PARAM_INT)
                );
        } else
        {
            $this->set_configitem(
                'departmentid',
                (int)$id
                );
        }
        
        // Обновление опций
        $this->set_options();
    }
    
    /**
     * Включить режим симуляции
     *
     * @return void
     */
    public final function simulation_on()
    {
        $this->set_configitem('simulation', true);
    }
    
    /**
     * Выключить режим симуляции
     *
     * @return void
     */
    public final function simulation_off()
    {
        $this->set_configitem('simulation', false);
    }
    
    /**
     * Вернуть состояние симуляции
     *
     * @return bool
     */
    public final function get_simulation_status()
    {
        return $this->get_configitem('simulation');
    }
    
    /**
     * Установка логгера
     *
     * @param dof_storage_logs_queuetype_base $logger
     *
     * @return void
     */
    public function set_logger(dof_storage_logs_queuetype_base $logger)
    {
        $this->set_configitem('logger', $logger);
        
        // Установка логгера стратегии
        $this->strategy->set_logger($logger);
    }
    
    /**
     * Получить информационный блок о полях импорта данных
     *
     * @return string - HTML-код
     */
    public function get_importfields_infoblock()
    {
        $strategy = $this->strategy;
        
        // Таблица полей
        $table = new stdClass();
        $table->data = [];
        
        foreach ( $this->get_importfields() as $fieldcode => $fieldinfo )
        {
            // Описание поля
            $description = $strategy::get_fielddescription_localized($fieldcode);
            $descriptionadding = @$strategy::get_fielddescription_localized($fieldcode . '_description');
            if ( mb_substr($descriptionadding, 0, 1) != '[' && mb_substr($descriptionadding, mb_strlen($descriptionadding)-1, 1) != ']' )
            {
                $description .= $descriptionadding;
            }
            if ( isset($fieldinfo['displayedfieldcode']) )
            {// Переопределение поля
                $fieldcode = $fieldinfo['displayedfieldcode'];
            }
            // Опция
            $option = '';
            if ( ! empty($fieldinfo['option']) )
            {// Поле является опцией
                $option = $this->dof->get_string('optional_field', 'transmit', null, 'modlib');
                $option = dof_html_writer::tag('b', $option);
            }
            $fieldcode = dof_html_writer::tag('b', $fieldcode);
            $table->data[] = [$fieldcode, $option, $description];
        }
        return $this->dof->modlib('widgets')->print_table($table, true);
    }
    
    /**
     * Получить список полей импорта, с которыми работает текущая маска
     *
     * @return array
     */
    public function get_importfields()
    {
        // Получение полного набора полей стратегии
        $strategy = $this->strategy;
        $maskfields = (array)$strategy::$importfields;
        
        return $maskfields;
    }
    
    /**
     * Получить список полей экспорта, с которыми работает текущая маска
     *
     * @return array
     */
    public function get_exportfields()
    {
        // Получение полного набора полей стратегии
        $strategy = $this->strategy;
        $maskfields = $strategy::$exportfields;
        
        return (array)$maskfields;
    }
    
    /**
     * Процесс импорта
     *
     * @param array $fields
     * @param array $dataitem
     *
     * @return array
     */
    public final function transmit_import(array $fields, array $dataitem)
    {
        // Первичная проверка
        if ( count($fields) !== count($dataitem) )
        {
            throw new dof_modlib_transmit_exception('mask_error', 'transmit');
        }

        // Создание массива
        $data = array_combine($fields, $dataitem);

        // Базовая инициализация полей импорта
        $this->transmit_import_init($data);
        
        // Фильтрация данных импорта
        $this->transmit_import_filter($data);
        
        // Отдаем обработанные маской поля и данные стратегии на обработку
        return $this->strategy->transmit_import($data);
    }
    
    /**
     * Подготовка процесса экспорта единичного объекта
     *
     * Подготовительные действия для запуска процесса экспорта.
     * Добавление в пулл данных для запуска обработчиков экспорта.
     *
     * @return array
     */
    protected function transmit_import_init(&$data)
    {
        // Добавление текущего подразделения
        $data['__departmentid'] = $this->get_configitem('departmentid');
        
        // Метка о симуляции процесса
        if ( $this->get_configitem('simulation') )
        {// Если включен режим симуляции, добавим в пул поле
            $data['simulation'] = true;
        }
    }
    
    /**
     * Фильтрация процесса импорта
     *
     * Метод для фильтрации пулла данных
     *
     * @return void
     */
    protected function transmit_import_filter(&$data)
    {
        // Белый список полей маски
        $fields = $this->get_importfields();
        $fields['__departmentid'] = null;
        $fields['simulation'] = null;
        $fields['__main_sync_downid'] = null;
        $fields['__main_sync_upid'] = null;
        
        // Базовая фильтрация списка полей
        $filter = array_intersect_key($data, $fields);
        // Добавление в набор регулярных полей
        foreach ( $fields as $field => $fieldinfo )
        {
            if ( @preg_match((string)$field, null) !== false )
            {// Поле является регулярным выражением
                
                // Поиск полей в пулле, подходящих под регулярное выражение
                foreach ( $data as $poolfield => $pooldata )
                {
                    if ( preg_match((string)$field, $poolfield) === 1 )
                    {// Поле в пулле данных подходит под регулярное выражение
                        // Поле подходит под регулярное выражение
                        $filter[$poolfield] = $pooldata;
                    }
                }
            }
        }
        $data = $filter;
        unset($filter);
    }
    
    /**
     * Процесс экспорта данных из стратегии обмена данных
     *
     * В пулле генерируются данные по одному объекту экспорта. Для этого в пулл помещаются
     * начальные данные (например - идентификатор персоны). Экспортеры, которым
     * достаточно данных в пулле, добавляют новые данные в пулл.
     * Результат очищается от лишних данных и передается для экспорта.
     *
     * @return array - Данные одного объекта экспорта
     */
    public final function transmit_export()
    {
        // Базовая инициализация полей экспорта
        $data = [];
        $this->transmit_export_init($data);
        
        // Получение данных
        $data = $this->strategy->transmit_export($data);
        
        // Фильтрация данных экспорта
        $this->transmit_export_filter($data);

        return (array)$data;
    }
    
    /**
     * Подготовка процесса экспорта единичного объекта
     *
     * Подготовительные действия для запуска процесса экспорта.
     * Добавление в пулл данных для запуска обработчиков экспорта.
     *
     * @return array
     */
    protected function transmit_export_init(&$data)
    {
        // Добавление текущего подразделения
        $data['__departmentid'] = $this->get_configitem('departmentid');
    }
    
    /**
     * Фильтрация процесса экспорта
     *
     * Метод для фильтрации пулла данных
     *
     * @return void
     */
    protected function transmit_export_filter(&$data)
    {
        $matchingfields = $this->get_configitem('exportfields');
        
        $filterdata = [];
        // Коррекция имен экспортируемых данных в соответствие с конфигурацией
        foreach ( $data as $field => $value )
        {
            if ( ! empty($matchingfields[$field]) )
            {// Поле требуется для экспорта
            
                $fieldname = $matchingfields[$field];
                $filterdata[$fieldname] = $value;
            }
        }
        $data = $filterdata;
    }
    
    
    
    
    
    
    
    

    
    
    
    /**
     * Заполнить форму дополнительными настройками
     *
     * @param dof_modlib_transmit_configurator_configform_base $form
     * @param MoodleQuickForm $mform
     *
     * @return void
     */
    public final function configform_definition_import(dof_modlib_transmit_configurator_configform_base &$form, MoodleQuickForm &$mform)
    {
        foreach ( $this->classes as $class )
        {
            $class::configform_definition_import_prepared($form, $mform);
        }
    }
    
    /**
     * Заполнить форму данными
     *
     * @param dof_modlib_transmit_configurator_configform_base $form
     * @param MoodleQuickForm $mform
     *
     * @return void
     */
    public function configform_definition_after_data_import(dof_modlib_transmit_configurator_configform_base &$form, MoodleQuickForm &$mform)
    {
        foreach ( $this->classes as $class )
        {
            $class::configform_definition_after_data_import_prepared($form, $mform);
        }
    }
    
    /**
     * Валидация формы
     *
     * @param dof_modlib_transmit_configurator_configform_base $form
     * @param MoodleQuickForm $mform
     * @param array $data
     * @param array $files
     *
     * @return array
     */
    public final function configform_validation_import(dof_modlib_transmit_configurator_configform_base &$form, MoodleQuickForm &$mform, $data, $files)
    {
        // Массив ошибок
        $errors = [];
        
        foreach ( $this->classes as $class )
        {
            $errors = array_merge($errors, $class::configform_validation_import_prepared($form, $mform, $data, $files));
        }
        
        return $errors;
    }
    
    /**
     * Установка конфига
     *
     * @param dof_modlib_transmit_configurator_configform_base $form
     * @param MoodleQuickForm $mform
     * @param stdClass $formdata
     *
     * @return void
     */
    public final function configform_setupconfig_import(dof_modlib_transmit_configurator_configform_base &$form, MoodleQuickForm &$mform, $formdata)
    {
        foreach ( $this->classes as $class )
        {
            $class::configform_setupconfig_import_prepared($form, $mform, $formdata);
        }
    }
    
    /**
     * Заполнить форму дополнительными настройками
     *
     * @param dof_modlib_transmit_configurator_configform_base $form
     * @param MoodleQuickForm $mform
     *
     * @return void
     */
    public function configform_definition_export(dof_modlib_transmit_configurator_configform_base &$form, MoodleQuickForm &$mform)
    {
        // Поля экспорта
        $strategy = $this->strategy;
        $exportfields = $this->get_configitem('exportfields');
        foreach ( $exportfields as $strategyfieldcode => $externalfieldcode )
        {
            // Описание поля
            $description = $strategy::get_fielddescription_localized($strategyfieldcode);
            
            $mform->addElement(
                'text',
                'mask_matchfield_'.$strategyfieldcode,
                $description
            );
            $mform->setType('mask_matchfield_'.$strategyfieldcode, PARAM_RAW_TRIMMED);
            $mform->setDefault('mask_matchfield_'.$strategyfieldcode, $externalfieldcode);
        }
    }
    
    /**
     * Заполнить форму данными
     *
     * @param dof_modlib_transmit_configurator_configform_base $form
     * @param MoodleQuickForm $mform
     *
     * @return void
     */
    public function configform_definition_after_data_export(dof_modlib_transmit_configurator_configform_base &$form, MoodleQuickForm &$mform)
    {
    }
    
    /**
     * Валидация формы
     *
     * @param dof_modlib_transmit_configurator_configform_base $form
     * @param MoodleQuickForm $mform
     * @param array $data
     * @param array $files
     *
     * @return array
     */
    public function configform_validation_export(dof_modlib_transmit_configurator_configform_base &$form, MoodleQuickForm &$mform, $data, $files)
    {
        // Массив ошибок
        $errors = [];
        return $errors;
    }
    
    /**
     * Установка конфига
     *
     * @param dof_modlib_transmit_configurator_configform_base $form
     * @param MoodleQuickForm $mform
     * @param stdClass $formdata
     *
     * @return void
     */
    public function configform_setupconfig_export(dof_modlib_transmit_configurator_configform_base &$form, MoodleQuickForm &$mform, $formdata)
    {
        $exportfields = $this->get_configitem('exportfields');
        // Переопределение полей экспорта
        foreach ( $exportfields as $strategyfieldcode => &$externalfieldcode )
        {
            // Переопределение поля экспорта
            $externalfieldcode = $formdata->{'mask_matchfield_'.$strategyfieldcode};
        }
        $this->set_configitem('exportfields', $exportfields);
    }
    
    
    /** РАБОТА С КОНФИГУРАЦИЕЙ МАСКИ **/
    
    /**
     * Получить полную конфигурацию маски
     *
     * @return array
     */
    public function get_configitems()
    {
        return (array)$this->config;
    }
    
    /**
     * Получить элемент конфигурации маски
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
     * Установить элемент конфигурации маски
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
     * Получить полную конфигурацию маски в запакованном виде
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
     * Установить полную конфигурацию маски из запакованного формата
     *
     * @param string $configdata - Запакованные данные маски
     *
     * @return void
     *
     * @throws dof_modlib_transmit_exception - В случае если формат настроек не валиден
     * @deprecated использовать set_configitem()
     */
    public function set_configdata($configdata)
    {
        throw new dof_modlib_transmit_exception('exception_deprecated_mask_set_configdata', 'modlib_transmit');
    }
    
    /**
     * Получение конфигурации по умолчанию для текущей маски
     *
     * @return array
     */
    protected function config_defaults()
    {
        // Конфигурация для базового источника
        $configdata = [];
        
        // Хранилище логов для сохранения процесса работы маски
        $configdata['logger'] = null;
        
        // Процесс симуляции
        $configdata['simulation'] = false;
        
        // Целевое подразделение
        $configdata['departmentid'] = optional_param('departmentid', 0, PARAM_INT);
        
        // Опции импорта
        $configdata['importoptions'] = [];
        
        // Сравнение экспортных полей
        $exportfields = array_keys($this->get_exportfields());
        $configdata['exportfields'] = array_combine($exportfields, $exportfields);
        
        return $configdata;
    }
    
    /**
     * Сброс конфигурации маски
     *
     * @return array
     */
    public function config_reset()
    {
        $this->config = $this->config_defaults();
    }
    
    
    
    
    
    
    
    
    /**
     * Установка опций
     *
     * @return void
     */
    public final function set_options()
    {
        // Установка классов
        $classes = [];
        $classes[] = get_class($this);
        if ( $this->chaining )
        {// Установим классы родителей
            $classes = array_merge($classes, class_parents($this));
            if ( isset($classes['dof_modlib_transmit_strategy_mask_base']) )
            {// Удаляем базовый класс из списка
                unset($classes['dof_modlib_transmit_strategy_mask_base']);
            }
        }
        
        // Установка классов
        $this->classes = $classes;
        
        // Установка префиксов
        $this->prefix = [];
        if ( property_exists(static::class, 'prefixes') )
        {
            $this->prefix = static::$prefixes;
        }
    }
    
    /**
     * Включить цепочку наследования
     *
     * @return void
     */
    public final function chaining_on()
    {
        $this->chaining = true;
        
        // Установка филдов
        $this->set_options();
    }
    
    /**
     * Выключить цепочку наследования
     *
     * @return void
     */
    public final function chaining_off()
    {
        $this->chaining = false;
        
        // Установка филдов
        $this->set_options();
    }
}
