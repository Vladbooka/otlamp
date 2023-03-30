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

namespace local_crw\feedback;

use local_crw\model\items as baseitems;

class items extends baseitems {
    
    const TABLE = 'crw_feedback';
    const ITEMCLASS = '\local_crw\feedback\item';
    
    public static function export_for_template($items, $itemsdata=[])
    {
        $result = ['items' => []];
        
        foreach ($items as $item)
        {
            $exporteditem = $item->export_for_template();
            if (!empty($exporteditem))
            {
                $result['items'][] = array_merge($exporteditem, $itemsdata[$item->id]);
            }
        }
        
        return $result;
    }
    
    protected static function get_items_data($conditions=null, $sort='', $pagenum=0, $perpage=0, $fields='*', $totals=false)
    {
        global $DB;
        
        $result = false;
        
        $itemclass = static::ITEMCLASS;
        if (class_exists($itemclass))
        {
            $liststatuses = null;
            if (array_key_exists('status', $conditions) && is_array($conditions['status']))
            {
                $liststatuses = $conditions['status'];
                unset($conditions['status']);
            }
            
            $records = $DB->get_records(
                static::TABLE,
                $conditions,
                $sort,
                is_null($liststatuses) ? $fields : 'id',
                is_null($liststatuses) ? $perpage * $pagenum : 0,
                is_null($liststatuses) ? $perpage : 0
                );
            if (!is_null($liststatuses) && !empty($records))
            {
                list($stsqlin, $stparams) = $DB->get_in_or_equal($liststatuses, SQL_PARAMS_NAMED, 'st');
                list($idsqlin, $idparams) = $DB->get_in_or_equal(array_keys($records), SQL_PARAMS_NAMED, 'id');
                $sql = "id ".$idsqlin." AND status ".$stsqlin;
                $params = $stparams + $idparams;
                $records = $DB->get_records_select(
                    static::TABLE,
                    $sql,
                    $params,
                    $sort,
                    $fields,
                    $perpage * $pagenum,
                    $perpage
                    );
                if ($totals)
                {
                    $totalcount = $DB->count_records_select(static::TABLE, $sql, $params);
                }
            } else
            {
                if ($totals)
                {
                    $totalcount = $DB->count_records(static::TABLE, $conditions);
                }
            }
            
            if (!empty($records))
            {
                $result = [
                    'items' => $records
                ];
                if ($totals)
                {
                    $result['totalcount'] = $totalcount;
                }
            }
        }
        
        return $result;
        
    }
}