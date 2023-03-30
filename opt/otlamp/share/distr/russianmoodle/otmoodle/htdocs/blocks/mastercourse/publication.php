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
 * Settings view for block_mylearninghistory
 *
 * @package    block_mylearninghistory
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(dirname(__FILE__) . '/../../config.php');
require_once($CFG->dirroot . '/blocks/mastercourse/locallib.php');
require_once($CFG->dirroot . '/blocks/mastercourse/form_publication.php');

$blockcontextid = required_param('ctx', PARAM_INT);

require_login();
if (isguestuser()) {
    print_error('guestsarenotallowed');
}

$blockcontext = context::instance_by_id($blockcontextid);

require_capability('block/mastercourse:manage_publication', $blockcontext);

$PAGE->set_context($blockcontext);

$pageurl = new moodle_url('/blocks/mastercourse/publication.php', ['ctx' => $blockcontextid]);
$PAGE->set_url($pageurl);
$PAGE->set_pagelayout('standard');

$PAGE->set_title(get_string('page__publication', 'block_mastercourse'));
$PAGE->set_heading(get_string('page__publication', 'block_mastercourse'));

$html = '';

$parentcontext = $blockcontext->get_parent_context();
if ($parentcontext->contextlevel == CONTEXT_COURSE && $parentcontext->instanceid != get_site()->id)
{
    $html .= html_writer::div(
        html_writer::link(
            new moodle_url('/course/view.php', ['id' => $parentcontext->instanceid]),
            get_string('page__publication__back_to_the_course', 'block_mastercourse')
        ),
        'bttc-link'
    );
    foreach(get_all_services() as $serviceshortname)
    {
        $serviceclass = '\block_mastercourse\\eduportal\\'.$serviceshortname;
        $customdata = new stdClass();
        $customdata->serviceinstance = new $serviceclass($blockcontext);
        $serviceform = new block_mastercourse_form_publication($pageurl, $customdata);
        // меняем статус на новый
        $serviceform->process();
        $html .= $serviceform->render();
    }
} else {
    $html .= get_string('course_publication_only', 'block_mastercourse');
}

echo $OUTPUT->header();

echo $html;

echo $OUTPUT->footer();
