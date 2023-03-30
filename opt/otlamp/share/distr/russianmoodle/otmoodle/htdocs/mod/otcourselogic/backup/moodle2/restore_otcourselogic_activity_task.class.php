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
 * @package    mod_otcourselogic
 * @subpackage backup-moodle2
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/mod/otcourselogic/backup/moodle2/restore_otcourselogic_stepslib.php'); 

class restore_otcourselogic_activity_task extends restore_activity_task {

    /**
     * {@inheritDoc}
     * @see restore_activity_task::define_my_settings()
     */
    protected function define_my_settings() 
    {
    }

    /**
     * {@inheritDoc}
     * @see restore_activity_task::define_my_steps()
     */
    protected function define_my_steps() 
    {
        $this->add_step(new restore_otcourselogic_activity_structure_step('otcourselogic_structure', 'otcourselogic.xml'));
    }

    /**
     * Define the contents in the activity that must be
     * processed by the link decoder
     */
    static public function define_decode_contents() {
        $contents = array();
        return $contents;
    }

   
    /**
     * 
     * @return array
     */
    static public function define_decode_rules() 
    {
        return [];
    }

    /**
     * @return restore_log_rule[]
     */
    static public function define_restore_log_rules() 
    {
        $rules = [];

        $rules[] = new restore_log_rule('otcourselogic', 'add', 'view.php?id={course_module}', '{otcourselogic}');
        $rules[] = new restore_log_rule('otcourselogic', 'update', 'view.php?id={course_module}', '{otcourselogic}');
        $rules[] = new restore_log_rule('otcourselogic', 'view', 'view.php?id={course_module}', '{otcourselogic}');
        $rules[] = new restore_log_rule('otcourselogic', 'choose', 'view.php?id={course_module}', '{otcourselogic}');
        $rules[] = new restore_log_rule('otcourselogic', 'choose again', 'view.php?id={course_module}', '{otcourselogic}');
        $rules[] = new restore_log_rule('otcourselogic', 'report', 'report.php?id={course_module}', '{otcourselogic}');

        return $rules;
    }

    /**
     * @return restore_log_rule[]
     */
    static public function define_restore_log_rules_for_course() 
    {
        $rules = [];

        $rules[] = new restore_log_rule('otcourselogic', 'view all', 'index?id={course}', null, null, null, 'index.php?id={course}');
        $rules[] = new restore_log_rule('otcourselogic', 'view all', 'index.php?id={course}', null);

        return $rules;
    }
}
