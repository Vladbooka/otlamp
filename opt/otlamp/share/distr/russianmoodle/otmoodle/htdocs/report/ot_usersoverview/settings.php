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
 * Сводка по пользователям. Настройки модуля
 *
 * @package    report_ot_usersoverview
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

$ADMIN->add('reports', new admin_externalpage('report_ot_usersoverview', get_string('pluginname', 'report_ot_usersoverview'), "$CFG->wwwroot/report/ot_usersoverview/index.php",'report/ot_usersoverview:view'));

// настройки плагина
$settings->add(
        new admin_setting_configcheckbox('report_ot_usersoverview/enablecron',
                get_string('enablecron', 'report_ot_usersoverview'),
                get_string('enablecron_desc', 'report_ot_usersoverview'),
                0)
        );