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
 * Блок согласования мастеркурса, настройки
 *
 * @package    block_mastercourse
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

if ($ADMIN->fulltree)
{
    // Отображение метки в хлебных крошках
    $settings->add(
        new admin_setting_configcheckbox(
            'block_mastercourse/display_navbar_caption',
            get_string('config_display_navbar_caption', 'block_mastercourse'),
            get_string('config_display_navbar_caption_desc', 'block_mastercourse'),
            1
        )
    );
    // Отображение метки в панели согласования
    $settings->add(
        new admin_setting_configcheckbox(
            'block_mastercourse/display_verification_panel_caption',
            get_string('config_display_verification_panel_caption', 'block_mastercourse'),
            get_string('config_display_verification_panel_caption_desc', 'block_mastercourse'),
            1
        )
    );
    // Публикация курсов во внешней системе
    $settings->add(
        new admin_setting_configcheckbox(
            'block_mastercourse/display_publication_panel',
            get_string('config_display_publication_panel_caption', 'block_mastercourse'),
            get_string('config_display_publication_panel_caption_desc', 'block_mastercourse'),
            0
        )
    );
}
