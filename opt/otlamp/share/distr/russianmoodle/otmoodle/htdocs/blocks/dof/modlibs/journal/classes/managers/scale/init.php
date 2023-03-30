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

/**
 * Подсистема шкал/проходного балла ЭД
 * 
 * @package    block_dof
 * @subpackage modlib_journal
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

class dof_modlib_journal_scale extends dof_modlib_journal_basemanager
{
    /**
     * Нормализация переменной с указанием названия поля
     *
     * @param stdClass|array|int $cstreamid - переменная для нормализации
     * @param string $search - название поля для нормализации
     *
     * @return string - результат переменной после нормализации
     */
    public function normalize($var = null, $search = 'id')
    {
        $normalized = null;
        
        // Нормализация данных
        if ( is_object($var) &&
                property_exists($var, $search) &&
                ! empty($var->{$search}) )
        {// Передан объект
            $normalized = $var->{$search};
        } elseif ( is_array($var) &&
                isset($var[$search]) &&
                ! empty($var[$search]) )
        {// Передан массив
            $normalized = $var[$search];
        } else 
        {
            $normalized = $var;
        }
        
        return $normalized;
    }
    
    /**
     * Нормализация шкалы
     *
     * @param array $scale - шкала
     *
     * @return array
     */
    public function normalize_scale($scale = [])
    {
        if ( empty($scale) &&
                ! is_array($scale) )
        {
            return [];
        }
        
        foreach ( $scale as &$grade )
        {
            $grade = strip_tags(trim($grade));
            if ( is_numeric($grade) )
            {
                continue;
            }
            switch ( $grade )
            {
                case 'н':
                case 'н/о':
                case 'незачтено':
                case 'не зачтено':
                case 'незачет':
                case 'незачёт':
                    $grade = 0;
                    break;
                case 'зачтено':
                case 'зачет':
                case 'зачёт':
                    $grade = 3;
                    break;
                default:
                    break;
            }
        }
        
        // Сортировка массива
        asort($scale);
        
        return $scale;
    }
    
    /**
     * Метод проверяющий возможно смены шкалы у КТ
     * Если по КТ есть хотя бы одна выставленная оценка, то шкалу менять нельзя
     *
     * @param int $planid
     *
     * @return bool
     */
    public function can_change_plan_scale($planid)
    {
        $plan = $this->dof->modlib('journal')->get_manager('lessonprocess')->get_plan($planid);
        if ( empty($plan) )
        {
            return false;
        }
        
        $grades = $this->dof->storage('cpgrades')->get_plan_grades($planid);
        foreach ( $grades as $grade )
        {
            if ( strlen($grade->grade) > 0 )
            {
                return false;
            }
        }
        
        return true;
    }
    
    /**
     * Можно ли менять шкалу дисциплины
     * 
     * @param $pitemid $pitem
     * @param array $newscale
     * 
     * @return bool
     */
    public function can_change_programmitem_scale($pitemid, $newscale)
    {
        $programmitem = $this->dof->storage('programmitems')->get_record(['id' => $pitemid]);
        if ( empty($programmitem) )
        {
            return false;
        }
        if ( ! empty($programmitem->lessonscale) )
        {
            $scale = $programmitem->lessonscale;
        } else
        {
            $scale = $programmitem->scale;
        }
        
        if ( $scale == $newscale )
        {
            return true;
        }
        
        $cstreams = $this->dof->storage('cstreams')->get_records(
                [
                    'programmitemid' => $programmitem->id,
                    'status' => array_keys($this->dof->workflow('cstreams')->get_meta_list('real'))
                ]
                );
        if ( empty($cstreams) )
        {
            return true;
        }
        
        foreach ($cstreams as $cstream)
        {
            $plans = $this->dof->storage('plans')->get_records(
                    [
                        'linktype' => 'cstreams', 
                        'linkid' => $cstream->id,
                        'status' => array_keys($this->dof->workflow('plans')->get_meta_list('real'))
                    ]
                    );
            if ( ! empty($plans) )
            {
                foreach ($plans as $plan)
                {
                    $grades = $this->dof->storage('cpgrades')->get_records(
                            [
                                'planid' => $plan->id
                            ]
                            );
                    foreach ($grades as $grade)
                    {
                        if ( strlen($grade->grade) > 0 )
                        {
                            return false;
                        }
                    }
                }
            }
        }
        
        return true;
    }
    
    /** 
     * Проверяет правильность задания шкалы оценок
     *
     * @param string $scale - шкала оценок
     *
     * @todo предусмотреть случай с отрицательными числами
     * @todo добавить проверку того, действительно ли по возрастанию расположены числовые оценки
     * 
     * @return []
     */
    public function is_scale_valid($scale, $varname = 'scale')
    {
        if ( ! trim($scale) )
        {// шкала не задана - это ошибка
            return [$varname => $this->dof->get_string('err_scale', 'journal', null, 'modlib')];
        }
        // разбиваем шкалу на отдельные части
        $scale = explode(',',  trim($scale));
        
        foreach ( $scale as $element )
        {// начинаем проверять переданную шкалу
            if ( ! trim($element) AND trim($element) != '0')
            {// пустые элементы в шкале неодпустимы
                return [$varname => $this->dof->get_string('err_scale_null_element', 'journal', null, 'modlib')];
            }
            if ( preg_match('/-/', $element) )
            {// это диапазон
                $boundaries = explode('-', $element);
                if ( count($boundaries) != 2 )
                {// диапазон задан неправильно
                    return [$varname => $this->dof->get_string('err_scale', 'journal', null, 'modlib')];
                }
                // определим границы максимальных и минимальных значений
                $min = $boundaries[0];
                $max = $boundaries[1];
                if ( ($min == '' AND ! is_numeric($max)) OR (! is_numeric($min) AND $max == '') OR
                        ($min != '' AND $max != '' AND (! is_numeric($max) OR ! is_numeric($min))) )
                {// диапазоны могут быть только числовыми
                    return [$varname => $this->dof->get_string('err_scale_not_number_diapason', 'journal', null, 'modlib')];
                }
                if ( $min == $max )
                {// максимальная оценка в диапазоне равна минимальной:
                    // диапазон задан неверно
                    return [$varname => $this->dof->get_string('err_scale_max_min_must_be_different', 'journal', null, 'modlib')];
                }
            }
        }
        // если ошибки есть - то возвращаем массив, в котором указано, что именно произошло
        // если нет - то просто пустой массив
        return [];
    }
    
    /** 
     * Определяет, допустима ли переданная оценка для данной дисциплины
     *  
     * @param $grade string
     * @param scale array
     * 
     * @return bool
     */
    public function is_grade_valid($grade, $scale)
    {
        if ( is_null($scale) )
        {// шкала оценок не указана - берем ее из базы
            return false;
        }
        if ( is_null($grade) OR ( !trim($grade) AND trim($grade) != '0') )
        {// нет оценки - значит мы не можем ее выставить';
            return false;
        }
        // преобразуем шкула в массив
        if ( ! is_array($scale) )
        {
            $scale = $this->get_grades_scale_str($scale);
        }
        if ( in_array($grade, $scale) )
        {
            return true;
        }
        
        return false;
    }
    
    /** Определить, является ли переданная оценка положительной
     * (достаточной, для продолжения обучения)
     * @return bool
     * @param int $programmitemid - id предмета, по которому выставляется итоговая оценка
     * @param string $grade - выставляемая оценка
     * @todo разбить функцию на более мелкие фрагменты
     */
    public function is_positive_grade($grade, $mingrade, $scale)
    {
        if ( ! $this->is_grade_valid($grade, $scale) )
        {// переданная оценка недопустима
            return false;
        }
        
        if ( ! trim($mingrade) )
        {// минимальная оценка не задана - считаем любую оценку положительной
            return true;
        }
        
        if ( ! $this->is_grade_passed($grade, $mingrade, $scale) )
        {// путем анализа шкалы, мы установили, что оценка положительная
            return false;
        }
        
        // все проверки прошли успешно
        return true;
    }
    
    /** 
     * Определить, является ли переданная оценка допустимой для шкалы текущего предмета
     *
     * @todo доработать вариант со шкалой, определенной ва обратном порядке
     * (например, где 1-максимум, а 10-минимум)
     *
     * @param object $pitem - объект из таблицы programmitems
     * @param string $grade - выставляемая оценка
     * 
     * @return bool
     */
    protected function is_grade_passed($grade, $mingrade, $scale)
    {
        // преобразеум шкалу в индексный массив
        if ( ! is_array($scale) )
        {
            $scale = $this->get_grades_scale_str($scale);
        }
        $scale = array_values($scale);
        $key_mingrade = array_keys($scale, $mingrade);
        $key_grade = array_keys($scale, $grade);
        if ( $key_grade[0] >= $key_mingrade[0] )
        {
            return true;
        }
        return false;
    }
    
    /**
     * Получить шкалу оценок для дисциплины
     *
     * @param int $programmitemid
     *
     * @return []
     */
    public function get_programmitem_scale($programmitemid = null)
    {
        global $addvars;
        
        $programmitem = $this->dof->storage('programmitems')->get_record(['id' => $programmitemid]);
        if ( empty($programmitem) )
        {
            return [];
        }
        
        // получение шкалы дисциплины
        $scale = $programmitem->scale;
        if ( empty($scale) )
        {
            // получение шкалы из конфига
            $scale = $this->dof->storage('config')->get_config_value('scale', 'storage', 'plans', $addvars['departmentid']);
        }
        
        $scale = $this->get_grades_scale_str($scale);
        
        // Результат
        return (array)$scale;
    }
    
    /**
     * Получить шкалу оценок для КТ
     *
     * @param stdClass|array|int $planid - объект/массив/значение КТ
     * @param bool $convert - необходимость конвертации все строк в числа
     *
     * @return []
     */
    public function get_plan_scale($plan = null, $convert = false)
    {
        global $addvars;
        
        // Нормализация
        if ( ! is_object($plan) ||
                empty($plan) )
        {
            $plan = $this->normalize($plan, 'id');
            if ( empty($plan) )
            {
                return [];
            }
        }
        
        $scale = [];
        if ( is_null($plan->scale) )
        {
            $csid = $plan->linkid;
            $pitemid = $this->dof->storage('cstreams')->get_field($csid, 'programmitemid');
            
            // получение шкалы для занятий из дисциплины
            $scale = $this->dof->storage('programmitems')->get_field($pitemid, 'lessonscale');
            if ( empty($scale) )
            {
                // получение шкалы дисциплины
                $scale = $this->dof->storage('programmitems')->get_field($pitemid, 'scale');
                if ( empty($scale) )
                {
                    // получение шкалы из конфига
                    $scale = $this->dof->storage('config')->get_config_value('scale', 'storage', 'plans', $addvars['departmentid']);
                }
            }
        } else
        {
            $scale = $plan->scale;
        }
        
        $scale = $this->get_grades_scale_str($scale);
        if ( $convert && ! empty($scale) )
        {
            $scale = $this->normalize_scale($scale);
        }
        
        // Результат
        return (array)$scale;
    }
    
    /**
     * Получить проходной балл для КТ
     *
     * @param stdClass|array|int $planid - объект/массив/значение КТ
     *
     * @return []
     */
    public function get_plan_mingrade($plan = null)
    {
        // Нормализация
        if ( ! is_object($plan) ||
                empty($plan) )
        {
            $plan = $this->normalize($plan, 'id');
            if ( empty($plan) )
            {
                return [];
            }
        }
        
        $mingrade = '';
        if ( is_null($plan->scale) )
        {
            $csid = $plan->linkid;
            $pitemid = $this->dof->storage('cstreams')->get_field($csid, 'programmitemid');
            
            // получение шкалы для занятий из дисциплины
            $mingrade = $this->dof->storage('programmitems')->get_field($pitemid, 'lessonpassgrade');
            if ( empty($mingrade) )
            {
                // получение шкалы дисциплины
                $mingrade = $this->dof->storage('programmitems')->get_field($pitemid, 'mingrade');
            }
        } else
        {
            $mingrade = $plan->mingrade;
        }
        
        // Результат
        return $mingrade;
    }
    
    /**
     * Получить опции конвертации оценки для дисциплины
     *
     * @param stdClass|array|int $pitem
     *
     * @return []
     */
    public function get_programmitem_grades_conversation_options($pitem)
    {
        // Нормализация
        if ( ! is_object($pitem) ||
                empty($pitem) )
        {
            $pitem = $this->normalize($pitem, 'id');
            if ( empty($pitem) )
            {
                return [];
            }
        }
        
        $options = $pitem->coursegradesconversation;
        if ( ! empty($options) )
        {
            $options = $this->dof->modlib('journal')->get_manager('scale')->parse_grades_conversation_options($options);
            if ( $options === false )
            {
                return [];
            }
        }
        
        return ! empty($options) ? $options : [];
    }
    
    /**
     * Получить опции конвертации оценки для занятия
     *
     * @param stdClass|array|int $planid - объект/массив/значение КТ
     *
     * @return []
     */
    public function get_plan_grades_conversation_options($plan)
    {
        // Нормализация
        if ( ! is_object($plan) ||
                empty($plan) )
        {
            $plan = $this->normalize($plan, 'id');
            if ( empty($plan) )
            {
                return [];
            }
        }
        
        $options = $plan->modulegradesconversation;
        if ( is_null($options) )
        {
            $csid = $plan->linkid;
            $pitemid = $this->dof->storage('cstreams')->get_field($csid, 'programmitemid');
            
            // получение опций для занятий из дисциплины
            $options = $this->dof->storage('programmitems')->get_field($pitemid, 'modulegradesconversation');
            if ( is_null($options) )
            {
                // получение шкалы дисциплины
                $options = $this->dof->storage('programmitems')->get_field($pitemid, 'coursegradesconversation');
            }
        }
        
        if ( ! empty($options) )
        {
            $options = $this->dof->modlib('journal')->get_manager('scale')->parse_grades_conversation_options($options);
            if ( $options === false )
            {
                return [];
            }
        }
        
        return empty($options) ? [] : $options;
    }
    
    /** 
     * Разбивает шкалу оценок на массив оценок
     * 
     * @param string $scale - шкала оценок
     * 
     * @return array - массив оценок - ассоциативный
     */
    public function get_grades_scale_str($scale)
    {// объявим массив
        $grades = array();
        $scl = explode(',', $scale);
        foreach ($scl as $element)
        {// перебираем все оценки шкалы
            if ( preg_match('/-/', $element) )
            {
                $boundaries = explode('-', $element);
                // определим границы максимальных и минимальных значений
                $min = $boundaries[0];
                $max = $boundaries[1];
                if ( $min != '' AND $max != '')
                {// диапозон то записываем
                    if ( $min > $max )
                    {// если обратная шкала
                        $mini = $min;
                        $min = $max;
                        $max = $mini;
                    }
                    for ($i=$min; $i<=$max; $i++)
                    {
                        $grades[$i] = "$i";
                    }
                    continue;
                }
            }
            $grades[trim($element)] = $element;
        }
        return $grades;
    }
    
    /**
     * Приведение процентов к оценке по шкале
     *
     * @param string $percent - оценка в процентах
     * @param array $scale - массив элементов шкалы
     * @param array $intervals - массив интервало шкалы
     * 
     * @return string|bool Оценка сответствующая переданной шкале или false в случае неудачи
     */
    public function bring_grade_to_scale($percent, $scale, $intervals = [])
    {
        // Если шкала нас не устраивает
        if ( ! is_array($scale) || empty($scale) )
        {
            return false;
        }
        
        // больше 100 процентов не может быть
        if ( $percent > 100 )
        {
            $percent = 100;
        }

        // переданы интервалы, поиск по нему
        // если не будет найден необходимый интервал, то выполним конвертацию стандартным способом
        if ( ! empty($intervals) )
        {
            $pos = 1;
            foreach ($intervals as $grade => $interval)
            {
                if ( ($interval['from'] == $interval['to'] && ($interval['from'] == $percent) ) ||
                        ( $pos != count($interval) && ($interval['from'] <= $percent) && ($percent < $interval['to']) ) ||
                        ( $pos == count($interval) && ($interval['from'] <= $percent) && ($percent <= $interval['to']) ) )
                {
                    if ( in_array($grade, $scale) )
                    {
                        return $grade;
                    }
                }
                $pos++;
            }
        } 
        
        // немного преобразуем шкалу (нам не нравятся ключи той, которую нам передали)
        // там в ключах были значения элементов, а нам нужен просто порядковый номер начиная с 0
        $scale = array_values($scale);
        
        // Получаем шаг, кол-во процентов, соответствующих одному шагу шкалы
        // (сколько процентов приходится на один элемент шкалы)
        $step = 100/count($scale);
        
        // Получаем номер элемента в массиве шкалы, которому соответствует наша оценка
        $num = floor($percent/$step);
        
        // ... но с одной поправочкой - если наша оценка 100%, то мы получим $num,
        // который на 1 больше чем максимальный ключ массива
        if ($num == count($scale))
        {
            $num--;
        }
        
        // На всякий случай проверим
        $scalegrade = @$scale[$num];
        if (null === $scalegrade)
        {
            return false;
        }
        
        return $scalegrade;
    }
    

    /**
     * Конвертация оценки из шкалы электронного деканата в другую диапазонную
     * 
     * @param string $grade
     * @param array  $scale
     * @param string $tomin
     * @param string $tomax
     * @param array $intervals
     * 
     * @return NULL|number
     */
    public function convert_grade_from_one_scale_to_another($grade, $scale, $tomin, $tomax, $scaleintervals = [])
    {
        if ( is_null($grade) ) 
        {
            return null;
        }
        if ( empty($scaleintervals) )
        {
            $scale = array_values($scale);
            $step = 100/count($scale);
            
            $from = 0;
            $to = $step;
            foreach ( $scale as $scaleval )
            {
                $scaleintervals[$scaleval]['from'] = $from;
                $scaleintervals[$scaleval]['to'] = $to;
                
                $from = $to;
                $to += $step;
            }
            
            $scaleintervals[$scaleval]['to'] = 100;
        }
        
        if ( array_key_exists($grade, $scaleintervals) && array_key_exists('to', $scaleintervals[$grade]) )
        {
            $percent = $scaleintervals[$grade]['to'];
            if ( empty($percent) )
            {
                return null;
            }
            $itoggrade = $tomin + ($tomax - $tomin) * $percent/100;
            if ( $itoggrade >= $tomin && $itoggrade <= $tomax )
            {
                return $itoggrade;
            } else
            {
                return null;
            }
        } else 
        {
            return $tomin;
        }
    }
    
    /**
     * Парсинг параметров конвертации шкалы
     * 
     * @param string $optionsasstring
     * 
     * @return false|array
     */
    public function parse_grades_conversation_options($optionsasstring)
    {
        try 
        {
            return \otcomponent_yaml\Yaml::parse($optionsasstring, \otcomponent_yaml\Yaml::PARSE_OBJECT);
        }
        catch (Exception $e)
        {
            return false;
        }
    }
    
    /**
     * Проверка, что параметры конвертации соответствуют шкале
     * 
     * @param string $optionsasstring
     * @param string $scale
     * 
     * @return bool
     */
    public function is_valid_grades_conversation_options($optionsasstring, $scale, &$arrayoferrors = [])
    {
        $parsedoptions = $this->parse_grades_conversation_options($optionsasstring);
        
        // не удалось спарсить разметку
        if ( ! $parsedoptions )
        {
            $arrayoferrors[] = $this->dof->get_string('err_grades_conversation_options_invalid_markup', 'journal', null, 'modlib');
            return false;
        }
        
        // если пустые опции конвертации, то используем стандартное равномерное деление
        if ( empty($parsedoptions) )
        {
            return true;
        }
        
        // получение шкалы в виде массива
        $scale = $this->get_grades_scale_str($scale);
        if ( empty($scale) )
        {
            $arrayoferrors[] = $this->dof->get_string('err_scale', 'journal', null, 'modlib');
            return false;
        }
        
        // количество элементов шкалы и разметки не должно отличаться
        if ( count($scale) != count($parsedoptions) )
        {
            $arrayoferrors[] = $this->dof->get_string('err_grades_conversation_options_invalid_count', 'journal',
                    (object)[
                        'ok' => count($scale),
                        'invalid' => count($parsedoptions)
                    ], 'modlib');
            return false;
        }
        
        $pos = 0;
        $sum = 0;
        $prev = null;
        $first = true;
        foreach ($parsedoptions as $grade => $option)
        {
            $pos++;
            if ( ! array_key_exists($grade, $scale) )
            {
                // оценка, указанная в разметке отсутствует в шкале
                $arrayoferrors[] = $this->dof->get_string('err_grades_conversation_options_invalid_grade', 'journal', $grade, 'modlib');
                return false;
            }
            
            if ( ! array_key_exists('from', $option) )
            {
                // отсутствует параметр from
                $arrayoferrors[] = $this->dof->get_string('err_grades_conversation_options_invalid_from', 'journal', $grade, 'modlib');
                return false;
            }
            
            if ( ! array_key_exists('to', $option) )
            {
                // отсутствует параметр to
                $arrayoferrors[] = $this->dof->get_string('err_grades_conversation_options_invalid_to', 'journal', $grade, 'modlib');
                return false;
            }
            
            if ( ! is_number($option['from']) ||
                    ! is_number($option['to']) )
            {
                // параметры from/to могут быть только int значениями
                $arrayoferrors[] = $this->dof->get_string('err_grades_conversation_options_invalid_params_type', 'journal', null, 'modlib');
                return false;
            }
            
            if ( $option['from'] > $option['to'] )
            {
                // параметр from не может быть больше
                $arrayoferrors[] = $this->dof->get_string('err_grades_conversation_options_invalid_from_more_to', 'journal', null, 'modlib');
                return false;
            }
            
            if ( ! is_null($prev) && $option['from'] < $prev['to'] )
            {
                // следующее деление шкалы не может быть по диапазону меньше предыдущего
                $arrayoferrors[] = $this->dof->get_string('err_grades_conversation_options_invalid_from_more_prev_to', 'journal', null, 'modlib');
                return false;
            }
            
            if ( $first && $option['from'] != 0 )
            {
                // крайнее левое значение первого диапазона должно начинаться с 0
                $arrayoferrors[] = $this->dof->get_string('err_grades_conversation_options_invalid_first_from', 'journal', null, 'modlib');
                return false;
            } else 
            {
                $first = false;
            }
            
            if ( $pos == count($parsedoptions) && $option['to'] != 100 )
            {
                // крайнее правое значение последнего диапазона должно заканчиваться на 100
                $arrayoferrors[] = $this->dof->get_string('err_grades_conversation_options_invalid_last_to', 'journal', null, 'modlib');
                return false;
            }
            
            $sum += $option['to'] - $option['from'];
            $prev = $option;
        }
        
        if ( $sum != 100 )
        {
            // если итоговая сумму диапазона не равняется 100, то разметка сформирована неверно
            $arrayoferrors[] = $this->dof->get_string('err_grades_conversation_options_invalid_sum', 'journal', null, 'modlib');
            return false;
        }
        
        return true;
    }
    
    /**
     * Получение доступных типов синхронизаций оценок КТ с оцениваемым элементом Moodle
     *
     * @return []string
     */
    public function get_grades_synctypes()
    {
        return [
            0 => $this->dof->get_string('grades_synctype_off', 'journal', null, 'modlib'),
            1 => $this->dof->get_string('grades_synctype_manually', 'journal', null, 'modlib'),
            2 => $this->dof->get_string('grades_synctype_auto', 'journal', null, 'modlib')
        ];
    }
    
    /**
     * Получение списка доступных приоритетов оценок для КТ
     *
     * @return []string
     */
    public function get_grades_priority()
    {
        return [
            'moodle' => $this->dof->get_string('grades_priority_moodle', 'journal', null, 'modlib'),
            'dof' => $this->dof->get_string('grades_priority_dof', 'journal', null, 'modlib')
        ];
    }
}
