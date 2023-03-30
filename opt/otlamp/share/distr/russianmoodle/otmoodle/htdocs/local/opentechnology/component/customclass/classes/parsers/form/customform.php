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

/**
 * Настраиваемые формы
 *
 * @package    local_opentechnology
 * @subpackage otcomponent_customclass
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace otcomponent_customclass\parsers\form;

require_once("$CFG->libdir/formslib.php");
require_once("$CFG->libdir/accesslib.php");

use moodleform;
use coding_exception;

class customform extends moodleform
{
    /**
     * Поля формы
     *
     * @var array
     */
    protected $fields = [];
    
    /**
     * Флаг готовности формы к использованию
     *
     * @var bool
     */
    protected $ready = false;
    
    protected $action = null;
    
    /**
     * Объявления полей формы
     * {@inheritDoc}
     * @see moodleform::definition()
     */
    protected function definition()
    {
        // необходимо вызвать метод setForm перед использованием формы
        if ( ! $this->ready )
        {
            throw new coding_exception('form_not_set');
        }
        
        // установка класса форме
        // используется для JS обработки сабмита
        $class = (string)$this->_form->getAttribute('class');
        $class .= ' otcustomform';
        $attributes = $this->_form->getAttributes();
        $attributes['class'] = $class;
        $this->_form->setAttributes($attributes);
        
        // добавление полей в форму
        if ( ! empty($this->fields) )
        {
            $processed = [];
            foreach ( $this->fields as $fieldname => $fieldattrs )
            {
                if (!in_array($fieldname, $processed))
                {
                    $processed = array_merge(
                        $processed,
                        $this->add_element($fieldname, $fieldattrs)
                    );
                }
            }
        }
    }
    
    function extract_system_data(&$fieldattrs)
    {
        $repeatgroup = $fieldattrs['repeatgroup'] ?? null;
        unset($fieldattrs['repeatgroup']);
        
        // тип поля
        $type = ! empty($fieldattrs['type']) ? $fieldattrs['type'] : 'text';
        unset($fieldattrs['type']);
        
        // проверяем, есть ли правила валидации
        $rules = ! empty($fieldattrs['rules']) ? $fieldattrs['rules'] : [];
        unset($fieldattrs['rules']);
        
        // фильтр поля
        $filter = ! empty($fieldattrs['filter']) ? $fieldattrs['filter'] : 'text';
        unset($fieldattrs['filter']);
        
        // значение по умолчанию
        $default = ! empty($fieldattrs['default']) ? $fieldattrs['default'] : null;
        unset($fieldattrs['default']);
        
        $disabledif = ! empty($fieldattrs['disabledif']) ? $fieldattrs['disabledif'] : null;
        unset($fieldattrs['disabledif']);
        
        // expanded
        // advanced
        // helpbutton
        
        return [
            'repeatgroup' => $repeatgroup,
            'type' => $type,
            'rules' => $rules,
            'filter' => $filter,
            'default' => $default,
            'disabledif' => $disabledif
        ];
    }
    
    function add_element($fieldname, $fieldattrs, $returnelement=false)
    {
        list($repeatgroup, $type, $rules, $filter, $default, $disabledif) = array_values($this->extract_system_data($fieldattrs));
        
        // При добавлении radio должно использоваться одно имя в нескольких элементах
        // однако в существующем формате имя поля идет в ключе массива
        // в качестве решения используются [] с уникальными значениями в названии поля
        // если указан атрибут autoindex для поля, то [] с содержимым удаляются и элемент добавляется с простым названием
        if (!empty($fieldattrs['autoindex']))
        {
            $fieldname = preg_replace('/(\[.*\])/', '', $fieldname);
            unset($fieldattrs['autoindex']);
        }
        
        // формирование массива аргументов
        $args = [$type, $fieldname] + $fieldattrs;
        
        // Проверка существования дополнительной обработки поля
        $class = 'otcomponent_customclass\parsers\form\element\\' . $type;
        if ( class_exists($class) && method_exists($class, 'handle') )
        {
            $class::handle($args);
        } else
        {
            \otcomponent_customclass\parsers\form\element::handle($args);
        }
        
        // вызываем функцию addElement и передаем туда остальные аргументы
        // делается это для устранения проблемы переопределения конструктора элементами
        // к примеру у чекбокса аттрибуты идут пятым параметром, в том время как у текстового поля аттрибуты идут четвертым параметром
        $formelement = @call_user_func_array([$this->_form, 'createElement'], $args);
        
        
        // Проверка существования постобработки поля
        if ( class_exists($class) && method_exists($class, 'post_processing') )
        {
            $element = new $class($this, $formelement);
        } else
        {
            $element = new \otcomponent_customclass\parsers\form\element($this, $formelement);
        }
        $this->elements[$fieldname] = &$element;
        
        // постобработка поля
        $element->post_processing();
        
        
        if ($returnelement)
        {
            return $element->get();
        }
        
        if (!is_null($repeatgroup))
        {
            $repeatelements = [];
            $repeatoptions = [];
            
            $repeatelements[$repeatgroup.'_wrapperstart'] = $this->_form->createElement('html', '<div class="otcomponent_customclass_repeater_wrapper">');
            
            $repeatelements[$fieldname] = $element->get();
            $repeatoptions[$fieldname] = ['type' => $type];
            
            foreach ( $this->fields as $repeatfieldname => $repeatfieldattrs )
            {
                if (isset($repeatfieldattrs['repeatgroup']) && $repeatfieldattrs['repeatgroup'] == $repeatgroup)
                {
                    $repeatelements[$repeatfieldname] = $this->add_element($repeatfieldname, $repeatfieldattrs, true);
                    $repeatoptions[$repeatfieldname] = $this->extract_system_data($fieldattrs);
                    $this->elements[$repeatfieldname]->set_repeat_group($repeatgroup);
                }
            }
            
            $repeatelements[$repeatgroup.'_del'] = $this->add_element($repeatgroup.'_del', ['type' => 'submit', 'label' => 'Удалить'], true);
            
            $repeatelements[$repeatgroup.'_wrapperend'] = $this->_form->createElement('html', '</div>');
            
            $this->add_repeat($repeatgroup, $repeatelements, $repeatoptions);
            
            return array_keys($repeatelements);
        } else
        {
            @call_user_func([$this->_form, 'addElement'], $element->get());
            
            $this->_form->setType($fieldname, $filter);
            $this->_form->setDefault($fieldname, $default);
            if (!is_null($disabledif))
            {
                call_user_func_array([$this->_form, 'disabledIf'], $disabledif);
            }
            
            // обработка правила
            foreach ($rules as $rule)
            {
                if ( $rule == 'required' )
                {
                    $this->_form->addRule($fieldname, get_string('required', 'otcomponent_customclass'), 'required', null, 'client', false, true);
                }
            }
            
            return [$fieldname];
        }
        
    }
    
    function add_repeat($repeatgroup, $repeatelements, $repeatoptions)
    {
        $repeatscount = $this->_customdata['repeatscount'][$repeatgroup] ?? 1;
        // повторение элементов
        $this->repeat_elements(
            $repeatelements,
            $repeatscount,
            $repeatoptions,
            $repeatgroup.'_count',
            $repeatgroup.'_add_field',
            1,
            null,
            true
        );
    }
    
    /**
     * Переопределение конструктора для отложенного вызова
     */
    public function __construct()
    {
    }
    
    /**
     * Установка формы
     */
    public function setForm($action=null, $customdata=null, $method='post', $target='', $attributes=null, $editable=true, $ajaxformdata=null)
    {
        if ( ! $this->ready )
        {
            $this->ready = true;
            $this->action = $action;
            parent::__construct($action, $customdata, $method, $target, $attributes, $editable, $ajaxformdata);
        }
    }
    
    public function get_form_action()
    {
        return $this->action;
    }
    
    /**
     * Установка полей формы
     *
     * @param array $fields
     *
     * @throws coding_exception
     */
    public function set_fields($fields)
    {
        if ( ! is_array($fields) )
        {
            throw new coding_exception('invalid_fields_type');
        }
        $this->fields = $fields;
    }
    
    /**
     * Жесткий фриз всех элементов
     *
     * @return void
     */
    public function hardFreezeAll()
    {
        $this->_form->hardFreezeAllVisibleExcept([]);
    }
    
    public function get_fields()
    {
        return $this->fields;
    }
    
    public function set_data($data)
    {
        $this->values = $data;
        $defaultvalues = [];
        foreach($data as $fieldname => $defaultvalue)
        {
            if (is_array($defaultvalue))
            {
                foreach($defaultvalue as $n => $realdefaultvalue)
                {
                    $defaultvalues[$fieldname.'['.$n.']'] = $realdefaultvalue;
                }
            }
            
            $defaultvalues[$fieldname] = $defaultvalue;
        }
        
        parent::set_data($defaultvalues);
    }
    
    /**
     * @return \MoodleQuickForm
     */
    public function get_form()
    {
        return $this->_form;
    }
    
    public function get_element($name)
    {
        if (array_key_exists($name, $this->elements))
        {
            return $this->elements[$name];
        }
        return null;
    }
    
    public function get_repeat_group_elements($repeatgroup)
    {
        $rgelements = [];
        foreach($this->elements as $elementname => $element)
        {
            if ($element->get_repeat_group() == $repeatgroup)
            {
                $rgelements[$elementname] = $element;
            }
        }
        return $rgelements;
    }
    
    public function get_export_fields_for_template($elementnames=null, $restrictedtypes=['submit'])
    {
        $result = [];
        
        $rgprocessed = [];
        foreach($this->elements as $element)
        {
            if (in_array($element->get()->getType(), $restrictedtypes))
            {
                continue;
            }
            
            if (!is_null($elementnames) && !in_array($element->get()->getName(), $elementnames))
            {
                continue;
            }
            
            
            
            $rg = $element->get_repeat_group();
            
            if (!is_null($rg))
            {
                if (!in_array($rg, $rgprocessed))
                {
                    $result[] = [
                        'repeatgroup' => true,
                        'name' => $rg,
                        'name_'.$rg => true,
                        'groups' => $this->get_repeat_group_display_value($rg)
                    ];
                    $rgprocessed[] = $rg;
                }
                
            }
            
            
            
            $elementdata = $element->export_for_template();
            $elementdata['element'] = true;
            
            $indexes = $element->get_values_indexes();
            if (is_array($indexes))
            {
                foreach($indexes as $n)
                {
                    $indexelement = $element->export_for_template($n);
                    $elementdata['index_'.$n] = fullclone($indexelement);
                    $indexelement['element'] = true;
                    $indexelement['index_element'] = true;
                    $result[] = $indexelement;
                }
                $result[count($result)-1]['last'] = true;
            }
            
            $result[] = $elementdata;
        }
        return $result;
    }
    
    
    protected function get_repeat_group_display_value($repeatgroupname)
    {
        $rgvalues = [];
        foreach($this->get_repeat_group_elements($repeatgroupname) as $rgelement)
        {
            $indexes = $rgelement->get_values_indexes();
            if (is_array($indexes))
            {
                foreach($rgelement->get_values_indexes() as $n)
                {
                    $rgelemendata = $rgelement->export_for_template($n);
                    $rgelemendata['element'] = true;
                    $rgvalues[$n]['elements'][] = $rgelemendata;
                }
            }
        }
        foreach($rgvalues as $n => $data)
        {
            $rgvalues[$n]['elements'][count($rgvalues[$n]['elements'])-1]['last'] = true;
        }
        
        return $rgvalues;
    }
}
