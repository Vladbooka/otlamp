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
 * Блок согласования мастер-курса. Веб-сервисы
 *
 * @package    block_mastercourse
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace block_mastercourse;

defined('MOODLE_INTERNAL') || die();

require_once("$CFG->libdir/externallib.php");
require_once($CFG->dirroot . '/blocks/mastercourse/locallib.php');

use external_api;
use external_function_parameters;
use external_value;

class external extends external_api
{
    /**
     * Returns description of method parameters
     * @return external_function_parameters
     */
    public static function accept_coursedata_parameters()
    {
        $programmitemid = new external_value(
            PARAM_INT,
            'Programmitem id used to accept mastercourse',
            VALUE_REQUIRED
        );
        $params = [
            'programmitemid' => $programmitemid
        ];
        
        return new external_function_parameters($params);
    }
    
    /**
     * Returns form updated after attempt to accept mastercourse
     * @return string html form
     */
    public static function accept_coursedata($programmitemid) 
    {
        global $CFG, $PAGE;
        
        $PAGE->set_context(\context_system::instance());
        
        $result = 'false';

        if( file_exists($CFG->dirroot . '/blocks/dof/locallib.php') )
        {
            require_once($CFG->dirroot . '/blocks/dof/locallib.php');
            global $DOF;
            $programmitem = $DOF->storage('programmitems')->get($programmitemid);
            if( ! empty($programmitem->mdlcourse) )
            {
                $coursecontext = \context_course::instance($programmitem->mdlcourse);

                if( ($blockcontext = find_block_in_course($coursecontext)) && 
                        has_capability('block/mastercourse:respond_requests', $blockcontext) )
                {
                    $options = ['external_capability_verified' => true];
                    // запрос на проверку
                    $requestresult = $DOF->storage('programmitems')->accept_coursedata($programmitemid, $options);

                    if( ! empty($requestresult) )
                    {
                        $options = [
                            'no-wrapper' => true,
                            'external_capabilities_verified' => [
                                'request_verification' => has_capability('block/mastercourse:request_verification', $blockcontext),
                                'respond_requests' => true
                            ]
                        ];
                        // получение html-кода дисциплины
                        return $DOF->im('programmitems')->coursedata_verification_panel(
                            $programmitem->mdlcourse,
                            $programmitemid,
                            $options
                        );
                    }
                } elseif ( find_block_in_system() &&
                        has_capability('block/mastercourse:respond_requests', $coursecontext) )
                {
                    $options = ['external_capability_verified' => true];
                    // запрос на проверку
                    $requestresult = $DOF->storage('programmitems')->accept_coursedata($programmitemid, $options);
                    
                    if( ! empty($requestresult) )
                    {
                        $options = [
                            'no-wrapper' => true,
                            'external_capabilities_verified' => [
                                'request_verification' => has_capability('block/mastercourse:request_verification', $coursecontext),
                                'respond_requests' => true
                            ]
                        ];
                        // получение html-кода дисциплины
                        return $DOF->im('programmitems')->coursedata_verification_panel(
                                $programmitem->mdlcourse,
                                $programmitemid,
                                $options
                                );
                    }
                }
            }
        }
        //
        
        return $result;
    }
    
    /**
     * Returns description of method result value
     * @return external_description
     */
    public static function accept_coursedata_returns()
    {
        return new external_value(PARAM_RAW, 'Result of accepting');
    }
    
    
    
    

    /**
     * Returns description of method parameters
     * @return external_function_parameters
     */
    public static function decline_coursedata_parameters()
    {
        $programmitemid = new external_value(
            PARAM_INT,
            'Programmitem id used to decline mastercourse',
            VALUE_REQUIRED
        );
        $params = [
            'programmitemid' => $programmitemid
        ];
    
        return new external_function_parameters($params);
    }
    
    /**
     * Returns form updated after attempt to decline mastercourse
     * @return string html form
     */
    public static function decline_coursedata($programmitemid)
    {
        global $CFG, $PAGE;
        
        $PAGE->set_context(\context_system::instance());
        
        $result = 'false';
    
        if( file_exists($CFG->dirroot . '/blocks/dof/locallib.php') )
        {
            require_once($CFG->dirroot . '/blocks/dof/locallib.php');
            global $DOF;
            $programmitem = $DOF->storage('programmitems')->get($programmitemid);
            if( ! empty($programmitem->mdlcourse) )
            {
                $coursecontext = \context_course::instance($programmitem->mdlcourse);
                
                if( ($blockcontext = find_block_in_course($coursecontext)) &&
                        has_capability('block/mastercourse:respond_requests', $blockcontext) )
                {
                    $options = ['external_capability_verified' => true];
                    // запрос на проверку
                    $requestresult = $DOF->storage('programmitems')->decline_coursedata($programmitemid, $options);
                    
                    if( ! empty($requestresult) )
                    {
                        $options = [
                            'no-wrapper' => true,
                            'external_capabilities_verified' => [
                                'request_verification' => has_capability('block/mastercourse:request_verification', $blockcontext),
                                'respond_requests' => true
                            ]
                        ];
                        // получение html-кода дисциплины
                        return $DOF->im('programmitems')->coursedata_verification_panel(
                                $programmitem->mdlcourse,
                                $programmitemid,
                                $options
                                );
                    }
                } elseif ( find_block_in_system() && 
                        has_capability('block/mastercourse:respond_requests', $coursecontext) )
                {
                    $options = ['external_capability_verified' => true];
                    // запрос на проверку
                    $requestresult = $DOF->storage('programmitems')->decline_coursedata($programmitemid, $options);
                    
                    if( ! empty($requestresult) )
                    {
                        $options = [
                            'no-wrapper' => true,
                            'external_capabilities_verified' => [
                                'request_verification' => has_capability('block/mastercourse:request_verification', $coursecontext),
                                'respond_requests' => true
                            ]
                        ];
                        // получение html-кода дисциплины
                        return $DOF->im('programmitems')->coursedata_verification_panel(
                                $programmitem->mdlcourse,
                                $programmitemid,
                                $options
                                );
                    }
                }
            }
        }
        //
    
        return $result;
    }
    
    /**
     * Returns description of method result value
     * @return external_description
     */
    public static function decline_coursedata_returns()
    {
        return new external_value(PARAM_RAW, 'Result of accepting');
    }
    
    
    
    

    /**
     * Returns description of method parameters
     * @return external_function_parameters
     */
    public static function request_coursedata_verification_parameters()
    {
        $programmitemid = new external_value(
            PARAM_INT,
            'Programmitem id used to request mastercourse verification',
            VALUE_REQUIRED
            );
        $params = [
            'programmitemid' => $programmitemid
        ];
    
        return new external_function_parameters($params);
    }
    
    /**
     * Returns form updated after attempt to request mastercourse verification
     * @return string html form
     */
    public static function request_coursedata_verification($programmitemid)
    {
        global $CFG, $PAGE;
        
        $PAGE->set_context(\context_system::instance());
        
        $result = 'false';
    
        if( file_exists($CFG->dirroot . '/blocks/dof/locallib.php') )
        {
            require_once($CFG->dirroot . '/blocks/dof/locallib.php');
            global $DOF;
            $programmitem = $DOF->storage('programmitems')->get($programmitemid);
            if( ! empty($programmitem->mdlcourse) )
            {
                $coursecontext = \context_course::instance($programmitem->mdlcourse);
                
                if( ($blockcontext = find_block_in_course($coursecontext)) &&
                        has_capability('block/mastercourse:request_verification', $blockcontext) )
                {
                    $options = ['external_capability_verified' => true];
                    // запрос на проверку
                    $requestresult = $DOF->storage('programmitems')->request_coursedata_verification($programmitemid, $options);
                    
                    if( ! empty($requestresult) )
                    {
                        $options = [
                            'no-wrapper' => true,
                            'external_capabilities_verified' => [
                                'request_verification' => true,
                                'respond_requests' => has_capability('block/mastercourse:respond_requests', $blockcontext)
                            ]
                        ];
                        // получение html-кода дисциплины
                        return $DOF->im('programmitems')->coursedata_verification_panel(
                                $programmitem->mdlcourse,
                                $programmitemid,
                                $options
                                );
                    }
                } elseif ( find_block_in_system() &&
                        has_capability('block/mastercourse:request_verification', $coursecontext) )
                {
                    $options = ['external_capability_verified' => true];
                    // запрос на проверку
                    $requestresult = $DOF->storage('programmitems')->request_coursedata_verification($programmitemid, $options);
                    
                    if( ! empty($requestresult) )
                    {
                        $options = [
                            'no-wrapper' => true,
                            'external_capabilities_verified' => [
                                'request_verification' => true,
                                'respond_requests' => has_capability('block/mastercourse:respond_requests', $coursecontext)
                            ]
                        ];
                        // получение html-кода дисциплины
                        return $DOF->im('programmitems')->coursedata_verification_panel(
                                $programmitem->mdlcourse,
                                $programmitemid,
                                $options
                                );
                    }
                }
            }
        }
        //
    
        return $result;
    }
    
    /**
     * Returns description of method result value
     * @return external_description
     */
    public static function request_coursedata_verification_returns()
    {
        return new external_value(PARAM_RAW, 'Result of accepting');
    }
}