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
 * Настраиваемый провайдер авторизации
 *
 * @package    auth
 * @subpackage otoauth
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace auth_otoauth;

use otcomponent_yaml\Yaml;

/**
 * @property-read int $id Идентификатор записи в БД настраиваемого провайдера
 * @property-read string $name Наименование настраиваемого провайдера
 * @property-read string $code Уникальный код настраиваемого провайдера
 * @property-read string $description Описание настраиваемого провайдера
 * @property-read string $config Конфигурация настраиваемого провайдера в yaml
 * @property-read string $status Статус настраиваемого провайдера (код статуса, хранимый в БД)
 * @property-read string $displaystatus Статус настраиваемого провайдера для отображения (строка из языкового пакета, соответствующая выбранному статусу)
 * @property-read array $configarray Конфигурация настраиваемого провайдера, представленная в виде массива
 */
class customprovider
{
    protected $id = null;
    protected $name = null;
    protected $code = null;
    protected $description = null;
    protected $config = null;
    protected $status = null;
    
    public function __construct($record)
    {
        foreach (self::properties() as $property)
        {
            if (property_exists($record, $property))
            {
                $this->{$property} = $record->{$property};
            }
        }
    }
    
    private static function properties()
    {
        return [
            'id',
            'name',
            'code',
            'description',
            'config',
            'status'
        ];
    }
    
    protected function get_display_status()
    {
        $statuses = self::get_status_list();
        
        if (array_key_exists($this->status, $statuses))
        {
            return $statuses[$this->status];
        }
        
        return $this->status;
    }
    
    protected function get_config_array()
    {
        if (is_null($this->id))
        {
            throw new customprovider_exception('Custom provider id missing for unknown reasons');
        }
        
        if (is_null($this->config))
        {
            throw new customprovider_exception('Custom provider ['.$this->id.'] config field is empty');
        }
        
        return self::parse_config($this->config, $this->id);
    }
    
    public static function parse_config($yaml, $id=null)
    {
        try {
            
            if (empty($yaml))
            {
                throw new customprovider_exception(get_string('cp_misconfig_config_is_empty', 'auth_otoauth'));
            }
            
            $config = Yaml::parse($yaml, Yaml::PARSE_OBJECT);
            
            if (!is_array($config))
            {
                throw new customprovider_exception(get_string('cp_misconfig_config_is_not_an_array', 'auth_otoauth'));
            }
            
        } catch (\Exception $ex) {
            throw new customprovider_exception(get_string('cp_misconfig', 'auth_otoauth', [
                'id' => (is_null($id) ? ' ' : ' ['.$id.'] '),
                'message' =>  $ex->getMessage()
            ]));
        }
        
        return $config;
    }
    
    public function __get($name)
    {
        if (in_array($name, self::properties()))
        {
            return $this->{$name};
        }
        if ($name == 'displaystatus')
        {
            return $this->get_display_status();
        }
        if ($name == 'configarray')
        {
            return $this->get_config_array();
        }
    }
    
    
    
    public function to_object($propprefix='')
    {
        $object = new \stdClass();
        
        foreach(self::properties() as $property)
        {
            $object->{$propprefix.$property} = $this->{$property};
        }
        
        return $object;
    }
    
    public static function get_status_list()
    {
        return [
            'disabled' => get_string('customprovider_status_disabled', 'auth_otoauth'),
            'active' => get_string('customprovider_status_active', 'auth_otoauth'),
        ];
    }
    
    public static function get_custom_provider($id)
    {
        $customproviders = self::get_custom_providers(['id' => $id]);
        if (!empty($customproviders))
        {
            return array_shift($customproviders);
        }
        return null;
    }
    
    /**
     * Получить список кастомных провайдеров по условиям
     * @param array $conditions
     * @return \auth_otoauth\customprovider[] массив объектов кастомных провайдеров
     */
    public static function get_custom_providers($conditions=[])
    {
        global $DB;
        
        $customproviders = [];
        
        
        $getconditions = [];
        if (array_key_exists('id', $conditions))
        {
            $getconditions['id'] = $conditions['id'];
        }
        if (array_key_exists('status', $conditions) && array_key_exists($conditions['status'], self::get_status_list()))
        {
            $getconditions['status'] = $conditions['status'];
        }
        if (array_key_exists('code', $conditions))
        {
            $getconditions['code'] = $conditions['code'];
        }
        
        $cprecords = $DB->get_records('auth_otoauth_custom_provider', $getconditions);
        if (!empty($cprecords))
        {
            foreach($cprecords as $cprecord)
            {
                $customproviders[$cprecord->code] = new \auth_otoauth\customprovider($cprecord);
            }
        }
        
        return $customproviders;
    }
    
    public static function add_custom_provider($object)
    {
        global $DB;
        
        $customprovider = new \stdClass();
        foreach(self::properties() as $property)
        {
            if ($property == 'id')
            {
                continue;
            }
            
            if (!property_exists($object, $property))
            {
                throw new customprovider_exception(get_string('custom_provider_error_missing_required_property', 'auth_otoauth'));
            }
            
            $customprovider->{$property} = $object->{$property};
        }
        
        $DB->insert_record('auth_otoauth_custom_provider', $customprovider);
    }
    
    public static function edit_custom_provider($object)
    {
        global $DB;
        
        $customprovider = new \stdClass();
        
        foreach(self::properties() as $property)
        {
            if (!property_exists($object, $property))
            {
                throw new customprovider_exception(get_string('custom_provider_error_missing_required_property', 'auth_otoauth'));
            }
            
            $customprovider->{$property} = $object->{$property};
        }
        
        $DB->update_record('auth_otoauth_custom_provider', $customprovider);
    }
    
    public static function delete_custom_provider($id)
    {
        global $DB;
        
        $DB->delete_records('auth_otoauth_custom_provider', ['id' => $id]);
    }
}
