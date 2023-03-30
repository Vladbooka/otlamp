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
 * Создания одноразового таска для исправления таблицы local_learninghistory_module
 *
 * @package    tool
 * @subpackage fixlocallearninghistorymodule
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once('../../../config.php');
require_once("$CFG->libdir/adminlib.php");

$PAGE->set_url('/admin/tool/fixlocallearninghistorymodule/index.php');
admin_externalpage_setup('toolfixlocallearninghistorymodule');

$html = '';

$form = new tool_fixlocallearninghistorymodule_form();
$taskstatus = $form->process();

if ( !empty(\core\task\manager::get_adhoc_tasks(
    '\tool_fixlocallearninghistorymodule\task\fix_local_learninghistory_module')))
{
    $html .= get_string('task_exist', 'tool_fixlocallearninghistorymodule');
} else {
    $html .= $form->render();
    if ($taskstatus) {
        if ($taskstatus == 'ok') {
            \core\notification::info(get_string('task_ok', 'tool_fixlocallearninghistorymodule'));
        } elseif ($taskstatus == 'error') {
            \core\notification::error(get_string('task_error', 'tool_fixlocallearninghistorymodule'));
        }
    }
}
echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('pageheader', 'tool_fixlocallearninghistorymodule'));

echo $html;

echo $OUTPUT->footer();
