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
 * Плагин информации о пользователе. Страница настроек.
 *
 * @package    im
 * @subpackage achievements
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// Подключаем библиотеки
require_once('lib.php');
require_once('form.php');

// Получаем подразделение
$departmentid = optional_param('departmentid', $DOF->storage('departments')->get_default(), PARAM_INT);

$baselimitnum = (int)$DOF->modlib('widgets')->get_limitnum_bydefault($departmentid);
$limitnum = optional_param('limitnum', $baselimitnum, PARAM_INT);
if ( $limitnum < 1 )
{
    $limitnum = $baselimitnum;
}

$DOF->modlib('nvg')->add_js('im', 'achievements', '/js/achievements_sort_fields.js', false);

$html = '';
$html .= $DOF->im('achievements')->sortfields_panel($departmentid);

// Печать шапки страницы
$DOF->modlib('nvg')->print_header(NVG_MODE_PORTAL);

// Вывод системных сообщений
$messages = $DOF->im('achievements')->messages();
if ( ! empty($messages) )
{
    foreach ( $messages as $message )
    {
        echo $DOF->modlib('widgets')->success_message($message);
    }
}

echo $html;

// Печать подвала
$DOF->modlib('nvg')->print_footer(NVG_MODE_PORTAL);

