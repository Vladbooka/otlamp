<?php
////////////////////////////////////////////////////////////////////////////
//                                                                        //
// NOTICE OF COPYRIGHT                                                    //
//                                                                        //
// Dean`s Office for Moodle                                               //
// Электронный деканат                                                    //
// <http://deansoffice.ru/>                                               //
//                                                                        ////                                                                        //
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

// Подключаем библиотеки
require_once('lib.php');

//добавление уровня навигации
$DOF->modlib('nvg')->add_level($DOF->get_string('title','crm'), $DOF->url_im('crm','/index.php', $addvars));

// Выводим шапку
$DOF->modlib('nvg')->print_header(NVG_MODE_PORTAL);

$order = $DOF->modlib('billing')->order('refill');

$orderobj = new stdClass();
$orderobj->departmentid = 1;
$orderobj->ownerid = 2;
$orderobj->date = time();
$orderobj->data->id = 5;
// Сохраняем приказ в БД и привязываем экземпляр приказа к id
$order->save($orderobj);
// Подписываем от имени персоны 2
$order->sign(4);

// подвал
$DOF->modlib('nvg')->print_footer(NVG_MODE_PORTAL);
?>