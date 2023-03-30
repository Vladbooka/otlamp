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
 * Удалить раздел достижений
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

// Сформируем массив GET параметров
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

$errors = [];
$achievementcat = $DOF->storage('achievementcats')->get($id);
if ( empty($achievementcat) )
{// Раздел не найден
    $errors[] = $DOF->get_string('error_achievementcat_not_found', 'achievements');
}
if ( $achievementcat->status == 'deleted' )
{// Статус не поддерживается
    $errors[] = $DOF->get_string('error_achievementcat_deleted', 'achievements');
}

// Права доступа
$DOF->im('achievements')->require_access('admnistration');
$DOF->im('achievements')->require_access('category/delete', $id);

if ( $confirm && empty($errors) )
{// Удалить раздел
    // Удаление раздела
    $errors = $DOF->im('achievements')->delete_category($id);
    if ( empty($errors) )
    {// Успех
        // Удалим записи настроек категорий
        $conditions['plugintype'] = 'storage';
        $conditions['plugincode'] = 'achievementcats';
        $conditions['objectid'] = $id;
        $DOF->storage('cov')->delete_records($conditions);
        // Очистим кеш категорий
        $DOF->storage('achievementcats')->del_categories_config_cache();
        // Ссылка на страницу родителя блокируемого элемента
        $somevars = $addvars;
        $somevars['parentcat'] = $achievementcat->parentid;
        $somevars['catdeletesuccess'] = 1;
        $link = $DOF->url_im('achievements', '/admin_panel.php', $somevars);
        // Редирект по ссылке
        redirect($link);
    }
}

// Добавление уровня навигации
$addvars['id'] = $id;
$DOF->modlib('nvg')->add_level(
        $DOF->get_string('delete_achievementcat', 'achievements'),
        $DOF->url_im('achievements', '/delete_category.php'), $addvars
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
    $somevars = $addvars;
    $somevars['parentcat'] = $achievementcat->parentid;
    $link = $DOF->url_im('achievements', '/admin_panel.php', $somevars);
    $errorlink = dof_html_writer::link(
            $link,
            $DOF->get_string('back', 'achievements')
    );
    echo dof_html_writer::tag('h4', $errorlink);
} else 
{// Отобразим форму подтверждения
    // Ссылка на подтверждение
    $addvars['confirm'] = 1;
    $linkyes = $DOF->url_im('achievements', '/delete_category.php', $addvars);
    // Языковая строка подтверждения
    $confirmation = $DOF->get_string('confirmation_delete_achievementcat', 'achievements');
    
    // Форма подтверждения удаления
    $DOF->modlib('widgets')->notice_yesno($confirmation, $linkyes, $backlink);
}

// Печать подвала
$DOF->modlib('nvg')->print_footer(NVG_MODE_PORTAL);

?>