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
 * История обучения. Веб-сервисы
 *
 * @package    local_learninghistory
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_learninghistory;

require_once(dirname(realpath(__FILE__)).'/../../../config.php');
require_once($CFG->dirroot . '/local/learninghistory/classes/activetime.php');
require_once($CFG->dirroot . '/local/learninghistory/classes/attempt/attempt_base.php');
require_once($CFG->dirroot . '/local/learninghistory/classes/attempt/mod/attempt_mod_assign.php');
require_once($CFG->dirroot . '/local/learninghistory/classes/attempt/mod/attempt_mod_quiz.php');
require_once("$CFG->libdir/externallib.php");

defined('MOODLE_INTERNAL') || die();

use external_api;
use external_function_parameters;
use external_value;
use moodle_exception;
use context_course;

class external extends external_api
{
    /**
     * Returns description of method parameters
     * @return external_function_parameters
     */
    public static function add_activetime_updated_log_parameters()
    {
        $userid = new external_value(
            PARAM_INT,
            'Id-value for user',
            VALUE_REQUIRED
        );
        $courseid = new external_value(
            PARAM_INT,
            'Id-value for course',
            VALUE_REQUIRED
        );
        $contextid = new external_value(
            PARAM_INT,
            'Id-value for context',
            VALUE_REQUIRED
        );

        $params = [
            'userid' => $userid,
            'courseid' => $courseid,
            'contextid' => $contextid
        ];

        return new external_function_parameters($params);
    }
    
    /**
     * Добавление лога обновления времени изучения курса
     * @param int $userid
     * @param int $courseid
     * @param bool $addlog
     * @return boolean
     */
    public static function add_activetime_updated_log($userid, $courseid, $contextid)
    {
        global $CFG;
        $params = self::validate_parameters(self::add_activetime_updated_log_parameters(), [
            'userid' => $userid,
            'courseid' => $courseid,
            'contextid' => $contextid
        ]);
        $coursecontext = context_course::instance($courseid);
        if( is_enrolled($coursecontext, $userid) )
        {
            $activetime = new activetime($courseid);
            if( ! empty($activetime) )
            {
                try
                {
                    $activetime->add_log($userid, $contextid);
                } catch(moodle_exception $e)
                {
                    return false;
                }
            
            } else
            {
                return false;
            }
            return true;
        } else
        {
            return false;
        }
    }

    /**
     * Returns description of method result value
     * @return external_description
     */
    public static function add_activetime_updated_log_returns()
    {
        return new external_value(PARAM_BOOL, 'Add or not add log');
    }
    
    /**
     * Returns description of method parameters
     * @return external_function_parameters
     */
    public static function get_current_activetime_parameters()
    {
        $userid = new external_value(
            PARAM_INT,
            'Id-value for user',
            VALUE_REQUIRED
            );
        $courseid = new external_value(
            PARAM_INT,
            'Id-value for course',
            VALUE_REQUIRED
            );
    
        $params = [
            'userid' => $userid,
            'courseid' => $courseid
        ];
    
        return new external_function_parameters($params);
    }
    
    /**
     * Возвращает текущее значение времени затраченного на изучение курса
     * @param int $userid
     * @param int $courseid
     * @return number
     */
    public static function get_current_activetime($userid, $courseid)
    {
        global $CFG;
        
        $params = self::validate_parameters(self::get_current_activetime_parameters(), [
            'userid' => $userid,
            'courseid' => $courseid
        ]);
        
        $activetime = 0;
        $atlastupdate = null;
        
        require_once($CFG->dirroot.'/local/learninghistory/classes/activetime.php');
        
        $activetimeobj = new activetime($courseid);
        if (!empty($activetimeobj))
        {
            $record = $activetimeobj->get_first_active($userid);
            $activetime = (int)$record->activetime;
            $atlastupdate = (int)$record->atlastupdate;
        }
        
        return json_encode([
            'activetime' => $activetime,
            'atlastupdate' => $atlastupdate
        ]);
        
    }
    
    /**
     * Returns description of method result value
     * @return external_description
     */
    public static function get_current_activetime_returns()
    {
        return new external_value(PARAM_RAW_TRIMMED, 'JSON-encoded string, containing data like {"activetime":0,"atlastupdate":null}');
    }
}