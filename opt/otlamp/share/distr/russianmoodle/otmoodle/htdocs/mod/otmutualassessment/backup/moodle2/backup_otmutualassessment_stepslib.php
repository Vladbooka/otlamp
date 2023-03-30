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
 * Define all the backup steps that will be used by the backup_assign_activity_task
 *
 * @package   mod_otmutualassessment
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Define the complete choice structure for backup, with file and id annotations
 *
 * @package   mod_otmutualassessment
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class backup_otmutualassessment_activity_structure_step extends backup_activity_structure_step {

    /**
     * Define the structure for the assign activity
     * @return void
     */
    protected function define_structure() {

        // To know if we are including userinfo.
        $userinfo = $this->get_setting_value('userinfo');
        $groupinfo = $this->get_setting_value('groups');

        // Define each element separated.
        $otmutualassessment = new backup_nested_element('otmutualassessment', ['id'],
                                                 ['name',
                                                  'intro',
                                                  'introformat',
                                                  'timecreated',
                                                  'timemodified',
                                                  'strategy',
                                                  'grade',
                                                  'completionsetgrades',]);

        $points = new backup_nested_element('points');

        $point = new backup_nested_element('point', ['id'],
                                                ['grader',
                                                 'graded',
                                                 'groupid',
                                                 'point',
                                                 'timecreated',
                                                 'timemodified',]);

        $grades = new backup_nested_element('grades');

        $grade = new backup_nested_element('grade', ['id'],
                                                ['userid',
                                                 'grade',
                                                 'timecreated',
                                                 'timemodified',]);
                                                
        $statuses = new backup_nested_element('statuses');
                                                
        $status = new backup_nested_element('status', ['id'],
                                                    ['userid',
                                                     'groupid',
                                                     'status',
                                                     'timecreated',
                                                     'timemodified',]);

        // Build the tree.
        $otmutualassessment->add_child($points);
        $points->add_child($point);
        $otmutualassessment->add_child($grades);
        $grades->add_child($grade);
        $otmutualassessment->add_child($statuses);
        $statuses->add_child($status);

        // Define sources.
        $otmutualassessment->set_source_table('otmutualassessment', ['id' => backup::VAR_ACTIVITYID]);

        if ($userinfo) {
            $pointparams = ['otmutualassessmentid' => backup::VAR_PARENTID];
            if (!$groupinfo) {
                // Without group info, skip group submissions.
                $pointparams['groupid'] = backup_helper::is_sqlparam(0);
            }
            $point->set_source_table('otmutualassessment_points', $pointparams);
            
            $grade->set_source_table('otmutualassessment_grades',
                ['otmutualassessmentid' => backup::VAR_PARENTID]);
            
            $statusparams = ['otmutualassessmentid' => backup::VAR_PARENTID];
            if (!$groupinfo) {
                // Without group info, skip group submissions.
                $statusparams['groupid'] = backup_helper::is_sqlparam(0);
            }
            $status->set_source_table('otmutualassessment_statuses', $statusparams);
        }

        // Define id annotations.
        $otmutualassessment->annotate_ids('scale', 'grade');
        $point->annotate_ids('user', 'grader');
        $point->annotate_ids('user', 'graded');
        $point->annotate_ids('group', 'groupid');
        $grade->annotate_ids('user', 'userid');
        $status->annotate_ids('user', 'userid');
        $status->annotate_ids('group', 'groupid');

        // Define file annotations.
        // These file areas don't have an itemid.
        $otmutualassessment->annotate_files('mod_otmutualassessment', 'intro', null);
        // Return the root element (choice), wrapped into standard activity structure.

        return $this->prepare_activity_structure($otmutualassessment);
    }
}
