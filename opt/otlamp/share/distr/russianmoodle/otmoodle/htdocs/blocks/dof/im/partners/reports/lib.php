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
 * Базовые функции отчетов плагина
 *
 * @package    im
 * @subpackage partners
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
 
// Загрузка библиотек верхнего уровня
require_once(dirname(realpath(__FILE__))."/../lib.php");

// Тип отчета
$type = optional_param('type', '', PARAM_TEXT);

// Общая страница
$DOF->modlib('nvg')->add_level(
        $DOF->get_string('report_panel_title', 'partners'),
        $DOF->url_im('partners', '/reports/report_panel.php', $addvars)
);

// Добавление уровня навигации
switch ( $type )
{
    case 'admins' : 
    case 'teachers' :
    case 'students' :
        // Страница отчета
        $addvars['type'] = $type;
        $DOF->modlib('nvg')->add_level(
                $DOF->get_string('report_panel_'.$type.'_title', 'partners'),
                $DOF->url_im('partners', '/reports/report_panel.php', $addvars)
        );
        break;
    default:
}


?>