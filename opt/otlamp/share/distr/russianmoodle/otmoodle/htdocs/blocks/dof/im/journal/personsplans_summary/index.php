<?php
////////////////////////////////////////////////////////////////////////////
//                                                                        //
// NOTICE OF COPYRIGHT                                                    //
//                                                                        //
// Dean`s Office for Moodle                                               //
// Электронный деканат                                                    //
// <http://sourceforge.net/projects/freedeansoffice/>                     //
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
 * Рилтайм отчет. Страница отображения
 *
 * @package    im
 * @subpackage rtreport
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// Подключение библиотек
require_once('form.php');

GLOBAL $DOF;

// Параметры
$addvars['pt'] = $pt = 'im';
$addvars['pc'] = $pc = 'journal';
$addvars['type'] = $rtreport_type = 'personsplans_summary';

// HTML вывод
$html = '';

if ( $rtreport = $DOF->modlib('rtreport')->get_rtreport($pt, $pc, $rtreport_type) )
{
    if ( $table = $rtreport->run() )
    {
        $rtreport_html = dof_html_writer::div(dof_html_writer::table($table), 'personsplans_summary_table');
        
        // Заголовок
        $html .= $rtreport->get_header();
        
        // Установка навигации
        $rtreport->set_nvg();
        
        $html .= $rtreport_html;
    } else
    {
        // Нехватка данных
        $html .= $DOF->get_string('not_enough_data', 'rtreport');
    }
} else
{
    // Переданы неверные данные
    $html .= $DOF->get_string('invalid_data', 'rtreport');
}

// Печать шапки страницы
$DOF->modlib('nvg')->print_header(NVG_MODE_PORTAL);

// Вывод сообщений
$DOF->messages->display();

// Вывод
echo $html;

// Печать подвала
$DOF->modlib('nvg')->print_footer(NVG_MODE_PORTAL);
