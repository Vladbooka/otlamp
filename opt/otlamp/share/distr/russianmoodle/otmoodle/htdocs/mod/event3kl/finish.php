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

require_once("../../config.php");
require_once("lib.php");

use mod_event3kl\session;
use mod_event3kl\event3kl;

$sid = required_param('him', PARAM_INT);

$session = new session($sid);
$session->try_finish();

$event3kl = new event3kl($session->get('event3klid'));
$cm = $event3kl->obtain_cm();
$viewurl = new moodle_url('/mod/event3kl/view.php', ['id' => $cm->id]);

redirect($viewurl);

