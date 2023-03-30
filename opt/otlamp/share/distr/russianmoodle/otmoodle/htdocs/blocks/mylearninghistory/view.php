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

use block_mylearninghistory\utilities;

require_login();
if (isguestuser()) {
    print_error('guestsarenotallowed');
}
$view = optional_param('view', 'config', PARAM_TEXT);

$urlparams = [];
$plugintypes = ['mod'];

$context = context_system::instance();
$PAGE->set_context($context);

if ($view) {
    $urlparams['view'] = $view;
}
$url = new moodle_url('/blocks/mylearninghistory/view.php', $urlparams);
$PAGE->set_url($url);
$PAGE->set_pagelayout('standard');

$PAGE->set_title(get_string('learninghistory', 'block_mylearninghistory'));
$PAGE->set_heading(get_string('learninghistory', 'block_mylearninghistory'));

//$managefeeds = new moodle_url('/blocks/rss_client/managefeeds.php', $urlparams);
$PAGE->navbar->add(get_string('blocks'));
//$PAGE->navbar->add(get_string('pluginname', 'block_rss_client'));
//$PAGE->navbar->add(get_string('managefeeds', 'block_rss_client'), $managefeeds);
//$PAGE->navbar->add($strviewfeed);

//$settingsform = new settings_form($url->out(false));
//$settingsform->process();

echo $OUTPUT->header();
//block_mylearninghistory\local\utilities::
//$settingsform->display();
echo $OUTPUT->footer();
