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
 * Страница модерации
 *
 * @package    im
 * @subpackage achievements
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// Подключаем библиотеки
require_once('lib.php');

// Массив ошибок
$errors = [];

// Получение GET параметров
$id = optional_param('id', 0, PARAM_INT);
$confirm = optional_param('confirm', 0, PARAM_INT);
$additionalid = optional_param('additionalid', NULL, PARAM_INT);
$additionalname = optional_param('additionalname', NULL, PARAM_INT);
$additionalid2 = optional_param('additionalid2', NULL, PARAM_INT);
$personid = optional_param('personid', 0, PARAM_INT);
$filter = optional_param('filter', NULL, PARAM_RAW);
$limitfrom = optional_param('limitfrom', NULL, PARAM_INT);
$limitnum = optional_param('limitnum', NULL, PARAM_INT);
$confirmall = optional_param('confirmall', false, PARAM_BOOL);

if ( $personid > 0 )
{
    $addvars['personid'] = $personid;
}
if( ! empty($filter) )
{
    $addvars['filter'] = $filter;
}
if( ! empty($limitfrom) )
{
    $addvars['limitfrom'] = $limitfrom;
}
if( ! empty($limitnum) )
{
    $addvars['limitnum'] = $limitnum;
}
if( ! empty($id) )
{
    $addvars['lastmoderated'] = $id;
}
if ( ! empty($confirmall) )
{
    $addvars['confirmall'] = $confirmall;
}

// Получение достижения
$instance = $DOF->storage('achievementins')->get($id);
$backlinkaddvars = $addvars;
if ( empty($instance) )
{// Достижение не найдено
    $errors[] = $DOF->get_string('error_achievementins_not_found', 'achievements');
} else 
{// Достижние получено
    $personinst = $instance->userid;
    $backlinkaddvars['personid'] = $personinst;
}
if( $instance->status == 'archived' )
{// Достижение в архиве
    $errors[] = $DOF->get_string('error_achievementins_is_archived', 'achievements');
}
$backlink = $DOF->url_im('achievements', '/my.php', $backlinkaddvars);
// Добавление уровня навигации
$DOF->modlib('nvg')->add_level(
    $DOF->get_string('mypage_title', 'achievements'),
    $backlink
);

$ach = $DOF->storage('achievements')->get($instance->achievementid, '*', MUST_EXIST);
$cat = $DOF->storage('achievementcats')->get($ach->catid, '*', MUST_EXIST);
// получение объекта класса шаблона
$achievementobj = $DOF->storage('achievements')->object($ach->id);
if ( $achievementobj->is_autocompletion() )
{
    // автоматическое подтверждение достижения
    // вернем на страницу портфолио
    redirect($backlink);
}

// проверка прав на модерацию достижения
// при этом достижение в статусе "достижения", а не "цели"
$access = array_key_exists($instance->status, $DOF->workflow('achievementins')->get_meta_list('achievement_real')) && 
    ($DOF->im('achievements')->is_access('achievementins/moderate', $instance->id) ||
        $DOF->im('achievements')->is_access('achievementins/moderate_category', $cat->id) ||
            $DOF->im('achievements')->is_access('achievementins/moderate_except_myself', $instance->id));
if ( empty($access) )
{// Доступа нет
    $errors[] = $DOF->get_string('error_achievementinst_moderation_access', 'achievements');
}

if ( $confirm && empty($errors) )
{
    // Передача модерации классу
    $options = [];
    $options['additionalid'] = $additionalid;
    $options['additionalname'] = $additionalname;
    $options['additionalid2'] = $additionalid2;
    $options['confirmall'] = $confirmall;
    $result = $DOF->storage('achievementins')->moderate_confirm($id, $options);

    if ( empty($result) )
    {// Ошибка
        $errors[] = $DOF->get_string('error_achievementins_moderation_error', 'achievements');
    } else 
    {// Успех
        // Редирект по ссылке
        redirect($backlink);
    }
}

// Добавление уровня навигации
$addvars['id'] = $id;
if ( ! is_null($additionalid) )
{
    $addvars['additionalid'] = $additionalid;
}
if ( ! is_null($additionalname) )
{
    $addvars['additionalname'] = $additionalname;
}
if ( ! is_null($additionalid2) )
{
    $addvars['additionalid2'] = $additionalid2;
}
$DOF->modlib('nvg')->add_level(
        $DOF->get_string('moderation_achievementin', 'achievements'),
        $DOF->url_im('achievements', '/moderation.php'), $addvars
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
    $confirmaddvars = $addvars; 
    $confirmaddvars['confirm'] = 1;
    $linkyes = $DOF->url_im('achievements', '/moderation.php', $confirmaddvars);
    if ( $additionalid2 == 1 )
    {// Языковая строка подтверждения
        $confirmation = $DOF->get_string('confirmation_moderation_achievementin_deconfirm', 'achievements');
    } else 
    {
        // Языковая строка подтверждения
        $confirmation = $DOF->get_string('confirmation_moderation_achievementin', 'achievements');
    }

    // Форма подтверждения удаления
    $DOF->modlib('widgets')->notice_yesno($confirmation, $linkyes, $backlink, $confirmaddvars, $backlinkaddvars);
}

// Печать подвала
$DOF->modlib('nvg')->print_footer(NVG_MODE_PORTAL);

?>