<?php
////////////////////////////////////////////////////////////////////////////
//                                                                        //
// NOTICE OF COPYRIGHT                                                    //
//                                                                        //
// Dean`s Office for Moodle                                               //
// Электронный деканат                                                    //
// <http://deansoffice.ru/>                                               //
//                                                                        //
// This program is free software: you can redistribute it and/or modify   //
// it under the terms of the GNU General Public License as published by   //
// the Free Software Foundation, either version 3 of the Licensen.        //
//                                                                        //
// This program is distributed in the hope that it will be useful,        //
// but WITHOUT ANY WARRANTY; without even the implied warranty of         //
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the          //
// GNU General Public License for more details.                           //
//                                                                        //
// You should have received a copy of the GNU General Public License      //
// along with this program.  If not, see <http://www.gnu.org/licenses/>.  //
//                                                                        //
////////////////////////////////////////////////////////////////////////////

/**
 * Скрипт обновления плагина
 *
 * @package    local_opentechnology
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use local_opentechnology\plugin_manager as ot_plugin_manager;

defined('MOODLE_INTERNAL') || die();
require_once($CFG->dirroot . '/local/opentechnology/classes/otserial.php');

/** Обновление
 *
 * @param int $oldversion
 */
function xmldb_local_opentechnology_upgrade($oldversion) {
    global $DB, $OUTPUT, $CFG;

    $otapi = new \local_opentechnology\otserial(true);
    $result = $otapi->issue_serial_and_get_data();
    if (isset($result['response']) && !empty($result['message']))
    {
        echo $OUTPUT->notification($result['message'], \core\output\notification::NOTIFY_SUCCESS);

    } else if(!isset($result['response']))
    {
        echo $OUTPUT->notification($result['message']??'Unknown error', \core\output\notification::NOTIFY_ERROR);
    }

    if ( $oldversion < 2018121300 )
    {

        ot_plugin_manager::delete_plugin(
            'block_myskills',
            [
                '\\local_opentechnology\\plugin_manager',
                'delete_all_block_instances'
            ],
            ['myskills']
        );

        ot_plugin_manager::delete_plugin(
            'block_my_portfolio',
            [
                '\\local_opentechnology\\plugin_manager',
                'delete_all_block_instances'
            ],
            ['my_portfolio']
        );
    }

    if ( $oldversion < 2019021100 )
    {
        ot_plugin_manager::delete_plugin('local_mobile');
    }

    if ( $oldversion < 2019031300 )
    {
        ot_plugin_manager::delete_plugin(
            'block_myskills',
            [
                '\\local_opentechnology\\plugin_manager',
                'delete_all_block_instances'
            ],
            ['myskills']
        );

        ot_plugin_manager::delete_plugin(
            'block_my_portfolio',
            [
                '\\local_opentechnology\\plugin_manager',
                'delete_all_block_instances'
            ],
            ['my_portfolio']
        );
    }


    if ( $oldversion < 2019071100 )
    {
        ot_plugin_manager::delete_plugin('mod_rucontbook');
    }

    // Повторное удаление плагинов для инсталляций, где во время обновления возникли ошибки
    if ($oldversion < 2019091900) {
        ot_plugin_manager::delete_plugin(
            'block_myskills',
            [
                '\\local_opentechnology\\plugin_manager',
                'delete_all_block_instances'
            ],
            ['myskills']
        );

        ot_plugin_manager::delete_plugin(
            'block_my_portfolio',
            [
                '\\local_opentechnology\\plugin_manager',
                'delete_all_block_instances'
            ],
            ['my_portfolio']
        );

        ot_plugin_manager::delete_plugin('local_mobile');

        ot_plugin_manager::delete_plugin('mod_rucontbook');
    }

    if ($oldversion < 2020111800) {
        // capability must exist
        if (get_capability_info('local/opentechnology:view_about')) {
            // Добавим право просматривать техническую информацию об инсталляции управляющим
            $systemcontext = context_system::instance();
            if ($roles = get_archetype_roles('manager')) {
                foreach ($roles as $role) {
                    // Assign a site level capability.
                    if (!assign_capability('local/opentechnology:view_about', CAP_ALLOW, $role->id, $systemcontext->id, true)) {
                        debugging('assign capability failed');
                    }
                }
            }
        }
    }

    if ($oldversion < 2020121800) {
        $dbman = $DB->get_manager();
        $table = new xmldb_table('zoom');
        if ($dbman->table_exists($table)) {
            $sql = 'SELECT cm.id, cm.course, m.name, md.name as modname
                  FROM {course_modules} cm
             LEFT JOIN {modules} md
                    ON md.id = cm.module
             LEFT JOIN {zoom} m
                    ON cm.instance = m.id
                 WHERE md.name = \'zoom\' AND m.id IS NOT NULL';
            if (!$DB->record_exists_sql($sql)) {
                // Удаляем плагин mod_zoom, если нет добавленных модулей курса
                ot_plugin_manager::delete_plugin('mod_zoom');
            }
        }
    }

    if ($oldversion < 2021030900) {
        // удаление устаревшего блока объединение отчетов
        // на смену ему давно пришел плагин типа отчет
        // теперь пришло время удалить и сам блок, содержащий только ссылку на отчет
        ot_plugin_manager::delete_plugin(
            'block_reports_union',
            [
                '\\local_opentechnology\\plugin_manager',
                'delete_all_block_instances'
            ],
            ['reports_union']
        );
    }

    return true;
}

