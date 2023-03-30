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
 * @package moodlecore
 * @subpackage backup-moodle2
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Define all the restore steps that will be used by the restore_otresourcelibrary_activity_task
 */

/**
 * Structure step to restore one otresourcelibrary activity
 */
class restore_otresourcelibrary_activity_structure_step extends restore_activity_structure_step {
    
    protected function define_structure() {

        $paths = [];

        $paths[] = new restore_path_element('otresourcelibrary', '/activity/otresourcelibrary');

        // Return the paths wrapped into standard activity structure
        return $this->prepare_activity_structure($paths);
    }

    protected function process_otresourcelibrary($olddata) {
        global $DB;

        $olddata = (object)$olddata;
        $data = new stdClass();
        $data = $olddata;
        
        $data->course = $this->get_courseid();
        $data->timemodified = $this->apply_date_offset($olddata->timemodified);

        // insert the otresourcelibrary record
        $newitemid = $DB->insert_record('otresourcelibrary', $data);
        // immediately after inserting "activity" record, call this
        $this->apply_activity_instance($newitemid);
    }



    protected function after_execute() {
        global $CFG;

        // Add otresourcelibrary related files, no need to match by itemname (just internally handled context)
        $this->add_related_files('mod_otresourcelibrary', 'files', null);
    }
    
      
    private function get_backupinfo($info) {
        $backupinfo = backup_general_helper::get_backup_information(basename($this->get_task()->get_basepath()));
        if (object_property_exists($backupinfo, $info)){
            return $backupinfo->$info;
        } 
        return false;
    }
}
