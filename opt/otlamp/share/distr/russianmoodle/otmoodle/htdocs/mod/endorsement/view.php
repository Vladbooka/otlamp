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

// Необходимость отобразить форму, несмотря на то, что отзыв уже оставлен
$forceformdisplay = optional_param('onemore', false, PARAM_BOOL);

// Успешно сохранен отзыв
$itemsaved = optional_param('success', null, PARAM_BOOL);

$page = optional_param('page', 0, PARAM_INT);

if ($id) {
    $cm             = get_coursemodule_from_id('endorsement', $id, 0, false, MUST_EXIST);
    $course         = $DB->get_record('course', array('id' => $cm->course), '*', MUST_EXIST);
    $moduleinstance = $DB->get_record('endorsement', array('id' => $cm->instance), '*', MUST_EXIST);
} else if ($e) {
    $moduleinstance = $DB->get_record('endorsement', array('id' => $e), '*', MUST_EXIST);
    $course         = $DB->get_record('course', array('id' => $moduleinstance->course), '*', MUST_EXIST);
    $cm             = get_coursemodule_from_instance('endorsement', $moduleinstance->id, $course->id, false, MUST_EXIST);
} else {
    print_error(get_string('missingidandcmid', 'mod_endorsement'));
}

require_login($course, true, $cm);

$modulecontext = context_module::instance($cm->id);

// $event = \mod_endorsement\event\course_module_viewed::create(array(
//     'objectid' => $moduleinstance->id,
//     'context' => $modulecontext
// ));
// $event->add_record_snapshot('course', $course);
// $event->add_record_snapshot('mod_endorsement', $moduleinstance);
// $event->trigger();


$pageurl = new moodle_url('/mod/endorsement/view.php', ['id' => $cm->id]);
$newitemurl = fullclone($pageurl);
$newitemurl->param('onemore', true);

$PAGE->set_url($pageurl);
$PAGE->set_title(format_string($moduleinstance->name));
$PAGE->set_heading(format_string($course->fullname));
$PAGE->set_context($modulecontext);


$statuses = [];
$endorsementstatuses = mod_endorsement\userside::get_statuses();
foreach($endorsementstatuses as $statuscode => $statusname)
{
    if ( has_capability('mod/endorsement:view_'.$statuscode.'_own_endorsements', $modulecontext) )
    {
        $statuses[] = $statuscode;
    }
}

$commentarea = 'course';
$itemid = $course->id;

if (!empty($statuses))
{
    $itemsresult = local_crw\feedback\items::get_items([
        'contextid' => $modulecontext->id,
        'commentarea' => $commentarea,
        'itemid' => $itemid,
        'userid' => $USER->id,
        'status' => $statuses
    ], 'timecreated DESC', $page, \mod_endorsement\userside::DISPLAY_RESULTS_PER_PAGE);
}
$feedbackitems = $itemsresult['items'] ?? [];
$totalcount = $itemsresult['totalcount'] ?? 0;

$renderer = $PAGE->get_renderer('mod_endorsement');

$feedbackview = '';
if ((empty($feedbackitems) || !empty($forceformdisplay))
    && has_capability('mod/endorsement:to_endorse', $modulecontext))
{
    $cancelurl = empty($feedbackitems) ? null : $pageurl;
    $successurl = fullclone($pageurl);
    $successurl->param('success', true);
    $feedbackview = \mod_endorsement\form\new_endorsement::render_form($cm->id, $newitemurl, $successurl, $cancelurl);
    
} elseif (!empty($feedbackitems))
{
    $feedbackview = $renderer->render_userside(
        $feedbackitems,
        $totalcount,
        $pageurl,
        has_capability('mod/endorsement:to_endorse', $modulecontext) ? $newitemurl : null,
        $page,
        isset($itemsaved)
    );
}

$moderatelinkwrapper = '';
if (has_capability('mod/endorsement:moderate_endorsements', $modulecontext) ||
    has_capability('mod/endorsement:view_endorsements', $modulecontext))
{
    $moderateurl = new moodle_url('/mod/endorsement/list.php', ['id' => $cm->id]);
    $moderatelink = html_writer::link($moderateurl, get_string('endorsement_list', 'mod_endorsement'), ['class' => 'btn btn-primary']);
    $moderatelinkwrapper = html_writer::div($moderatelink, 'mod_endorsement_list_link');
}

$html = '';
if ($feedbackview == '' && $moderatelinkwrapper == '')
{
    $html = get_string('mod_view_no_data', 'mod_endorsement');
} else
{
    $html = $moderatelinkwrapper . $feedbackview;
}

echo $OUTPUT->header();

echo $html;

echo $OUTPUT->footer();
