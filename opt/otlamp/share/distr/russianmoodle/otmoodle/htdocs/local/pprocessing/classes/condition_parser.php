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

use local_pprocessing\sql_condition\user_main_field;
use local_pprocessing\sql_condition\user_custom_field;
use local_pprocessing\sql_condition\user_preference;

/**
 * Класс преобразования структуры данных в sql-запрос на выборку
 *
 */
class condition_parser
{
    /**
     * Переданная структура данных (формат можно посмотреть в unit-тестах плагина)
     * @var array
     */
    protected $structure;
    
    /**
     * Объект контейнера
     * @var local_pprocessing\container
     */
    protected $container;

    /**
     * Логические операторы
     * @var array
     */
    protected $logicoperators = ['AND', 'OR'];
    
    /**
     * Массив поддерживаемых типов полей
     * @var array
     */
    protected $types = ['user_main_field', 'user_custom_field', 'user_preference', 'cohort'];
    
    /**
     * Индекс для формирования массива параметров, передаваемых в запрос на выборку
     * @var integer
     */
    protected $index = 1;
    
    /**
     * @var mixed $lastresult - результат, возвращенный последним обработчиком
     */
    protected $lastresult = null;
    
    protected $debugging_level = 2;
    
    /**
     * Массив параметров, передаваемых в запрос на выборку
     * @var array
     */
    protected $params = [];

    /**
     * Констурктор
     * @param array $structure
     */
    public function __construct($structure, $container, $lastresult=null)
    {
        // Инициализация структуры и контейнера
        $this->structure = $structure;
        $this->container = $container;
        $this->lastresult = $lastresult;
    }
    
    public function set_debugging_level($level)
    {
        $this->debugging_level = $level;
    }

    /**
     * Рекурсивная функция, преобразует переданную структуру в строку для sql-запроса
     * @param array $structure
     * @param array $containers массив контейнеров, из которых можно брать данные для подстановки значений
     * @return boolean|string|array если передана не верная структура, вернет false, если передана пустая структура, вернет истинное условие
     */
    protected function get_select_array($structure = null)
    {
        if( is_null($structure) )
        {// Если при вызове не передана структура, берем ту, что указана при создании объекта
            $structure = $this->structure;
        }
        // Иницализация переменных
        $select = $selectpart = $error = [];
        $selectstr = '';
        foreach($structure as $groupskey => $groups)
        {
            if( is_array($groups) )
            {// Если структура не из одного элемента
                foreach($groups as $group)
                {
                    $finalgroup = true;
                    foreach($this->logicoperators as $logicoperator)
                    {// Если в ключах есть логический оператор, то не конечная група элементов
                        if( array_key_exists($logicoperator, $group) )
                        {
                            $finalgroup = false;
                            break;
                        }
                    }
                    if( ! $finalgroup && count($groups) <= 1 )
                    {// Если не конечная группа элементов и количество элементов не больше одного - передана не верная структура, выбросим ошибку
                        $error['structure'] = true;
                        break;
                    }
                    if( $finalgroup )
                    {// Если конечная группа элементов, передадим управление на преобразование группы в строку для поиска
                        list($selectstr, $params) = $this->get_group_select($group, $this->index);
                        $selectpart[] = '(' . $selectstr . ')';
                        $this->params = array_merge($this->params, $params);
                        $this->index++;
                    } else
                    {// Если не конечная группа
                        $elements = current($group);
                        if( count($elements) <= 1 )
                        {// Если для объединения логическим оператором передан только один элемент - передана не верная структура
                            $error['structure'] = true;
                            break;
                        }
                        // Собираем матрешку, объединяя с самого низа кусочки логическими операторами
                        $selectpart[] = '(' . implode(' ' . $groupskey . ' ', $this->get_select_array($group)) . ')';
                    }
                }
                if( ! empty($error) )
                {// Если есть ошибки - заканчиваем работу
                    break;
                }
                // Объединяем собранные куски
                $select[] = implode(' ' . $groupskey . ' ', $selectpart);
            } else
            {// Если передан один элемент
                list($select[], $params) = $this->get_group_select($structure, $this->index);
                $this->params = array_merge($this->params, $params);
                $this->index++;
                break;
            }
        }
        if( empty($select) )
        {// Если передана пустая структура
            $select[] = '1=1';
        }
        return empty($error) ? $select : false;
    }

    protected function get_group_select($group, $index)
    {
        $cond = condition::construct_from_config($this->container, $this->lastresult, $group, $index, $this->debugging_level);
        return $cond->get_sql_condition();
    }
    
    /**
     * Возвращает массив вида [$sql, $params]
     * @return array|boolean возвращает false, если передана структура не верного формата
     */
    public function parse()
    {
        $result = $this->get_select_array();
        if( $result !== false )
        {
            $sql = '(' . array_shift($result) . ')';
            return [$sql, $this->params];
        } else
        {
            return false;
        }
    }
}