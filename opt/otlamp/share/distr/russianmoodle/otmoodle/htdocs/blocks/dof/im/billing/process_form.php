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

// Получаем данные из запроса
$depid = optional_param('departmentid', 0, PARAM_INT);
$confirmed = optional_param('confirm', 0, PARAM_INT);
$task = required_param('task', PARAM_TEXT);

// Формируем параметры для передачи по ссылкам
$addvars['departmentid'] = $depid;

// В зависимости от задачи выполняем действия
switch ($task)
{// Отмена операции
    case 'cancel' :
        // Получаем параметры для отмены
        $accentryid = required_param('aid', PARAM_INT);
        $contractid = required_param('cid', PARAM_INT);
        
        // Добавляем параметры для передачи по ссылкам
        $somevars['departmentid'] = $depid;
        $somevars['task'] = $task;
        $somevars['aid'] = $accentryid;
        $somevars['cid'] = $contractid;
        
        // Получаем операцию
        $accentry = $DOF->storage('accentryes')->get_record(array('id' => $accentryid));
        
        // Если мы пытаемся отменить отмену( у такой операции сумма отрецательная ),
        // либо операцию с неверным статусом, то выкидываем ошибку
        
        // Получаем все активные статусы
        $activestatuses = $DOF->workflow('accentryes')->get_meta_list('active');
        
        if ( $accentry->amount < 0 || ! array_key_exists($accentry->status, $activestatuses) )
        {
            $DOF->print_error(
                    $DOF->get_string('error_cancel_accentry','billing'),
                    $linkno,
                    NULL, 'im', 'billing');
        }
        
        if ($accentry)
        // Если не получили операцию
        if ( empty($accentry) )
        {
            $DOF->print_error(
		              $DOF->get_string('error_get_accentry','billing'),
		              $linkno,
		              NULL, 'im', 'billing');
        }
        
         // Получаем id главного счета
		$mainaccentryid = $DOF->modlib('billing')->get_main_account_id();
		
        // В зависимости от операции проверяем доступ
        if ( $accentry->fromid === $mainaccentryid )
        {// Пополнение
            $DOF->modlib('billing')->require_access('create:billinrefill');
        } else
        {// Списание
            $DOF->modlib('billing')->require_access('create:billinwriteof');
        }
        
        // Ссылка на возврат
        $linkno = $DOF->url_im('billing', '/accentry_detail.php', $somevars);
        
        // Добавляем параметр подтверждения отмены
        $somevars['confirm'] = 1;
        // Ссылка на подтверждение отмены
        $linkyes = $DOF->url_im('billing', '/process_form.php', $somevars);
        
        // Если операция подтверждена
        if ( $confirmed )
        {
            /**** ФОРМИРУЕМ ПОЛЯ ОТМЕНЯЕМОЙ ОПЕРАЦИИ ****/ 
            
            if ( ! empty($accentry->extentryopts) )
            {
                $extentryopts = unserialize($accentry->extentryopts);
                if ( isset($extentryopts['amount']) )
                {// Если в опциях есть поле суммы - меняем знак
                    $extentryopts['amount'] = -$accentry->amount;
                }
            } else 
            {
                $extentryopts = null;
            }
            
            // Меняем описание
            $about = 'Возврат средств по операции: <br/>'.$accentry->about;
            
            // Генерируем операцию - отмену
            $cancelaccentry = $DOF->storage('accentryes')->generate_accentry_record(
                        $accentry->fromid, 
                        $accentry->toid, 
                        -$accentry->amount, 
                        $accentry->date + 1, 
                        $extentryopts, 
                        null,
                        $about
                    );

            //  Создаем приказ
            $order = $DOF->modlib('billing')->cancell_contract_balance($contractid,$accentryid);
            if ( ! $order )
            {// Если приказ не создался
                $DOF->print_error(
		              $DOF->get_string('error_cancel_accentry','billing'),
		              $linkno,
		              NULL, 'im', 'billing');
            }
            
            // Добавляем id приказа
            $cancelaccentry->orderid = $order;
            // Сбрасываем хэш для нашей операции
            $cancelaccentry->extentryoptshash = null;

            // Отменяем операцию
            if ( ! $DOF->storage('accentryes')->add_accentry($cancelaccentry) )
            {// Если операция не добавилась
                $DOF->print_error(
                        $DOF->get_string('error_cancel_accentry','billing'),
                        $linkno,
                        NULL, 'im', 'billing');
            } else
            {
                
                if ( ! $DOF->workflow('accentryes')->change($accentry->id, 'rejected') )
                {
                    $DOF->print_error(
                            $DOF->get_string('error_change_accentry_status','billing'),
                            $linkno,
                            NULL, 'im', 'billing');
                }
                // Редирект при успехе
                redirect($linkno);
            }
        }
        
        // Выводим шапку
        $DOF->modlib('nvg')->print_header(NVG_MODE_PORTAL);
        
        // Если еще не подтверждали отмену операции - выведем нотис подтверждения
        if ( ! $confirmed )
        {
            $DOF->modlib('widgets')->notice_yesno(
                    $DOF->get_string('confirmation_cancel_accentry','billing'),
                    $linkyes,
                    $linkno
            );
        }
        
        // подвал
        $DOF->modlib('nvg')->print_footer(NVG_MODE_PORTAL);
        break;
    default:
        redirect($DOF->url_im('billing', '/index.php', $addvars));
        break;
}

?>