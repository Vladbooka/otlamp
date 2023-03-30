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
 * @package    mod_otresourcelibrary
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();
require_once($CFG->dirroot . '/mod/otresourcelibrary/classes/otserial.php');

/**
 * Хук обновления плагина
 * @param string $oldversion
 * @return boolean
 */
function xmldb_otresourcelibrary_upgrade($oldversion) {
    global $DB, $OUTPUT, $CFG;

    $otapi = new \mod_otresourcelibrary\otserial(true);
    $result = $otapi->issue_serial_and_get_data();
    if (isset($result['response']) && !empty($result['message']))
    {
        echo $OUTPUT->notification($result['message'], \core\output\notification::NOTIFY_SUCCESS);
        
    } else if(!isset($result['response']))
    {
        echo $OUTPUT->notification($result['message']??'Unknown error', \core\output\notification::NOTIFY_ERROR);
    }
    
    return true;
}

