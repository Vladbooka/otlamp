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

/**
 * Панель управления приемной комиссии. Базовые функции.
 *
 * @package    im
 * @subpackage sel
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// Загрузка библиотек верхнего уровня
require_once(dirname(realpath(__FILE__))."/../lib.php");
require_once(dirname(realpath(__FILE__)).'/locallib.php');

// Добавление таблицы стилей плагина
$DOF->modlib('nvg')->add_css('im', 'sel', '/styles.css');

// Инициализация рендера HTML
$DOF->modlib('widgets')->html_writer();

// Добавление общих GET параметров плагина
$DOF->im('sel')->get_plugin_addvars($addvars);

// Добавление уровня навигации плагина
$DOF->modlib('nvg')->add_level(
    $DOF->get_string('page_main_name', 'sel'),
    $DOF->url_im('sel', '/index.php'),
    $addvars
);
?>