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
 * Поле
 *
 * @package    local_crw
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_crw\model;

class field {
    
    private $name;
    private $type;
    private $required = false;
    private $allowable = null;
    private $value = null;
    
    public function __construct($name, $type, $required=false, $allowable=null, $value=null)
    {
        $this->name = $name;
        $this->type = $type;
        $this->required = !empty($required);
        $this->allowable = $allowable;
        if (!is_null($value))
        {
            $this->set_value($value);
        }
    }
    
    public function set_value($value)
    {
        $this->value = $this->validate($value);
    }
    
    public function get_value()
    {
        return $this->validate($this->value);
    }
    
    public function validate($value)
    {
        if (!is_null($this->allowable))
        {
            if (is_array($this->allowable))
            {
                if (!in_array($value, $this->allowable))
                {
                    throw new \invalid_parameter_exception('value is not in list of allowable values for \''.$this->name.'\' field');
                }
            } elseif ($this->allowable != $value)
            {
                throw new \invalid_parameter_exception('value is not allowable for \''.$this->name.'\' field');
            }
        }
        return validate_param(
            $value,
            $this->type,
            $this->required ? NULL_NOT_ALLOWED : NULL_ALLOWED
        );
    }
}