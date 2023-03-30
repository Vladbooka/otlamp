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
require_once('form.php');

// Получаем данные из GET
$accentrytid = required_param('aid', PARAM_INT);
$contractid = required_param('cid', PARAM_INT);
$depid = optional_param('departmentid', 0, PARAM_INT);

// Проверяем права пользователя на просмотр информации
$DOF->im('billing')->require_access('view:billing/my', $contractid);

// Получаем id счета договора
$accountid = $DOF->modlib('billing')->get_contract_account($contractid);
// Получаем операцию по счету из Справочника
$accentry = $DOF->storage('accentryes')->get_record(array('id' => $accentrytid));

// Если не нашли такой операции
if ( ! $accentry )
{
    // Формируем строку параметров для ссылки со страницы ошибки
    $somevars = array('departmentid' => $depid, 'id' => $contractid);
    $DOF->print_error(
		               $DOF->get_string('error_accentry_not_found','billing'), 
		               $DOF->url_im('billing','/contract_detail.php', $somevars),
		                NULL, 'im', 'billing'
    );
}
// Если операция не принадлежит договору
if ( ( ! $accentry->fromid == $accountid ) && ( ! $accentry->toid == $accountid ) )
{
    // Формируем строку параметров для ссылки со страницы ошибки
    $somevars = array('departmentid' => $depid, 'id' => $contractid);
    $DOF->print_error(
            $DOF->get_string('error_accentry_belongs','billing'),
            $DOF->url_im('billing','/contract_detail.php', $somevars),
            NULL, 'im', 'billing'
    );
}

// Формируем массивы параметров для хлебных крошек
$addvars['cid'] = $contractid;
$addvars['aid'] = $accentrytid;
$addvars['departmentid'] = $depid;
$somevars['id'] = $contractid;
$somevars['departmentid'] = $depid;

// Добавляем хлебные крошки
$DOF->modlib('nvg')->add_level($DOF->get_string('contract_page','billing'), $DOF->url_im('sel','/contracts/view.php', $somevars));
$DOF->modlib('nvg')->add_level($DOF->get_string('contract_detail','billing'), $DOF->url_im('billing','/contract_detail.php', $somevars));
$DOF->modlib('nvg')->add_level($DOF->get_string('accentry_detail','billing'), $DOF->url_im('billing','/accentry_detail.php', $addvars));

// Шапка
$DOF->modlib('nvg')->print_header(NVG_MODE_PORTAL);

// Заголовок страницы
$DOF->modlib('widgets')->print_heading($DOF->get_string('accentry_detail','billing'));
    
// Печатаем таблицу
$DOF->im('billing')->get_accentry_detail_table($accentry, $contractid, $depid);

// Заголовок страницы
$DOF->modlib('widgets')->print_heading($DOF->get_string('accentry_order_detail','billing'));

// Получаем id главного счета
$mainaccentryid = $DOF->modlib('billing')->get_main_account_id();

// Печатаем таблицу
if ( $accentry->amount < 0 )
{//Класс cancel
    $DOF->im('billing')->get_accentry_order_detail_table($accentry->orderid, 'cancel', $depid);
}
if ( $accentry->toid == $mainaccentryid )
{//Класс writeof
    $DOF->im('billing')->get_accentry_order_detail_table($accentry->orderid, 'writeof', $depid);
}
if ( $accentry->fromid == $mainaccentryid )
{//Класс refill
    $DOF->im('billing')->get_accentry_order_detail_table($accentry->orderid, 'refill', $depid);
}
    
// Подвал
$DOF->modlib('nvg')->print_footer(NVG_MODE_PORTAL);
   
?>