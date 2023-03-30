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

global $DOF, $addvars;

// Добвление уровня навигации
$DOF->modlib('nvg')->add_level(
    $DOF->get_string('finalgrades_title', 'journal'),
    $DOF->url_im('journal', '/finalgrades/index.php', $addvars)
);
// Добавление стилей
$DOF->modlib('nvg')->add_css('im','journal','/finalgrades/styles.css');

/**
 * Формирование вкладок для отчета
 *
 * @param string $tabname - Название вкладки
 * @param array $addvars - Массив GET-параметорв
 *
 * @return string - HTML-код вкладок
 */
function im_journal_finagrades_render_tabs($tabname, $addvars = [])
{
    global $DOF;
    
    // Вкладки
    $tabs = [];
    
    $addvars['tab'] = 'summarygrades';
    // Сводный отчет по оценкам за учебные периоды
    $link = $DOF->url_im('journal', '/finalgrades/index.php', $addvars);
    $text = $DOF->get_string('tab_summarygrades', 'journal');
    $tabs[] = $DOF->modlib('widgets')->create_tab('summarygrades', $link, $text, null, false);

    $addvars['tab'] = 'finalgrades';
    // Ведомость оценок учащихся за учебные периоды
    $link = $DOF->url_im('journal', '/finalgrades/index.php', $addvars);
    $text = $DOF->get_string('tab_finalgrades', 'journal');
    $tabs[] = $DOF->modlib('widgets')->create_tab('finalgrades', $link, $text, null, false);
    
    // Формирование блока вкладок
    return $DOF->modlib('widgets')->print_tabs($tabs, $tabname, null, null, true);
}