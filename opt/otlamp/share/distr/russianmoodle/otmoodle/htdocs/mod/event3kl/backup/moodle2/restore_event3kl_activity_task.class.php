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
 * @package    mod_event3kl
 * @subpackage backup-moodle2
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use mod_event3kl\event3kl;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/mod/event3kl/backup/moodle2/restore_event3kl_stepslib.php'); 

class restore_event3kl_activity_task extends restore_activity_task {

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
        $this->add_step(new restore_event3kl_activity_structure_step('event3kl_structure', 'event3kl.xml'));
    }

    /**
     * Define the contents in the activity that must be
     * processed by the link decoder
     */
    static public function define_decode_contents() {
        $contents = array();
        
        $contents[] = new restore_decode_content('event3kl', array('intro'), 'event3kl');
        
        return $contents;
    }

   
    /**
     * 
     * @return array
     */
    static public function define_decode_rules() 
    {
        $rules = [];
        
        $rules[] = new restore_decode_rule('EVENT3KLVIEWBYID', '/mod/event3kl/view.php?id=$1', 'course_module');
        
        return $rules;
    }

    /**
     * @return restore_log_rule[]
     */
    static public function define_restore_log_rules() 
    {
        $rules = [];

        $rules[] = new restore_log_rule('event3kl', 'add', 'view.php?id={course_module}', '{event3kl}');
        $rules[] = new restore_log_rule('event3kl', 'update', 'view.php?id={course_module}', '{event3kl}');
        $rules[] = new restore_log_rule('event3kl', 'view', 'view.php?id={course_module}', '{event3kl}');

        return $rules;
    }

    /**
     * @return restore_log_rule[]
     */
    static public function define_restore_log_rules_for_course() 
    {
        $rules = [];

        // Просмотр списка экземпляров модуля, пока что не реализован
        $rules[] = new restore_log_rule('event3kl', 'view all', 'index.php?id={course}', null);

        return $rules;
    }
    
    /*
     * This function is called after all the activities in the backup have been restored.
     * This allows us to get the new course module ids, as they may have been restored after the
     * event3kl module, meaning no id was available at the time.
     */
    public function after_restore(){
        
        global $DB;
        
        if ($record = $DB->get_record('event3kl', array('id' => $this->get_activityid()))){
            $event3kl = new event3kl();
            $event3kl = $event3kl->from_record($record);
            $event3kl->actualize_sessions();
        }
    }
}
