<?php
use mod_endorsement;

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
 * Prints an instance of mod_endorsement.
 *
 * @package     mod_endorsement
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require(__DIR__.'/../../config.php');
require_once(__DIR__.'/lib.php');

// Course_module ID, or
$id = optional_param('id', 0, PARAM_INT);

// ... module instance id.
$e  = optional_param('e', 0, PARAM_INT);

$status = optional_param('status', NULL, PARAM_ALPHA);

$courseid = optional_param('courseid', NULL, PARAM_INT);

$page = optional_param('page', 0, PARAM_INT);

$feedbackconditions = [
    'commentarea' => 'course'
];

if ($id || $e) {
    if ($id) {
        $cm             = get_coursemodule_from_id('endorsement', $id, 0, false, MUST_EXIST);
        $course         = $DB->get_record('course', array('id' => $cm->course), '*', MUST_EXIST);
        $moduleinstance = $DB->get_record('endorsement', array('id' => $cm->instance), '*', MUST_EXIST);
    } else if ($e) {
        $moduleinstance = $DB->get_record('endorsement', array('id' => $e), '*', MUST_EXIST);
        $course         = $DB->get_record('course', array('id' => $moduleinstance->course), '*', MUST_EXIST);
        $cm             = get_coursemodule_from_instance('endorsement', $moduleinstance->id, $course->id, false, MUST_EXIST);
    }
    
    require_login($course, true, $cm);
    
    $modulecontext = context_module::instance($cm->id);
    $PAGE->set_context($modulecontext);
    $PAGE->set_title(format_string($moduleinstance->name));
    $PAGE->set_heading(format_string($course->fullname));
    
    $pageurl = new moodle_url('/mod/endorsement/list.php', ['id' => $cm->id]);
    
    $feedbackconditions['itemid'] = $course->id;
    
}else {
    require_login();
    $PAGE->set_context(context_system::instance());
    $PAGE->set_title(format_string(get_string('endorsement_list_page_title', 'mod_endorsement')));
    $PAGE->set_heading(format_string(get_string('endorsement_list_page_heading', 'mod_endorsement')));
    
    $pageurl = new moodle_url('/mod/endorsement/list.php');
}

if (!empty($courseid) && $courseid !== 1)
{
    $feedbackconditions['itemid'] = $courseid ?? 0;
    $pageurl->param('courseid', $courseid ?? 0);
}
if (!is_null($status))
{
    $feedbackconditions['status'] = $status;
    $pageurl->param('status', $status);
}

$PAGE->set_url($pageurl);


$itemsresult = local_crw\feedback\items::get_items(
    $feedbackconditions,
    'timecreated DESC', $page, \mod_endorsement\moderatorside::DISPLAY_RESULTS_PER_PAGE
);
$feedbackitems = $itemsresult['items'] ?? [];
$totalcount = $itemsresult['totalcount'] ?? 0;


$renderer = $PAGE->get_renderer('mod_endorsement');


$endorsementsview = $renderer->render_moderatorside(
    $feedbackitems,
    $totalcount,
    $pageurl,
    $page,
    $feedbackconditions['itemid'] ?? 1
);

$PAGE->requires->js_call_amd('mod_endorsement/moderation', 'init', [
    'contextid' => $PAGE->context->id,
    'pageurl' => $pageurl->out(false)
]);

echo $OUTPUT->header();

echo $endorsementsview;

echo $OUTPUT->footer();
