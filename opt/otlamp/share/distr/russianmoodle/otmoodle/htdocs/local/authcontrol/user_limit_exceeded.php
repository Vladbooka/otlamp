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
 * Панель управления доступом в СДО
 *
 * Страница с сообщением о превышении максимального числа активных пользователей 
 *
 * @package    local_authcontrol
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once ('../../config.php');
require_once ('lib.php');

// Текущий URL
$title = get_string('title_onlineusersoverlimit', 'local_authcontrol');
$message = get_string('message_onlineusersoverlimit', 'local_authcontrol');

// Установка свойств страницы
$PAGE->set_pagelayout('standard');
$PAGE->set_cacheable(false);
$PAGE->set_context(context_system::instance());
$PAGE->set_title($title);
$PAGE->set_heading($title);
$PAGE->set_url('/local/authcontrol/user_limit_exceeded.php');

if( ! isloggedin() )
{
    $html = html_writer::div($message);
} else 
{
    $html = '';
}
$html .= html_writer::div(html_writer::link('/', get_string('back_home', 'local_authcontrol'), ['class' => 'btn']));

// Печать страницы
echo $OUTPUT->header();
print($html);
echo $OUTPUT->footer();