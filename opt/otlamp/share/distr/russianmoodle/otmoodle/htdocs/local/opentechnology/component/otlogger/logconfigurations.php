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
 * Настроки системы логирования OTLogger.
 *
 * @package    local_opentechnology
 * @subpackage otcomponent_otlogger
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use otcomponent_otlogger\form\logger_settings_form;

require_once('../../../../config.php');
require_once($CFG->libdir . '/adminlib.php');

admin_externalpage_setup('componentotlogger');

$html = '';

$form = new logger_settings_form();
$form->process();
$html .= $form->render();

echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('pluginname', 'otcomponent_otlogger'));

echo $html;

echo $OUTPUT->footer();