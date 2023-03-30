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
if ( $achievementins->status == 'archive' )
{// Статус не поддерживается
    $errors[] = $DOF->get_string('error_achievementins_archived', 'achievements');
}
if ( $achievementins->status == 'suspend' )
{// Статус не поддерживается
    $errors[] = $DOF->get_string('error_achievementins_suspend_to_archived', 'achievements');
}

// Права доступа
$DOF->im('achievements')->require_access('my');
$DOF->storage('achievementins')->require_access('archive', $id);

if ( $confirm && empty($errors) )
{// Удалить достижение
    $errors = $DOF->im('achievements')->archive_achievementin($id);
    if ( empty($errors) )
    {// Успех
    // Редирект по ссылке
        redirect($backlink);
    }
}

// Добавление уровня навигации
$addvars['id'] = $id;
$DOF->modlib('nvg')->add_level(
    $DOF->get_string('archive_achievementins', 'achievements'),
    $DOF->url_im('achievements', '/archive_achievementinst.php'), $addvars
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
    $linkyes = $DOF->url_im('achievements', '/archive_achievementinst.php', $addvars);
    // Языковая строка подтверждения
    $confirmation = $DOF->get_string('confirmation_archive_achievementins', 'achievements');
    
    // Форма подтверждения удаления
    $DOF->modlib('widgets')->notice_yesno($confirmation, $linkyes, $backlink);
}

// Печать подвала
$DOF->modlib('nvg')->print_footer(NVG_MODE_PORTAL);
