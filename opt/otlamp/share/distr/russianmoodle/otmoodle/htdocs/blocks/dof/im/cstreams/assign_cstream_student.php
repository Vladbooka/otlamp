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
$DOF->im('cstreams')->require_access('editcurriculum');
// получаем id группы
$agroupid = optional_param('agroupid', 0, PARAM_INT);
// получаем id группы
$sbcid = optional_param('sbcid', 0, PARAM_INT);
// получаем id группы
$pitemid = optional_param('pitemid', 0, PARAM_INT);
// получаем id периода
$ageid = optional_param('ageid', 0, PARAM_INT);

// для привязки всех обязательных дисциплин
// получим нажатие кнопки привязать все
$bindall = optional_param('bindall',0,PARAM_TEXT);
// программа
$programmid = optional_param('programmid', 0, PARAM_INT);
// параллеь
$agenum = optional_param('agenum', 0, PARAM_INT);

$pitems = $DOF->storage('programmitems')->get_pitems_list($programmid,$agenum);


if ( ! $agroupid AND ! $sbcid )
{
    $DOF->print_error(
        'student_or_group_not_found',
        '',
        null,
        'im',
        'cstreams'
    );
}
// проверяем правильность переданных параметров
if ( ! $pitem = $DOF->storage('programmitems')->get($pitemid) AND ! $bindall )
{// не найдена переданная академическая группа
    $DOF->print_error(
        'error_programmitem',
        '',
        null,
        'im',
        'cstreams'
    );
}
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
if ( $sbcid AND ! $DOF->storage('programmsbcs')->is_exists($sbcid) )
{// не найден переданный период
    $DOF->print_error(
        'sbc_not_found',
        '',
        $sbcid,
        'im',
        'cstreams'
    );
}
// проверяем правильность статусов группы
if ( $agroupid AND ! in_array($DOF->storage('agroups')->get_field($agroupid,'status'), 
                              array('plan','active','formed')) )
{// не найдена переданная академическая группа
    $DOF->print_error(
        'error_group_status',
        '',
        null,
        'im',
        'cstreams'
    );
}
// проверяем правильность статусов подписки
if ( $sbcid AND ! in_array($DOF->storage('programmsbcs')->get_field($sbcid,'status'), 
                           array('plan','active','application','condactive')) )
{// не найден переданный период
    $DOF->print_error(
        'error_sbc_status',
        '',
        null,
        'im',
        'cstreams'
    );
}
// создаем объект дополнительных данных для формы
$customdata = new stdClass();
$customdata->cstream = new stdClass;

// помещаем туда соответствующие значения
$customdata->dof       = $DOF;
$customdata->cstream->agroupid  = $agroupid;
$customdata->cstream->sbcid = $sbcid;
$customdata->cstream->ageid   = $ageid;
$customdata->cstream->pitemid = $pitemid;
$customdata->cstream->programmid = $programmid;
$customdata->cstream->agenum = $agenum;
$customdata->bindall = $bindall;
//добавление уровня навигации
$DOF->modlib('nvg')->add_level($DOF->get_string('title', 'cstreams'), 
                     $DOF->url_im('cstreams','/list.php'),$addvars);
$a = new stdClass;
if ( ! $bindall )
{
    $a->item = $pitem->name; 
    $DOF->modlib('nvg')->add_level($pitem->name.'['.$pitem->code.']', $DOF->url_im('programmitems','/view.php?pitemid='.$pitemid,$addvars));
    if ( $sbcid )
    {
        $a->student = $DOF->storage('persons')->get_field($DOF->storage('contracts')->get_field(
            $DOF->storage('programmsbcs')->get_field($sbcid,'contractid'),'studentid'),'sortname');
        $DOF->modlib('nvg')->add_level($DOF->get_string('assign_student', 'cstreams', $a), 
        $DOF->url_im('cstreams','/assign_cstream_student.php'),array_merge($addvars,(array)$customdata->cstream));
    }elseif ( $agroupid )
    {
        $a->student = $DOF->storage('agroups')->get_field($agroupid,'name');
        $DOF->modlib('nvg')->add_level($DOF->get_string('assign_group', 'cstreams', $a), 
        $DOF->url_im('cstreams','/assign_cstream_student.php'),array_merge($addvars,(array)$customdata->cstream));
    }
}else 
{
  
    if ( $sbcid )
    {
        $a->student = $DOF->storage('persons')->get_field($DOF->storage('contracts')->get_field(
            $DOF->storage('programmsbcs')->get_field($sbcid,'contractid'),'studentid'),'sortname');
        $DOF->modlib('nvg')->add_level($DOF->get_string('assign_studentall', 'cstreams', $a),
        $DOF->url_im('cstreams','/assign_cstream_student.php'),array_merge($addvars));
    }elseif ( $agroupid )
    {
        $a->student = $DOF->storage('agroups')->get_field($agroupid,'name');
        $DOF->modlib('nvg')->add_level($DOF->get_string('assign_groupall', 'cstreams', $a),
        $DOF->url_im('cstreams','/assign_cstream_student.php'),array_merge($addvars));
    }
}
    


// создаем объект формы
$form = new dof_im_cstreams_assign_student_form($DOF->url_im('cstreams', 
                    '/assign_cstream_student.php',array_merge($addvars,(array)$customdata->cstream)), $customdata);

// подключение обработчика
$message = $form->execute_form();
//печать шапки страницы
$DOF->modlib('nvg')->print_header(NVG_MODE_PORTAL);
if ( $message != '' )
{// вывод ошибок
    print $message;
}
// вывод формы на экран
$form->display();
//печать подвала
$DOF->modlib('nvg')->print_footer(NVG_MODE_PORTAL);

?>