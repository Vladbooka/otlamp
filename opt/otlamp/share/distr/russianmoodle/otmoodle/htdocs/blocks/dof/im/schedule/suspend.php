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



// Подключаем библиотеки
require_once('lib.php');
//id записи о теме занятия
$templateid = required_param('id', PARAM_INT);
// получаем id потока
$addvars['cstreamid'] = optional_param('cstreamid',0,PARAM_INT);
// получаем id студента
$addvars['studentid'] = optional_param('studentid',0,PARAM_INT);
// получаем id учителя
$addvars['teacherid'] = optional_param('teacherid',null,PARAM_INT);
// получаем id группы
$addvars['agroupid']  = optional_param('agroupid',0,PARAM_INT);
$suspend = optional_param('suspend', 0, PARAM_BOOL);
// проверки
// не найден элемент учебного плана
if ( ! $template  = $DOF->storage('schtemplates')->get($templateid) )
{// вывод сообщения и ничего не делаем
    print_error($DOF->get_string('template_not_exists','schedule',$templateid));
}
//проверка прав доступа
$DOF->workflow('schtemplates')->require_access('changestatus');  

// ссылки на подтверждение и непотдверждение сохранения приказа
$linkyes ='/suspend.php?id='.$templateid.'&suspend=1';
$linkno ='/view_week.php';
if ( $suspend )
{// если сказали удалить - сменим статус
    $DOF->workflow('schtemplates')->change($templateid,'suspend');
    redirect($DOF->url_im('schedule',$linkno,$addvars));
}else
{
    //вывод на экран
    //печать шапки страницы
    if ( isset($addvars['ageid']) AND $age = $DOF->storage('ages')->get($addvars['ageid']) )
    {// на конкретный периож
        $DOF->modlib('nvg')->add_level($DOF->get_string('title_on', 'schedule', $age->name), 
        $DOF->url_im('schedule','/index.php',array('departmentid'=>$addvars['departmentid'],
                                                   'ageid'=>$addvars['ageid'])) ); 
    }else
    {// без периода
        $DOF->modlib('nvg')->add_level($DOF->get_string('title', 'schedule'), 
        $DOF->url_im('schedule','/index.php',array('departmentid'=>$addvars['departmentid'],
                                                   'ageid'=>$addvars['ageid'])) ); 
    }      
    $DOF->modlib('nvg')->add_level($DOF->get_string('suspend_template', 'schedule'),
                                   $DOF->url_im('schedule','/suspend.php',$addvars));
    $DOF->modlib('nvg')->print_header(NVG_MODE_PAGE);
    // спросим об удалении
    $DOF->modlib('widgets')->notice_yesno($DOF->get_string('confirmation_suspend_template','schedule'), 
            $DOF->url_im('schedule',$linkyes,$addvars),
            $DOF->url_im('schedule',$linkno,$addvars));
    //печать подвала
    $DOF->modlib('nvg')->print_footer(NVG_MODE_PAGE);
}

?>
