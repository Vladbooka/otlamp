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
 * Создание учебных потоков для программы и параллели
 */
require_once('lib.php');
// подключение форм
require_once('form.php');
//проверка прав доступа
$DOF->storage('cstreams')->require_access('create');
// получаем id группы
$programmid = optional_param('programmid', 0, PARAM_INT);
// получаем номер параллели
$agenum     = optional_param('agenum', 0, PARAM_INT);
// получаем id периода
$ageid      = optional_param('ageid', 0, PARAM_INT);


//добавление уровня навигации
$DOF->modlib('nvg')->add_level($DOF->get_string('title', 'cstreams'), 
                     $DOF->url_im('cstreams','/list.php'),$addvars);
$DOF->modlib('nvg')->add_level($DOF->get_string('newcstream', 'cstreams'), 
                     $DOF->url_im('cstreams','/create_cstreams_forprogramm.php'),$addvars);
// создаем объект дополнительных данных для формы
$customdata = new stdClass();
// помещаем туда соответствующие значения
$customdata->dof        = $DOF;
$customdata->programmid = $programmid;
$customdata->agenum     = $agenum;
$customdata->ageid      = $ageid;
// создаем объект формы
$form = new dof_im_cstreams_create_forprogramm($DOF->url_im('cstreams', 
                    '/create_cstreams_forprogramm.php',$addvars), $customdata);

// проверяем правильность переданных параметров
if ( $programmid AND ! $programm = $DOF->storage('programms')->get($programmid) )
{// не найдена учебная программа
    $DOF->print_error(
        'program_not_found',
        '',
        null,
        'im',
        'cstreams'
    );
}elseif ( $programmid AND $programm = $DOF->storage('programms')->get($programmid) AND $agenum )
{// программа есть
    if ( ( $programm->agenums  < $agenum ) OR ( $agenum <= 0 ) )
    {// переданный agenum больше чем количество периодов в программе,
        // либо значение agenum некорректно
            $DOF->print_error(
                'agenum_is_incorrect',
                '',
                null,
                'im',
                'cstreams'
            );
    }
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
require_once('forprogramm_process_form.php');
//печать шапки страницы
$DOF->modlib('nvg')->print_header(NVG_MODE_PORTAL);
// отображаем форму
$form->display();
//печать подвала
$DOF->modlib('nvg')->print_footer(NVG_MODE_PORTAL);

?>