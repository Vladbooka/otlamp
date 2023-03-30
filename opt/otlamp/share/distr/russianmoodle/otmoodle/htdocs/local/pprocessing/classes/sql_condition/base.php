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

namespace local_pprocessing\sql_condition;

defined('MOODLE_INTERNAL') || die();

/**
 * Абстрактный класс для формирования части sql-запроса на выборку по переданным параметрам
 *
 */
class base
{
    /**
     * Метод формирования sql-строки (пример: list($sql, $params) = $obj->get_select_pieces($el, & $i, & $con))
     * @param array $element массив вида [
     *                                       'type' => тип поля (может отсутствовать),
     *                                       'field' => имя поля,
     *                                       'operator' => оператор сравнения,
     *                                       'value' => значение для сравнения (может выбираться из контейнера)
     *                                   ]
     * @param int $index внешний индекс для формирования параметров
     * @param local_pprocessing\container $container объект контейнера
     * @return array [$sql, $params] массив с частью запроса и параметрами
     */
    public function get_select_pieces($field, $operator, $value, $prefix)
    {
        list($sqlpart, $params) = $this->prepare_sql_part($field, $operator, $value, $prefix);
        
        $table = '';
        if (!is_null($this->get_table_alias()))
        {
            $table = $this->get_table_alias() . '.';
        }
        
        return [$table . $field . ' ' . $sqlpart, $params];
    }
    
    /**
     * Алиас таблицы для поля
     * @var string
     */
    protected function get_table_alias()
    {
        return null;
    }
    
    /**
     * Префикс для формирования списка параметров
     * @var string
     */
    protected function get_param_prefix()
    {
        return null;
    }
    
    public function prepare_sql_part($field, $operator, $value, $prefix)
    {
        global $DB;
        
        $paramprefix = '';
        if (!is_null($this->get_param_prefix()))
        {
            $paramprefix .= $this->get_param_prefix() . '_';
        }
        $paramprefix .= (string)$prefix;
        
        $params = [];
        $sqlpart = '';
        
        switch($operator)
        {
            case 'LIKE':
                $sqlpart = $DB->sql_like('', ':value_' . $paramprefix);
                $params['value_' . $paramprefix] = '%' . $value . '%';
                break;
            case 'NOT LIKE':
                $sqlpart = $DB->sql_like('', ':value_' . $paramprefix, true, true, true);
                $params['value_' . $paramprefix] = '%' . $value . '%';
                break;
            case 'IN':
                if( ! is_array($value) )
                {
                    $value = [$value];
                }
                list($sqlpart, $params) = $DB->get_in_or_equal($value, SQL_PARAMS_NAMED, $paramprefix.'_');
                break;
            case 'NOT IN':
                if( ! is_array($value) )
                {
                    $value = [$value];
                }
                list($sqlpart, $params) = $DB->get_in_or_equal($value, SQL_PARAMS_NAMED, $paramprefix.'_', false);
                break;
            case 'IS NULL':
            case 'IS NOT NULL':
                $sqlpart = $operator;
                break;
            case '<>':
            case '=':
            case '>=':
            case '<=':
            case '>':
            case '<':
            default:
                $sqlpart = $operator . ' :value_' . $paramprefix;
                $params['value_' . $paramprefix] = $value;
                break;
        }
        return [$sqlpart, $params];
    }
}