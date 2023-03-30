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

/** Страница массовых действий с шаблонами расписания
 * @todo добавить проверку прав
 */

// Подключаем библиотеки
require_once('lib.php');
require_once($DOF->plugin_path('im', 'schedule', '/form.php'));

// Добавляем CSS
$DOF->modlib('nvg')->add_css('im', 'schedule', '/styles.css');

// Инициализация генератора HTML
$DOF->modlib('widgets')->html_writer();

// параметры отображения расписания
$displayvars = array();
// время начала уроков
$displayvars['begin']      = optional_param('begin',  null, PARAM_INT);
// время окончания уроков
$displayvars['end']        = optional_param('end',    null, PARAM_INT);

foreach ( $displayvars as $name => $value )
{// Добавляем к ссылке все переданные параметры отображения расписания
    if ( ! is_null($value) )
    {
        $addvars[$name] = $value;
    }
}

$html = '';
if ( isset($addvars['ageid']) AND $age = $DOF->storage('ages')->get($addvars['ageid']) )
{// на конкретный период
    $DOF->modlib('nvg')->add_level($DOF->get_string('title_on', 'schedule', $age->name),
        $DOF->url_im('schedule','/index.php',$addvars) );
    if( $DOF->im('schedule')->is_access('edit:bulk', $addvars['ageid'], null, $age->departmentid) )
    {
        // формируем данные для формы
        $formaction = $DOF->url_im('schedule', '/multiple_actions.php', $addvars);
        $customdata  = new stdClass();
        $customdata->dof = $DOF;
        $customdata->ageid = $addvars['ageid'];
    
        // создаем форму выбора режима просмотра
        $displayform = new dof_im_schedule_change_begindate_form($formaction, $customdata);
        // Обрабатываем данные (если нужно)
        $displayform->process($addvars);
    
        // Показываем форму с режимом отображения
        $html .= $displayform->render();
    
        
    } else
    {
        $html .= $DOF->get_string('permission_denied', 'schedule');
    }
} else 
{
    $DOF->modlib('nvg')->add_level($DOF->get_string('title', 'schedule'),
        $DOF->url_im('schedule','/index.php',$addvars) );
    $html .= '<div align="center"><b>'.$DOF->get_string('select_ageid_of_display', 'schedule').'</b></div>';
}

//печать шапки страницы
$DOF->modlib('nvg')->print_header(NVG_MODE_PORTAL);

$DOF->modlib('widgets')->print_heading($DOF->modlib('ig')->igs('you_from_timezone',dof_usertimezone()));

echo $html;

//печать подвала
$DOF->modlib('nvg')->print_footer(NVG_MODE_PORTAL);

