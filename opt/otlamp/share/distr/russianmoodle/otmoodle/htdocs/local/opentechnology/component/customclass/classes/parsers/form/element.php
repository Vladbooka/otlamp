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


class element
{
    /**
     * @var \HTML_QuickForm_element $element
     */
    protected $element;
    
    /**
     * @var customform
     */
    protected $customform;
    protected $repeatgroup = null;
    
    public function __construct(&$customform, &$element)
    {
        $this->customform = &$customform;
        $this->element = &$element;
    }
    /**
     * Перекомпановка аргументов для addElement
     *
     * @return array
     */
    public static function handle(&$params)
    {
    }
    
    public function post_processing()
    {
    }
    
    public function get() {
        return $this->element;
    }
    
    public function set_repeat_group($repeatgroup)
    {
        $this->repeatgroup = $repeatgroup;
    }

    public function get_repeat_group()
    {
        return $this->repeatgroup;
    }
    
    protected function get_render_data($n)
    {
        include_once('HTML/QuickForm/Renderer/Array.php');
        $renderer = new \HTML_QuickForm_Renderer_Array();
        $elementname = $this->element->getName();
        if (!is_null($n))
        {
            $elementname .= '['.$n.']';
        }
        if ($this->customform->get_form()->elementExists($elementname))
        {
            /**
             * @var \HTML_QuickForm_element $element
             */
            $element = $this->customform->get_form()->getElement($elementname);
            $element->freeze();
            $element->setPersistantFreeze(false);
            $element->accept($renderer);
            return $renderer->toArray();
        }
        return null;
    }
    
    protected function get_values()
    {
        if (!empty($this->customform->values))
        {
            $values = json_decode(json_encode($this->customform->values), true);
        } else
        {
            $values = $this->customform->get_form()->exportValues();
        }
        return $values;
    }
    
    public function get_values_indexes()
    {
        $exportvalues = $this->get_values();
        $name = $this->get()->getName();
        if (array_key_exists($name, $exportvalues))
        {
            if (is_array($exportvalues[$name]))
            {
                return array_keys($exportvalues[$name]);
            }
        }
        return null;
    }
    
    protected function get_display_value($n)
    {
        $renderdata = $this->get_render_data($n);
        if (!is_null($renderdata) && isset($renderdata['elements'][0]['html']))
        {
            return $renderdata['elements'][0]['html'];
        }
        return null;
    }
    
    protected function get_value($n=null)
    {
        $name = $this->get()->getName();
        
        $values = $this->get_values();
        
        if (array_key_exists($name, $values))
        {
            $elementvalues = $values[$name];
            if (!is_null($n))
            {
                if(is_array($elementvalues) && array_key_exists($n, $elementvalues))
                {
                    return $elementvalues[$n];
                }
            } else
            {
                return $elementvalues;
            }
        }
        
        return null;
    }
    
    public function render_display_value($n=null, $divider = ', ')
    {
        $indexes = $this->get_values_indexes();
        if (is_null($n) && is_array($indexes))
        {
            $result = [];
            foreach($indexes as $n)
            {
                $result[$n] = $this->render_display_value($n);
            }
            return implode($divider, $result);
        } else
        {
            return $this->get_display_value($n);
        }
    }
    
    
    
    public function export_for_template($n=null, $divider=', ')
    {
        $data = [];
        $name = $this->get()->getName();
        $data['basename'] = $name;
        $data['basename_'.$name] = true;
        if (!is_null($n))
        {
            $name .= '['.$n.']';
        }
        $data['name'] = $name;
        $data['name_'.$name] = true;
        $data['type'] = $this->get()->getType();
        $data['type_'.$data['type']] = true;
        $data['label'] = $this->get()->getLabel();
        $data['value'] = $this->render_display_value($n, $divider);
        $data['is_null_value'] = is_null($data['value']);
        $data['repeatgroup_name'] = $this->get_repeat_group();
        
        return $data;
    }
}