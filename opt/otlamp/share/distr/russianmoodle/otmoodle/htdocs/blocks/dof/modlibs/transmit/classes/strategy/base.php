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
 * Обмен данных с внешними источниками. Базовый класс стратегий обмена данными.
 *
 * @package    modlib
 * @subpackage transmit
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
abstract class dof_modlib_transmit_strategy_base
{
    /**
     * Доступные поля для импорта данных
     *
     * @var array
     */
    public static $importfields = [];
    
    /**
     * Доступные поля для экспорта данных
     *
     * @var array
     */
    public static $exportfields = [];
    
    /**
     * Пул обработки данных
     *
     * @var array
     */
    protected $datapool = [];
    
    /**
     * Контроллер ЭД
     *
     * @var dof_control
     */
    protected $dof;
    
    /**
     * Пул валидаторов
     *
     * @var array
     */
    protected $validators = [];
    
    /**
     * Пул конвертеров
     *
     * @var array
     */
    protected $converters = [];
    
    /**
     * Пул импортеров
     *
     * @var array
     */
    protected $importers = [];
    
    /**
     * Пул экспортеров
     *
     * @var array
     */
    protected $exporters = [];
    
    /**
     * Получение кода стратегии импорта
     *
     * @return string
     */
    public static final function get_code()
    {
        return str_replace('dof_modlib_transmit_strategy_', '', static::class);
    }
    
    /**
     * Получить локализованное имя стратегии
     *
     * @return string
     */
    public static function get_name_localized()
    {
        global $DOF;
        return $DOF->get_string('strategy_'.static::get_code().'_name', 'transmit', null, 'modlib');
    }
    
    /**
     * Получить локализованное описание стратегии
     *
     * @return string
     */
    public static function get_description_localized()
    {
        global $DOF;
        return $DOF->get_string('strategy_'.static::get_code().'_description', 'transmit', null, 'modlib');
    }
    
    /**
     * Получить локализованное описание поля
     *
     * @return string
     */
    public static function get_fielddescription_localized($fieldcode)
    {
        global $DOF;
        return $DOF->get_string('strategy_'.static::get_code().'_fieldname_'.$fieldcode, 'transmit', null, 'modlib');
    }
    
    /**
     * Конструктор стратегии обмена данными
     *
     * @param dof_control $dof
     *
     * @return void
     */
    public function __construct($dof)
    {
        $this->dof = $dof;
        
        // Заполнение конфигурации значениями по умолчанию
        $this->config = $this->config_defaults();
    }
    
    /**
     * Установка очереди логирования, с которой будет работать текущая стратегия
     *
     * @param dof_storage_logs_queuetype_base $logger
     *
     * @return void
     */
    public function set_logger(dof_storage_logs_queuetype_base $logger)
    {
        $this->set_configitem('logger', $logger);
    }
    
    /** РАБОТА С ДАННЫМИ ДЛЯ ОБМЕНА **/
    
    /**
     * Запуск процесса импорта
     *
     * @param array $data - Пулл данных для импорта
     *
     * @return array
     */
    public final function transmit_import(array $data)
    {
        // Очистка стратегии перед обработкой
        $this->clean();
        
        // Установка текущего пула
        $this->datapool = $data;
        
        // Запуск обработчиков
        do
        {
            // Текущее состояине пулла
            $poolstate = $this->datapool;
            
            // Исполнение валидаторов
            foreach ( $this->validators as $processorcode => $slotconfigs )
            {// Запуск валидаторов указанного типа
                
                // Получение класса обработчика
                $processorclass = $this->get_processor_class($processorcode);

                // Запуск обработчика для каждой конфигурации полей
                foreach ( $slotconfigs as $slotconfig )
                {
                    // Получение наборов исполения обработчика
                    $sets = $this->get_field_sets($slotconfig);
                    
                    foreach ( $sets as $set )
                    {// Запуск обработчика для каждого из наборов
                        
                        // Получение данных для запуска обработчиков
                        $inputdata = $this->get_field_inputdata($set);
                        if ( $inputdata )
                        {// Данные получены
                            
                            // Проверка наличия поля в пулле
                            if ( ! array_intersect_key($this->datapool, array_flip($set['output_slots'])) )
                            {// В пулле нет ни одного исходящего поля обработчика
                                // Запуск обработчика
                                $output = $processorclass::execute(
                                    $inputdata,
                                    $this->dof,
                                    $this->get_configitem('logger'),
                                    $this->get_configitem('filemanager')
                                );
                                // Добавление в пулл данных из обработчика
                                foreach ( $set['output_slots'] as $outputfield => $datafield )
                                {
                                    if ( array_key_exists($outputfield, (array)$output) )
                                    {
                                        $outputdata = $output[$outputfield];
                                        if ( is_array($outputdata) )
                                        {// Получены комплексные данные
                                            foreach ( $outputdata as $key => $data )
                                            {
                                                $complexdatafield = str_replace('$1', $key, $datafield);
                                                $this->datapool[$complexdatafield] = $data;
                                            }
                                        } else
                                        {// Стандартные данные
                                            $this->datapool[$datafield] = $output[$outputfield];
                                        }
                                        
                                    }
                                }
                                $diff = array_diff_key($this->datapool, $poolstate);
                                if ( ! empty($diff) )
                                {// Найдены изменения в текущей итерации
                                    continue 4;
                                }
                            }
                        }
                    }
                }
            }
            
            $diff = array_diff_key($this->datapool, $poolstate);
            if ( ! empty($diff) )
            {// Найдены изменения в текущей итерации
                continue;
            }
            
            // Исполнение конвертеров
            foreach ( $this->converters as $processorcode => $slotconfigs )
            {// Запуск конвертера указанного типа
                
                // Получение класса обработчика
                $processorclass = $this->get_processor_class($processorcode);
                
                // Запуск обработчика для каждой конфигурации полей
                foreach ( $slotconfigs as $slotconfig )
                {
                    // Получение наборов исполения обработчика
                    $sets = $this->get_field_sets($slotconfig);
                    
                    foreach ( $sets as $set )
                    {// Запуск обработчика для каждого из наборов

                        // Получение данных для запуска обработчиков
                        $inputdata = $this->get_field_inputdata($set);
                        if ( $inputdata )
                        {// Данные получены
                            
                            // Проверка наличия поля в пулле
                            if ( ! array_intersect_key($this->datapool, array_flip($set['output_slots'])) )
                            {// В пулле нет ни одного исходящего поля обработчика
                                
                                // Запуск обработчика
                                $output = $processorclass::execute(
                                    $inputdata,
                                    $this->dof,
                                    $this->get_configitem('logger'),
                                    $this->get_configitem('filemanager')
                                );

                                // Добавление в пулл данных из обработчика
                                foreach ( $set['output_slots'] as $outputfield => $datafield )
                                {
                                    if ( array_key_exists($outputfield, (array)$output) )
                                    {
                                        $outputdata = $output[$outputfield];
                                        if ( is_array($outputdata) )
                                        {// Получены комплексные данные
                                            foreach ( $outputdata as $key => $data )
                                            {
                                                $complexdatafield = str_replace('$1', $key, $datafield);
                                                $this->datapool[$complexdatafield] = $data;
                                            }
                                        } else
                                        {// Стандартные данные
                                            $this->datapool[$datafield] = $output[$outputfield];
                                        }
                                    }
                                }
                                $diff = array_diff_key($this->datapool, $poolstate);
                                if ( ! empty($diff) )
                                {// Найдены изменения в текущей итерации
                                    continue 4;
                                }
                            }
                        }
                    }
                }
            }
            
            $diff = array_diff_key($this->datapool, $poolstate);
            if ( ! empty($diff) )
            {// Найдены изменения в текущей итерации
                continue;
            }

            // Исполнение импортеров
            foreach ( $this->importers as $processorcode => $slotconfigs )
            {// Запуск конвертера указанного типа
                
                // Получение класса обработчика
                $processorclass = $this->get_processor_class($processorcode);
                
                
                // Запуск обработчика для каждой конфигурации полей
                foreach ( $slotconfigs as $slotconfig )
                {
                    // Получение наборов исполения обработчика
                    $sets = $this->get_field_sets($slotconfig);
                    
                    foreach ( $sets as $set )
                    {// Запуск обработчика для каждого из наборов
                        
                        // Получение данных для запуска обработчиков
                        $inputdata = $this->get_field_inputdata($set);
                        
                        if ( $inputdata )
                        {// Данные получены
                            
                            $intersect = array_intersect_key($this->datapool, array_flip($set['output_slots']));
                            // Проверка наличия поля в пулле
                            if ( (! $intersect) || ( ! empty($intersect) && (count($intersect) != count($set['output_slots'])) ) )
                            {// В пулле нет ни одного исходящего поля обработчика
                                // Запуск обработчика
                                $output = $processorclass::execute(
                                    $inputdata,
                                    $this->dof,
                                    $this->get_configitem('logger'),
                                    $this->get_configitem('filemanager')
                                );
                                // Добавление в пулл данных из обработчика
                                foreach ( $set['output_slots'] as $outputfield => $datafield )
                                {
                                    if ( array_key_exists($outputfield, (array)$output) )
                                    {
                                        $outputdata = $output[$outputfield];
                                        if ( is_array($outputdata) )
                                        {// Получены комплексные данные
                                            foreach ( $outputdata as $key => $data )
                                            {
                                                $complexdatafield = str_replace('$1', $key, $datafield);
                                                $this->datapool[$complexdatafield] = $data;
                                            }
                                        } else
                                        {// Стандартные данные
                                            $this->datapool[$datafield] = $output[$outputfield];
                                        }
                                        
                                    }
                                }
                                $diff = array_diff_key($this->datapool, $poolstate);

                                if ( ! empty($diff) )
                                {// Найдены изменения в текущей итерации
                                    continue 4;
                                }
                            }
                        }
                    }
                }
            }
            
            $diff = array_diff_key($this->datapool, $poolstate);
            if ( ! empty($diff) )
            {// Найдены изменения в текущей итерации
                continue;
            }
            
            // В текущей итерации не было изменений в пулле данных
            break;
        } while ( true );
        
        // возвращаем конечный пулл
        return $this->datapool;
    }
    
    /**
     * Запуск процесса экспорта
     *
     * @return void
     */
    public final function transmit_export($data)
    {
        // Очистка стратегии перед обработкой
        $this->clean();
        
        // Установка текущего пула
        $this->datapool = $data;
        
        // Запуск обработчиков
        do
        {
            // Текущее состояине пулла
            $poolstate = $this->datapool;
            
            // Исполнение валидаторов
            foreach ( $this->exporters as $processorcode => $slotconfigs )
            {// Запуск валидаторов указанного типа
                
                // Получение класса обработчика
                $processorclass = $this->get_processor_class($processorcode);
                
                // Запуск обработчика для каждой конфигурации полей
                foreach ( $slotconfigs as $slotconfig )
                {
                    // Получение наборов исполения обработчика
                    $sets = $this->get_field_sets($slotconfig);
                    
                    foreach ( $sets as $set )
                    {// Запуск обработчика для каждого из наборов
                        
                        // Получение данных для запуска обработчиков
                        $inputdata = $this->get_field_inputdata($set);
                        if ( $inputdata )
                        {// Данные получены
                            
                            // Проверка наличия поля в пулле
                            if ( ! array_intersect_key($this->datapool, array_flip($set['output_slots'])) )
                            {// В пулле нет ни одного исходящего поля обработчика
                                // Запуск обработчика
                                $output = $processorclass::execute(
                                    $inputdata,
                                    $this->dof,
                                    $this->get_configitem('logger'),
                                    $this->get_configitem('filemanager')
                                    );
                                // Добавление в пулл данных из обработчика
                                foreach ( $set['output_slots'] as $outputfield => $datafield )
                                {
                                    if ( array_key_exists($outputfield, (array)$output) )
                                    {
                                        $this->datapool[$datafield] = $output[$outputfield];
                                    }
                                }
                                $diff = array_diff_key($this->datapool, $poolstate);
                                if ( ! empty($diff) )
                                {// Найдены изменения в текущей итерации
                                    continue 4;
                                }
                            }
                        }
                    }
                }
            }
            $diff = array_diff_key($this->datapool, $poolstate);
            if ( ! empty($diff) )
            {// Найдены изменения в текущей итерации
                continue;
            }
            
            // В текущей итерации не было изменений в пулле данных
            break;
        } while ( true );
        
        return $this->datapool;
    }
    
    /**
     * Генерация наборов полей для обработчика
     *
     * @param array $config - Конфигурация полей обработчика
     *
     * @return array - Массив наборов полей для исполнения
     */
    private function get_field_sets($config)
    {
        // Входящие необязательные поля
        $inputslots = $config['input_slots'];
        // Входящие обязательные поля
        $requiredslots = $config['required_slots'];
        // Входящие статичные поля
        $staticslots = $config['static_slots'];
        // Исходящие поля
        $outputslots = $config['output_slots'];
        
        // Генерация наборов
        foreach ( array_merge($inputslots, $requiredslots) as $processorfieldname => $poolfieldname )
        {// Замена поля значением из пулла
            
            if ( @preg_match((string)$poolfieldname, null) !== false )
            {// Поле является регулярным выражением - ожидается несколько полей в пулле
                
                // Массив наборов данных для исполнения
                $sets = [];
                
                // Создание наборов данных по числу подходящих полей в пулле
                foreach ( $this->datapool as $field => $value )
                {
                    $matches = [];
                    if ( preg_match((string)$poolfieldname, $field, $matches) === 1 )
                    {// Поле в пулле данных подходит под регулярное выражение
                        
                        // Создание нового набора для найденного поля в пулле
                        $setinputslots = $inputslots;
                        $setrequiredslots = $requiredslots;
                        $setoutputslots = $outputslots;
                        $setstaticslots = $staticslots;
                        
                        // Замена регулярного поля его экземпляром
                        if ( isset($setinputslots[$processorfieldname]) )
                        {// Поле среди необязательных
                            $setinputslots[$processorfieldname] = $field;
                        }
                        if ( isset($setrequiredslots[$processorfieldname]) )
                        {// Поле среди обязательных
                            $setrequiredslots[$processorfieldname] = $field;
                        }
                        
                        // Модификация полей набора с учетом макроподстановок регулярного выражения ( пример - customfield_$1_name )
                        foreach ( $setinputslots as $processorfield => &$poolfield )
                        {
                            $poolfield = preg_replace((string)$poolfieldname, $poolfield, $field);
                        }
                        foreach ( $setrequiredslots as $processorfield => &$poolfield )
                        {
                            $poolfield = preg_replace((string)$poolfieldname, $poolfield, $field);
                        }
                        foreach ( $setoutputslots as $processorfield => &$poolfield )
                        {
                            $poolfield = preg_replace((string)$poolfieldname, $poolfield, $field);
                        }
                        foreach ( $setstaticslots as $processorfield => &$staticdata )
                        {
                            $staticdata = preg_replace((string)$poolfieldname, $staticdata, $field);
                        }
                        
                        // Создание набора
                        $sets[] = [
                            'input_slots' => $setinputslots,
                            'required_slots' => $setrequiredslots,
                            'output_slots' => $setoutputslots,
                            'static_slots' => $setstaticslots
                        ];
                    }
                }
                
                return $sets;
            }
        }
           
        // Регулярного поля нет - один набор
        return [$config];
    }
    
    /**
     * Заполнение входящего набора полей данными
     *
     * @param array $set - Набор для запуска обработчика
     *
     * @return array|null - Массив входных данных
     */
    private function get_field_inputdata($set)
    {
        // Входящие поля
        $inputdata = [];

        // Добавление обязательных полей
        foreach ( $set['required_slots'] as $processorfield => $poolfieldname )
        {// Замена поля значением из пулла
            if ( ! array_key_exists($poolfieldname, $this->datapool) )
            {// Обязательное поле не найдено в пулле
                return null;
            }
            $inputdata[$processorfield] = $this->datapool[$poolfieldname];
        }
        
        // Добавление необязательных полей
        foreach ( $set['input_slots'] as $processorfield => $poolfieldname )
        {// Замена поля значением из пулла
            $inputdata[$processorfield] = null;
            if ( array_key_exists($poolfieldname, $this->datapool) )
            {// Обязательное поле не найдено в пулле
                $inputdata[$processorfield] = $this->datapool[$poolfieldname];
            }
        }
        
        // Добавление статичных полей
        $inputdata = array_merge($inputdata, $set['static_slots']);
        
        return $inputdata;
    }
    
    /**
     * Получить класс обработчика по его полному коду
     *
     * @param $fullcode - Полный код обработчика
     *
     * @return string - Имя класса
     */
    protected function get_processor_class($fullcode)
    {
        return 'dof_modlib_transmit_processor_'.$fullcode;
    }
    
    /**
     * Очистка пулла данных
     *
     * @return void
     */
    protected final function clean()
    {
        $this->datapool = [];
    }
    
    /** РАБОТА С КОНФИГУРАЦИЕЙ СТРАТЕГИИ **/
    
    /**
     * Получить полную конфигурацию стратегии
     *
     * @return array
     */
    public function get_configitems()
    {
        return (array)$this->config;
    }
    
    /**
     * Получить элемент конфигурации стратегии
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
     * Установить элемент конфигурации стратегии
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
     * Получить полную конфигурацию стратегии в запакованном виде
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
     * Установить полную конфигурацию стратегии из запакованного формата
     *
     * @param string $configdata - Запакованные данные источника
     *
     * @return void
     *
     * @throws dof_modlib_transmit_exception - В случае если формат настроек не валиден
     */
    public function set_configdata($configdata)
    {
        $configitems = unserialize((string)$configitems);
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
     * Получение конфигурации по умолчанию для текущей стратегии
     *
     * @return array
     */
    protected function config_defaults()
    {
        // Конфигурация для базовой стратегии
        $configdata = [];
        
        // Хранилище логов для сохранения процесса работы стратегии
        $configdata['logger'] = null;
        
        // Менеджер работы с файлами
        $configdata['filemanager'] = null;
        
        return $configdata;
    }
    
    /**
     * Сброс конфигурации стартегии
     *
     * @return array
     */
    public function config_reset()
    {
        $this->config = $this->config_defaults();
    }
}
