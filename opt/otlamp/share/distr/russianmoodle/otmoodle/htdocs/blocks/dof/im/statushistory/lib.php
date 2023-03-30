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
 * Интерфейс библиотека
 *
 * @package    im
 *
 * @package    statushistory
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// Загрузка библиотек верхнего уровня
require_once(dirname(realpath(__FILE__))."/../lib.php");
// Инициализируем генератор HTML
$DOF->modlib('widgets')->html_writer();

// Добавление уровня навигации плагина
$DOF->modlib('nvg')->add_level(
        $DOF->get_string('title', 'statushistory'),
        $DOF->url_im('statushistory', '/index.php'), $addvars);
// Подключение стилей
$DOF->modlib('nvg')->add_css('im', 'statushistory', '/styles.css');

$depid = optional_param('departmentid', null, PARAM_INT);
if ( ! isset($depid) )
{
    // Получаем подразделение пользователя
    $depid = $DOF->storage('departments')->get_user_default_department();
    // Путь перенаправления
    $path = $DOF->url_im('statushistory','/index.php?departmentid=' . $depid);
    // Перенаправление
    redirect($path);
}

?>