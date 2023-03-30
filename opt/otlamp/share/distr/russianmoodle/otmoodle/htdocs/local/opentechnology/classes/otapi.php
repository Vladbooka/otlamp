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

namespace local_opentechnology;

use cache;


class otapi
{
    public static function get_all_otserials()
    {
        global $DB, $CFG;
        
        $result = [];
        
        $tables = ['config', 'config_plugins'];
        $conditions = ["(name=:otserial OR name=:otkey)"];
        $params = [
            'otserial' => 'otserial',
            'otkey' => 'otkey'
        ];
        foreach ($tables as $table)
        {
            $records = $DB->get_records_select($table, implode(' AND ', $conditions), $params);
            
            if (!empty($records))
            {
                foreach ($records as $record)
                {
                    $plugin = $record->plugin ?? 'core';
                    if (!array_key_exists($plugin, $result))
                    {
                        $result[$plugin] = [
                            'otserial' => null,
                            'otkey' => null
                        ];
                    }
                    $result[$plugin][$record->name] = $record->value;
                }
            }
        }
        
        if (!empty($CFG->otapi) && is_array($CFG->otapi))
        {
            foreach($CFG->otapi as $plugin => $otapidata)
            {
                $plugin = (in_array($plugin, ['local_opentechnology', 'core', 'moodle', '']) ? 'core' : $plugin);
                $result[$plugin] = [
                    'otserial' => $otapidata['otserial'] ?? null,
                    'otkey' => $otapidata['otkey'] ?? null,
                ];
            }
        }
        
        return $result;
    }
    
    public static function delete_all_otapi_data()
    {
        global $DB;
        
        $tables = ['config', 'config_plugins'];
        $conditions = ["(name=:otserial OR name=:otkey)"];
        $params = [
            'otserial' => 'otserial',
            'otkey' => 'otkey'
        ];
        foreach ($tables as $table)
        {
            $DB->delete_records_select($table, implode(' AND ', $conditions), $params);
        }
        
        static::purge_all_otapi_caches();
    }
    
    public static function purge_all_otapi_caches()
    {
        purge_all_caches();
        
        // Решение ниже почему-то не работает, некогда разбираться, видимо purge не чистит кэши
        // Решение с delete нам не подходит, так как мы не уверены в полном списке плагинов, использующих кэш
        // Рзаработики будут забывать перечислить их тут, а получить список неоткуда, кроме конфигов
        // Получать из конфигов - не вариант, так как конфиги чистятся не централизовано и есть вероятность,
        // что конфига нет, а кэш есть. В случае организации здесь цикла с delete, мы такой кэш не очистим
        
        // $otserialcache = cache::make('local_opentechnology', 'otserial');
        // $otserialcache->purge();
        
        // $statuscache = cache::make('local_opentechnology', 'otserial_status');
        // $statuscache->purge();
    }
}