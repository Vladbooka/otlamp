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

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/mod/endorsement/backup/moodle2/restore_endorsement_stepslib.php');

class restore_endorsement_activity_task extends restore_activity_task {

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
        $this->add_step(new restore_endorsement_activity_structure_step('endorsement_structure', 'endorsement.xml'));
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

}
