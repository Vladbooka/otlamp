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
 * Поиск и удаление удаленных пользователей из глобальной группы
 *
 * @package    tool
 * @subpackage removedeletedusersfromcohorts
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once('../../../config.php');
require_once("$CFG->libdir/adminlib.php");

$PAGE->set_url('/admin/tool/removedeletedusersfromcohorts/index.php');
admin_externalpage_setup('toolremovedeletedusersfromcohorts');

$html = '';

$form = new tool_removedeletedusersfromcohorts_form();
$form->process();
$html .= $form->render();

echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('pageheader', 'tool_removedeletedusersfromcohorts'));

echo $html;

echo $OUTPUT->footer();
