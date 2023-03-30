<?php
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

/**
 * Интерфейс управления расписанием. Страница сохранения шаблона расписания.
 *
 * @package    im
 * @subpackage schedule
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// Подключение библиотек
require_once('lib.php');
require_once('form.php');

// ПОЛУЧЕНИЕ GET-ПАРАМЕТРОВ
// ID сохраняемого шаблона
$templateid = optional_param('id', 0, PARAM_INT);
// Время старта события по шаблону
$begintime  = optional_param('begintime', 0, PARAM_INT);
// Тип занятия
$formlesson = optional_param('formlesson','internal',PARAM_TEXT);
// Учебный процесс, к которому привязан шаблон
$cstreamid  = optional_param('cstreamid', 0, PARAM_INT);
// ID студента для фильтрации доступных учебных процессов
$studentid = optional_param('studentid',0,PARAM_INT);
// ID преподавателя для фильтрации доступных учебных процессов
$teacherid = optional_param('teacherid',null,PARAM_INT);
// ID академической группы для фильтрации доступных учебных процессов
$agroupid  = optional_param('agroupid',0,PARAM_INT);

// Формирование данных шаблона по умолчанию
$template = new stdClass();
$template->daynum = $addvars['daynum'];
$template->dayvar = $addvars['dayvar'];

// Проверка прав доступа
if ( $templateid == 0 )
{// Создание нового шаблона
    $DOF->storage('schtemplates')->require_access('create');
} else
{// Редактирование шаблона

    // Проверка существования шаблона
    $template = $DOF->storage('schtemplates')->get($templateid);
    if ( ! $template )
    {// Шаблон не найден
        $DOF->print_error('template_not_found', $DOF->url_im('schedule'), NULL, 'im', 'schedule');
    }
    
    // Проверка доступа
    $DOF->storage('schtemplates')->require_access('edit', $templateid);
}

// НОРМАЛИЗАЦИЯ GET-ПАРАМЕТРОВ
if ( ! $cstreamid && isset($template->cstreamid) )
{// Нормализация учебного процесса
    $cstreamid = (int)$template->cstreamid;
}

// Установка подразделения для шаблона
$departmentid = (int)$addvars['departmentid'];
if ( $cstreamid )
{// Учебный процесс указан

    // Получение учебного процесса
    $cstream = $DOF->storage('cstreams')->get($cstreamid);
    
    if ( ! $cstream )
    {// Учебный процесс не найден
        $DOF->print_error('error:cstream_not_found', $errorlink, NULL, 'im', 'schedule');
    }
    
    // Перелинковка возможных ошибок на страницу учебного процесса
    $errorlink = $DOF->url_im('cstreams','/view.php',
        array_merge($addvars, [
            'cstreamid' => $cstreamid
        ])
    );
    
    // Получение учебного периода
    if ( $age = $DOF->storage('ages')->get($cstream->ageid) )
    {// Учебный период получен
        // Установка подразделения шаблона
        $departmentid = (int)$age->departmentid;
        
        // Проверка текущего местоположения пользователя
        if ( (int)$addvars['departmentid'] != $departmentid && 
             ( isset($template->departmentid) && (int)$addvars['departmentid'] != $template->departmentid ) )
        {
            // ПОльзователь находится в неправильном подразделении
            $DOF->print_error('error:department_not_meet_requirements', $errorlink, NULL, 'im', 'schedule');
        }
    } else
    {// Учебный период не найден
        $DOF->print_error('error:age_not_found', $errorlink, NULL, 'im', 'schedule');
    }
}

// Сформировать дополнительные данные для формы
$customdata = new stdClass();
$customdata->departmentid = $departmentid;
$customdata->dof          = $DOF;
$customdata->cstreamid    = $cstreamid;
$customdata->begintime    = $begintime;
$customdata->ageid        = $addvars['ageid'];
$customdata->studentid    = $studentid;
$customdata->teacherid    = $teacherid;
$customdata->agroupid     = $agroupid;
$customdata->formlesson   = $formlesson;

// Добавление уровня навигации плагина
if ( isset($addvars['ageid']) AND $age = $DOF->storage('ages')->get($addvars['ageid']) )
{// Ссылка на учебный период
    $DOF->modlib('nvg')->add_level(
        $DOF->get_string('title_on', 'schedule', $age->name), 
        $DOF->url_im('schedule', '/index.php', [
            'departmentid'=> $addvars['departmentid'],
            'ageid'=>$addvars['ageid']
        ])
    ); 
} else
{// Ссылка без учебного периода
    $DOF->modlib('nvg')->add_level(
        $DOF->get_string('title', 'schedule'), 
        $DOF->url_im('schedule', '/index.php', [
            'departmentid' => $addvars['departmentid'],
            'ageid'=>$addvars['ageid']
        ]) 
    ); 
}

// добавляем дополнительные параметры в навигацию
$addvars['id'] = $templateid;
$addvars['cstreamid'] = $cstreamid;
$addvars['begintime'] = $begintime;

// Форма сохранения шаблона
$form = new dof_im_schedule_edit_schetemplate_form(
    $DOF->url_im('schedule','/edit.php',$addvars), 
    $customdata
);

// Обработчик формы
$form->process();

// Установка данных формы
$form->set_data($template);

// Формирование сообщения о несовпадении часовой зоны
$hours   = 0;
$minutes = 0;
if ( isset($template->begin) )
{// Корректировка времени
    $hours   = floor($template->begin / 3600);
    $minutes = floor(($template->begin - $hours * 3600) / 60);
}

// Добавление уровня навигации плагина
$DOF->modlib('nvg')->add_level(
    $DOF->get_string('page_save_template', 'schedule'), 
    $DOF->url_im('schedule','/edit.php'), 
    $addvars
);

// Печать шапки страницы
$DOF->modlib('nvg')->print_header(NVG_MODE_PORTAL);

$DOF->modlib('widgets')->print_heading(
    $DOF->modlib('ig')->igs('you_from_timezone', dof_usertimezone())
);
$begin = dof_userdate(mktime($hours, $minutes),"%H"); //время начала
$midnight = intval(date("H",dof_make_timestamp(0, 0, 0))); // полночь в часовом поясе
$zonedate = dof_usergetdate($hours);
$date = getdate($hours);
if ( ($zonedate['wday']*24+$zonedate['hours']) > ($date['wday']*24+$date['hours']) )
{// положительная зона
    if ( ($hours > $midnight) OR ($hours <= ($midnight - 24)) )
    {
         echo '<div style="color: #FF0033; text-align: center;"><b>'.$DOF->get_string('warning_timezone', 'schedule').'</b></div>';
    }
}elseif ( ($zonedate['wday']*24+$zonedate['hours']) < ($date['wday']*24+$date['hours']) )
{// отрицательная зона
    if ( ($hours <= $midnight) OR ($hours > ($midnight + 24)) )
    {// выделим другим цветом те, которые из другого подразделения
         echo '<div style="color: #FF0033; text-align: center;"><b>'.$DOF->get_string('warning_timezone', 'schedule').'</b></div>';
    }
}
// печать формы
$form->display();


//печать подвала
$DOF->modlib('nvg')->print_footer(NVG_MODE_PORTAL);
?>