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

/**
 * Блок комментарий преподавателя
 *
 * @package     block_quiz_teacher_feedback
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

function xmldb_block_quiz_teacher_feedback_upgrade($oldversion)
{
    global $DB;

    $dbman = $DB->get_manager();
    if ( $oldversion < 2018121300 )
    {

        $table = new xmldb_table('block_quiz_teacher_feedback');
        $field = new xmldb_field('needsgrading', XMLDB_TYPE_INTEGER, 1, XMLDB_UNSIGNED, XMLDB_NOTNULL, null, 0);

        if ( !$dbman->field_exists($table, $field) )
        {
            $dbman->add_field($table, $field);
        }
    }
    
    return true;
}
