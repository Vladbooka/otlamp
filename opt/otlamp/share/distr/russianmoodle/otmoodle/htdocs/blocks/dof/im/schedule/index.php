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

/** Главная страница расписания
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
//проверяем доступ
$DOF->storage('schtemplates')->require_access('view');

foreach ( $displayvars as $name => $value )
{// Добавляем к ссылке все переданные параметры отображения расписания
    if ( ! is_null($value) )
    {
        $addvars[$name] = $value;
    }
}

//добавление уровня навигации
if ( isset($addvars['ageid']) AND $age = $DOF->storage('ages')->get($addvars['ageid']) )
{// на конкретный период
    $DOF->modlib('nvg')->add_level($DOF->get_string('title_on', 'schedule', $age->name), 
    $DOF->url_im('schedule','/index.php',$addvars) ); 
}else
{// без периода
    $DOF->modlib('nvg')->add_level($DOF->get_string('title', 'schedule'), 
    $DOF->url_im('schedule','/index.php',$addvars) ); 
}    

// формируем данные для формы
$formaction = $DOF->url_im('schedule', '/index.php', $addvars);
$customdata  = new stdClass();
$customdata->dof = $DOF;

// создаем форму выбора режима просмотра
$displayform = new dof_im_schedule_display_mode_form($formaction, $customdata);
// Обрабатываем данные (если нужно)
$displayform->process($addvars);
// устанавливаем значения по умолчанию
$displayform->set_data($addvars);

// Создаем объект для отображения данных шаблона
$schedule = new dof_im_schedule_display($DOF, $addvars['departmentid'],$addvars);


$contextmenu = $DOF->modlib('widgets')->context_menu();
// ссылка на создание шаблона
if ( $DOF->storage('schtemplates')->is_access('create') AND $addvars['ageid'] )
{// если есть право создавать шаблон
    $item = $contextmenu->get_item_link();
    $item->id = 'new_template';
    $item->text = $DOF->get_string('new_template', 'schedule');
    $item->url = new moodle_url($DOF->url_im('schedule','/edit.php'), $addvars);
    $menuitems[] = $item;
}
// cсылка на просмотр отчета нагрузки по шаблонам
if ( $DOF->storage('schtemplates')->is_access('view') AND $addvars['ageid'] )
{// если есть право создавать шаблон
    $item = $contextmenu->get_item_link();
    $item->id = 'report';
    $item->text = $DOF->get_string('report', 'schedule');
    $item->url = new moodle_url($DOF->url_im('schedule','/report_template.php'), $addvars);
    $menuitems[] = $item;
}
// ссылка на массовые действия
if ( ! empty($age) && $DOF->im('schedule')->is_access('edit:bulk', $addvars['ageid'], null, $age->departmentid) )
{
    $item = $contextmenu->get_item_link();
    $item->id = 'multiple_actions';
    $item->text = $DOF->get_string('multiple_actions', 'schedule');
    $item->url = new moodle_url($DOF->url_im('schedule','/multiple_actions.php'), $addvars);
    $menuitems[] = $item;
}
if( ! empty($menuitems) )
{
    $contextmenu->add_items($menuitems);
    $actions = dof_html_writer::div($contextmenu->render(), 'im_schedule_actions');
} else 
{
    $actions = '';
}

//печать шапки страницы
$DOF->modlib('nvg')->print_header(NVG_MODE_PORTAL);

$DOF->modlib('widgets')->print_heading($DOF->modlib('ig')->igs('you_from_timezone',dof_usertimezone()));

// TODO в будущем вынести ЭТО в стили
// тут мы выделяем другим цветом всю строку таблицы, отвечеющей нашим условиям
// класс, который при отображении по времени четных/нечетных ещё и показывает ЕЖЕНЕДЕЛЬНО дрю цветом
echo "
  <style type='text/css'>
    .mismatch_department .cell { color: #009900; }
  </style> ";

echo $actions;

// Показываем форму с режимом отображения
$displayform->display();
if ( $addvars['display'] == 'time' )
{// если отображение сделано по времени
    // показываем ряды вкладок
    echo $schedule->get_main_page_tabs($addvars['ageid'], $addvars['display'], $addvars['daynum'], $addvars['dayvar'], $addvars['intervalid'],$addvars['form']);
}
if ( ! $addvars['ageid'] )
{// предупреждение на отсутствие периода
    echo '<div align="center"><b>'.$DOF->get_string('select_ageid_of_display', 'schedule').'</b></div>';
}elseif ( ! $addvars['daynum'] AND $addvars['display'] == 'time' )
{// предупреждение на отсутствие дня
    echo '<div align="center"><b>'.$DOF->get_string('select_daynum', 'schedule').'</b></div>';
}elseif ( ! isset($addvars['intervalid']) AND $addvars['display'] == 'time' )
{// предупреждение на дневного интервала
    echo '<div align="center"><b>'.$DOF->get_string('select_intervalid', 'schedule').'</b></div>';
}else
{// выводим таблицы
    echo $schedule->print_full_schedule($addvars['ageid'], $addvars['display'], $addvars['daynum'], $addvars['dayvar'], $addvars['intervalid'], $addvars['form']);
}
//печать подвала
$DOF->modlib('nvg')->print_footer(NVG_MODE_PORTAL);
?>