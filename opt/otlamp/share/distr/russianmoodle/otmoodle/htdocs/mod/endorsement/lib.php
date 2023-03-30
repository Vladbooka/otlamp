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
 * Library of interface functions and constants.
 *
 * @package     mod_endorsement
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Return if the plugin supports $feature.
 *
 * @param string $feature Constant representing the feature.
 * @return true | null True if the feature is supported, null otherwise.
 */
function endorsement_supports($feature) {
    switch ($feature) {
        case FEATURE_MOD_INTRO:
            return true;
        case FEATURE_BACKUP_MOODLE2:
            return true;
        default:
            return null;
    }
}

/**
 * Saves a new instance of the mod_endorsement into the database.
 *
 * Given an object containing all the necessary data, (defined by the form
 * in mod_form.php) this function will create a new instance and return the id
 * number of the instance.
 *
 * @param object $moduleinstance An object from the form.
 * @param mod_endorsement_mod_form $mform The form.
 * @return int The id of the newly inserted record.
 */
function endorsement_add_instance($moduleinstance, $mform = null) {
    global $DB;

    $moduleinstance->timecreated = time();

    $id = $DB->insert_record('endorsement', $moduleinstance);

    return $id;
}

/**
 * Updates an instance of the mod_endorsement in the database.
 *
 * Given an object containing all the necessary data (defined in mod_form.php),
 * this function will update an existing instance with new data.
 *
 * @param object $moduleinstance An object from the form in mod_form.php.
 * @param mod_endorsement_mod_form $mform The form.
 * @return bool True if successful, false otherwise.
 */
function endorsement_update_instance($moduleinstance, $mform = null) {
    global $DB;

    $moduleinstance->timemodified = time();
    $moduleinstance->id = $moduleinstance->instance;

    return $DB->update_record('endorsement', $moduleinstance);
}

/**
 * Removes an instance of the mod_endorsement from the database.
 *
 * @param int $id Id of the module instance.
 * @return bool True if successful, false on failure.
 */
function endorsement_delete_instance($id) {
    global $DB;

    $exists = $DB->get_record('endorsement', array('id' => $id));
    if (!$exists) {
        return false;
    }

    $cm = get_coursemodule_from_instance('endorsement', $id);
    mod_endorsement_delete_feedbacks($cm->id);
    
    $DB->delete_records('endorsement', array('id' => $id));

    return true;
}

/**
 * Получение формы с фильтрацией в качестве фрагмента
 *
 * @param array $args Список именованных аргументов для загрузчика фрагмента
 * @return string
 */
function mod_endorsement_output_fragment_change_status($args)
{
    global $PAGE, $USER;
    
    $PAGE->set_context(context_system::instance());
    
    $itemid = $args['itemid'];
    $jsonformdata = $args['jsonformdata'];
    $baseurl = $aargs['baseurl'];
    
    $ajaxformdata = [];
    parse_str(json_decode($jsonformdata), $ajaxformdata);
    
    return \mod_endorsement\form\status_change_endorsement::render_form($itemid, $baseurl, $ajaxformdata);
    
}

/**
 * The elements to add the course reset form.
 *
 * @param moodleform $mform
 */
function endorsement_reset_course_form_definition(&$mform) {
    $mform->addElement('header', 'endorsementheader', get_string('modulenameplural', 'mod_endorsement'));
    $mform->addElement('checkbox', 'reset_endorsement_feedbacks', get_string('removeallendorsementfeedbacks', 'mod_endorsement'));
}



function mod_endorsement_delete_feedbacks($cmid)
{
    $modulecontext = context_module::instance($cmid);
    $feedbackconditions = [
        'component' => 'mod_endorsement',
        'contextid' => $modulecontext->id
    ];
    try {
        $deleteresult = local_crw\feedback\items::delete_items(
            $feedbackconditions
            );
    } catch(dml_exception $ex)
    {
        $deleteresult = false;
    }
    return $deleteresult;
}

/**
 * This function is used by the reset_course_userdata function in moodlelib.
 * @param $data the data submitted from the reset course.
 * @return array status array
 */
function endorsement_reset_userdata($data) {
    global $DB;
    
    $status = [];
    
    if (!empty($data->reset_endorsement_feedbacks)) {
        
        // Loop through the books and remove the tags from the chapters.
        if ($endorsements = $DB->get_records('endorsement', ['course' => $data->courseid]))
        {
            foreach ($endorsements as $endorsement)
            {
                if (!$cm = get_coursemodule_from_instance('endorsement', $endorsement->id))
                {
                    continue;
                }
                
                $deleteresult = mod_endorsement_delete_feedbacks($cm->id);
                
                $status[] = [
                    'component' => get_string('modulenameplural', 'mod_endorsement'),
                    'item' => get_string('feedbacks_deleted', 'mod_endorsement', $cm->name),
                    'error' => !empty($deleteresult)
                ];
            }
        }
        
    }
    
    return $status;
}

