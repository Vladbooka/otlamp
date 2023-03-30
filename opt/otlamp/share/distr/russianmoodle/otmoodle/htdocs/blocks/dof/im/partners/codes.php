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
 * Просмотр кодов партнера для регистрации пользователей
 *
 * @package    im
 * @subpackage partners
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// Подключаем библиотеки
require_once('lib.php');

// Проверка прав доступа
$DOF->im('partners')->require_access('codes/view', $addvars['departmentid']);

// Печать таблицы кодов
$options = [];
$options['addvars'] = $addvars;
$codestable = $DOF->im('partners')->get_codestable($addvars['departmentid'], $options);

// Печать шапки страницы
$DOF->modlib('nvg')->print_header(NVG_MODE_PORTAL); 

print($codestable);

// Печать подвала
$DOF->modlib('nvg')->print_footer(NVG_MODE_PORTAL);

?>