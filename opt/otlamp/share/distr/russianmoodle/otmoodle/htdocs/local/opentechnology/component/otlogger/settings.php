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
 * Настройки сабплагина логирования OTlogger 
 *
 * @package    local_opentechnology
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

if ($hassiteconfig){

    // Добавление страницы с настройками сабплагина OTlogger
    $pagename = get_string('pluginname', 'otcomponent_otlogger');
    $pageurl = "$CFG->wwwroot/local/opentechnology/component/otlogger/logconfigurations.php";
    $pagecap = 'otcomponent/otlogger:change_log_parameters';
    $ADMIN->add('otlogger', new admin_externalpage('componentotlogger', $pagename, $pageurl, $pagecap, false));
    $settings = null;

}
