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
 * Базовая модель объекта
 *
 * @package    local_crw
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_crw\model;


abstract class item {
    
    const TABLE = '';
    protected $fields;
    
    public function __construct($object = null)
    {
        $this->init_fields();
        
        if (!is_null($object))
        {
            $this->new($object);
        }
    }
    
    protected abstract function init_fields();
    
    protected function set_properties($object)
    {
        foreach ($object as $fieldname => $value)
        {
            if (array_key_exists($fieldname, $this->fields))
            {
                $this->{$fieldname} = $value;
            }
        }
    }
    
    protected function new($object)
    {
        if (!empty($object))
        {
            $this->set_properties($object);
        }
    }
    
    public static function get($id)
    {
        global $DB;
        
        $record = $DB->get_record(static::TABLE, ['id' => $id]);
        
        return new static($record);
//         $this->new($record);
    }
    
    protected function to_object()
    {
        $object = new \stdClass();
        
        foreach ($this->fields as $fieldname => $field)
        {
            $object->{$fieldname} = $field->get_value();
        }
        
        return $object;
    }
    
    public function save()
    {
        global $DB;
        
        $object = $this->to_object();
        
        if (!empty($object->id))
        {
            return $DB->update_record(static::TABLE, $object);
        } else
        {
            return $DB->insert_record(static::TABLE, $object);
        }
    }
    
    public function delete()
    {
        global $DB;
        
        if (!empty($this->id))
        {
            return $DB->delete_records(static::TABLE, ['id' => $this->id]);
        }
        return false;
    }
    
    public function __get($fieldname)
    {
        if (array_key_exists($fieldname, $this->fields))
        {
            return $this->fields[$fieldname]->get_value();
        } else
        {
            throw new \moodle_exception('undefined field');
        }
    }
    
    public function __set($fieldname, $value)
    {
        if (array_key_exists($fieldname, $this->fields))
        {
            $this->fields[$fieldname]->set_value($value);
        } else
        {
            throw new \moodle_exception('undefined field');
        }
    }
}