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
require_once('lib.php');

GLOBAL $DOF;

// Параметры
$addvars['pt'] = $pt = optional_param('pt', '', PARAM_RAW_TRIMMED);
$addvars['pc'] = $pc = optional_param('pc', '', PARAM_RAW_TRIMMED);
$addvars['type'] = $rtreport_type = optional_param('type', 0, PARAM_RAW_TRIMMED);
$export = optional_param('export', 0, PARAM_RAW_TRIMMED);

// HTML вывод
$html = '';

if ( $rtreport = $DOF->modlib('rtreport')->get_rtreport($pt, $pc, $rtreport_type, $export) )
{
    if ( $table = $rtreport->run() )
    {
        $rtreport_html = dof_html_writer::table($table);

        // Заголовок
        $html .= $rtreport->get_header();

        // Установка навигации
        $rtreport->set_nvg();

        // Формирование ссылок на скачивание
        $types = dof_modlib_rtreport_helper_exporter::get_available_formats();

        // Класс для вывода в языковой строке
        $string = new stdClass();

        $links = '';
        // Формирование кнопки экспорта
        foreach ( $types as $type )
        {
            $string->type = $type;

            // Формирование кнопки экспорта
            $links .= dof_html_writer::link(
                    $DOF->url_im(
                            'rtreport',
                            '/index.php',
                            array_merge($rtreport->get_variables(), ['export' => $type], $addvars)),
                    $DOF->get_string('rtreport_general_download', 'rtreport', $string),
                    ['class' => 'btn button dof_button btn-primary']).PHP_EOL;
        }

        $html .= $rtreport_html;
        $html .= dof_html_writer::div($links, 'mt-2');
        $html .= $rtreport->get_processors();
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
