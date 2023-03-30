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
 * Free Dean's Office settings and presets.
 *
 * @package    block
 * @subpackage dof
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/*
 * Т.к. это страница для admin/settings.php, весь вывод делается
 * через $settings и объекты admin_setting_*.
 */
defined('MOODLE_INTERNAL') || die();
require_once($CFG->dirroot . '/blocks/dof/classes/otserial.php');


if (!$ADMIN->fulltree)
{
    return;
}
// Создание объекта OTAPI
$otapi = new \block_dof\otserial();
// Добавление страницы управления тарифом
$response = $otapi->settings_page_fill($settings, [
    'plugin_string_identifiers' => [
        'otserial_settingspage_otserial' => 'otserial',
        'otserial_settingspage_issue_otserial' => 'get_otserial',
        'otserial_settingspage_otservice' => 'otservice',
        'otserial_exception_already_has_serial' => 'already_has_otserial',
        'otserial_error_get_otserial_fail' => 'get_otserial_fail',
        'otserial_error_otserial_check_fail' => 'otserial_check_fail',
        'otserial_error_tariff_wrong' => 'otserial_tariff_wrong',
        'otserial_error_otservice_expired' => 'otservice_expired',
        'otserial_notification_otserial_check_ok' => 'otserial_check_ok',
        'otserial_notification_otservice_active' => 'otservice_active',
        'otserial_notification_otservice_unlimited' => 'otservice_unlimited',
    ],
]);

if (!is_null($response))
{
    $choices = [
        'choose' => get_string('config_choose_category_content_role', 'block_dof')
    ];
    $coursecatroles = get_roles_for_contextlevels(CONTEXT_COURSECAT);
    $roles = get_all_roles();
    if ( $roles && $coursecatroles )
    {
        foreach($roles as $k => $role)
        {
            if( ! in_array($role->id, $coursecatroles))
            {
                unset($roles[$k]);
            }
        }
        $choices += role_fix_names($roles, null, ROLENAME_ORIGINAL, true);
    }
    
    $settings->add(new admin_setting_configselect(
        'block_dof/view_category_content_role',
        get_string('config_view_category_content_role', 'block_dof'),
        get_string('config_view_category_content_role_desc', 'block_dof'),
        'choose',
        $choices
    ));
    $settings->add(new admin_setting_configselect(
        'block_dof/edit_category_content_role',
        get_string('config_edit_category_content_role', 'block_dof'),
        get_string('config_edit_category_content_role_desc', 'block_dof'),
        'choose',
        $choices
    ));
    $settings->add(new admin_setting_configselect(
        'block_dof/manage_category_content_role',
        get_string('config_manage_category_content_role', 'block_dof'),
        get_string('config_manage_category_content_role_desc', 'block_dof'),
        'choose',
        $choices
    ));
    
    $choices = [
        1 => '1',
        2 => '2'
    ];
    $settings->add(new admin_setting_configselect(
        'block_dof/mdlcategoryid_number',
        get_string('config_mdlcategoryid_number', 'block_dof'),
        get_string('config_mdlcategoryid_number_desc', 'block_dof'),
        1,
        $choices
    ));
}