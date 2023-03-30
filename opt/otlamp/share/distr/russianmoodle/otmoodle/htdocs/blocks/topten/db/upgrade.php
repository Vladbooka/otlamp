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
 * Блок топ-10
 * 
 * @package    block
 * @subpackage topten
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Upgrade code for topten block.
 *
 * @param int $oldversion
 */
function xmldb_block_topten_upgrade($oldversion) {
    global $DB;
    
    if ( $oldversion < 2020051900 ) {
        $configdata = $DB->get_records('block_instances', ['blockname' => 'topten'], '', 'id, configdata');
        foreach ($configdata as $instanceid => $data) {
            $haschanges = false;
            $config = unserialize(base64_decode($data->configdata));
            if ($config->rating_type == 'user_selection') {
                // переназавем шаблон "преподаватели" в универсальный
                if ($config->selecttemplate == 'object_user_teacher') {
                    $config->selecttemplate = 'object_user_universal';
                    $haschanges = true;
                }
                // поменяем поля должность и кафедра в настраиваемые поля
                if (!empty($config->department)) {
                    $config->field[0] = $config->department;
                    $config->text_field[0] = '';
                    $haschanges = true;
                    if ((stripos($config->field[0], 'user_field_') === false)
                        && (stripos($config->field[0], 'user_profilefield_') === false)) {
                            $config->field[0] = 'user_field_' . $config->field[0];
                        }
                }
                if (!empty($config->position)) {
                    $config->field[1] = $config->position;
                    $config->text_field[1] = '';
                    $haschanges = true;
                    if ((stripos($config->field[1], 'user_field_') === false)
                        && (stripos($config->field[1], 'user_profilefield_') === false)) {
                            $config->field[1] = 'user_field_' . $config->field[1];
                        }
                }
                // добавим префикс ко всем стандартным полям пользователя
                if (isset($config->conditionfield)) {
                    foreach ($config->conditionfield as $key => $field) {
                        if ((stripos($field, 'user_field_') === false) && (stripos($field, 'user_profilefield_') === false)) {
                            $config->conditionfield[$key] = 'user_field_' . $field;
                            $haschanges = true;
                        }
                    }
                }
                if ($haschanges) {
                    $dataset = new \stdClass();
                    $dataset->id =  $instanceid;
                    $dataset->configdata = base64_encode(serialize($config));
                    $DB->update_record('block_instances', $dataset);
                }
            }
        }
    }

    return true;
}
