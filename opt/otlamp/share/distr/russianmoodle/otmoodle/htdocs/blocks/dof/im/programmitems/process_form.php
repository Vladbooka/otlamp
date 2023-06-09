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


//проверяем доступ
if ( ! $DOF->storage('programmitems')->is_access('create', $pitemid) AND
     ! $DOF->storage('programmitems')->is_access('create/meta', $pitemid) AND
     ! $DOF->storage('programmitems')->is_access('edit/meta', $pitemid) )
{
    $DOF->storage('programmitems')->require_access('edit');
}
// создаем путь на возврат
$path = $DOF->url_im('programmitems','/list.php',$addvars);
if ( $form->is_cancelled() )
{//ввод данных отменен - возвращаем на страницу просмотра предмета
    redirect($path);
}elseif ( $form->is_submitted() AND confirm_sesskey() AND $formdata = $form->get_data() )
{//даные переданы в текущей сессии - получаем
    // print_object($formdata);die;
    // создаем объект для сохранения в БД
    $pitem = new stdClass();
    $pitem->name          = trim($formdata->name);
    $pitem->sname         = trim($formdata->sname);
    $pitem->code          = trim(mb_strtolower($formdata->code,'utf-8')); // все коды записываются в нижнем регисте
    $pitem->scode         = trim(mb_strtolower($formdata->scode,'utf-8'));
    $pitem->departmentid  = $formdata->departmentid;
    $pitem->required      = $formdata->required;
    $pitem->selfenrol     = $formdata->selfenrol;
    if ( ! empty($pitem->selfenrol) )
    {
        $pitem->studentslimit = $formdata->studentslimit;
    } else 
    {
        $pitem->studentslimit = 0;
    }
    if ( ! empty($formdata->courselinktype) )
    {
        $pitem->courselinktype = $formdata->courselinktype;
    } else 
    {
        $pitem->courselinktype = 'direct';
    }
    $pitem->maxcredit     = $formdata->maxcredit;
    $pitem->eduweeks      = $formdata->eduweeks;
    $pitem->maxduration   = $formdata->maxduration * 3600 * 24; // максимальная длительность в днях
    if ( empty($formdata->autohours) )
    {// Если галочка 'Автоматически расчитывать количество "Часов всего"' не отмечена
        $pitem->hours = $formdata->hours;
    } else
    {
        $pitem->autohours     = $formdata->autohours;
    }
    $pitem->hourstheory   = $formdata->hourstheory;
    $pitem->hourspractice = $formdata->hourspractice;
    $pitem->hoursweek     = $formdata->hoursweek;
    $pitem->hourslab      = $formdata->hourslab;
    $pitem->hoursind      = $formdata->hoursind;
    $pitem->hourscontrol  = $formdata->hourscontrol;
    //$pitem->hoursselfstudywithteacher = $formdata->hoursselfstudywithteacher;
    $pitem->about         = trim($formdata->about['text']);
    $pitem->notice        = trim($formdata->notice);
    $pitem->mingrade      = trim($formdata->mingrade);
    $pitem->gradelevel    = $formdata->gradelevel;
    $pitem->controltypeid = $formdata->controltypeid;
    $pitem->type          = $formdata->type;
    $pitem->instrlevelid  = $formdata->instrlevelid;
    $pitem->gradesyncenabled = $formdata->gradesyncenabled; // синхр-я оценок разрешена
    $pitem->incjournwithoutgrade = $formdata->incjournwithoutgrade; // вкл в ведомость польз-й без оценки или не подпис-х на курс
    $pitem->incjournwithunsatisfgrade = $formdata->incjournwithunsatisfgrade; // вкл в ведомость польз-й с неуд-й оценкой
    $pitem->altgradeitem = $formdata->altgradeitem; // исп-ть другой grade_items
    $pitem->salfactor = round((float)$formdata->salfactor, 2);
    $pitem->billingtext = $formdata->billingtext['text'];
    //ставим условия в зависимости от того дисциплина это или метадисциплины
    if ($meta == 1)
    {
        $pitem->programmid = 0;   
        $pitem->metaprogrammitemid = 0;
        $pitem->agenum = $agenum;
    }
    else
    {           
        $pitem->programmid    = $formdata->progages[0]; // программа указана через hierselect
        $pitem->agenum        = $formdata->progages[1]; // периоды указаны через hierselect
        $pitem->metasyncon    = $formdata->metasyncon;
    }
        
    if ( $formdata->pitemid AND ! $pitem->code )
    {// если запись редактируется и код не указан - то заменим код на id
        $pitem->code = 'id'.$formdata->pitemid;
    }
    
    // очистим шкалу оценок от лишних символов
    $pitem->scale         = preg_replace('/[ \"\']{1,255}/i', '',  trim($formdata->scale));
    $pitem->coursegradesconversation = $formdata->coursegradesconversation;
    if ( empty($formdata->usediscscaleandpassgrade) )
    {
        // у занятий дисциплины своя шкала, проходной бал, параметры конвертации оценки
        $pitem->lessonscale = preg_replace('/[ \"\']{1,255}/i', '',  trim($formdata->lessonscale));
        $pitem->lessonpassgrade = trim($formdata->lessonpassgrade);
        $pitem->modulegradesconversation = trim($formdata->modulegradesconversation);
    } else 
    {
        $pitem->lessonscale = null;
        $pitem->lessonpassgrade = null;
        $pitem->modulegradesconversation = null;
    }
    // экранируем все потенциально опасные значения
    if ( $DOF->storage('programmitems')->is_access('edit:mdlcourse', $formdata->pitemid)  
            AND isset($formdata->mdlcourse) AND ($formdata->mdlcourse <> 0) )
    {// если курс в Moodle указан и пользователь имеет право редактирования id курса
        
        $status = $DOF->storage('programmitems')->get_field($formdata->pitemid,'status');
        if ( empty($status) OR $status == 'suspend' )
        {// есть статус и он не приостановленный - запрещаем редактирование
            
            // если -1 => это означает, что необходимо создать новый курс Moodle и привязать
            // если 0 => курс Moodle отсутствует
            // если > 0 => идентификатор курса в Moodle
            // плагин sync/mcourses отлавливает событие добавления/редактирования 
            $pitem->mdlcourse = intval($formdata->mdlcourse);
        }
    }
    if ( isset($formdata->pitemid) AND $formdata->pitemid )
    {// предмет редактировался и ошибок нет - обновим запись в БД
        if ( $DOF->storage('programmitems')->update($pitem,$formdata->pitemid) )
        {// возвращаем на страниу просмотра предмета
            if($meta !== 1)
            {
                redirect($DOF->url_im('programmitems','/view.php?pitemid='.$formdata->pitemid,$addvars));
            }
            else
            {
                redirect($DOF->url_im('programmitems','/view.php?meta=1&pitemid='.$formdata->pitemid,$addvars));
            }    
        }else
        {// сообщим об ошибке
            $error .= '<br>'.$DOF->get_string('errorsavepitem','programmitems').'<br>';
        }
    }else
    {// предмет создавался
        // сохраняем запись в БД
        if( $id = $DOF->storage('programmitems')->insert($pitem) )
        {// все в порядке - сохраняем статус и возвращаем на страниу просмотра предмета
            $DOF->workflow('programmitems')->init($id);
            
          // redirect($DOF->url_im('programmitems','/view.php?pitemid='.$id,$addvars));
            if($meta !== 1)
            {
                redirect($DOF->url_im('programmitems','/view.php?pitemid='.$id,$addvars));
            }
            else
            {
                redirect($DOF->url_im('programmitems','/view.php?meta=1&pitemid='.$id,$addvars));
            }
            
        }else
        {// предмет выбран неверно - сообщаем об ошибке
            $error .=  '<br>'.$DOF->get_string('errorsavepitem','programmitems').'<br>';
        }
    }
}
?>