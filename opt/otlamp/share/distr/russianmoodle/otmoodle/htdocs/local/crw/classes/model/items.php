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


class items {
    
    const TABLE = '';
    const ITEMCLASS = '';
    
    protected static function get_items_data($conditions=null, $sort='', $pagenum=0, $perpage=0, $fields='*', $totals=false)
    {
        global $DB;
        
        $result = false;
        $itemclass = static::ITEMCLASS;
        
        if (class_exists($itemclass))
        {
            $records = $DB->get_records(
                static::TABLE,
                $conditions,
                $sort,
                $fields,
                $perpage * $pagenum,
                $perpage
            );
            
            if (!empty($records))
            {
                $result = [
                    'items' => $records
                ];
                
                if ($totals)
                {
                    $totalcount = $DB->count_records(static::TABLE, $conditions);
                    $result['totalcount'] = $totalcount;
                }
            }
        }
        
        return $result;
        
    }
    
    public static function get_items($conditions=null, $sort='', $pagenum=0, $perpage=0)
    {
        $result = static::get_items_data($conditions, $sort, $pagenum, $perpage, '*', true);
        $itemclass = static::ITEMCLASS;
        
        if ($result && is_array($result['items']) && class_exists($itemclass))
        {
            foreach($result['items'] as $itemid => $itemrecord)
            {
                $result['items'][$itemid] = new $itemclass($itemrecord);
            }
        }
        return $result;
    }
    
    public static function delete_items($conditions=null)
    {
        global $DB;
        
        $itemsdata = static::get_items_data($conditions, '', 0, 0, 'id');
        
        if ($itemsdata && is_array($itemsdata['items']) && count($itemsdata['items']) > 0)
        {
            
            list($idsqlin, $idparams) = $DB->get_in_or_equal(
                array_keys($itemsdata['items']),
                SQL_PARAMS_NAMED,
                'id'
            );
        
            return $DB->delete_records_select(static::TABLE, "id ".$idsqlin, $idparams);
        }
        
        return false;
    }
}