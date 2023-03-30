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
 * @package    atto_otmagnifier
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$ADMIN->add('editoratto', new admin_category('atto_otmagnifier', new lang_string('pluginname', 'atto_otmagnifier')));

$settings = new admin_settingpage('atto_otmagnifier_settings', new lang_string('settings', 'atto_otmagnifier'));
if ($ADMIN->fulltree) {
    
    $options = [
        'disabled' => new lang_string('clickhandler_disabled', 'atto_otmagnifier'),
        'open' => new lang_string('clickhandler_open', 'atto_otmagnifier'),
        'openseparatewindow' => new lang_string('clickhandler_openseparatewindow', 'atto_otmagnifier'),
        'openseparatewindowfullscreen' => new lang_string('clickhandler_openseparatewindowfullscreen', 'atto_otmagnifier'),
        
    ];
    $settings->add(new admin_setting_configselect(
        'atto_otmagnifier/clickhandler',
        new lang_string('clickhandler', 'atto_otmagnifier'),
        new lang_string('clickhandler_desc', 'atto_otmagnifier'),
        'disabled',
        $options
    ));
}
