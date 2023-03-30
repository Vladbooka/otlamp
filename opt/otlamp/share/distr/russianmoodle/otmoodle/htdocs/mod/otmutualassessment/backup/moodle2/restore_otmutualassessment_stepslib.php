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
 * Define all the restore steps that will be used by the restore_otmutualassessment_activity_task
 *
 * @package   mod_otmutualassessment
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Define the complete otmutualassessment structure for restore, with file and id annotations
 *
 * @package   mod_otmutualassessment
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class restore_otmutualassessment_activity_structure_step extends restore_activity_structure_step {

    /**
     * Define the structure of the restore workflow.
     *
     * @return restore_path_element $structure
     */
    protected function define_structure() {

        $paths = [];
        // To know if we are including userinfo.
        $userinfo = $this->get_setting_value('userinfo');

        // Define each element separated.
        $paths[] = new restore_path_element('otmutualassessment', '/activity/otmutualassessment');
        if ($userinfo) {
            $point = new restore_path_element('otmutualassessment_points', '/activity/otmutualassessment/points/point');
            $paths[] = $point;
            $grade = new restore_path_element('otmutualassessment_grades', '/activity/otmutualassessment/grades/grade');
            $paths[] = $grade;
            $status = new restore_path_element('otmutualassessment_statuses', '/activity/otmutualassessment/statuses/status');
            $paths[] = $status;
        }

        return $this->prepare_activity_structure($paths);
    }

    /**
     * Process an otmutualassessment restore.
     *
     * @param object $data The data in object form
     * @return void
     */
    protected function process_otmutualassessment($data) {
        global $DB;

        $data = (object)$data;
        $oldid = $data->id;
        $data->course = $this->get_courseid();
        
        $data->timecreated = $this->apply_date_offset($data->timecreated);
        $data->timemodified = $this->apply_date_offset($data->timemodified);
        if ($data->grade < 0) { // Scale found, get mapping.
            $data->grade = -($this->get_mappingid('scale', abs($data->grade)));
        }

        $newitemid = $DB->insert_record('otmutualassessment', $data);
        $this->set_mapping('otmutualassessment', $oldid, $newitemid);
        $this->apply_activity_instance($newitemid);
    }

    /**
     * Process a point restore
     * @param object $data The data in object form
     * @return void
     */
    protected function process_otmutualassessment_points($data) {
        global $DB;

        $data = (object)$data;
        $oldid = $data->id;

        $data->otmutualassessmentid = $this->get_new_parentid('otmutualassessment');

        $data->grader = $this->get_mappingid('user', $data->grader);
        $data->graded = $this->get_mappingid('user', $data->graded);
        
        $data->groupid = $this->get_mappingid('group', $data->groupid);
        
        $data->timecreated = $this->apply_date_offset($data->timecreated);
        $data->timemodified = $this->apply_date_offset($data->timemodified);
        
        $newitemid = $DB->insert_record('otmutualassessment_points', $data);
        $this->set_mapping('otmutualassessment_points', $oldid, $newitemid, false); // no files associated
    }
    
    /**
     * Process a grade restore
     * @param object $data The data in object form
     * @return void
     */
    protected function process_otmutualassessment_grades($data) {
        global $DB;
        
        $data = (object)$data;
        $oldid = $data->id;
        
        $data->otmutualassessmentid = $this->get_new_parentid('otmutualassessment');
        
        $data->userid = $this->get_mappingid('user', $data->userid);
        
        $data->timecreated = $this->apply_date_offset($data->timecreated);
        $data->timemodified = $this->apply_date_offset($data->timemodified);
        
        $newitemid = $DB->insert_record('otmutualassessment_grades', $data);
        $this->set_mapping('otmutualassessment_grades', $oldid, $newitemid, false); // no files associated
    }
    
    /**
     * Process a status restore
     * @param object $data The data in object form
     * @return void
     */
    protected function process_otmutualassessment_statuses($data) {
        global $DB;
        
        $data = (object)$data;
        $oldid = $data->id;
        
        $data->otmutualassessmentid = $this->get_new_parentid('otmutualassessment');
        
        $data->userid = $this->get_mappingid('user', $data->userid);
        
        $data->groupid = $this->get_mappingid('group', $data->groupid);
        
        $data->timecreated = $this->apply_date_offset($data->timecreated);
        $data->timemodified = $this->apply_date_offset($data->timemodified);
        
        $newitemid = $DB->insert_record('otmutualassessment_statuses', $data);
        $this->set_mapping('otmutualassessment_statuses', $oldid, $newitemid, false); // no files associated
    }

    /**
     * Once the database tables have been fully restored, restore the files
     * @return void
     */
    protected function after_execute() {
        $this->add_related_files('mod_otmutualassessment', 'intro', null);
    }
}
