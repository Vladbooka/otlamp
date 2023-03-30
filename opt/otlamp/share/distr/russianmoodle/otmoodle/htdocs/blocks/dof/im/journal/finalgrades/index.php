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
 * Сводный отчет по оценкам и ведомость оценок учащихся за учебные периоды по параллели
 *
 * @package    im
 * @subpackage journal
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// Подключение библиотек
require_once('form.php');
require_once('lib.php');

GLOBAL $DOF;
$html = '';

// Параметры

// выбранная вкладка
$tab = $addvars['tab'] = optional_param('tab', 'summarygrades', PARAM_ALPHA);
// выбранный период
$ageid = $addvars['ageid'] = optional_param('ageid', null, PARAM_INT);
// выбранная программа
$programmid = $addvars['programmid'] = optional_param('programmid', null, PARAM_INT);
// выбранная параллель
$parallel = $addvars['parallel'] = optional_param('parallel', null, PARAM_INT);
// экспорт
$export = optional_param('export', null, PARAM_RAW_TRIMMED);



$form = new dof_im_journal_finalgrades(
    $DOF->url_im('journal','/finalgrades/index.php', $addvars),
    [
        'dof' => $DOF,
        'addvars' => $addvars,
        'export' => $export
    ]
);

$html .= $form->render();
$formresult = $form->process();
if( ! is_null($ageid) && ! is_null($programmid) && ! is_null($parallel) )
{// основные данные для сбора отчета имеются
    // вывод вкладок
    $html .= im_journal_finagrades_render_tabs($tab, $addvars);
    // вывод результата обработки формы (отчета)
    $html .= $formresult;
}

// Печать шапки страницы
$DOF->modlib('nvg')->print_header(NVG_MODE_PORTAL);

// Вывод сообщений
$DOF->messages->display();

// Вывод
echo $html;

// Печать подвала
$DOF->modlib('nvg')->print_footer(NVG_MODE_PORTAL);
 