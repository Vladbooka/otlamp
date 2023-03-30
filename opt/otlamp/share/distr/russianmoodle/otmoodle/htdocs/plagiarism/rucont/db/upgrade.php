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
 * Плагин определения заимствований Руконтекст. Обновление.
 *
 * @package    plagiarism
 * @subpackage rucont
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();
// Подключение библиотек
require_once($CFG->dirroot . '/plagiarism/rucont/lib.php');

/**
 * Действия при обновлении плагина
 * 
 * @return boolean - Результат установки
 */
function xmldb_plagiarism_rucont_upgrade($oldversion) 
{
    if ($oldversion < 2020122200) {
        // Удаляем старые не нужные настройки
        unset_config('rucont_use', 'plagiarism');
        $supported_mods = ['assign'];
        foreach ($supported_mods as $mod) {
            unset_config('rucont_use_mod_' . $mod, 'plagiarism');
        }
        // Замена deprecated настройки apru_use на enabled
        $old = (int) get_config('plagiarism_rucont', 'rucont_use');
        set_config('enabled', $old, 'plagiarism_rucont');
        unset_config('rucont_use', 'plagiarism_rucont');
    }
    
    return true;
}