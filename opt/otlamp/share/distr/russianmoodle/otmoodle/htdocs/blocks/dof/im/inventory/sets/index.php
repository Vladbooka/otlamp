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

// Подключаем библиотеки
require_once('lib.php');


// создадим массив, который хранит кол-во элементов в той или иной вкладке
$count_tab = array();
// тип отображения
$display = array('all','granted','available');
foreach ( $display as $value )
{
    $conds = $addvars;
    $conds['displaytype'] = $value; 
    $count_tab[$value] = $DOF->storage('invsets')->get_listing($conds,null,null,'','*',true);  
}

// настройки
$config = $DOF->storage('config')->get_config('report_teachers', 'storage', 'reports', $addvars['departmentid']);
//вывод на экран
//печать шапки страницы
$DOF->modlib('nvg')->print_header(NVG_MODE_PORTAL);

// доп навигация по категориям
echo $DOF->im('inventory')->additional_nvg('/sets/index.php', $addvars);

// распечатеам кладки
echo $DOF->im('inventory')->print_tab($addvars,'sets');
// Второй уровень вкладок - фильтр оборудования по статусу
echo $DOF->im('inventory')->print_set_tabs($addvars, 'operation_sets', $count_tab); 


// Ссылка на выдачу комплекта
if ( $DOF->storage('orders')->is_access('use', NULL, NULL, $addvars['departmentid']) )
{// есть права - покажем
    echo '<a id="im_inventory_delivery_set" href='.$DOF->url_im('inventory','/sets/delivery.php',$addvars).'>'
        .$DOF->get_string('deliver_one_set','inventory').'</a><br>';
}
// ссылка для просмотра приказов
if ( $DOF->storage('orders')->is_access('view', NULL, NULL, $addvars['departmentid']) )
{// есть права - покажем
    echo '<a id="im_inventory_resource_all" href='.$DOF->url_im('inventory','/invorders/list_orders.php',$addvars).'>'
        .$DOF->get_string('list_orders','inventory').'</a><br>';
}
// ссылка на приказ формирования комплекта
if ( $DOF->storage('orders')->is_access('create', NULL, NULL, $addvars['departmentid']) )
{// есть права - покажем
    echo '<a id="im_inventory_set_invset" href='.$DOF->url_im('inventory','/invorders/set_invset.php',$addvars).'>'
        .$DOF->get_string('order_set_invset','inventory').'</a><br>';
}

// отчеты
if ( $DOF->storage('reports')->is_access('view_inventory',NULL,NULL,$addvars['departmentid']) AND
    (! empty($config->value) OR $DOF->is_access('datamanage')) )
{    
echo '<a id="im_inventory_order_items" href='.$DOF->url_im('inventory','/reports/index.php?type=items',$addvars).'>'
    .$DOF->get_string('report_items','inventory').'</a><br>';
}      

//печать подвала
$DOF->modlib('nvg')->print_footer(NVG_MODE_PORTAL);

?>