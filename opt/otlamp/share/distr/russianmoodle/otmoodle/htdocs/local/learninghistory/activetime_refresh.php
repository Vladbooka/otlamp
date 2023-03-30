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
 * Интерфейс добавления одноразовой задачи на пересчет времени непрерывного изучения курса
 *
 * @package    local
 * @subpackage learninghistory
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once('../../config.php');
require_once("$CFG->libdir/adminlib.php");
require_once("$CFG->dirroot/local/learninghistory/formlib.php");

$PAGE->set_url('/local/learninghistory/activetime_refresh.php');
admin_externalpage_setup('tool_activetime_refresh');

$html = '';

$form = new activetime_refresh_form();
$taskadded = $form->process();
if ($form->is_submitted()) {
    if ($taskadded) {
        \core\notification::info(get_string('task_added', 'local_learninghistory'));
    } else {
        \core\notification::error(get_string('task_not_added', 'local_learninghistory'));
    }
}
$html .= $form->render();

echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('activetime_refresh', 'local_learninghistory'));

echo $html;

echo $OUTPUT->footer();
