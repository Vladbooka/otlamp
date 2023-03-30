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
 * Список отчетов
 *
 * @package    im
 * @subpackage achievements
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// Подключаем библиотеки
require_once('lib.php');

GLOBAL $PAGE;
$rtroptions = optional_param('rtroptions', null, PARAM_RAW);
$export = optional_param('export', null, PARAM_ALPHA);

// Добавление уровня навигации
$DOF->modlib('nvg')->add_level(
    $DOF->get_string('reports_title', 'achievements'),
    $DOF->url_im('achievements', '/reports.php'),
    $addvars
);

// Добавим таблицу стилей
$DOF->modlib('nvg')->add_css('im', 'achievements', '/reports.css');

// Установим ссылку странице
$PAGE->set_url($DOF->url_im('achievements', '/reports.php'));

// Код для вывода
$html = '';

if( $DOF->im('achievements')->is_access('view_reports') )
{
    if( is_null($rtroptions) )
    {// Отчет не выбран
        // Получение списка отчетов
        $reportslinks = $DOF->im('achievements')->get_achievements_reports_links($addvars['departmentid']);
        // Вывод списка отчетов
        foreach($reportslinks as $reportlink)
        {
            $html .= dof_html_writer::div($reportlink);
        }
    } else
    {// Выбран отчет
        // добавление опций отчета в переменные для формирования ссылок
        $addvars['rtroptions'] = $rtroptions;
        $rtroptions = json_decode($rtroptions);
        
        // Для отчетов, использующих текущее подразделение, укажем его (в противном случае они могут сами жестко укзаать в опциях)
        if( empty($rtroptions->departmentid) )
        {
            $rtroptions->departmentid = $addvars['departmentid'];
        }
        // Добавление уровня навигации
        $DOF->modlib('nvg')->add_level(
            $rtroptions->report_name,
            $DOF->url_im('achievements', '/reports.php'),
            $addvars
        );
        
        if ( is_null($export) )
        {
            $accessallowed = $DOF->im('achievements')->is_access(
                'view:rtreport/'.$rtroptions->type, 
                null, 
                null, 
                $rtroptions->departmentid
            );
        } else
        {
            $accessallowed = $DOF->im('achievements')->is_access(
                'export:rtreport/'.$rtroptions->type, 
                null, 
                null, 
                $rtroptions->departmentid
            );
        }
        
        if( $accessallowed )
        {            
            // инициализация отчета
            $rtreport = $DOF->modlib('rtreport')->get_rtreport(
                $rtroptions->pt, 
                $rtroptions->pc, 
                $rtroptions->type
            );
            if ( $rtreport )
            {
                // передача данных в отчет
                $rtreport->set_data(['input' => $rtroptions]);
                $rtreport->set_exporter($export);
                // сбор отчета
                if ( $table = $rtreport->run() )
                {
                    $table->attributes['class'] .= ' '.$rtroptions->type;
                    // Заголовок отчета
                    $html .= $rtreport->get_header();
                    
                    // Вывод отчета
                    $html .= dof_html_writer::div(
                        dof_html_writer::table($table),
                        $rtroptions->type.'_table'
                    );
                    
                    
                    if( $DOF->im('achievements')->is_access('export:rtreport/'.$rtroptions->type) )
                    {
                        $exportaddvars = $addvars;
                        $exportaddvars['export'] = 'xls';
                        $exporturl = $DOF->url_im('achievements', '/reports.php', $exportaddvars);
                        $exportlink = dof_html_writer::link(
                            $exporturl,
                            $DOF->get_string('report_expoort_xls', 'achievements')
                        );
                        $html .= $exportlink;
                    }
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
        } else 
        {// нет доступа к просмотру/экспорту отчета
            $DOF->messages->add(
                $DOF->get_string(
                    (is_null($export) ? 'view' : 'export') . ':rtreport/' . $rtroptions->type . '_denied', 
                    'achievements'
                ),
                DOF_MESSAGE_ERROR
            );
        }
    }
} else
{
    $DOF->messages->add(
        $DOF->get_string('view_reports_denied', 'achievements'),
        DOF_MESSAGE_ERROR
    );
}


// Печать шапки страницы
$DOF->modlib('nvg')->print_header(NVG_MODE_PORTAL);

echo $html;

// Печать подвала
$DOF->modlib('nvg')->print_footer(NVG_MODE_PORTAL);

?>