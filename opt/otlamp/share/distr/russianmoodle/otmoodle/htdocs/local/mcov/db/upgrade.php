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
 * @package    local_mcov
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/** Обновление
 *
 * @param int $oldversion
 */
function xmldb_local_mcov_upgrade($oldversion) {
    global $DB;

    if ( $oldversion < 2021020100 )
    {
        // на момент апгрейда, у нас были использования local_mcov для хранения с привязкой всего к двум сущностям:
        // cohort с данными, которые были добавлены для полей, заданных пользовательским конфигом
        // захардкоженных полей для cohort не было
        // user с данными, которые были добавлены для полей, захардкоженных плагином local_otcontrolpanel
        // пользовательских полей для user не могло быть задано - не было такого интерфейса
        // публичные теперь должны храниться с префиксом - произведем для них замену (соответственно, только cohort)
        $records = $DB->get_records('local_mcov', ['entity' => 'cohort']);
        foreach($records as $record)
        {
            $record->prop = 'pub_'.$record->prop;
            $DB->update_record('local_mcov', $record);
        }
    }

    return true;
}

