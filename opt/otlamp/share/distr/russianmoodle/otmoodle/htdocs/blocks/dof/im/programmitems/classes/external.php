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
 * Вебсервис дисцилпин
 *
 * @package    im
 * @subpackage programmitems
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once('../lib.php');
require_once($CFG->libdir . '/weblib.php');

class dof_external_api_plugin extends dof_external_api_plugin_base
{
    /**
     * Включение ожидания проверки для дисцилпины
     *
     * @param int $programmitemid
     *
     * @return string - html код журнала
     */
    public static function request_coursedata_verification($programmitemid)
    {
        GLOBAL $DOF, $PAGE;
        
        $PAGE->set_context(\context_system::instance());
        
        // запрос на проверку
        $requestresult = $DOF->storage('programmitems')->request_coursedata_verification($programmitemid);
        if( ! empty($requestresult) )
        {
            // получение дисциплины
            $programmitem = $DOF->storage('programmitems')->get($programmitemid);
            if( ! empty($programmitem) )
            {
                // получение html-кода дисциплины
                return $DOF->im('programmitems')->coursedata_verification_panel(
                    $programmitem->mdlcourse, 
                    $programmitem->id, 
                    [
                        'no-wrapper' => true
                    ]
                );
            }
        }
        
        return false;
    }
    /**
     * Подтверждение контента курса для дисцилпины
     *
     * @param int $programmitemid
     *
     * @return string - html код журнала
     */
    public static function accept_coursedata($programmitemid)
    {
        GLOBAL $DOF, $PAGE;
        
        $PAGE->set_context(\context_system::instance());
        
        // запрос на проверку
        $requestresult = $DOF->storage('programmitems')->accept_coursedata($programmitemid);
        if( ! empty($requestresult) )
        {
            // получение дисциплины
            $programmitem = $DOF->storage('programmitems')->get($programmitemid);
            if( ! empty($programmitem) )
            {
                // получение html-кода дисциплины
                return $DOF->im('programmitems')->coursedata_verification_panel(
                    $programmitem->mdlcourse, 
                    $programmitem->id, 
                    [
                        'no-wrapper' => true
                    ]
                );
            }
        }
        
        return false;
    }
    /**
     * Отклонение принятия изменения контента курса для дисцилпины
     *
     * @param int $programmitemid
     *
     * @return string - html код журнала
     */
    public static function decline_coursedata($programmitemid)
    {
        GLOBAL $DOF, $PAGE;
        
        $PAGE->set_context(\context_system::instance());
        
        // запрос на проверку
        $requestresult = $DOF->storage('programmitems')->decline_coursedata($programmitemid);
        if( ! empty($requestresult) )
        {
            // получение дисциплины
            $programmitem = $DOF->storage('programmitems')->get($programmitemid);
            if( ! empty($programmitem) )
            {
                // получение html-кода дисциплины
                return $DOF->im('programmitems')->coursedata_verification_panel(
                    $programmitem->mdlcourse, 
                    $programmitem->id, 
                    [
                        'no-wrapper' => true
                    ]
                );
            }
        }
        
        return false;
    }
    
}
