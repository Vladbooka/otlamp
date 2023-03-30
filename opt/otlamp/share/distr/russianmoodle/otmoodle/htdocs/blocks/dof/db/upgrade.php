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

defined('MOODLE_INTERNAL') || die();
require_once($CFG->dirroot . '/blocks/dof/classes/otserial.php');
require_once($CFG->dirroot . '/blocks/dof/locallib.php');

/** Обновление таблиц блока dof
 *
 * @param int $oldversion
 * @todo сделать drop_enum_from_field для всех старых полей (проблема: метод drop_enum_from_field не существует в moodle 2.3+)
 */
function xmldb_block_dof_upgrade($oldversion)
{
    global $CFG, $DB, $DOF;

    ////////////////////////////////////////
    // OTSerial-part
    global $OUTPUT;
    $otapi  = new block_dof\otserial(true);
    $result = $otapi->issue_serial_and_get_data();
    if (isset($result['response']) && !empty($result['message']))
    {
        echo $OUTPUT->notification($result['message'], \core\output\notification::NOTIFY_SUCCESS);
        
    } else if(!isset($result['response']))
    {
        echo $OUTPUT->notification($result['message']??'Unknown error', \core\output\notification::NOTIFY_ERROR);
    }


    ////////////////////////////////////////
    // Native DOF-part
    $dbman = $DB->get_manager();

    if ( $oldversion < 2012101000 )
    {

        // Define field personid to be added to block_dof_todo
        $table = new xmldb_table('block_dof_todo');
        $field = new xmldb_field('personid', XMLDB_TYPE_INTEGER, '11', null, XMLDB_NOTNULL, null, '0', 'exdate');

        // Conditionally launch add field personid
        if ( !$dbman->field_exists($table, $field) )
        {
            $dbman->add_field($table, $field);
        }

        $index = new xmldb_index('personid', XMLDB_INDEX_NOTUNIQUE, array('personid'));

        // Conditionally launch add index personid
        if ( !$dbman->index_exists($table, $index) )
        {
            $dbman->add_index($table, $index);
        }

        // dof savepoint reached
        upgrade_block_savepoint(true, 2012101000, 'dof');
    }

    if ( $oldversion < 2015020200 )
    {// Исправим некорректную версию плагина
        $params = array('type' => 'workflow', 'code' => 'contracts', 'version' => 20011082200);
        if ( $installedlplugin = $DB->get_record('block_dof_plugins', $params) )
        {
            $installedlplugin->version = 2015020200;
            $DB->update_record('block_dof_plugins', $installedlplugin);
        }
    }
    
    if ( $oldversion < 2018101700 )
    {
        // удаление компетенций из ЭД
        // im/skills
        // storage/skills
        // storage/skilllinks
        // workflow/skills
        // sync/skills
        $pluginstodelete = [
            ['type' => 'sync', 'code' => 'skills'],
            ['type' => 'im', 'code' => 'skills'],
            ['type' => 'storage', 'code' => 'skilllinks'],
            ['type' => 'workflow', 'code' => 'skills'],
            ['type' => 'storage', 'code' => 'skills'],
        ];
        foreach ($pluginstodelete as $plugininfo)
        {
            if ( $DOF->plugin_exists($plugininfo['type'], $plugininfo['code']) )
            {
                $DOF->plugin_uninstall($plugininfo['type'], $plugininfo['code']);
            }
        }
    }
    
    if ($oldversion < 2020042201)
    {
        // плагины storage_skills, storage_skilllinks удалены, но таблицы почему-то остались
        // пробуем удалить вручную
        $junktablenames = [
            'block_dof_s_skilllinks',
            'block_dof_s_skills'
        ];
        foreach ($junktablenames as $junktablename)
        {
            $junktable = new xmldb_table($junktablename);
            
            if ($dbman->table_exists($junktable)) {
                $dbman->drop_table($junktable);
            }
        }
    }
    

    ////////////////////////////////////////
    // DB is up to date. Start plugin installation
    if( defined('CLI_SCRIPT') && CLI_SCRIPT )
    {
        $eol = "\n";
    } else
    {
        $eol = "<br />";
    }
    $DOF->mtrace(1, get_string('plugin_installation', 'block_dof'), $eol);
    $result = $DOF->plugin_setup();
    if($result)
    {
        $DOF->mtrace(1, get_string('plugin_installation_success', 'block_dof'), $eol);
    } else
    {
        $DOF->mtrace(1, get_string('plugin_installation_error', 'block_dof'), $eol);
    }
    
    return true;
}

?>