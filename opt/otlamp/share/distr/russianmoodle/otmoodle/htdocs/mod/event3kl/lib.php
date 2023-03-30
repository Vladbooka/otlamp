<?php
use mod_event3kl\formats;
use mod_event3kl\event3kl;

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
 * This file contains the moodle hooks for the 3klevent module.
 *
 * @package   mod_3klevent
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die();

/**
 * Return the list if Moodle features this module supports
 *
 * @param string $feature FEATURE_xx constant for requested feature
 * @return mixed True if module supports feature, null if doesn't know
 */
function event3kl_supports($feature) {
    switch ($feature) {
        case FEATURE_GROUPS:
            return true;
        case FEATURE_GROUPINGS:
            return true;
        case FEATURE_MOD_INTRO:
            return true;
        case FEATURE_COMPLETION_TRACKS_VIEWS:
            return true;
//         case FEATURE_COMPLETION_HAS_RULES:
//             return true;
//         case FEATURE_GRADE_HAS_GRADE:
//             return true;
//         case FEATURE_GRADE_OUTCOMES:
//             return true;
        case FEATURE_BACKUP_MOODLE2:
            return true;
        case FEATURE_SHOW_DESCRIPTION:
            return true;

        default:
            return null;
    }
}

/**
 * Adds an event3kl instance
 *
 * @param stdClass $data
 * @param mod_event3kl_mod_form $form
 * @return int The instance id of the new event3kl
 */
function event3kl_add_instance(stdClass $data, mod_event3kl_mod_form $form = null) {
    $event3kl = new event3kl();
    $event3kl->from_form_data($data);
    return $event3kl->create()->get('id');
}

/**
 * Update an event3kl instance
 *
 * @param stdClass $data
 * @param stdClass $form - unused
 * @return object
 */
function event3kl_update_instance(stdClass $data, mod_event3kl_mod_form $form) {
    $event3kl = new event3kl($data->instance);
    $event3kl->from_form_data($data);
    return $event3kl->update();
}

/**
 * delete an event3kl instance
 * @param int $id
 * @return bool
 */
function event3kl_delete_instance($id) {
    $event3kl = new event3kl($id);
    return $event3kl->delete();
}

// /**
//  * Obtains the automatic completion state for this module based on any conditions
//  * in event3kl settings.
//  *
//  * @param object $course Course
//  * @param object $cm Course-module
//  * @param int $userid User ID
//  * @param bool $type Type of comparison (or/and; can be used as return value if no conditions)
//  * @return bool True if completed, false if not, $type if conditions not set.
//  */
// function event3kl_get_completion_state($course, $cm, $userid, $type) {

// }

/**
 * Create grade item for given event3kl.
 *
 * @param stdClass $event3kl record with extra cmidnumber
 * @param array $grades optional array/object of grade(s); 'reset' means reset grades in gradebook
 * @return int 0 if ok, error code otherwise
 */
function event3kl_grade_item_update($event3kl, $grades = null) {

}

/**
 * Update activity grades.
 *
 * @param stdClass $event3kl database record
 * @param int $userid specific user only, 0 means all
 * @param bool $nullifnone - not used
 */
function event3kl_update_grades($event3kl, $userid = 0, $nullifnone = true) {

}

/**
 * Return grade for given user or all users.
 *
 * @param stdClass $event3kl record of event3kl with an additional cmidnumber
 * @param int $userid optional user id, 0 means all users
 * @return array array of grades, false if none
 */
function event3kl_get_user_grades($event3kl, $userid = 0) {

}

/**
 * Rescale all grades for this activity and push the new grades to the gradebook.
 *
 * @param stdClass $course Course db record
 * @param stdClass $cm Course module db record
 * @param float $oldmin
 * @param float $oldmax
 * @param float $newmin
 * @param float $newmax
 */
function event3kl_rescale_activity_grades($course, $cm, $oldmin, $oldmax, $newmin, $newmax) {

}

/**
 * extend an event3kl navigation settings
 *
 * @param settings_navigation $settings
 * @param navigation_node $navref
 * @return void
 */
function event3kl_extend_settings_navigation(settings_navigation $settings, navigation_node $navref) {

}

/**
 * Serve the files from the MYPLUGIN file areas
 *
 * @param stdClass $course the course object
 * @param stdClass $cm the course module object
 * @param stdClass $context the context
 * @param string $filearea the name of the file area
 * @param array $args extra arguments (itemid, path)
 * @param bool $forcedownload whether or not force download
 * @param array $options additional options affecting the file serving
 * @return bool false if the file not found, just send the file otherwise and do not return anything
 */
function event3kl_pluginfile($course, $cm, $context, $filearea, $args, $forcedownload, array $options=array()) {
    // Check the contextlevel is as expected - if your plugin is a block, this becomes CONTEXT_BLOCK, etc.
    if ($context->contextlevel != CONTEXT_MODULE) {
        return false;
    }

    // Make sure the filearea is one of those used by the plugin.
    if ($filearea !== 'sessionrecord') {
        return false;
    }

    // Make sure the user is logged in and has access to the module (plugins that are not course modules should leave out the 'cm' part).
    require_login($course, true, $cm);

    // Check the relevant capabilities - these may vary depending on the filearea being accessed.
    if (!has_capability('mod/event3kl:view', $context)) {
        return false;
    }

    // Leave this line out if you set the itemid to null in make_pluginfile_url (set $itemid to 0 instead).
    $itemid = array_shift($args); // The first item in the $args array.

    // Use the itemid to retrieve any relevant data records and perform any security checks to see if the
    // user really does have access to the file in question.

    // Extract the filename / filepath from the $args array.
    $filename = array_pop($args); // The last item in the $args array.
    if (!$args) {
        $filepath = '/'; // $args is empty => the path is '/'
    } else {
        $filepath = '/'.implode('/', $args).'/'; // $args contains elements of the filepath
    }

    // Retrieve the file from the Files API.
    $fs = get_file_storage();
    $file = $fs->get_file($context->id, 'mod_event3kl', $filearea, $itemid, $filepath, $filename);
    if (!$file) {
        return false; // The file does not exist.
    }

    // We can now send the file back to the browser - in this case with a cache lifetime of 1 day and no filtering.
    send_stored_file($file, 86400, 0, $forcedownload, $options);
}
