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
 * Интерфейс управления причинами отсутствия. Базовые функции.
 *
 * @package    im
 * @subpackage schabsenteeism
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// Загрузка библиотек верхнего уровня
require_once(dirname(realpath(__FILE__))."/../lib.php");

// Добавление таблицы стилей плагина
$DOF->modlib('nvg')->add_css('im', 'schabsenteeism', '/styles.css');

// Инициализация рендера HTML
$DOF->modlib('widgets')->html_writer();

// Добавление общих GET параметров плагина
// Лимит записей на странице
$baselimitnum = (int)$DOF->modlib('widgets')->get_limitnum_bydefault($addvars['departmentid']);
$limitnum = optional_param('limitnum', $baselimitnum, PARAM_INT);
if ( $limitnum < 1 )
{// Нормализация
    $limitnum = $baselimitnum;
}
$addvars['limitnum'] = $limitnum;

// Добавление уровня навигации плагина
$DOF->modlib('nvg')->add_level(
    $DOF->get_string('page_main_name', 'schabsenteeism'),
    $DOF->url_im('schabsenteeism', '/index.php'),
    $addvars
);

// Проверка базового права доступа к плагину
if ( ! $DOF->storage('schabsenteeism')->is_access('viewdesk') )
{
    $DOF->messages->add(
        $DOF->get_string('error_interface_base_access_denied', 'schabsenteeism'),
        'error'
    );
}