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
 * mod_otmutualassessment settings and presets.
 *
 * @package    mod
 * @subpackage otmutualassessment
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/mod/otmutualassessment/locallib.php');

$category = new admin_category('mod_otmutualassessment', get_string('pluginname', 'mod_otmutualassessment'));
// Добавим категорию настроек
$ADMIN->add('modsettings', $category);

// Объявляем страницу настроек плагина
$settings = new admin_settingpage(
    'modsettingotmutualassessment',
    get_string('settings_page_general', 'mod_otmutualassessment'),
    'mod/otmutualassessment:managesettings'
);

if ($ADMIN->fulltree) {
    // Настройка сохранени истории оценивания
    $yesno = [
        0 => get_string('no'),
        1 => get_string('yes')
    ];
    $settings->add(
        new admin_setting_configselect(
            'mod_otmutualassessment/savegraderhistory',
            get_string('settings_savegraderhistory', 'mod_otmutualassessment'),
            get_string('settings_savegraderhistory_desc', 'mod_otmutualassessment'),
            1,
            $yesno
        )
    );
    // Настройка режима обновления оценок при изменении условий
    $efficiencylist = [
        'live' => get_string('live_efficiency', 'mod_otmutualassessment'),
        'cron' => get_string('cron_efficiency', 'mod_otmutualassessment')
    ];
    $settings->add(
        new admin_setting_configselect(
            'mod_otmutualassessment/efficiencyofrefresh',
            get_string('settings_efficiencyofrefresh', 'mod_otmutualassessment'),
            get_string('settings_efficiencyofrefresh_desc', 'mod_otmutualassessment'),
            'cron',
            $efficiencylist
        )
    );
}
// Добавим страницу общих настроек в меню администратора
$ADMIN->add('mod_otmutualassessment', $settings);

$category = new admin_category('mod_otmutualassessment_strategies', get_string('settings_category_strategies', 'mod_otmutualassessment'));
// Добавим категорию настроек
$ADMIN->add('mod_otmutualassessment', $category);

$strategylist = mod_otmutualassessment_get_strategy_list();
foreach ($strategylist as $code => $classname) {
    if (class_exists($classname)) {
        $strategy = new $classname(null, null, null);
        if ($strategy->has_config()) {
            // Объявляем страницу настроек плагина
            $settings = new admin_settingpage('mod_otmutualassessment_strategy_' . $code, get_string('strategy_' . $code, 'mod_otmutualassessment'));
            if ($ADMIN->fulltree) {
                $strategy->add_common_settings($settings);
                $strategy->add_custom_settings($settings);
            }
            // Добавим страницу основных нестроек в меню администратора
            $ADMIN->add('mod_otmutualassessment_strategies', $settings);
        }
    }
}

// У плагина нет стандартной страницы настроек, вернем NULL
$settings = NULL;
