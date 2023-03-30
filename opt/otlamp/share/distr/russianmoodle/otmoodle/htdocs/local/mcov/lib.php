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
 * Настраиваемые поля. Стандартные функции плагина
 *
 * @package    local_mcov
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


/**
 * Метод, добавляющий ссылку на страницу дополнительных настроек курса
 *
 * @param settings_navigation $settingsnav
 * @param context $context
 */
function local_mcov_extend_settings_navigation(settings_navigation $settingsnav, context $context) {

    foreach(\local_mcov\helper::get_entities() as $entity)
    {
        $entity->extend_settings_navigation($settingsnav, $context);
    }
}

/**
 * Add nodes to myprofile page.
 *
 * @param \core_user\output\myprofile\tree $tree Tree object
 * @param stdClass $user user object
 * @param bool $iscurrentuser
 * @param stdClass $course Course object
 *
 * @return bool
 */
function local_mcov_myprofile_navigation(\core_user\output\myprofile\tree $tree, $user, $iscurrentuser, $course) {

    foreach(\local_mcov\helper::get_entities() as $entity)
    {
        $entity->myprofile_navigation($tree, $user, $iscurrentuser, $course);
    }
}

/**
 * Регистрация в local_mcov служебных полей
 * @return \local_mcov\hcfield[]
 */
function local_mcov_get_hardcoded_mcov_fields() {
    // регистрируем под себя поле привязанное к локальной группе в курсе, чтобы хранить дату старта группы
    // у группы нет ни такого поля, ни кастомных полей
    return [
        new \local_mcov\hcfield_group_datestart()
    ];
}