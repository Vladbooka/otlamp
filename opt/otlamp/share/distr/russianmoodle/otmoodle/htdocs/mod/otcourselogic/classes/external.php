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
 * Обработка AJAX методов
 *
 * @package    mod_otcourselogic
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_otcourselogic;

require_once("$CFG->libdir/externallib.php");

use context_course;
use external_api;
use external_function_parameters;
use external_multiple_structure;
use external_single_structure;
use external_value;
use mod_otcourselogic\apanel\helper;

class external extends external_api
{

    public static function set_sortorder_actions_parameters()
    {
        return new external_function_parameters(
                [
                    'order' => new external_multiple_structure(
                            new external_single_structure(
                                    [
                                        'actionid' => new external_value(PARAM_RAW_TRIMMED),
                                        'sortorder' => new external_value(PARAM_RAW_TRIMMED)
                                    ])),
                    'processor' => new external_value(PARAM_INT)
                ]);
    }
    
    public static function set_sortorder_actions_returns()
    {
        return new external_value(PARAM_BOOL, 'Result of the operation');
    }
    
    public static function set_sortorder_actions($order, $processor)
    {
        global $DB;
        
        // Валидация данных
        $processor = helper::get_processor($processor);
        if ( empty($processor) )
        {
            return false;
        }
        $actions = helper::get_actions($processor->id);
        if ( empty($actions) )
        {
            return false;
        }
        if ( count($actions) != count($order) )
        {
            return false;
        }
        $cm_data = get_course_and_cm_from_instance($processor->otcourselogicid, 'otcourselogic');
        
        // Проверка прав
        require_capability('mod/otcourselogic:admin_panel', context_course::instance($cm_data[0]->id));
        
        // Результат выполнения
        $result = true;
        
        foreach( $order as $actionorderobj )
        {
            if ( ! array_key_exists($actionorderobj['actionid'], $actions) )
            {
                echo $actionorderobj['actionid'];die;
                $result = false;
            }
        }
        
        if ( ! $result )
        {
            // Пришли некорректные данные
            return $result;
        }
        
        foreach( $order as $actionorderobj )
        {
            $result = $result && helper::save_action((object)['processorid' => $processor->id, 'id' => $actionorderobj['actionid'], 'sortorder' => $actionorderobj['sortorder']]);
        }
        sleep(1);
        
        return $result;
    }
}

