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
 * Языковые строки
 * 
 * @package    availability_assignfeedback
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$string['pluginname'] = 'Restriction by assign feedback';
$string['title'] = 'Assign feedback';
$string['description'] = 'Require students to achieve feedback in assign';

$string['inassign'] = 'In assign ';
$string['needfeedback'] = ' user has achieved feedback ';
$string['chooseassign'] = 'Choose assign...';
$string['chooseassignfeedback'] = 'Choose feedback type...';
$string['error_selectcmid'] = 'You must select an assign for the assign feedback condition.';
$string['error_selectfeedbackcode'] = 'You must select feedback type for the assign feedback condition.';

$string['unknown_assign'] = '[ Assign not found ]';
$string['unknown_feedbacktype'] = '[ Feedback type not found for assign ]';
$string['requires_feedback'] = 'You should achieve feedback of "{$a->feedbacktype}" type in assign "{$a->assignname}"';
$string['no_feedback_required'] = 'You shouldn\'t achieve feedback of "{$a->feedbacktype}" type in assign "{$a->assignname}"';