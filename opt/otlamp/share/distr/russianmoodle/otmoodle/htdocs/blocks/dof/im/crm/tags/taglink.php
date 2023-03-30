<?PHP
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

/*
 * Страница отображения объекта линковки
 */

// Подключаем библиотеки
require_once('lib.php');

// Получаем данные из GET запроса
$taglinkid = optional_param('taglinkid', '0', PARAM_INT);
$ptype = optional_param('ptype', '', PARAM_TEXT);
$pcode = optional_param('pcode', '', PARAM_TEXT);
$objectid = optional_param('objectid', '0', PARAM_INT);
$departmentid = optional_param('departmentid', '0', PARAM_INT);

//печать шапки страницы
$DOF->modlib('nvg')->print_header(NVG_MODE_PORTAL);

// Печать линка
$DOF->im('crm')->print_taglink($taglinkid, $ptype, $pcode, $objectid, $departmentid, $addvars);

//печать подвала
$DOF->modlib('nvg')->print_footer(NVG_MODE_PORTAL);
?>