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
 * otautoenrol enrolment plugin.
 *
 * This plugin automatically enrols a user onto a course the first time they try to access it.
 *
 * @package    enrol
 * @subpackage otautoenrol
 * @date       November 2014
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

function xmldb_enrol_otautoenrol_upgrade($oldversion) {
    global $DB;
    $dbman = $DB->get_manager();

    if ( $oldversion < 2018011517 )
    {
        // Удаление всех условий, связанных с полем confirmed/deleted
        $records = $DB->get_records('enrol', ['enrol' => 'otautoenrol'], '', 'id,enrol,customtext3');
        if ( ! empty($records) )
        {
            foreach ( $records as $record )
            {
                if ( ! empty($record->customtext3) )
                {
                    $conditionals = json_decode($record->customtext3);
                    $newconditionals = [];
                    if ( ! empty($conditionals) && is_array($conditionals) )
                    {
                        foreach ( $conditionals as $conditional )
                        {
                            if ( ($conditional->conditionfield == 'confirmed') || 
                                    ($conditional->conditionfield == 'suspended') )
                            {
                                continue;
                            }
                            $newconditionals[] = $conditional;
                        }
                        $record->customtext3 = json_encode($newconditionals);
                        $DB->update_record('enrol', $record);
                    }
                }
            }
        }
    }
    
    return true;
}
