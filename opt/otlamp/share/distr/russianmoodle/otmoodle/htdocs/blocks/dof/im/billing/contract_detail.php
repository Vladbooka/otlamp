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
$contractid = required_param('id', PARAM_INT);
$depid = optional_param('departmentid', 0, PARAM_INT);
$rsuccess = optional_param('rsuccess', 0, PARAM_INT);
$wsuccess = optional_param('wsuccess', 0, PARAM_INT);
$csuccess = optional_param('csuccess', 0, PARAM_INT);

$addvars['id'] = $contractid;
$addvars['departmentid'] = $depid;

// Проверяем права пользователя на просмотр информации
$DOF->im('billing')->require_access('view:billing/my', $contractid);

// Получаем информацию об операциях для договора
$history = $DOF->modlib('billing')->get_contract_history($contractid);

if ( ! is_object($history->account) )
{// Если счет не найден - выводим страницу ошибки.  

    // Выводим шапку
    $DOF->modlib('nvg')->print_header(NVG_MODE_PORTAL);
    
    $DOF->modlib('widgets')->print_heading($DOF->get_string('error_contract_not_found','billing'));
    // подвал
    $DOF->modlib('nvg')->print_footer(NVG_MODE_PORTAL);
} else 
{// Если объект счета передан - выводим страницу с информацией.  
    
    // Добавление уровня навигации
    // Если происходит просмотр главного счета
    if ( $contractid > 0 )
    {
        $DOF->modlib('nvg')->add_level($DOF->get_string('contract_page','billing'), $DOF->url_im('sel','/contracts/view.php', $addvars));
    }
    $DOF->modlib('nvg')->add_level($DOF->get_string('contract_detail','billing'), $DOF->url_im('billing','/contract_detail.php', $addvars));

    // Готовим формы пополнения и списания баланса
    $customdata = new stdClass;
    $customdata->dof = $DOF;
    $customdata->contract = $contractid;
    
    // Cоздаем объекты форм
    $refillform = new dof_im_billing_refill($DOF->url_im('billing','/contract_detail.php',$addvars),$customdata);
    $writeofform = new dof_im_billing_writeof($DOF->url_im('billing','/contract_detail.php',$addvars),$customdata);
    
    // Проверяем, можно ли отобразить форму пополнения счета
    if ( $DOF->im('billing')->is_access('create:billinrefill') )
    {
        $refillform->process($addvars);
    }
    
    // Проверяем, можно ли отобразить форму списания со счета
    if ( $DOF->im('billing')->is_access('create:billinwriteof') )
    {
        $writeofform->process($addvars);
    }
    
    // Выводим шапку
    $DOF->modlib('nvg')->print_header(NVG_MODE_PORTAL);
    
    $DOF->modlib('widgets')->print_heading($DOF->get_string('contract_detail','billing'));
    
    // Печатаем блок краткой информации на сегодняшний день
    $DOF->im('billing')->get_contract_info($history, $depid);
    
    // Проверяем, можно ли отобразить форму пополнения счета
    if ( $DOF->im('billing')->is_access('create:billinrefill') )
    {
        if ( $rsuccess )
        {
            echo $DOF->get_string('refill_success','billing');
        }
        $refillform->display();
    }
    // Проверяем, можно ли отобразить форму списания со счета
    if ( $DOF->im('billing')->is_access('create:billinwriteof') )
    {
        if ( $wsuccess )
        {
            echo $DOF->get_string('writeof_success','billing');
        }
        $writeofform->display();
    }
    
    // Печатаем таблицу
    $DOF->im('billing')->get_contract_history_table($history, $depid);
    
    // подвал
    $DOF->modlib('nvg')->print_footer(NVG_MODE_PORTAL);
}
?>