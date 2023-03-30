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
 * Редактирование пользовательских достижений
 *
 * @package    im
 * @subpackage achievements
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
 
// Подключаем библиотеки
require_once('lib.php');
require_once('form.php');

// Получение GET параметров
$id = optional_param('id', 0, PARAM_INT);
$achievementid = optional_param('aid', 0, PARAM_INT);
$creategoal = optional_param('create_goal', false, PARAM_BOOL);
$personid = optional_param('personid', 0, PARAM_INT);

if( ! empty($creategoal) )
{
    $addvars['create_goal'] = true;
}
if ( $personid > 0 )
{
    $addvars['personid'] = $personid;
}

// Дополнительные переменные
$errors = [];
$backlink = $DOF->url_im('achievements', '/my.php', $addvars);

// Добавление уровня навигации
$DOF->modlib('nvg')->add_level(
    $DOF->get_string('mypage_title', 'achievements'),
    $DOF->url_im('achievements', '/my.php'),
    $addvars
);

// Права доступа
$DOF->im('achievements')->require_access('my');

$catid = 0;
$userdata = [];

// Права доступа
if ( empty($id) )
{// Создание пользовательского достижения
    if( $creategoal )
    {
        $DOF->storage('achievementins')->is_access_goal('create', $achievementid, $personid, null, null, true);
    } else
    {
        $DOF->storage('achievementins')->require_access('create', $personid);
    }
    
    // Добавление уровня навигации плагина
    $addvars['aid'] = $achievementid;
    
    if ( $creategoal )
    {
        $DOF->modlib('nvg')->add_level(
                $DOF->get_string('create_goal', 'achievements'),
                $DOF->url_im('achievements', '/edit_achievementinst.php'), $addvars);
    } else
    {
        $DOF->modlib('nvg')->add_level(
                $DOF->get_string('create_achievementin', 'achievements'),
                $DOF->url_im('achievements', '/edit_achievementinst.php'), $addvars);
    }

    // Проверка на наличие шаблона
    $achievement = $DOF->storage('achievements')->get($achievementid);
    if ( empty($achievement) )
    {// Шаблон не найден
        $errors[] = $DOF->get_string('message_form_achievementins_achievement_not_found', 'achievements');
    } else 
    {
        // Проверка на статус
        $statuses = $DOF->workflow('achievements')->get_meta_list('active');
        if ( ! isset($statuses[$achievement->status]) )
        {// Шаблон не активен
            $errors[] = $DOF->get_string('message_form_achievementins_achievement_status_error', 'achievements');
        }
        
        if( $creategoal && ! $DOF->storage('achievements')->is_goal_add_allowed($achievement->scenario) )
        {// происходит добавление цели, а шаблон достижения не поддерживает цели
            $errors[] = $DOF->get_string('message_form_achievementins_scenario_goal_support_error', 'achievements');
        }
        if( ! $creategoal && ! $DOF->storage('achievements')->is_achievement_add_allowed($achievement->scenario) )
        {// происходит добавление достижения, а шаблон настроен на цель и не поддерживает прямое добавление достижения
            $errors[] = $DOF->get_string('message_form_achievementins_scenario_achievement_support_error', 'achievements');
        }
        
        $catid = $achievement->catid;
    }
} else
{// Редактирование пользовательского достижения
    
    $instance = $DOF->storage('achievementins')->get($id);
    if ( ! empty($instance) )
    {// Пользовательское достижение найдено
        
        // проверка права на редактирование
        // включает себя проверку прав как достижения, так и цели
        $DOF->storage('achievementins')->require_access('edit', $id);
        
        $achievement = $DOF->storage('achievements')->get($instance->achievementid);

        if ( empty($achievement) )
        {// Достижение не прилинковано к шаблону
            $errors[] = $DOF->get_string('message_form_achievementins_instanse_not_linked', 'achievements');
        } else 
        {// Добавление уровня навигации
            $addvars['id'] = $id;
            $DOF->modlib('nvg')->add_level(
                $achievement->name,
                $DOF->url_im('achievements', '/edit_achievementinst.php'),
                $addvars
            );
            $achievementid = $achievement->id;
            $catid = $achievement->catid;
            $userdata = unserialize($instance->data);
        }
    } else 
    {// Пользовательское достижение не найдено
        $errors[] = $DOF->get_string('message_form_achievementins_achievementins_not_found', 'achievements');
    }
}

// Сформируем контекст пользователя
$person = $DOF->storage('persons')->get_bu(NULL, true);
if ( empty($person->mdluser) )
{
    $errors[] = $DOF->get_string('message_form_achievementins_user_not_found', 'achievements');
} else 
{
    $userid = $person->mdluser;
    $usercontext = context_user::instance($userid);
}

if ( empty($errors) )
{
    // Проверка на доступность шаблона
    if ( ! $DOF->im('achievements')->is_access('achievement/use', $achievementid) )
    {// Ошибка доступа
        $errors[] = $DOF->get_string('message_form_achievementins_access_denied_achievement_use', 'achievements');
    }
}

$success = optional_param('success', NULL, PARAM_INT);
if ( empty($success) && ! is_null($success) )
{// Ошибка сохранения
    $errors[] = $DOF->get_string('message_form_achievementins_edit_save_error', 'achievements');
}

if ( empty($errors) )
{
    // Создаем объект шаблона
    $achievementobj = $DOF->storage('achievements')->object($achievement->id);
    if ( ! empty($achievementobj) )
    {// Объект создан
        // Сформируем url формы
        $url = $DOF->url_im('achievements', '/edit_achievementinst.php', $addvars);
        
        // Сформируем дополнительные данные
        $customdata = new stdClass();
        $customdata->dof = $DOF;
        $customdata->id = $id;
        $customdata->userdata = $userdata;
        $customdata->addvars = $addvars;
        $customdata->context = $usercontext;
        $customdata->create_goal = $creategoal;
        
        // Получим форму
        $achievementobj->userform($url, $customdata);
        
        if ( ! empty($achievementobj->userform) )
        {// Получить состояние пользовательских данных по достижению
            $userformresult = $achievementobj->userform->process();

            if ( ! empty($userformresult) )
            {// Обработка прошла успешно
                if ( empty($personid) )
                {// Целевой пользователь не указан
                    if( empty($id) )
                    {// Создание достижения для себя
                        $targetperson = $DOF->storage('persons')->get_bu();
                    } else 
                    {// Редактирование достижения, получим владельца
                        $achievementin = $DOF->storage('achievementins')->get($id);
                        if( ! empty($achievementin) )
                        {
                            $targetperson = $DOF->storage('persons')->get($achievementin->userid);
                        }
                    }
                } else
                {// Целевой пользователь передан
                    $targetperson = $DOF->storage('persons')->get($personid);
                }
                
                if ( ! isset($targetperson->id) || ! isset($achievement->id) )
                {// Обязательные данные не определены
                    $addvars['success'] = 0;
                    redirect($DOF->url_im('achievements', '/edit_achievementinst.php', $addvars));
                }
                
                // Готовим объект достижения для сохранения
                $achievementinst = new stdClass();
                $achievementinst->id = $id;
                if( ! empty($userformresult['userdata']) )
                {
                    $achievementinst->data = serialize($userformresult['userdata']);
                }
                if( ! empty($userformresult['goaldeadline']) )
                {
                    $achievementinst->goaldeadline = $userformresult['goaldeadline'];
                }
                $achievementinst->achievementid = $achievement->id;
                $achievementinst->userid = (int)$targetperson->id;
                $id = $DOF->storage('achievementins')->save(
                    $achievementinst, 
                    ['create_goal' => $creategoal]
                );
                if ( $id )
                {// Успешное сохранение
                    $addvars['achinsavesuccess'] = 1;
                    redirect($DOF->url_im('achievements', '/my.php', $addvars));
                } else
                {// Ошибки
                    $addvars['success'] = 0;
                    redirect($DOF->url_im('achievements', '/edit_achievementinst.php', $addvars));
                }
            }
        }
    } else
    {// Ошибка при генерации формы
        $errors[] = $DOF->get_string('message_form_achievementins_render_form_error', 'achievements');
    }
}

if ( ! empty($errors) )
{// Есть ошибки
    // Печать шапки страницы
    $DOF->modlib('nvg')->print_header(NVG_MODE_PORTAL);
    
    foreach( $errors as $error )
    {
        echo $DOF->modlib('widgets')->error_message($error);
    }
    // Ссылка на возврат
    $errorlink = dof_html_writer::link(
            $backlink,
            $DOF->get_string('back', 'achievements')
    );
    echo dof_html_writer::tag('h4', $errorlink);
} else 
{// Ошибок нет
    // Печать шапки страницы
    $DOF->modlib('nvg')->print_header(NVG_MODE_PORTAL);
    if ( $success === 1 )
    {// Успешное завершение
        echo $DOF->modlib('widgets')->success_message(
                $DOF->get_string('message_form_achievementins_edit_save_suссess', 'achievements')
             );
    }
    
    if ( ! empty($achievementobj) && ! empty($achievementobj->userform) )
    {// Объект создан
        $achievementobj->userform->display();
    }
}

// Печать подвала
$DOF->modlib('nvg')->print_footer(NVG_MODE_PORTAL);

?>