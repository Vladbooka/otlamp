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
 * Блок мессенджера курса. Главные настройки.
 *
 * @package    block_coursemessage
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

if ($ADMIN->fulltree)
{
    $blockname = 'block_coursemessage';

    // Выбор метода определения получателей сообщения
    $configname = 'recipientselectionmode';
    $name = $blockname.'/'.$configname;
    $visiblename = get_string('config_' . $blockname. '_recipientselectionmode', 'block_coursemessage');
    $description = get_string('config_' . $blockname. '_recipientselectionmode_desc', 'block_coursemessage');
    $recipientselectionmodes = [
        'sendtoall' => get_string('config_sendtoall', 'block_coursemessage'),
        'allowuserselect' => get_string('config_allowuserselect', 'block_coursemessage'),
        'automaticcontact' => get_string('config_automaticcontact', 'block_coursemessage')
    ];
    $setting = new admin_setting_configselect($name, $visiblename, $description, 'sendtoall', $recipientselectionmodes);
    $settings->add($setting);
}