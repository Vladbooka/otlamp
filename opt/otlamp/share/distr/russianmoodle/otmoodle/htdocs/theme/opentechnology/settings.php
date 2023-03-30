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
 * Тема СЭО 3KL. Настройки.
 *
 * @package    theme
 * @subpackage opentechnology
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

// Добавление раздела настроек темы
$themecategory = new admin_category(
    'theme_opentechnology', 
    get_string('pluginname', 'theme_opentechnology')
);
$ADMIN->add('themes', $themecategory);

// Добавление ссылки на панель управления профилями в раздел Внешний вид
$url = new moodle_url('/theme/opentechnology/profiles/index.php');
$context = context_system::instance();

if ( get_capability_info('theme/opentechnology:profile_view') )
{// Право будет создано только после обновления темы
    $capabilities = [
        'theme/opentechnology:profile_view',
    ];
    $profilesettings = new admin_externalpage(
        'theme_opentechnology_profiles_control_panel', 
        get_string('theme_opentechnology_profiles_control_panel', 'theme_opentechnology'), 
        $url, 
        $capabilities,
        false, 
        $context
    );
    $themecategory->add($themecategory->name, $profilesettings);
}

if ($hassiteconfig || has_capability('theme/opentechnology:settings_edit', $context))
{
    // Инициализация менеджера профилей
    $profilemanager = \theme_opentechnology\profilemanager::instance();
    
    // Добавление разделов настроек для каждого из профилей
    $profilemanager->admin_settingpage_add_settings($themecategory);
}
$settings = null;