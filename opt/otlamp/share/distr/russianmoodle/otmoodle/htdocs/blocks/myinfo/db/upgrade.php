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
 * Блок информации о пользователе. Скрипт обновления.
 *
 * @package    block
 * @subpackage myinfo
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

function xmldb_block_myinfo_upgrade($oldversion)
{
    global $DB, $CFG;
    if ($oldversion < 2017112006)
    {
        $exists = $DB->record_exists('config_plugins', [
            'plugin' => 'block_myinfo',
            'name' => 'displayfields'
        ]);
        if( ! $exists )
        {
            $fields = [];
            foreach(['email','department','degre','faculty','group'] as $oldfield)
            {
                $displayrecord = $DB->get_record('config_plugins', [
                    'plugin' => 'block_myinfo',
                    'name' => 'display_'.$oldfield
                ]);
                if( ! empty($displayrecord->value) )
                {
                    if($oldfield == 'email')
                    {
                        $fields[] = 'email';
                        continue;
                    }
                    $fieldrecord = $DB->get_record('config_plugins', [
                        'plugin' => 'block_myinfo',
                        'name' => $oldfield
                    ]);
                    if( ! empty($fieldrecord->value) )
                    {
                        $fields[] = 'profile_field_'.$fieldrecord->value;
                    }
                }
            }
            
            $configplugin = new stdClass();
            $configplugin->plugin = 'block_myinfo';
            $configplugin->name = 'displayfields';
            $configplugin->value = implode(',', $fields);
            $DB->insert_record('config_plugins', $configplugin);
        }
        
        upgrade_block_savepoint(true, 2017112006, 'myinfo');
    }
    return true;
}

?>