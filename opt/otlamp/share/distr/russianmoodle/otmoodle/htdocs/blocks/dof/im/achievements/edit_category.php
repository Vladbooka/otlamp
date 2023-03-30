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
 * Редактирование раздела достижений
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
$parentid = optional_param('parentcat', 0, PARAM_INT);
if ( isset($addvars['departmentid']) )
{
    $departmentid = $addvars['departmentid'];
} else
{
    $departmentid = optional_param('departmentid', 0, PARAM_INT);
}


// Добавление уровня навигации плагина
$DOF->modlib('nvg')->add_level(
        $DOF->get_string('admin_panel_title', 'achievements'),
        $DOF->url_im('achievements', '/admin_panel.php', $addvars)
);

// Формирование массива GET параметров
$addvars['parentcat'] = $parentid;

// Дополнительные переменные
$errors = [];
$backlink = $DOF->url_im('achievements', '/admin_panel.php', $addvars);

$parent = $DOF->storage('achievementcats')->get($parentid);
if ( ! empty($parent) )
{// Родитель определен, добавим ссылку на страницу
    // Добавление уровня навигации плагина
    $DOF->modlib('nvg')->add_level(
        $parent->name,
        $backlink
    );
}

// Права доступа
$DOF->im('achievements')->require_access('admnistration');

$addvars['id'] = $id;

// Права доступа
if ( empty($id) )
{// Создание раздела
    // Права доступа
    $DOF->im('achievements')->require_access('category/create', $parentid);
    // Добавление уровня навигации плагина
    $DOF->modlib('nvg')->add_level(
            $DOF->get_string('create_achievementcat', 'achievements'),
            $DOF->url_im('achievements', '/edit_category.php'), $addvars);
} else
{// Редактирование раздела
    $achievementcat = $DOF->storage('achievementcats')->get($id);
    if ( empty($achievementcat) )
    {// Раздел не найден
        $errors[] = $DOF->get_string('error_achievementcat_not_valid_status', 'achievements');
    } else 
    {
        // Права доступа
        $DOF->im('achievements')->require_access('category/edit', $id);
        // Добавление уровня навигации плагина
        $DOF->modlib('nvg')->add_level(
                $DOF->get_string('edit_achievementcat', 'achievements'),
                $DOF->url_im('achievements', '/edit_category.php'), $addvars);
    }
}

$success = optional_param('success', NULL, PARAM_INT);
if ( empty($success) && ! is_null($success) )
{// Ошибка
    $errors[] = $DOF->get_string('message_form_achievementcats_edit_save_error', 'achievements');
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

    // Сформируем url формы
    $url = $DOF->url_im('achievements', '/edit_category.php', $addvars);

    // Сформируем дополнительные данные
    $customdata = new stdClass;
    $customdata->dof = $DOF;
    $customdata->id = $id;
    $customdata->departmentid = $departmentid;
    $customdata->addvars = $addvars;

    // Сформируем форму
    $form = new dof_im_achievementcats_edit_form($url, $customdata);

    // Обработчик формы
    $form->process();
    
    // Печать шапки страницы
    $DOF->modlib('nvg')->print_header(NVG_MODE_PORTAL);
    
    if ( $success === 1 )
    {// Успешное завершение
        echo $DOF->modlib('widgets')->success_message(
                $DOF->get_string('message_form_achievementcats_edit_save_suссess', 'achievements')
             );
    }
    
    // Отобразим форму
    $form->display();
}

// Печать подвала
$DOF->modlib('nvg')->print_footer(NVG_MODE_PORTAL);

?>