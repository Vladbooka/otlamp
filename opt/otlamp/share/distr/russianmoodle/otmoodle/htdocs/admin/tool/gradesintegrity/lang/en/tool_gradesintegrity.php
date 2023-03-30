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
 * Файл языковых строк
 *
 * @package    tool
 * @subpackage gradesintegrity
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$string['pluginname'] = 'Grades integrity tool';
$string['pageheader'] = 'Restoring the grades tables integrity';
$string['form_description'] = 'The utility allows you to restore the integrity of the database in part of the tables associated with the estimates.
                               To start, click on the button below.';
$string['form_doit'] = 'Perform recovery';
$string['gi_deleted_successfully'] = 'Record from the grade_items table with id={$a->id} was successfully deleted.';
$string['gi_deleting_failed'] = 'Failed to delete record from grade_items table with id={$a->id}.';
$string['gg_deleted_successfully'] = 'Record from the grade_grades table with id={$a->id} was successfully deleted.';
$string['not_found_records_part_one'] = 'No records were found in the grade_items table that did not match the course_modules table';
$string['not_found_records_part_two'] = 'No records were found in the grade_grades table that did not match the grade_items table';