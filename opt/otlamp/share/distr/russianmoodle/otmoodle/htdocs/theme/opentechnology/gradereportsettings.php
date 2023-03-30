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
 * Тема СЭО 3KL. Настройки отчета по оценкам.
 *
 * @package    theme
 * @subpackage opentechnology
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once('../../config.php');
require_once($CFG->dirroot.'/theme/opentechnology/formslib.php');
require_once($CFG->dirroot.'/user/editlib.php');

$userid = optional_param('id', $USER->id, PARAM_INT);    // User id.
// $userid = required_param('userid', PARAM_INT);    // User id.
$courseid = optional_param('courseid', SITEID, PARAM_INT);   // Course id (defaults to Site).

$PAGE->set_url('/theme/opentechnology/gradereportsettings.php', ['userid' => $userid, 'courseid' => $courseid]);

list($user, $course) = useredit_setup_preference_page($userid, $courseid);

// Create form.
$settingsform = new theme_opentechnology_gradereportsettings_form(null, ['userid' => $user->id, 'courseid' => $courseid]);

$settingsform->set_data($user);

$redirect = new moodle_url("/user/preferences.php", ['userid' => $user->id]);
if ($settingsform->is_cancelled()) {
    redirect($redirect);
} else if ($data = $settingsform->get_data()) {

    $user->preference_verticaldisplay = $data->preference_verticaldisplay;
    
    useredit_update_user_preference($user, false, false);
    // Trigger event.
    \core\event\user_updated::create_from_userid($user->id)->trigger();

    redirect($redirect);
}

// Display page header.
$streditmygradereportsettings = get_string('gradereportsettings', 'theme_opentechnology');
$userfullname     = fullname($user, true);

$PAGE->navbar->includesettingsbase = true;

$PAGE->set_title($streditmygradereportsettings);
$PAGE->set_heading($userfullname);

echo $OUTPUT->header();
echo $OUTPUT->heading($streditmygradereportsettings);

// Finally display THE form.
$settingsform->display();

// And proper footer.
echo $OUTPUT->footer();