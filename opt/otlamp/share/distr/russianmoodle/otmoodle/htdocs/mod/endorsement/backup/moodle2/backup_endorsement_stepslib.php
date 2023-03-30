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
 * @package    mod_endorsement
 * @subpackage backup-moodle2
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

class backup_endorsement_activity_structure_step extends backup_activity_structure_step
{
    /**
     * {@inheritDoc}
     * @see backup_structure_step::define_structure()
     */
    protected function define_structure() {
        
        // To know if we are including userinfo
        $userinfo = $this->get_setting_value('userinfo');
        
        // Define each element separated
        $endorsement = new backup_nested_element('endorsement', ['id'], [
            'course', 'name', 'intro', 'introformat', 'timecreated', 'timemodified'
        ]);
        
        $feedback = new backup_nested_element('feedback', ['id'], [
            'contextid', 'component', 'commentarea', 'itemid', 'content', 'format',
            'userid', 'status', 'acceptor', 'timeaccepted', 'timecreated'
        ]);
        
        // Build the tree
        $endorsement->add_child($feedback);
        
        // Define sources
        $endorsement->set_source_table('endorsement', ['id' => backup::VAR_ACTIVITYID]);
        if($userinfo)
        {
            $feedbacksql = 'SELECT *
                              FROM {crw_feedback}
                             WHERE contextid = ?
                               AND component = \'mod_endorsement\'';
            $feedback->set_source_sql($feedbacksql, [backup::VAR_CONTEXTID]);
            // Define id annotations
            $feedback->annotate_ids('user', 'userid');
        }
        
        // Return the root element (choice), wrapped into standard activity structure
        return $this->prepare_activity_structure($endorsement);
        
    }
}