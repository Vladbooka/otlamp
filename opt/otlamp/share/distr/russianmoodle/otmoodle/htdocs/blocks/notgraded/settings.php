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

defined('MOODLE_INTERNAL') || die;

if ($ADMIN->fulltree) {
    $options = array('all'=>get_string('all_notgraded', 'block_notgraded'), 
                     'none'=>get_string('none_notgraded', 'block_notgraded'), 
                     'none_group'=>get_string('none_notgraded_group', 'block_notgraded'));

    $settings->add(new admin_setting_configselect('block_notgraded_list', get_string('confignotgradedlist', 'block_notgraded'),
        get_string('confignotgradedlist', 'block_notgraded'), 'all', $options));

    // Время жини кэша
    $name = 'block_notgraded/cache_lifetime';
    $visiblename = get_string('setting_cache_lifetime', 'block_notgraded');
    $description = get_string('setting_cache_lifetime_desc', 'block_notgraded');
    $defaultsetting = 3600;
    $defaultunit = 3600;
    $setting = new admin_setting_configduration($name, $visiblename, $description, $defaultsetting, $defaultunit);
    $settings->add($setting);
    
    // Режим обновления кэша
    $choices = [
        0 => get_string('setting_cache_update_mode_events_off', 'block_notgraded'),
        1 => get_string('setting_cache_update_mode_events_on', 'block_notgraded')
    ];
    $name = 'block_notgraded/cache_update_mode';
    $visiblename = get_string('setting_cache_update_mode', 'block_notgraded');
    $description = get_string('setting_cache_update_mode_desc', 'block_notgraded');
    $defaultsetting = 0;
    $setting = new admin_setting_configselect($name, $visiblename, $description, $defaultsetting, $choices);
    $settings->add($setting);
}
