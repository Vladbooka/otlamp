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
 * Панель управления дополнительными полями Деканата. Библиотека плагина.
 *
 * @package    im
 * @subpackage customfields
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// Загрузка библиотек верхнего уровня
require_once(dirname(realpath(__FILE__))."/../lib.php");

// Добавление таблицы стилей плагина
$DOF->modlib('nvg')->add_css('im', 'customfields', '/styles.css');

// Добавление JS
$DOF->modlib('nvg')->add_js('im', 'customfields', '/script.js', false);

// Инициализируем генератор HTML
$DOF->modlib('widgets')->html_writer();

// Добавление общих GET параметров плагина
// Количество элементов на странице
$baselimitnum = (int)$DOF->modlib('widgets')->get_limitnum_bydefault($addvars['departmentid']);
$limitnum = optional_param('limitnum', $baselimitnum, PARAM_INT);
if ( $limitnum < 1 )
{
    $limitnum = $baselimitnum;
}
$addvars['limitnum'] = $limitnum;
// Смещение элементов
$addvars['limitfrom'] = optional_param('limitfrom', 1, PARAM_INT);
if ( $addvars['limitfrom'] < 1 )
{
    $addvars['limitfrom'] = 1;
}
// Направление сортировки
$addvars['dir'] = strtolower(optional_param('dir', 'asc', PARAM_TEXT));
if ( $addvars['dir'] != 'desc' )
{
    $addvars['dir'] = 'asc';
}
// Поле сортировки
$addvars['sort'] = strtolower(optional_param('sort', '', PARAM_TEXT));
$availablesortfields = [
    'num',
    'code',
    'plugintype',
    'plugincode',
    'fieldtype',
    'status'
];
if ( array_search($addvars['sort'], $availablesortfields) === false )
{
    $addvars['sort'] = '';
}
// Фильтр
$filter = optional_param('filter', '', PARAM_RAW_TRIMMED);
if ( $filter )
{
    $addvars['filter'] = $filter;
}

// Добавление уровня навигации плагина
$DOF->modlib('nvg')->add_level(
    $DOF->get_string('title', 'customfields'),
    $DOF->url_im('customfields', '/index.php', $addvars)
);