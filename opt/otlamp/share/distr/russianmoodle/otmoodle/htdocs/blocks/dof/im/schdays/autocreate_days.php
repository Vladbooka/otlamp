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
 * Отображает форму добавления и редактирования. 
 * Если передан параметр id, 
 * то отображается редактирование, 
 * если не передан - добавление. 
 */

// Подключаем библиотеки
require_once('lib.php');
require_once('form.php');
// id дня который сейчас редактируется

// Флаг подтверждения действия
$confirm = optional_param('confirm', 1, PARAM_INT);

// Текущий учебный период
$age = $DOF->storage('ages')->get($addvars['ageid']);

// Проверка возможности создания дней
if ( empty($age) || $addvars['departmentid'] == 0 || (int)$addvars['departmentid'] !=  (int)$age->departmentid )
{// Создание дней для учебного периода запрещено в текущем подразделении
    $DOF->modlib('nvg')->print_header(NVG_MODE_PORTAL);
    $errorlink = $DOF->url_im('schdays', '/calendar.php', $addvars);
    $DOF->print_error('error:department_not_meet_requirements', $errorlink, NULL, 'im', 'schdays');
}

// создаем дополнительные данные для формы
$customdata = new stdClass();
// id подразделения (из lib.php)
$customdata->departmentid = $addvars['departmentid'];
$customdata->dof          = $DOF;
$customdata->ageid        = $addvars['ageid'];

if ( !$age = $DOF->storage('ages')->get($customdata->ageid) )
{
    $DOF->modlib('nvg')->print_header(NVG_MODE_PORTAL);
    $errorlink = $DOF->url_im('schdays','/calendar.php',$addvars);
    $DOF->print_error('error:no_age', $errorlink, NULL, 'im', 'schdays');
}

if ( $confirm )
{
    $type = 'working';
    if ( ! preg_match("/{$age->schstartdaynum}/", $age->schedudays) )
    {
        $type = 'dayoff';
    }
    $result = $DOF->im('schdays')->auto_create_days(
            $age->id,
            $customdata->departmentid, 
            $age->begindate, 
            $age->schstartdaynum, 
            1, 
            $type
    );
    redirect($DOF->url_im('schdays','/calendar.php',$addvars));
}

// Создаем объект формы
$form = new dof_im_schdays_auto_create_days_form($DOF->url_im('schdays','/autocreate_days.php',$addvars), $customdata);
// обрабатываем пришедшие данные (если нужно)
$message = $form->process();

//проверяем доступ
$DOF->storage('schdays')->require_access('create');
$pagetitle = $DOF->get_string('auto_create_days', 'schdays');

// добавляем уровни навигации 
$DOF->modlib('nvg')->add_level($pagetitle, $DOF->url_im('schdays','/autocreate_days.php'), $addvars);
//печать шапки страницы
$DOF->modlib('nvg')->print_header(NVG_MODE_PORTAL);

// печать формы
$form->display();

echo $message;
//печать подвала
$DOF->modlib('nvg')->print_footer(NVG_MODE_PORTAL);
?>