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

require_once('../../config.php');
require_once($CFG->libdir . '/adminlib.php');

use \local_opentechnology\form\reset_site_identifier;

global $OUTPUT, $PAGE;

admin_externalpage_setup('resetsiteidentifier');

$html = '';

$form = new reset_site_identifier($PAGE->url->out(false), null, 'post', '', ['class' => 'reset_site_identifier']);
$form->process();
$html .= $form->render();

// Header
echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('reset_site_identifier_title', 'local_opentechnology'), 2);
// Info
echo $html;
// Footer
echo $OUTPUT->footer();
