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
 * Редактирование шаблона достижений
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
$notificationdatachanged = optional_param('notoficationdatachanged', false, PARAM_BOOL);
$updatedeadlines = optional_param('updatedeadlines', false, PARAM_BOOL);

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
{// Создание шаблона
    // Права доступа
    $DOF->im('achievements')->require_access('achievement/create', $parentid);
    // Добавление уровня навигации плагина
    $DOF->modlib('nvg')->add_level(
            $DOF->get_string('create_achievement', 'achievements'),
            $DOF->url_im('achievements', '/edit_achievement.php'), $addvars);
} else
{// Редактирование шаблона
    $achievement = $DOF->storage('achievements')->get($id);
    if ( empty($achievement) )
    {// Шаблон не найден
        $errors[] = $DOF->get_string('error_achievement_not_found', 'achievements');
    } else 
    {
        // Права доступа
        $DOF->im('achievements')->require_access('achievement/edit', $id);
        // Добавление уровня навигации плагина
        $DOF->modlib('nvg')->add_level(
                $DOF->get_string('edit_achievement', 'achievements'),
                $DOF->url_im('achievements', '/edit_achievement.php'), $addvars);
    }
}

$success = optional_param('success', NULL, PARAM_INT);
if ( empty($success) && ! is_null($success) )
{// Ошибка
    $errors[] = $DOF->get_string('message_form_achievements_edit_save_error', 'achievements');
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
    $url = $DOF->url_im('achievements', '/edit_achievement.php', $addvars);
    
    if ( $notificationdatachanged || $updatedeadlines )
    {
        // изменились данные по уведомлениям
        if ( $updatedeadlines )
        {
            // обновим дедлайны достижений и отредиректим
            $addvars['updatedeadlines'] = 0;
            $DOF->storage('achievementins')->update_deadlines($achievement);
            $DOF->messages->add($DOF->get_string('success_update_deadlines', 'achievements'), DOF_MESSAGE_SUCCESS);
            redirect($url);
        }
        if ( $notificationdatachanged )
        {
            // форма подтверждения
            $addvars['updatedeadlines'] = 0;
            $linkno = $DOF->url_im('achievements', '/edit_achievement.php', $addvars);
            $addvars['updatedeadlines'] = 1;
            $linkyes = $DOF->url_im('achievements', '/edit_achievement.php', $addvars);
            
            // Печать шапки страницы
            $DOF->modlib('nvg')->print_header(NVG_MODE_PORTAL);
            
            // Форма подтверждения удаления
            $DOF->modlib('widgets')->notice_yesno($DOF->get_string('confirmation_update_deadlines', 'achievements'), $linkyes, $linkno);
        }
    } else 
    {
        // Сформируем дополнительные данные
        $customdata = new stdClass;
        $customdata->dof = $DOF;
        $customdata->id = $id;
        $customdata->addvars = $addvars;
        $customdata->departmentid = $parent->departmentid;
    
        // Сформируем форму
        $form = new dof_im_achievements_edit_form($url, $customdata);
    
        // Обработчик формы
        $form->process();
        
        // Создаем объект шаблона
        $opts = [];
        $system_rating_enabled = $DOF->storage('config')->
            get_config_value('system_rating_enabled', 'im', 'achievements', $parent->departmentid);
        $opts['rating_enabled'] = (bool)($system_rating_enabled);
        $system_moderation_enabled = $DOF->im('achievements')->is_access('moderation', $parent->departmentid);
        $opts['moderation_enabled'] = (bool)($system_moderation_enabled);
        $achievementobj = $DOF->storage('achievements')->object($id, $opts);
        if ( ! empty($achievementobj) )
        {// Объект создан
            // Создание формы настроек
            $achievementobj->settingsform($url, $customdata);
            if ( ! empty($achievementobj->settingsform) )
            {// Обработчик формы настроек
                $achievementobj->settingsform->process();
            }
        }
        
        // Печать шапки страницы
        $DOF->modlib('nvg')->print_header(NVG_MODE_PORTAL);
        
        if ( $success === 1 )
        {// Успешное завершение
            echo $DOF->modlib('widgets')->success_message(
                    $DOF->get_string('message_form_achievements_edit_save_suссess', 'achievements')
                 );
        }
        
        // Отобразим форму
        $form->display();
        if ( ! empty($achievementobj) && ! empty($achievementobj->settingsform) )
        {// Объект создан
            $achievementobj->settingsform->display();
        }
    }
}

// Печать подвала
$DOF->modlib('nvg')->print_footer(NVG_MODE_PORTAL);

?>