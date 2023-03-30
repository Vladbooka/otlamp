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
 * Заблокировать/Разблокировать раздел достижений
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

// Добавление уровня навигации плагина
$DOF->modlib('nvg')->add_level(
        $DOF->get_string('admin_panel_title', 'achievements'),
        $DOF->url_im('achievements', '/admin_panel.php'),
        $addvars
);

// Парва доступа
$DOF->im('achievements')->require_access('admnistration');

// Формирование массива GET параметров
$addvars['id'] = $id;
$addvars['parentcat'] = optional_param('parentcat', 0, PARAM_INT);

// Ссылка на возврат
$backlink = $DOF->url_im('achievements', '/admin_panel.php', $addvars);

// Добавление уровня навигации плагина
$parent = $DOF->storage('achievementcats')->get($addvars['parentcat']);
if ( ! empty($parent) )
{// Родитель определен
    $DOF->modlib('nvg')->add_level(
        $parent->name,
        $backlink
    );
}

// Базовые переменные
$errors = [];
$confirmation = '';

$achievementcat = $DOF->storage('achievementcats')->get($id);
if ( empty($achievementcat) )
{// Раздел не найден
    $errors[] = $DOF->get_string('error_achievementcat_not_found', 'achievements');
} else 
{// Раздел найден
    // Действия в зависимости от текущего статуса
    switch ( $achievementcat->status )
    {
        case 'available' :
            // Права доступа
            $DOF->im('achievements')->require_access('category/hide', $id);
            
            if ( $confirm && empty($errors) )
            {// Заблокировать раздел
                // Блокировка раздела
                $result = $DOF->workflow('achievementcats')->change($id, 'notavailable');
                
                if ( empty($result) )
                {// Ошибка при блокировке
                    $errors[] = $DOF->get_string('error_achievementcat_hiding_error', 'achievements');
                } else 
                {// Успех
                    // Ссылка на страницу родителя блокируемого элемента
                    $somevars = $addvars;
                    unset($somevars['id']);
                    $somevars['parentcat'] = $achievementcat->parentid;
                    $somevars['catblocksuccess'] = 1;
                    $link = $DOF->url_im('achievements', '/admin_panel.php', $somevars);
                    
                    // Редирект по ссылке
                    redirect($link);
                }
            }
            
            // Добавление уровня навигации
            $DOF->modlib('nvg')->add_level(
                    $DOF->get_string('hide_achievementcat', 'achievements'),
                    $DOF->url_im('achievements', '/hide_category.php'), $addvars
            );
            // Языковая строка подтверждения действия
            $confirmation = $DOF->get_string('confirmation_hide_achievementcat', 'achievements');
            
            break;
        case 'notavailable' :
            // Права доступа
            $DOF->im('achievements')->require_access('category/show', $id);
            
            if ( $confirm && empty($errors) )
            {// Разблокировать раздел
                // Разблокировка раздела
                $result = $DOF->workflow('achievementcats')->change($id, 'available');
                
                if ( empty($result) )
                {// Ошибка при блокировке
                    $errors[] = $DOF->get_string('error_achievementcat_showing_error', 'achievements');
                } else
                {// Успех
                    // Ссылка на страницу родителя разблокируемого элемента
                    $somevars = $addvars;
                    unset($somevars['id']);
                    $somevars['parentcat'] = $achievementcat->parentid;
                    $somevars['catunblocksuccess'] = 1;
                    $link = $DOF->url_im('achievements', '/admin_panel.php', $somevars);
                    
                    // Редирект по ссылке
                    redirect($link);
                }
            }
            
            // Добавление уровня навигации
            $DOF->modlib('nvg')->add_level(
                    $DOF->get_string('show_achievementcat', 'achievements'),
                    $DOF->url_im('achievements', '/hide_category.php'), $addvars
            );
            // Языковая строка подтверждения
            $confirmation = $DOF->get_string('confirmation_show_achievementcat', 'achievements');
            
            break;
        default :
            $errors[] = $DOF->get_string('error_achievementcat_not_valid_status', 'achievements');
            break;
    }
}

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
    $linkyes = $DOF->url_im('achievements', '/hide_category.php', $addvars);

    // Форма подтверждения удаления
    $DOF->modlib('widgets')->notice_yesno($confirmation, $linkyes, $backlink);
}

// Печать подвала
$DOF->modlib('nvg')->print_footer(NVG_MODE_PORTAL);

?>