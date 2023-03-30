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
 * Базовые функции плагина
 * 
 * @package    im
 * @subpackage achievements
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// Загрузка библиотек верхнего уровня
require_once(dirname(realpath(__FILE__))."/../lib.php");
// Добавим таблицу стилей
$DOF->modlib('nvg')->add_css('im', 'achievements', '/style.css');

// Инициализируем генератор HTML
$DOF->modlib('widgets')->html_writer();

// Добавление общих GET параметров плагина
$baselimitnum = (int)$DOF->modlib('widgets')->get_limitnum_bydefault($addvars['departmentid']);
$limitnum = optional_param('limitnum', $baselimitnum, PARAM_INT);
if ( $limitnum < 1 )
{
    $limitnum = $baselimitnum;
}
$addvars['limitnum'] = $limitnum;

// Добавление уровня навигации плагина
$DOF->modlib('nvg')->add_level(
        $DOF->get_string('page_main_name', 'achievements'),
        $DOF->url_im('achievements', '/index.php'), $addvars);

?>