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
 * Архивация пользовательского достижения
 *
 * @package    im
 * @subpackage achievements
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// Подключаем библиотеки
require_once('lib.php');

// Получение GET параметров
$id = optional_param('id', 0, PARAM_INT);
$confirm = optional_param('confirm', 0, PARAM_INT);
$personid = optional_param('personid', 0, PARAM_INT);

if ( $personid > 0 )
{
    $addvars['personid'] = $personid;
}

// Добавление уровня навигации плагина
$DOF->modlib('nvg')->add_level(
    $DOF->get_string('mypage_title', 'achievements'),
    $DOF->url_im('achievements', '/my.php'),
    $addvars
);

// Ссылка на возврат
$backlink = $DOF->url_im('achievements', '/my.php', $addvars);

$errors = [];
$achievementins = $DOF->storage('achievementins')->get($id);
if ( empty($achievementins) )
{// Достижение не найдено
    $errors[] = $DOF->get_string('error_achievementinst_not_found', 'achievements');
}

// флаг о том, что достижение было в одном из статусов цели
$hasgoalstatus = false;
if ( $DOF->storage('statushistory')->has_status('storage', 'achievementins', $id, array_keys($DOF->workflow('achievementins')->get_meta_list('goal_real'))) )
{
    $hasgoalstatus = true;
}

$ach = $DOF->storage('achievements')->get($achievementins->achievementid, '*', MUST_EXIST);
$cat = $DOF->storage('achievementcats')->get($ach->catid, '*', MUST_EXIST);
// получение объекта класса шаблона
$achievementobj = $DOF->storage('achievements')->object($ach->id);

// получение доступных статусов для достижения
if ( ($achievementins->status != 'notavailable') || !$hasgoalstatus || $achievementobj->is_autocompletion()  )
{// статус не поддерживается
    $errors[] = $DOF->get_string('error_achievementins_returntogoal', 'achievements');
}

// проверка прав на модерацию достижения
$access = ($DOF->im('achievements')->is_access('achievementins/moderate', $achievementins->id) ||
        $DOF->im('achievements')->is_access('achievementins/moderate_category', $cat->id) ||
        $DOF->im('achievements')->is_access('achievementins/moderate_except_myself', $achievementins->id));
if ( empty($access) )
{// Доступа нет
    $errors[] = $DOF->get_string('error_achievementinst_moderation_access', 'achievements');
}

if ( $confirm && empty($errors) )
{// Удалить достижение
    if ( $DOF->storage('achievementins')->achievementin_return_to_goal($achievementins) )
    {
        // успех
        $DOF->messages->add($DOF->get_string('success_achievementinst_returntogoal', 'achievements'), DOF_MESSAGE_SUCCESS);
    } else 
    {
        // неудача
        $DOF->messages->add($DOF->get_string('fail_achievementinst_returntogoal', 'achievements'), DOF_MESSAGE_SUCCESS);
    }
    redirect($backlink);
}

// Добавление уровня навигации
$addvars['id'] = $id;
$DOF->modlib('nvg')->add_level(
    $DOF->get_string('returntogoal_achievementins', 'achievements'),
    $DOF->url_im('achievements', '/returntogoal_achievementinst.php'), $addvars
);

// Печать шапки
$DOF->modlib('nvg')->print_header(NVG_MODE_PORTAL);

if ( ! empty($errors) )
{// Есть ошибки
    // Выведем ошибки
    foreach ( $errors as $error )
    {// Распечатаем каждую
        echo $DOF->modlib('widgets')->error_message($error);
    }
    // Ссылка на возврат
    $errorlink = dof_html_writer::link(
        $backlink,
        $DOF->get_string('back', 'achievements')
    );
    echo dof_html_writer::tag('h4', $errorlink);
} else
{// Отобразим форму подтверждения
    // Ссылка на подтверждение
    $addvars['confirm'] = 1;
    $linkyes = $DOF->url_im('achievements', '/returntogoal_achievementinst.php', $addvars);
    // Языковая строка подтверждения
    $confirmation = $DOF->get_string('confirmation_returntogoal_achievementins', 'achievements');
    $DOF->modlib('widgets')->notice_yesno($confirmation, $linkyes, $backlink);
}

// Печать подвала
$DOF->modlib('nvg')->print_footer(NVG_MODE_PORTAL);
