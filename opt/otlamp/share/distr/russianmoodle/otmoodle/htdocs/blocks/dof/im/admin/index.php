<?PHP
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
// Подключаем библиотеки
require_once('lib.php');

//проверка прав доступа
$DOF->im('admin')->require_access('admin');
// Настраиваем отображение секций
$sections = array(); //хранит блоки, отображаемые на левой стороне страницы
$sections[] = array('im'=>'standard','name'=>'fullinfo','id'=>1, 'title'=>$DOF->get_string('project_info'));
$DOF->modlib('nvg')->print_header(NVG_MODE_PORTAL, 'left');

$DOF->modlib('nvg')->print_sections($sections);
//$pathright = $DOF->plugin_path('im', 'standard').'/cfg/right.php';
$DOF->modlib('nvg')->print_footer(NVG_MODE_PORTAL,'right');
?>