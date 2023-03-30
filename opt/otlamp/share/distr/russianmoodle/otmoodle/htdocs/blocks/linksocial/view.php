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
require_once('../../config.php');
global $DB, $OUTPUT, $PAGE, $CFG, $COURSE, $USER;

require_once($CFG->dirroot . '/blocks/linksocial/lib.php');
require_once($CFG->dirroot . '/lib/outputcomponents.php');
require_once($CFG->libdir . '/outputcomponents.php');

require_login();

$page      = optional_param('page', 0, PARAM_INT);
$limitnum  = optional_param('limitnum', 40, PARAM_INT);

$PAGE->set_context(context::instance_by_id($COURSE->id));
$PAGE->set_url('/blocks/linksocial/view.php');
$PAGE->set_pagelayout('standard');
$PAGE->set_heading(get_string('pluginname', 'block_linksocial'));
$PAGE->set_title(get_string('pluginname', 'block_linksocial'));

// хлебные крошки
$PAGE->navbar->ignore_active();
$PAGE->navbar->add(get_string('pluginname', 'block_linksocial'), new moodle_url('/blocks/linksocial/view.php'));
$previewnode = $PAGE->navigation->add(get_string('pluginname', 'block_linksocial'), new moodle_url('/blocks/linksocial/view.php'), navigation_node::TYPE_CONTAINER);

echo $OUTPUT->header();

if (is_siteadmin($USER->id)) {
    $totalcount = $DB->count_records('block_linksocial');
} else if ($DB->record_exists('cohort_members',array('userid'=>$USER->id, 'cohortid'=>RATING_MANAGER))) {
    $totalcount = manager_totalcount($USER->id);
} else if ($DB->record_exists('cohort_members',array('userid'=>$USER->id, 'cohortid'=>RATING_EMPLOYEE))) {
    $totalcount = user_totalcount($USER->id);
} else {
    $totalcount = 0;
}

echo show_rating_list($limitnum, $page * $limitnum);

if (!empty($limitnum)) {
    echo $OUTPUT->paging_bar($totalcount, $page, $limitnum, $CFG->wwwroot . 
                            '/blocks/linksocial/view.php?limitnum=' . $limitnum . '&amp;');
}
echo $OUTPUT->footer();