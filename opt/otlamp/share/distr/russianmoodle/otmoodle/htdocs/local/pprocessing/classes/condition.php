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

class condition {

    /**
     * @var array - допустимые цели
     */
    protected $targets = ['sql_condition', 'comparison_result'];

    /**
     * @var array - допустимые операторы
     */
    protected $operators = ['=', '<>', '>', '<', '>=', '<=', 'IN', 'NOT IN', 'LIKE', 'NOT LIKE', 'IS NULL', 'IS NOT NULL'];

    /** @var container $container- контейнер с переменными */
    protected $container = null;

    /** @var mixed $lastresult - результат, возвращенный последним обработчиком */
    protected $lastresult = null;

    /** @var int $index - индекс, используемый в виде префикса для исключения пересечения условий в запросе */
    protected $index = null;

    /** @var string $target         // цель условия
     *                              // sql_condition - возвращает sql условие и параметры
     *                              // comparison_result - возвращает результат сравнения двух значений
     */
    protected $target = null;

    /** @var string $type -         // используется при target=sql,
     *                              // в зависимости от переданного типа, корректно формирует запросы (в том числе подключает доп.таблицы)
     *                              // и подставляет параметры, используя префиксы
     *                              // по умолчанию - base, стандартная логика
     */
    protected $type = null;

    /**
     * @var string $field           // используется при target=sql,
     *                              // поле в таблице БД, которое подлежит сравнению с указанным значением
     */
    protected $field = null;

    /**
     * @var mixed $comparison_value // используется при target=comparison_result
     *                              // значение подлежащее сравнению
     *                              // должно быть представлено в виде скалярного значения
     *                              // может быть представлено в виде конфигурационного массива для получения значения из контейнера
     *                              //      - ключ элемента массива должен быть container
     *                              //      - при чтении переменной из контейнера, в качестве названия переменной
     *                              //        будет использоваться значение элемента конфигурационного массива
     *                              //      - условие к значению из контейнера - то же, что и для прямого указания - скаляр
     */
    protected $comparison_value = null;

    /**
     * @var string $operator        // оператор сравнения
     *                              // реализованные значения:
     *                              // '=' соответствует (равно) значению
     *                              // '<>' не соответствует (не равно) значению
     *                              // '>' больше указанного значения
     *                              // '<' меньше указанного значения
     *                              // '>=' больше указанного значения или соответствует ему
     *                              // '<=' меньше указанного значения или соответствует ему
     *                              // 'IN' входит в массив значений
     *                              // 'NOT IN' не входит в массив значений
     *                              // 'LIKE' - содержит указанное значение
     *                              // 'NOT LIKE' - не содержит указанное значение
     */
    protected $operator = null;

    /**
     * @var mixed $value            // значение для сравнения (правая часть сравниваемого выражения)
     *                              // может быть представлено в виде скалярного значения
     *                              // может быть представлено в виде массива значений (для операторов IN, NOT IN)
     *                              // может быть представлено в виде конфигурационного массива для получения значения из контейнера
     *                              //      - ключ элемента массива должен быть container
     *                              //      - при чтении переменной из контейнера, в качестве названия переменной
     *                              //        будет использоваться значение элемента конфигурационного массива
     *                              //      - может содержать элемент массива с ключом json_encoded
     *                              //        не пустое значение приведет к попытке декодирования значения контейнера
     *                              //      - может содержать элемент массива с ключом separated_list_divider
     *                              //        не пустое значение приведет к попытке разбиения значения контейнера на массив значений
     *                              //        с помощью указанного значения разделителя
     */
    protected $value = null;

    protected $debugging_level = 2;
    
    /**
     * Константа для сравнения значений типа float
     * @var number
     */
    protected $epsilon = 0.00001;


    /**
     * Создание экземпляра класса со свойствами, сконфигурированными в массиве
     * @param container $container - контейнер с переменными
     * @param mixed $lastresult - результат, возвращенный последним обработчиком
     * @param array $config - конфигурационный массив условия. Формат:
     *  [
     *      'target' =>             // sql_condition - возвращает sql условие и параметры
     *                              // comparison_result - возвращает результат сравнения двух значений
     *
     *      'type' =>               // используется при target=sql,
     *                              // в зависимости от переданного типа, корректно формирует запросы (в том числе подключает доп.таблицы)
     *                              // и подставляет параметры, используя префиксы
     *                              // по умолчанию - base, стандартная логика
     *
     *      'field' =>              // используется при target=sql,
     *                              // поле в таблице БД, которое подлежит сравнению с указанным значением
     *
     *      'comparison_value' =>   // используется при target=comparison_result
     *                              // значение подлежащее сравнению
     *                              // должно быть представлено в виде скалярного значения
     *                              // может быть представлено в виде конфигурационного массива для получения значения из контейнера
     *                              //      - ключ элемента массива должен быть container
     *                              //      - при чтении переменной из контейнера, в качестве названия переменной
     *                              //        будет использоваться значение элемента конфигурационного массива
     *                              //      - условие к значению из контейнера - то же, что и для прямого указания - скаляр
     *
     *      'operator' =>           // оператор сравнения
     *                              // реализованные значения:
     *                              // '=' соответствует (равно) значению
     *                              // '<>' не соответствует (не равно) значению
     *                              // '>' больше указанного значения
     *                              // '<' меньше указанного значения
     *                              // '>=' больше указанного значения или соответствует ему
     *                              // '<=' меньше указанного значения или соответствует ему
     *                              // 'IN' входит в массив значений
     *                              // 'NOT IN' не входит в массив значений
     *                              // 'LIKE' - содержит указанное значение
     *                              // 'NOT LIKE' - не содержит указанное значение
     *
     *      'value' =>              // значение для сравнения (правая часть сравниваемого выражения)
     *                              // может быть представлено в виде скалярного значения
     *                              // может быть представлено в виде массива значений (для операторов IN, NOT IN)
     *                              // может быть представлено в виде конфигурационного массива для получения значения из контейнера
     *                              //      - ключ элемента массива должен быть container
     *                              //      - при чтении переменной из контейнера, в качестве названия переменной
     *                              //        будет использоваться значение элемента конфигурационного массива
     *                              //      - может содержать элемент массива с ключом json_encoded
     *                              //        не пустое значение приведет к попытке декодирования значения контейнера
     *                              //      - может содержать элемент массива с ключом separated_list_divider
     *                              //        не пустое значение приведет к попытке разбиения значения контейнера на массив значений
     *                              //        с помощью указанного значения разделителя
     *
     *  ]
     * @param int $index - индекс, используемый в виде префикса для исключения пересечения условий в запросе
     *
     * @return condition - экземпляр класса условия
     */
    public static function construct_from_config(&$container, $lastresult, $config, $index=null, $debugginglevel=2)
    {
//         logger::write_log('executor', 'condition', 'debug', ['config' => $config]);

        $cond = new condition();
        $cond->set_container($container);
        $cond->set_last_result($lastresult);
        $cond->set_index($index);
        $cond->set_target_from_config($config);
        $cond->set_debugging_level($debugginglevel);
        switch($cond->get_target())
        {
            case 'sql_condition':
                $cond->set_type_from_config($config);
                $cond->set_field_from_config($config);
                break;
            case 'comparison_result':
                $cond->set_comparison_value_from_config($config);
                break;
        }
        $cond->set_operator_from_config($config);
        $cond->set_value_from_config($config);

        return $cond;
    }

    public function set_debugging_level($level)
    {
        $this->debugging_level = $level;
    }

    /**
     * Установка контейнера
     *
     * @param container $container
     *
     * @return void
     */
    public function set_container(&$container)
    {
        $this->container = &$container;
    }

    /**
     * Установка результата, полученного при исполнении последнего обработчика
     *
     * @param mixed $lastresult - результат, полученный при исполнении последнего обработчика
     *
     * @return void
     */
    public function set_last_result($lastresult)
    {
        $this->lastresult = $lastresult;
    }

    /**
     * Получение результата, полученного при исполнении последнего обработчика
     *
     * @return mixed
     */
    public function get_last_result()
    {
        return $this->lastresult;
    }

    /**
     * Установка индекса
     * @param string $index - индекс, используемый в виде префикса для исключения пересечения условий в запросе
     * @return void
     */
    public function set_index($index)
    {
        $this->index = $index;
    }

    /**
     * Получение индекса
     * @return string - индекс, используемый в виде префикса для исключения пересечения условий в запросе
     */
    public function get_index()
    {
        return $this->index;
    }

    /**
     * Извлечение цели из конфигурации и установка в качестве свойства экземпляра класса
     * @param array $config - конфигурационный массив условия. Формат см. в методе construct_from_config
     */
    public function set_target_from_config($config)
    {
        $target = 'sql_condition';

        if (array_key_exists('target', $config))
        {
            $target = $config['target'];
        }

        $this->set_target($target);
    }

    /**
     * Установка цели условия
     * @param string $target        // цель условия
     *                              // sql_condition - возвращает sql условие и параметры
     *                              // comparison_result - возвращает результат сравнения двух значений
     * @throws \Exception
     */
    public function set_target($target)
    {
        if (!in_array($target, $this->targets))
        {
            throw new \Exception('unknown target');
        }

        $this->target = $target;
    }
    /**
     * Получение цели условия
     * @return string $target        // цель условия
     *                              // sql_condition - возвращает sql условие и параметры
     *                              // comparison_result - возвращает результат сравнения двух значений
     */
    public function get_target()
    {
        return $this->target;
    }

    /**
     * Извлечение типа сравниваемого объекта из конфигурации и установка в качестве свойства экземпляра класса
     * @param array $config - конфигурационный массив условия. Формат см. в методе construct_from_config
     */
    public function set_type_from_config($config)
    {
        $type = 'base';

        if (array_key_exists('type', $config))
        {
            $type = $config['type'];
        }

        $this->set_type($type);
    }

    /**
     * Установка типа сравниваемого объекта
     * @param string $type -         // используется при target=sql,
     *                              // в зависимости от переданного типа, корректно формирует запросы (в том числе подключает доп.таблицы)
     *                              // и подставляет параметры, используя префиксы
     *                              // по умолчанию - base, стандартная логика
     * @throws \Exception
     */
    public function set_type($type)
    {
        $classname = '\\local_pprocessing\\sql_condition\\' . $type;

        if (!class_exists($classname))
        {
            throw new \Exception('class '.$classname.' not exists');
        }

        $this->type = $type;
    }

    /**
     * Получение типа сравниваемого объекта
     * @return string $type -         // используется при target=sql,
     *                              // в зависимости от переданного типа, корректно формирует запросы (в том числе подключает доп.таблицы)
     *                              // и подставляет параметры, используя префиксы
     *                              // по умолчанию - base, стандартная логика
     */
    public function get_type()
    {
        return $this->type;
    }
    /**
     * Извлечение поля таблицы, значение которого подлежит сравнению, из конфигурации и установка в качестве свойства экземпляра класса
     * @param array $config - конфигурационный массив условия. Формат см. в методе construct_from_config
     * @throws \Exception
     */
    public function set_field_from_config($config)
    {
        if (!array_key_exists('field', $config))
        {
            throw new \Exception('missing \'field\' field in configuration');
        }

        $this->set_field($config['field']);
    }
    /**
     * Установка поля таблицы, значение которого подлежит сравнению
     * @param string $field           // используется при target=sql,
     *                              // поле в таблице БД, которое подлежит сравнению с указанным значением
     */
    public function set_field($field)
    {
        $inputparam = new input_parameter($field, $this->container, $this->get_last_result());
        $this->field = $inputparam->get_value();
    }
    /**
     * Получение поля таблицы, значение которого подлежит сравнению
     * @return string $field           // используется при target=sql,
     *                              // поле в таблице БД, которое подлежит сравнению с указанным значением
     */
    public function get_field()
    {
        return $this->field;
    }

    /**
     * Извлечение значения, подлежащего сравнению из конфигурации и установка в качестве свойства экземпляра класса
     * @param array $config - конфигурационный массив условия. Формат см. в методе construct_from_config
     * @throws \Exception
     */
    public function set_comparison_value_from_config($config)
    {
        if (!array_key_exists('comparison_value', $config))
        {
            throw new \Exception('missing \'comparison_value\' field in configuration');
        }

        $this->set_comparison_value($config['comparison_value']);
    }
    /**
     * Установка значения, подлежащего сравнению
     * @param mixed $comparison_value // используется при target=comparison_result
     *                              // значение подлежащее сравнению
     *                              // должно быть представлено в виде скалярного значения
     *                              // может быть представлено в виде конфигурационного массива для получения значения из контейнера
     *                              //      - ключ элемента массива должен быть container
     *                              //      - при чтении переменной из контейнера, в качестве названия переменной
     *                              //        будет использоваться значение элемента конфигурационного массива
     *                              //      - условие к значению из контейнера - то же, что и для прямого указания - скаляр
     * @throws \Exception
     */
    public function set_comparison_value($compval)
    {
        $inputparam = new input_parameter($compval, $this->container, $this->get_last_result());
        $compval = $inputparam->get_value();

        if (!is_scalar($compval))
        {
            throw new \Exception('comparison value is not a scalar');
        }

        $this->comparison_value = $compval;
    }

    /**
     * Получение значения, подлежащего сравнению
     * @return mixed $comparison_value // используется при target=comparison_result
     *                              // значение подлежащее сравнению
     *                              // должно быть представлено в виде скалярного значения
     *                              // может быть представлено в виде конфигурационного массива для получения значения из контейнера
     *                              //      - ключ элемента массива должен быть container
     *                              //      - при чтении переменной из контейнера, в качестве названия переменной
     *                              //        будет использоваться значение элемента конфигурационного массива
     *                              //      - условие к значению из контейнера - то же, что и для прямого указания - скаляр
     */
    public function get_comparison_value()
    {
        return $this->comparison_value;
    }


    /**
     * Извлечение оператора сравнения из конфигурации и установка в качестве свойства экземпляра класса
     * @param array $config - конфигурационный массив условия. Формат см. в методе construct_from_config
     * @throws \Exception
     */
    public function set_operator_from_config($config)
    {
        if (!array_key_exists('operator', $config))
        {
            throw new \Exception('operator not specified');
        }

        $this->set_operator($config['operator']);
    }

    /**
     * Установка оператора сравнения
     * @param string $operator        // оператор сравнения
     *                              // реализованные значения:
     *                              // '=' соответствует (равно) значению
     *                              // '<>' не соответствует (не равно) значению
     *                              // '>' больше указанного значения
     *                              // '<' меньше указанного значения
     *                              // '>=' больше указанного значения или соответствует ему
     *                              // '<=' меньше указанного значения или соответствует ему
     *                              // 'IN' входит в массив значений
     *                              // 'NOT IN' не входит в массив значений
     *                              // 'LIKE' - содержит указанное значение
     *                              // 'NOT LIKE' - не содержит указанное значение
     * @throws \Exception
     */
    public function set_operator($operator)
    {

        $inputparam = new input_parameter($operator, $this->container, $this->get_last_result());
        $operator = $inputparam->get_value();

        if (!in_array($operator, $this->operators))
        {
            throw new \Exception('unknown operator');
        }

        $this->operator = $operator;
    }

    /**
     * Получение оператора сравнения
     * @return string $operator        // оператор сравнения
     *                              // реализованные значения:
     *                              // '=' соответствует (равно) значению
     *                              // '<>' не соответствует (не равно) значению
     *                              // '>' больше указанного значения
     *                              // '<' меньше указанного значения
     *                              // '>=' больше указанного значения или соответствует ему
     *                              // '<=' меньше указанного значения или соответствует ему
     *                              // 'IN' входит в массив значений
     *                              // 'NOT IN' не входит в массив значений
     *                              // 'LIKE' - содержит указанное значение
     *                              // 'NOT LIKE' - не содержит указанное значение
     *                              // 'IS NULL' - соответствует (равно) значению null
     *                              // 'IS NOT NULL' - не соответствует (не равно) значению null
     */
    public function get_operator()
    {
        return $this->operator;
    }


    /**
     * Извлечение значения (правая часть сравниваемого выражения) из конфигурации и установка в качестве свойства экземпляра класса
     * @param array $config - конфигурационный массив условия. Формат см. в методе construct_from_config
     * @throws \Exception
     */
    public function set_value_from_config($config)
    {
        if (!array_key_exists('value', $config))
        {
            throw new \Exception('value not specified');
        }

        $this->set_value($config['value']);
    }

    /**
     * Установка значения для сравнения (правая часть сравниваемого выражения)
     * @param mixed $value            // значение для сравнения (правая часть сравниваемого выражения)
     *                              // может быть представлено в виде скалярного значения
     *                              // может быть представлено в виде массива значений (для операторов IN, NOT IN)
     *                              // может быть представлено в виде конфигурационного массива для получения значения из контейнера
     *                              //      - ключ элемента массива должен быть container
     *                              //      - при чтении переменной из контейнера, в качестве названия переменной
     *                              //        будет использоваться значение элемента конфигурационного массива
     *                              //      - может содержать элемент массива с ключом json_encoded
     *                              //        не пустое значение приведет к попытке декодирования значения контейнера
     *                              //      - может содержать элемент массива с ключом separated_list_divider
     *                              //        не пустое значение приведет к попытке разбиения значения контейнера на массив значений
     *                              //        с помощью указанного значения разделителя
     * @throws \Exception
     */
    public function set_value($configvalue)
    {
        $inputparam = new input_parameter($configvalue, $this->container, $this->get_last_result());
        $this->value = $inputparam->get_value();
    }

    /**
     * Получение значения для сравнения
     * @return mixed $value            // значение для сравнения (правая часть сравниваемого выражения)
     *                              // может быть представлено в виде скалярного значения
     *                              // может быть представлено в виде массива значений (для операторов IN, NOT IN)
     *                              // может быть представлено в виде конфигурационного массива для получения значения из контейнера
     *                              //      - ключ элемента массива должен быть container
     *                              //      - при чтении переменной из контейнера, в качестве названия переменной
     *                              //        будет использоваться значение элемента конфигурационного массива
     *                              //      - может содержать элемент массива с ключом json_encoded
     *                              //        не пустое значение приведет к попытке декодирования значения контейнера
     *                              //      - может содержать элемент массива с ключом separated_list_divider
     *                              //        не пустое значение приведет к попытке разбиения значения контейнера на массив значений
     *                              //        с помощью указанного значения разделителя
     */
    public function get_value()
    {
        return $this->value;
    }


    /**
     * Получение sql-кода реализующего условие
     * @throws \Exception
     * @return array($sql, $params)
     */
    public function get_sql_condition()
    {
        $type = $this->get_type();
        $field = $this->get_field();
        $operator = $this->get_operator();
        $value = $this->get_value();

        logger::write_log('executor', str_pad('', ($this->debugging_level * 2), '-').'condition', 'debug', [
            'target' => $this->get_target(),
            '$type' => $type,
            '$field' => $field,
            '$operator' => $operator,
            '$value' => $value
        ]);

        if ($this->get_target() == 'sql_condition' &&
            !is_null($type) && !is_null($field) && !is_null($operator))
        {
            $classname = '\\local_pprocessing\\sql_condition\\' . $type;
            if (class_exists($classname))
            {
                /** @var \local_pprocessing\sql_condition\base $sqlcond **/
                $sqlcond = new $classname;
                return $sqlcond->get_select_pieces($field, $operator, $value, $this->get_index());
            } else
            {
                throw new \Exception('class '.$classname.' not exists');
            }
        }

        throw new \Exception('error occured while composing sql-condition, condition is misconfigured');
    }

    /**
     * Получение результата сравнения двух значений по условию
     * @throws \Exception
     * @return boolean
     */
    public function get_comparison_result()
    {

        $compval = $this->get_comparison_value();
        $operator = $this->get_operator();
        $value = $this->get_value();

        if ($this->get_target() == 'comparison_result' &&
            !is_null($compval) && !is_null($operator) && !is_null($value))
        {
            switch($operator)
            {
                case '=':
                    if (is_float($compval) || is_float($value)) {
                        if (abs($compval - $value) < $this->epsilon) {
                            return true;
                        } else {
                            return false;
                        }
                    } else {
                        return $compval == $value;
                    }
                    break;
                case '<>':
                    return $compval != $value;
                    break;
                case '>':
                    return $compval > $value;
                    break;
                case '<':
                    return $compval < $value;
                    break;
                case '>=':
                    return $compval >= $value;
                    break;
                case '<=':
                    return $compval <= $value;
                    break;
                case 'IN':
                    return (is_array($value) && in_array($compval, $value));
                    break;
                case 'NOT IN':
                    return (is_array($value) && !in_array($compval, $value));
                    break;
                case 'LIKE':
                    return strpos($compval, $value) !== false;
                    break;
                case 'NOT LIKE':
                    return strpos($compval, $value) === false;
                    break;
                case 'IS NULL':
                    return is_null($compval);
                    break;
                case 'IS NOT NULL':
                    return !is_null($compval);
                    break;
            }
        }

        throw new \Exception('error occured while comparing values, condition is misconfigured' . json_encode([
            'compval' => $compval,
            'operator' => $operator,
            'value' => $value
        ]));
    }

    private function debugging($data, $comment='')
    {
        $pad = str_pad('', ($this->debugging_level * 2), '-');
        logger::write_log('executor', $pad.'condition', 'debug', $data, $comment);
    }
}