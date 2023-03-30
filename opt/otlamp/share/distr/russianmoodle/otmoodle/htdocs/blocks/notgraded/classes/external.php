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
 * Блок Надо проверить. Веб-сервисы
 *
 * @package    block_notgraded
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


namespace block_notgraded;

defined('MOODLE_INTERNAL') || die();

require_once("$CFG->libdir/externallib.php");

use external_api;
use external_function_parameters;
use external_value;

class external extends external_api
{
    /**
     * Returns description of method parameters
     * @return external_function_parameters
     */
    public static function get_count_parameters()
    {
        $userid = new external_value(
            PARAM_INT,
            'Id-value for user whom notgraded items will be count',
            VALUE_REQUIRED
        );
        $requireupdate = new external_value(
            PARAM_BOOL,
            'If true, then cache will be updated and returned',
            VALUE_DEFAULT,
            false
        );
        
        $params = [
            'userid' => $userid,
            'requireupdate' => $requireupdate
        ];
        
        return new external_function_parameters($params);
    }
    
    /**
     * Returns count notgraded items
     * @return string welcome message
     */
    public static function get_count($userid, $requireupdate=false)
    {
        global $USER, $CFG;
        
        // Подключение API
        require_once($CFG->dirroot.'/blocks/notgraded/lib.php');
        
        $params = self::validate_parameters(self::get_count_parameters(), [
            'userid' => $userid,
            'requireupdate' => $requireupdate
        ]);
        
        if (!user_has_grade_capability_anywhere($userid))
        {
            throw new \moodle_exception('error_require_view_capability','block_notgraded');
        }
        
        $countnotgraded = null;

        $cache = new \block_notgraded_gradercache($userid);
        $cacherecord = $cache->get_cache();
        if( ! empty($cacherecord) )
        {
            $countnotgraded = $cacherecord->countnotgraded;
        }
        
        return $countnotgraded;
    }
    
    /**
     * Returns description of method result value
     * @return external_value
     */
    public static function get_count_returns()
    {
        return new external_value(PARAM_INT, 'Count of notgraded items');
    }
    
    
    
    
    /**
     * Returns description of method parameters
     * @return external_function_parameters
     */
    public static function get_count_after_connection_closed_parameters()
    {
        return self::get_count_parameters();
    }
    
    /**
     * Returns count notgraded items
     * @return string welcome message
     */
    public static function get_count_after_connection_closed($userid, $requireupdate=false)
    {
        global $USER, $CFG;
        
        // Подключение API
        require_once($CFG->dirroot.'/blocks/notgraded/lib.php');
        
        $params = self::validate_parameters(self::get_count_parameters(), [
            'userid' => $userid,
            'requireupdate' => $requireupdate
        ]);
        
        if (!user_has_grade_capability_anywhere($userid))
        {
            throw new \moodle_exception('error_require_view_capability','block_notgraded');
        }
        
        $countnotgraded = null;
        
        $cache = new \block_notgraded_gradercache($userid);
        $cacherecord = $cache->get_cache(true, (bool)$requireupdate);
        if( ! empty($cacherecord) )
        {
            $countnotgraded = $cacherecord->countnotgraded;
        }
        
        return $countnotgraded;
    }
    
    /**
     * Returns description of method result value
     * @return external_value
     */
    public static function get_count_after_connection_closed_returns()
    {
        return self::get_count_returns();
    }
}