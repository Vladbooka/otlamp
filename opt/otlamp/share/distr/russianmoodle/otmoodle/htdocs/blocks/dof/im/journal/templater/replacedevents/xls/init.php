<?php
////////////////////////////////////////////////////////////////////////////
//                                                                        //
// NOTICE OF COPYRIGHT                                                    //
//                                                                        //
// Dean`s Office for Moodle                                               //
// Электронный деканат                                                    //
// <http://deansoffice.ru/>                                               //
//                                                                        //
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
 * Класс шаблонизатора xls для отчета по студентам
*
* @package    im
* @subpackage journal
* @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
*/

global $DOF;
require_once($DOF->plugin_path('modlib','templater','/formats/xls/init.php'));

class dof_im_journal_replacedevents_format_xls extends dof_modlib_templater_format_xls
{
    /**
     * Указание поля отчета, в котором содержатся данные для экспорта
     * 
     * @return mixed string - Имя поля или bool false
     */
    protected function get_field_name()
    {
        return 'exportcsv';
    }
}
?>