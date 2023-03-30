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
 * @package   block_linksocial
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
global $DB, $OUTPUT, $PAGE, $CFG, $COURSE, $USER;

require_once('../../config.php');
require_once($CFG->dirroot . '/auth/otoauth/lib.php');
require_once($CFG->dirroot . '/lib/moodlelib.php');
require_once($CFG->dirroot . '/blocks/linksocial/email_form.php');
                
require_login();

$page = optional_param('page', 0, PARAM_INT);
$limitnum = optional_param('limitnum', 40, PARAM_INT);

$PAGE->set_context(context::instance_by_id($COURSE->id));
$PAGE->set_url('/blocks/linksocial/signup.php');
$PAGE->set_pagelayout('standard');
$PAGE->set_heading(get_string('pluginname', 'block_linksocial'));
$PAGE->set_title(get_string('pluginname', 'block_linksocial'));

// хлебные крошки
$PAGE->navbar->ignore_active();
$PAGE->navbar->add(get_string('acceptname', 'block_linksocial'), new moodle_url("$CFG->wwwroot/blocks/linksocial/signup.php"));
$previewnode = $PAGE->navigation->add(get_string('pluginname', 'block_linksocial'), new moodle_url('/blocks/linksocial/view.php'), navigation_node::TYPE_CONTAINER);

$emailform = new block_email_form();
$myurl = new moodle_url($CFG->wwwroot . '/my');
$wantsurl = optional_param('wantsurl', $myurl, PARAM_URL);

$emailform->process();

echo $OUTPUT->header();
echo html_writer::tag('span', get_string('emailsend', 'block_linksocial'));
$emailform->display();
echo $OUTPUT->footer();