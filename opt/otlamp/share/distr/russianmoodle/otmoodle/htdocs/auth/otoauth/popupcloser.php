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
 * Прокси страница для перенаправления пользователя после авторизации через popup окно
 *
 * @package    auth
 * @subpackage otoauth
 * @author     Dmitry Ivanov <dimka_ivanov@list.ru>
 * @copyright  2019
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require('../../config.php');
require_once ($CFG->libdir . '/classes/notification.php');

global $PAGE;
$PAGE->set_url('/auth/otoauth/popupcloser.php');
$PAGE->set_context(context_system::instance());
$PAGE->set_title(get_string('popupcloser_title', 'auth_otoauth'));
$PAGE->set_heading(get_string('popupcloser_title', 'auth_otoauth'));
$PAGE->set_pagelayout('popup');
$wantsurl = optional_param('wantsurl', '/', PARAM_TEXT);
$PAGE->requires->js_call_amd('auth_otoauth/displaypopupclose', 'init', [$wantsurl]);
$wantsurl = new moodle_url($wantsurl);
echo $OUTPUT->header();
\core\notification::info(get_string('popupcloser_notification', 'auth_otoauth', $wantsurl->out(false)));
echo $OUTPUT->footer();
