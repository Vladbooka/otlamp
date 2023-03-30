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
 * Менеджер подписок для плагина local_learninghistory
 * 
 * @package    local_learninghistory
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_learninghistory\local;

defined('MOODLE_INTERNAL') || die();

/**
 * Класс для отслеживания подписок пользователя на курс
 */
class enrol_manager {
    
    /**
     * Номер курса главной страницы
     * @var int 
     */
    private $siteid;
    
    public function __construct() {
        global $SITE;
        $this->siteid = $SITE->id;
        
    }

    public static function get_active_courses($userid = 0) {
        if (empty($userid)) {
            global $USER;
            $userid = $USER->id;
        }
        $courses = enrol_get_users_courses($userid);
        if (empty($courses)) {
            return false;
        }
        return $courses;
    }

    public static function get_user_enrol_info($ueid) {
        global $DB;
        $params = [
            'id' => $ueid
        ];

        $userenrolment = $DB->get_record('user_enrolments', $params);
        if (empty($userenrolment)) {
            return false;
        }
        $enrolparams = [
            'id' => $userenrolment->enrolid
        ];
        $enrol = $DB->get_record('enrol', $enrolparams);
        if (!empty($enrol)) {
            $userenrolment->enrol = $enrol;
        }
        return $userenrolment;


    }

}
