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
 * Глобальные настройки плагина
 *
 * @package    local_opentechnology
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;


// добавление страницы тех.поддержки
$pagename = get_string('pluginname', 'local_opentechnology');
$pageurl = new moodle_url('/local/opentechnology/index.php');
$pagecap = 'local/opentechnology:view_about';
$ADMIN->add('root', new admin_externalpage('local_opentechnology', $pagename, $pageurl, $pagecap, false));

// добавление страницы управления подключениями к внешним БД
$pagename = get_string('dbconnection_management', 'local_opentechnology');
$pageurl = new moodle_url('/local/opentechnology/dbconnection_management.php');
$pagecap = 'local/opentechnology:manage_db_connections';
$ADMIN->add('root', new admin_externalpage('local_opentechnology_dbconnection_management', $pagename, $pageurl, $pagecap, false));

// Del Moodle Services information page. (added in /admin/settings/top.php)
$ADMIN->prune('moodleservices');

if ($hassiteconfig)
{ // needs this condition or there is error on login page

    // Добавление категории под плагин в локальных плагинах
    $catname = get_string('pluginname', 'local_opentechnology');
    $ADMIN->add('localplugins', new admin_category('localopentechnology', $catname));

    // Добавление страницы сброса идентификатора сайта
    $pagename = get_string('reset_site_identifier', 'local_opentechnology');
    $pageurl = "$CFG->wwwroot/local/opentechnology/resetsiteidentifier.php";
    $pagecap = 'local/opentechnology:reset_site_identifier';
    $ADMIN->add('localopentechnology', new admin_externalpage('resetsiteidentifier', $pagename, $pageurl, $pagecap));
    
    // Добавление настроек компонентов
    foreach (core_plugin_manager::instance()->get_plugins_of_type('otcomponent') as $plugin){
        $plugin->load_settings($ADMIN, 'localopentechnology', $hassiteconfig);
    }
}