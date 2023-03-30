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
 * Вебсервис журнала
 *
 * @package    im
 * @subpackage journal
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once('../lib.php');
require_once($CFG->libdir . '/weblib.php');

class dof_external_api_plugin extends dof_external_api_plugin_base
{
    public static function save_fields_sort_indexes($fields, $departmentid)
    {
        global $DOF;
        $result = true;
        if( ! empty($fields) )
        {
            $result = false;
            $value = [];
            foreach($fields as $position => $elements)
            {
                foreach($elements as $index => $element)
                {
                    $temp = explode('_', $element);
                    $group = $temp[0];
                    unset($temp[0]);
                    $code = implode('_', $temp);
                    unset($temp);
                    if( strpos($code, 'addressid_') === 0 )
                    {
                        $data = explode('_', $code);
                        $datacode = $data[0];
                        unset($data[0]);
                        $dataname = implode('_', $data);
                        unset($data);
                        $value[$position][$group][$datacode][$dataname] = $index + 1;
                    } else 
                    {
                        $value[$position][$group][$code] = $index + 1;
                    }
                }
            }
            
            $params = [
                'departmentid' => $departmentid,
                'code' => 'userinfo_fields',
                'plugintype' => 'im',
                'plugincode' => 'achievements'
            ];
            
            $config = new stdClass();
            $config->departmentid = $departmentid;
            $config->code = 'userinfo_fields';
            $config->plugintype = 'im';
            $config->plugincode = 'achievements';
            $config->type = 'text';
            
            $configrecords = $DOF->storage('config')->get_records($params);
            
            if ( empty($configrecords) )
            {// Конфигурация не найдена
                $config->value = serialize($value);
                // Добавить значение
                $result = $this->dof->storage('config')->insert($config);
            } else 
            {
                $configrecord = array_pop($configrecords);
                $id = $configrecord->id;
                if ( count($configrecords) > 1 )
                {// Найдены дубли
                    foreach ( $configrecords as $record )
                    {// Удаление дубля
                        $this->dof->storage('config')->delete($record->id);
                    }
                }
                $config->value = serialize($value);
                $result = $DOF->storage('config')->update($config, $id);
            }
        }
        return $result;
    }
}