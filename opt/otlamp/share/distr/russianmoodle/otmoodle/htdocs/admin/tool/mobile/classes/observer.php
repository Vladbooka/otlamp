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
 * Обозреватель событий для плагина local_mobile
 * 
 * @package    tool_mobile
 * @author  2018 Dmitry Ivanov <dimka_ivanov@list.ru>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace tool_mobile;

use moodle_url;

defined('MOODLE_INTERNAL') || die();

/**
 * Event observer for mod_forum.
 */
class observer 
{

    /**
     * Triggered via user_loggedin event.
     *
     * @param \core\event\user_loggedin $event
     */
    public static function user_loggedin(\core\event\user_loggedin $event) 
    {
        global $SESSION;
        
        if( ! empty($SESSION->wantsurl) && strpos($SESSION->wantsurl, '/tool/mobile/launch.php') !== false )
        {
            $SESSION->toolmobilewantsurl = $SESSION->wantsurl;
        }
    }

    /**
     * Triggered via user_updated event.
     *
     * @param \core\event\user_updated $event
     */
    public static function user_updated(\core\event\user_updated $event) 
    {
        global $USER, $SESSION, $CFG;
        
        if( isset($SESSION->toolmobilewantsurl) && 
            ! user_not_fully_set_up($USER) && 
            (($CFG->sitepolicy && $USER->policyagreed == 1) || ! $CFG->sitepolicy) )
        {
            $url = new moodle_url($SESSION->toolmobilewantsurl);
            unset($SESSION->toolmobilewantsurl);
            redirect($url);
        }
    }
}
