<?php
////////////////////////////////////////////////////////////////////////////
//                                                                        //
// NOTICE OF COPYRIGHT                                                    //
//                                                                        //
// Dean`s Office for Moodle                                               //
// Электронный деканат                                                    //
// <http://deansoffice.ru/>                                               //
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

// Загрузка библиотек верхнего уровня
require_once(dirname(realpath(__FILE__))."/../lib.php");

$DOF->modlib('nvg')->add_css('im', 'sel', '/contracts/styles.css');

// Нормализация сортировки
$addvars['dir'] = strtolower(optional_param('dir', 'asc', PARAM_TEXT));

if ( $addvars['dir'] != 'desc' )
{
    $addvars['dir'] = 'asc';
}
$addvars['sort'] = strtolower(optional_param('sort', '', PARAM_TEXT));
$availablesortfields = [
    'num',
    'fullname',
    'date',
    'status'
];
if ( array_search($addvars['sort'], $availablesortfields) === false )
{
    $addvars['sort'] = '';
}

// Смещение
$addvars['limitfrom'] = optional_param('limitfrom', 1, PARAM_INT);

// Фильтр договоров
$filter = optional_param('filter', '', PARAM_RAW_TRIMMED);
if ( $filter )
{
    $addvars['filter'] = $filter;
}

// Добавление уровня навигации плагина
$DOF->modlib('nvg')->add_level(
    $DOF->get_string('page_contracts_list_name', 'sel'),
    $DOF->url_im('sel', '/contracts/list.php', $addvars)
);
