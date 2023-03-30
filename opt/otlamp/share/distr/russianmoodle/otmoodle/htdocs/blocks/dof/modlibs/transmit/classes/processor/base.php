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
 * Обмен данных с внешними источниками. Базовый класс обработчиков
 *
 * @package    sync
 * @subpackage transmit
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

abstract class dof_modlib_transmit_processor_base
{
    /**
     * Контроллер деканата
     *
     * @var dof_control
     */
    protected $dof = null;

    /**
     * Обязательные поля для исполнения обработчика
     *
     * @var array
     */
    protected $required_slots = [];
    
    /**
     * Слоты на вход
     *
     * @var array
     */
    protected $input_slots = [];
    
    /**
     * Слоты на выход
     *
     * @var array
     */
    protected $output_slots = [];
    
    /**
     * Слоты по регулярке
     *
     * @var array
     */
    protected $regular_slots = [];

    /**
     * Поля для экспорта
     *
     * @var array
     */
    protected $export_slots = [];
    
    /**
     * Префиксы при экспорте
     *
     * @var string
     */
    protected $output_prefix = '';
    
    /**
     * В пуле должен быть хотя бы одно из указанных полей, чтобы обработчик начал работу
     *
     * @var array
     */
    protected $stop_slots = [];
    
    /**
     * Логгер
     *
     * @var dof_storage_logs_queuetype_base
     */
    protected $logger = null;

    /**
     * Получить код обработчика
     *
     * @return string
     */
    public final static function get_code()
    {
        return str_replace('dof_modlib_transmit_processor_'.static::get_type().'_', '', static::class);
    }
    
    /**
     * Получить тип обработчика
     *
     * @return string
     */
    public final static function get_type()
    {
        $fullcode = static::get_fullcode();
        return (string)substr($fullcode, 0, strpos($fullcode, '_'));
    }
    
    /**
     * Получить полный код обработчика
     *
     * @return string
     */
    public final static function get_fullcode()
    {
        return str_replace('dof_modlib_transmit_processor_', '', static::class);
    }
    
    /**
     * Запуск обработчика
     * 
     * @param array $input - Входящие данные
     * @param dof_control $dof - Контроллер Электронного Деканата
     * @param dof_storage_logs_queuetype_base $logger - Очередь логов
     * @param dof_modlib_transmit_source_filemanager $filemanager - Менеджер файлов
     * 
     * @return array - Исходящие данные
     */
    public static function execute($input, $dof, $logger, $filemanager)
    {
    }
    
    
    
    
    /** ОБЪЕКТНЫЕ МЕТОДЫ **/
    
    /**
     * Получить значение дополнительного поля, если оно есть в пуле
     *
     * @param array $datapool
     * @param string $fieldname
     *
     * @return string|null
     */
    protected final function get_input_slot(array $datapool, $fieldname)
    {
        if ( ! empty($this->input_slots[$fieldname]) &&
                isset($datapool[$this->input_slots[$fieldname]]) )
        {// Пол персоны
            return $datapool[$this->input_slots[$fieldname]];
        } else 
        {
            return NULL;
        }
    }
    
    /**
     * Получить слоты по регуляркам
     *
     * @param array $datapool
     *
     * @return string|null
     */
    protected final function get_regular_slots(array $datapool)
    {
        // Результирующий массив данных о критериях
        $result_data = [];
        
        if ( ! empty($this->regular_slots) )
        {
            foreach ( $datapool as $element => $value )
            {
                foreach ( $this->regular_slots as $name => $regular )
                {
                    
                    if ( preg_match($regular, $element) )
                    {
                        // Запишем в результирующий массив
                        $result_data[$name][$element] = $value;
                        break;
                    }
                }
            }
        }
        
        return $result_data;
    }
    
    /**
     * Исполнение
     *
     * @return bool
     */
    protected function prepared_execute_regular(array $datapool)
    {
        return [];
    }
    
    /**
     * Исполнение
     *
     * @return bool
     */
    protected function prepared_execute(array $datapool)
    {
        return [];
    }
    
    /**
     * Исполнение
     *
     * @return array
     */
    protected function prepared_execute_export(array &$data)
    {
        return [];
    }
    
    /**
     * Конструктор
     *
     * @param dof_control $dof
     * @param array $required_slots
     * @param array $input_slots
     * @param array $output_slots
     *
     * @return void
     */
    public function __construct(dof_control $dof, array $required_slots = [], array $input_slots = [], array $output_slots = [], array $regular_slots = [], array $export_slots = [], $output_prefix = '')
    {
        $this->dof = $dof;
        
        // Обязательные слоты для запуска обработчика
        $this->required_slots = array_merge($this->required_slots, $required_slots);
        
        // Дополнительные слоты (необязательные)
        $this->input_slots = array_merge($this->input_slots, $input_slots);
        
        // Выходные слоты
        $this->output_slots = array_merge($this->output_slots, $output_slots);
        
        // Регулярные слоты
        $this->regular_slots = array_merge($this->regular_slots, $regular_slots);
        
        // Экспорт слоты
        $this->export_slots = $export_slots;
        
        // Регулярные слоты
        $this->output_prefix = (is_string($output_prefix) ? $output_prefix : '');
    }
    
    /**
     * Исполнение
     *
     * @param array $datapool - ссылка на текущий пул
     *
     * @return void
     */
    public final function execute_import(array &$datapool)
    {
        // Флаг того, что обработчик может начать работу
        $can_execute = true;
        
        // Если в пуле отсутсвуют обязательные поля, обработчик не запускается
        $diff_required = array_diff($this->required_slots, array_keys($datapool));
        
        // Проверим стоп слоты
        if ( ! empty($this->stop_slots) )
        {
            $stop_slots = array_intersect_key($this->input_slots, array_combine($this->stop_slots, $this->stop_slots));
            $fields = array_intersect($stop_slots, array_keys($datapool));
            if ( empty($fields) )
            {
                $can_execute = false;
            } 
        }
        
        if ( empty($diff_required) && $can_execute )
        {
            // Получение обработанных данных из обработчика
            $new_data = $this->prepared_execute($datapool);
            
            if ( ! empty($new_data) && 
                    is_array($new_data) )
            {
                // Если в пуле есть требуемое поле, обработчик не переопределяет
                $output_slots = array_diff($this->output_slots, array_keys($datapool));
                
                foreach ( $output_slots as $local_name => $output_name )
                {
                    if ( isset($new_data[$local_name]) )
                    {// Запрошенное поле было выдано обработчиком, добавление в пул
                        $datapool[$this->output_slots[$local_name]] = $new_data[$local_name];
                    }
                }
            }
            
            // Получение обработанных данных из обработчика (Регулярные выражения)
            if ( ! empty($this->regular_slots) )
            {
                $new_data_regular = $this->prepared_execute_regular($datapool);
                if ( ! empty($new_data_regular) )
                {
                    foreach ( $new_data_regular as $item => $value )
                    {
                        if ( ! isset($datapool[$item]) )
                        {
                            $datapool[$item] = $value;
                        }
                    }
                }
            }
        }
    }
    
    /**
     * Исполнение
     * 
     * $param array $data - текущий пул
     *
     * @return array
     */
    public final function execute_export(array $data)
    {
        // Получение обработанных данных
        $data = $this->prepared_execute_export($data);
        
        if ( ! empty($data) )
        {
            foreach ( $data[$this->output_prefix] as $id => &$item )
            {
                if ( ! empty($this->export_slots) )
                {
                    foreach ( $this->export_slots as $name => $val )
                    {
                        if ( array_key_exists($name, $item) )
                        {
                            $item[$val] = $item[$name];
                            unset($item[$name]);
                        } else 
                        {
                            $item[$val] = '';
                        }
                    }
                }
            }
        }
        
        return $data;
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
        $this->logger = $logger;
    }
}
