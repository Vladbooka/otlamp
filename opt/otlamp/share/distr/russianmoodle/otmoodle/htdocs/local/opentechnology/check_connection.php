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
 * Главная страница плагина
 *
 * @package    local_opentechnology
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . '/../../config.php');
require_once($CFG->libdir.'/adminlib.php');

$code = required_param('code', PARAM_TEXT);

require_login();
$PAGE->set_context(null);
$url = new \moodle_url('/local/opentechnology/check_connection.php', ['code' => $code]);
$PAGE->set_url($url);
require_capability('local/opentechnology:manage_db_connections', \context_system::instance());

$html = '';
$html .= $OUTPUT->heading(get_string('dbconnection_check_connection', 'local_opentechnology'));

$connection = new \local_opentechnology\dbconnection($code);
try {
    $db = $connection->get_connection();
    if ($connection->check_connection()) {
        $html .= html_writer::div(get_string('dbconnection_check_connection_successful', 'local_opentechnology'));
        $serverinfo = $connection->get_server_info();
        $html .= html_writer::div('description - ' . $serverinfo['description']);
        $html .= html_writer::div('version - ' . $serverinfo['version']);
        $db->Disconnect();
    } else {
        $html .= html_writer::div(get_string('dbconnection_check_connection_failed', 'local_opentechnology', $connection->get_error_message()));
    }
} catch (Exception $e) {
    $html .= html_writer::div(get_string('dbconnection_check_connection_failed', 'local_opentechnology'));
    $html .= html_writer::div('Error code - ' . $e->getCode());
    $html .= html_writer::div('Error message - ' . $e->getMessage());
}
$html .= html_writer::link('/local/opentechnology/dbconnection_management.php', get_string('dbconnection_back_to_dbconnections', 'local_opentechnology'), ['class' => 'btn btn-primary']);
// //////////////////////////////////////
// Вывод

echo $OUTPUT->header();
echo $html;
echo $OUTPUT->footer();

