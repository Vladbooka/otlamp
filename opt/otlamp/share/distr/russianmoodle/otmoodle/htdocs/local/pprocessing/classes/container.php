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

namespace local_pprocessing;


defined('MOODLE_INTERNAL') || die();

/**
 * Контейнер переменных
 *
 * @package    local_pprocessing
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class container
{
    /**
     * Переменные
     *
     * @var array
     */
    protected $variables = [];
    
    /**
     * Переменные, которые нельзя очистить
     *
     * @var array
     */
    protected $const = [];
    
    /**
     * Переменные в исходном значении (без конвертации),
     * но объекты приводятся к массиву при обработке для возможности последующего json-кодирования
     *
     * @var array
     */
    protected $originalvars = [];
    
    /**
     * Переменные, которые нельзя очистить, в исходном значении (без конвертации),
     * но объекты приводятся к массиву при обработке для возможности последующего json-кодирования
     *
     * @var array
     */
    protected $originalconst = [];
    
    /**
     * Конфертация в скаляр (user->email->domain => user.email.domain)
     *
     * @param array|object $value
     *
     * @return array
     */
    protected function convert(&$arr, $value, $hyphen = '')
    {
        foreach ( (array)$value as $varname => $value )
        {
            if ( is_object($value) || is_array($value) )
            {
                $this->convert($arr, $value, $hyphen . $varname . '.');
            } else
            {
                $arr[strtolower($hyphen . $varname)] = $value;
            }
        }
    }
    
    /**
     * Установка значения в контейнер (при попытке осуществить повторную запись в переменную с тем же именем, запись произведена не будет)
     *
     * @param string $varname
     * @param mixed $value
     * @param bool $convertfirst - флаг означает, нужно ли конвертировать в скаляр первый уровень.
     *                              К примеру если у нас массив пользователей, то удобнее их держать в массиве, но при этом каждый юзер в виде скаляра
     *                              $users[], [0][fullname], чем [0.fullname]
     * @param bool $const - запись в константный
     *
     * @return void
     */
    private function set($varname, $value, $convertfirst = true, $const = false)
    {
        if ( empty($value) && $value != '0')
        {
            return;
        }
        
        // сохранение исходных, несконвертированных данных
        // @TODO: реализовать парсинг запроса в get-методе и попытку получать переменную из исходных данных вместо конвертации
        if ( ! array_key_exists($varname, $this->originalvars) )
        {
            $this->originalvars[$varname] = $value;
            if ( $const )
            {
                // установим переменную в неизменяемый массив
                $this->originalconst[$varname] = $value;
            }
        }
        
        $processed = [];
        if ( is_object($value) || is_array($value) )
        {// объект/массив -> сконвертируем в скаляр
            if ( $convertfirst )
            {
                // с конвертацией первого уровня массива
                $this->convert($processed, $value, $varname . '.');
            } else
            {
                // без конвертации первого уровня массива
                $arr = [];
                foreach ( (array)$value as $vname => $v )
                {
                    if ( is_object($v) || is_array($v)  )
                    {
                        $arr[$vname] = [];
                        $this->convert($arr[$vname], $v);
                    } else
                    {
                        $arr[$vname] = $v;
                    }
                }
                $processed = [$varname => $arr];
            }
        } else
        {// скаляр
            $processed = [$varname => $value];
        }
        
        foreach ( $processed as $var => $val )
        {
            if ( ! array_key_exists($var, $this->variables) )
            {
                $this->variables[$var] = $val;
                if ( $const )
                {
                    // установим переменную в неизменяемый массив
                    $this->const[$var] = $val;
                }
            }
        }
        $this->override($varname, $value);
    }
    
    /**
     * Перезаписать значение в контейнере
     *
     * @param string $varname
     * @param mixed $value
     *
     * @return void
     */
    private function override($varname, $value)
    {
        if ( array_key_exists($varname, $this->originalvars) )
        {
            $this->originalvars[$varname] = $value;
            if ( array_key_exists($varname, $this->originalconst) )
            {
                $this->originalconst[$varname] = $value;
            }
        }
        if ( array_key_exists($varname, $this->variables) )
        {
            $this->variables[$varname] = static::make_json_encodable($value);
            if ( array_key_exists($varname, $this->const) )
            {
                $this->const[$varname] = static::make_json_encodable($value);
            }
        }
    }
    
    /**
     * Получить значение из контейнера
     *
     * @param string $var
     *
     * @return mixed|null
     */
    public function export($varname)
    {
        if ( array_key_exists($varname, $this->originalvars) )
        {
            return $this->originalvars[$varname];
        }
        
        return null;
    }
    
    public function read($varname, $vars=null, $scalaronly=true)
    {
        if (is_null($vars))
        {
            $vars = $this->originalvars;
        }
        
        $parts = explode('.', $varname, 2);
        if (count($parts) == 2)
        {
            list($key, $tail) = $parts;
            
        } else
        {
            $key = $parts[0];
        }
        if (is_array($vars) && array_key_exists($key, $vars))
        {
            $value = $vars[$key];
        }
        
        if (is_object($vars) && property_exists($vars, $key))
        {
            $value = $vars->{$key};
        }
        
//         logger::write_log(
//             'scenario',
//             'core',
//             'debug',
//             [
//                 'tail' => var_export($tail, true),
//                 'scalaronly' => $scalaronly,
//                 'is_scalar' => is_scalar($value),
//                 'is_array' => is_array($value),
//                 'is_object' => is_object($value),
//                 'value' => var_export($value, true),
//                 'originalvars' => $this->originalvars,
//                 'variables' => $this->variables
//             ],
//             'readvar'
//         );
        if (isset($value))
        {
            if (!isset($tail) && (!$scalaronly || is_scalar($value)))
            {
                return $value;
            }
            
            if ((is_array($value) || is_object($value)) && isset($tail))
            {
                return $this->read($tail, $value, $scalaronly);
            }
            
            return null;
            
        } else
        {
            return null;
        }
    }
    
    /**
     * Получить все переменные контейнера
     *
     * @return array
     */
    public function get_all()
    {
        return $this->variables;
    }
    
    /**
     * Получить все не преобразованные переменные контейнера
     *
     * @return array
     */
    public function export_all()
    {
        return $this->originalvars;
    }
    
    /**
     * Очистка контейнера
     *
     * @return void
     */
    public function clear()
    {
        $this->originalvars = $this->originalconst;
        $this->variables = $this->const;
    }
    
    /**
     * Очистка содержимого контейнера по ключу
     * @param unknown $key
     */
    public function delete($key)
    {
        if( ! array_key_exists($key, $this->const) )
        {// Удаляем только, если не в const
            unset($this->variables[$key]);
        }
        if( ! array_key_exists($key, $this->originalconst) )
        {// Удаляем только, если не в const
            unset($this->originalvars[$key]);
        }
    }
    
    
    public static function make_json_encodable($value)
    {
        if (is_scalar($value))
        {
            return $value;
        }
        
        if (is_object($value))
        {
            $value = get_object_vars($value);
        }
        
        if (is_array($value))
        {
            $result = [];
            foreach($value as $k=>$v)
            {
                $result[$k] = static::make_json_encodable($v);
            }
            return $result;
        }
        
        return null;
    }
    
    /**
     * Составление вложенного объекта из ключей и значения
     *
     * @param array $obj - исходный массив, в который будут добавляться вложенные уровни
     * @param array $keys - ключи каждого из уровней вложенности
     * @param unknown $val - значение, записываемое в пару к последнему ключу
     *
     * @return array
     */
    private function add_path($obj, $keys, $val)
    {
        $result = [];
        if (count($keys) > 1)
        {
            $key = array_shift($keys);
            $result[$key] = $this->add_path($obj, $keys, $val);
        } else
        {
            $key = array_shift($keys);
            $result[$key] = $val;
        }
        return $result;
    }
    
    public function write($varname, $value, $convertfirst = true, $const = false)
    {
        // установим само значение для совместимости с предыдущей архитектурой
        // чтобы read смог получить, к примеру, user.profile.sex
        $this->set($varname, $value, $convertfirst, $const);
        
        // в соответствии с новой архитектурой сделаем замену только последней части объекта по имени переменной
        // то есть, если сюда передана переменная user.profile.sex, то существующий в контейнере ранее user.id не изменится
        // изменится только user.profile.sex на указанный...
        // а если в контейнере user или user.profile не было, то он будет создан и заполнен
        $mod = $this->add_path([], explode('.', $varname), static::make_json_encodable($value));
        $this->variables = array_replace_recursive($this->variables, $mod);
    }
}

