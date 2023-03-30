<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

namespace otcomponent_otlogger;

defined('MOODLE_INTERNAL') || die();


abstract class log_base {
    
    /**
     * Имя плагина, к которому относится класс лога, в данном случае это родительский класс, поэтому 
     * не определено
     */
    const COMPONENT = null;
    
    /*
     * Тип лога
     */
    
    const LOG_TYPE = null;
    
    /*
     * Дефолтный тип лога
     */
    
    const LOG_KEY_DEFAULT = 'default';
    
    /**
     * Данные, которые будут залогированы для типа лога
     * 
     * @var array
     */
    private $data;
    
    /**
     *  Получение имени модуля. Для дочерних классов лучше получать языковую строку pluginname
     * 
     * @return string
     */
    final public  static function get_module_name(){
        return static::COMPONENT;
    }
    /**
     * Получить доступные коды логов 
     * 
     * @return array список доступнных кодов логов
     */
    
    final static public function get_available_keys(){
        
        $refl = new \ReflectionClass(get_called_class());
        $constants = $refl->getConstants();
        $keys = [];
        foreach ($constants as $constant => $value){
            if (preg_match('/^LOG_KEY_/', $constant)){
                $keys[] = $value;
            }
        }
        
        return $keys;
    }
    
    /**
     * Получить тип лога
     *
     * @return string тип лога
     */
    
    final public static function get_logtype(){
        return static::LOG_TYPE;
    }
    
    /**
     * Получить возможные свойства лога
     * 
     * @return array список доступных свойств лога - логируемых данных 
     */
    
    abstract public static function get_available_properties();
    
    /**
     * Записать лог
     * 
     *  Передает данные лога и сформированный код лога в log_manager для последующего логирования через выбранный получаатель
     *  @param mixed $messagedata - данные, которые нужно залогировать, чаще всего массив, может быть числом, строкой, массивом
     */
    public function create_log($key = 'default'){
        $keys = static::get_available_keys();
        $keys[] = 'default';
        if (! in_array($key, $keys)){
            throw new \coding_exception('Trying to create a log of an undefined type');
        }
        // Пишем что-то только если данные есть
        if (! empty($this->data)){
            $type = static::get_module_name() . '_' . static::get_logtype() . '_' . $key;
            
            log_manager::create_log($type, $this->data);
        }  
    }
    
    /**
     * Сеттер
     * 
     * @param string $name
     * @param mixed $value
     * @throws \coding_exception
     */
    
    final public function __set($name, $value){
        $availprops = static::get_available_properties();
        // Для любого лога доступно свойство other
        $availprops = array_merge($availprops, ['other']);
        // Записываем только свойства из списка доступных
        if (in_array($name,$availprops)){
            $this->data[$name] = $value;
        } else {
            throw new \coding_exception('Trying to set undeclared log property');
        }
    }
    
    /**
     * Геттер
     *
     * @param string $name
     * @param mixed $value
     * @throws \coding_exception
     */
    
    final public function __get($name){
        $availprops = static::get_available_properties();
        // Для любого лога доступно свойство other
        $availprops = array_merge($availprops, ['other']);
        // Возвращаем только доступные свойства
        if (in_array($name,$availprops)){
            if (array_key_exists($name, $this->data)){
                // вернем значение, если определено
                return $this->data[$name];
            } else {
                // Если свойство  не заполнено, вернем false
                return null;
            }
        } else {
            throw new \coding_exception('Trying to get undeclared log property');
        }
    }
    /**
     * Magic isset
     *
     * @param string $name
     * @param mixed $value
     * @throws \coding_exception
     */
    
    final public function __isset($name){
        $availprops = static::get_available_properties();
        // Для любого лога доступно свойство other
        $availprops = array_merge($availprops, ['other']);
        // Возвращаем только доступные свойства
        if (in_array($name,$availprops)){
            return isset($this->data[$name]);
        } else {
            throw new \coding_exception('Trying to get undeclared log property');
        }
    }
    
}