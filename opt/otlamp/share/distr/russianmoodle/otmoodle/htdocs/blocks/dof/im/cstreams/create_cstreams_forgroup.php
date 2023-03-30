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
/*
 * Массовое создание учебных потоков
 */
// подключение библиотек верхнего уровня
require_once('lib.php');
// подключение форм
require_once('form.php');
//проверка прав доступа
$DOF->storage('cstreams')->require_access('create');
// получаем id группы
$agroupid = optional_param('agroupid', 0, PARAM_INT);
// получаем id периода
$ageid    = optional_param('ageid', 0, PARAM_INT);
// создаем объект дополнительных данных для формы
$customdata = new stdClass();
// помещаем туда соответствующие значения
$customdata->dof      = $DOF;
$customdata->agroupid = $agroupid;
$customdata->ageid    = $ageid;

$DOF->modlib('nvg')->add_level($DOF->get_string('title', 'cstreams'), 
                     $DOF->url_im('cstreams','/list.php'),$addvars);
//добавление уровня навигации
if ( $group = $DOF->storage('agroups')->get($agroupid) )
{
   $DOF->modlib('nvg')->add_level($group->name.'['.$group->code.']', 
                        $DOF->url_im('agroups','/view.php?agroupid='.$agroupid,$addvars));
}

$DOF->modlib('nvg')->add_level($DOF->get_string('pross_group', 'cstreams', $group->name), 
                     $DOF->url_im('cstreams','/create_cstreams_forgroup.php',$addvars),$addvars);



// создаем объект формы
$form = new dof_im_cstreams_create_forgroup($DOF->url_im('cstreams', 
                    '/create_cstreams_forgroup.php',$addvars), $customdata);

// проверяем правильность переданных параметров
if ( $agroupid AND ! $DOF->storage('agroups')->is_exists($agroupid) )
{// не найдена переданная академическая группа
    $DOF->print_error(
        'agroup_not_found',
        '',
        null,
        'im',
        'cstreams'
    );
}
if ( $ageid AND ! $DOF->storage('ages')->is_exists($ageid) )
{// не найден переданный период
    $DOF->print_error(
        'age_not_found',
        '',
        null,
        'im',
        'cstreams'
    );
}

// подключение обработчика
require_once('forgroup_process_form.php');
//печать шапки страницы
$DOF->modlib('nvg')->print_header(NVG_MODE_PORTAL);
// вывод формы на экран
$form->display();
//печать подвала
$DOF->modlib('nvg')->print_footer(NVG_MODE_PORTAL);

?>