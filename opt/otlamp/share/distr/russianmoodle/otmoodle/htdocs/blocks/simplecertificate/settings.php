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
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle. If not, see <http://www.gnu.org/licenses/>.

/**
 * Сертификаты. Настройки.
 *
 * @package    block
 * @subpackage simplecertificate
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
 
defined('MOODLE_INTERNAL') || die();

$name = 'block_simplecertificate/title_general';
$title = get_string('settings_title_general','block_simplecertificate');
$description = get_string('settings_title_general_desc','block_simplecertificate');
$setting = new admin_setting_heading($name, $title, $description);
$settings->add($setting);

$name = 'block_simplecertificate/certificateimage';
$title = get_string('settings_certificateimage', 'block_simplecertificate');
$description = get_string('settings_certificateimage_desc', 'block_simplecertificate');
$setting = new admin_setting_configstoredfile($name, $title, $description, 'certificateimage');
$settings->add($setting);
