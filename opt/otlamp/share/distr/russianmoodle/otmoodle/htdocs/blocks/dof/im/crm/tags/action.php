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
 * Страница действий.
 * Отображает функционал страницы
 * в зависимости от переданной задачи action
 */

// Подключаем библиотеки
require_once('lib.php');
require_once('form.php');

// Получаем данные
$action = required_param('action', PARAM_TEXT);
$depid = optional_param('departmentid', 0, PARAM_INT);

switch ($action)
{
    case 'showtag' : // Отобразить детальную информацию о теге

        // Получаем ID тега
        $tagid = required_param('tagid', PARAM_INT);

        // Проверка доступа
        $DOF->storage('tags')->require_access('view/owner', $tagid, null, $depid);

        // GET параметры для хлебных крошек
        $addvars['action'] = $action;

        // добавляем уровень навигации
        $DOF->modlib('nvg')->add_level($DOF->get_string('tag','crm'), $DOF->url_im('crm','/tags/alltags.php'), $addvars);

        // Шапка
        $DOF->modlib('nvg')->print_header(NVG_MODE_PORTAL);

        // Отобразим страницу с информацией о теге
        $DOF->im('crm')->print_tag($tagid, $addvars);

        break;
     case 'newtag' : // Отобразить форму добавления тега

        // Проверяем доступ
        $DOF->storage('tags')->require_access('create');

        // Получаем GET параметры
        $success = optional_param('success', 0, PARAM_INT);
        $tagclass = optional_param('tagclass', '', PARAM_TEXT);

        // Формируем массив для ссылок
        $addvars['action'] = $action;

        // Добавляем уровень навигации
        $DOF->modlib('nvg')->add_level($DOF->get_string('tag_create', 'crm'),
               $DOF->url_im('crm','/tags/action.php'),$addvars);

        // Инициализируем данные для формы
        $customdata = new stdClass();
        $customdata->dof = $DOF;
        $customdata->departmentid = $depid;
        $customdata->tagclass = $tagclass;
        $customdata->tagid = 0;

        // В зависимости от того, передан класс или нет
        if ( empty($tagclass) )
        {// Форма выбора класса
            $form = new block_dof_im_crm_tag_form_select($DOF->url_im('crm','/tags/action.php', $addvars), $customdata);
        } else
        {// Форма добавления тега
            $addvars['tagclass'] = $tagclass;
            $form = new block_dof_im_crm_tag_form($DOF->url_im('crm','/tags/action.php', $addvars), $customdata);
        }

        $form->process($addvars);

        // Шапка
        $DOF->modlib('nvg')->print_header(NVG_MODE_PORTAL);
        // Отобразить форму
        $form->display();
        break;
     case 'edittag' : // Редактировать тег

        // Получаем GET параметры
        $success = optional_param('success', 0, PARAM_INT);
        $tagid = required_param('tagid', PARAM_INT);

        // Проверяем доступ
        $DOF->storage('tags')->require_access('edit/owner', $tagid);

        if ( ! $tag = $DOF->storage('tags')->get($tagid) AND $tagid > 0 )
        {// если тег не найден, выведем ошибку
            $DOF->modlib('nvg')->print_header(NVG_MODE_PORTAL);
            $DOF->print_error('tag_not_found', $DOF->url_im('crm','/tags/alltags.php', $addvars), $tagid, 'im', 'crm');
        }

        // Формируем массив для ссылок
        $addvars['action'] = $action;
        $addvars['tagid'] = $tagid;

        // Добавляем уровень навигации
        $DOF->modlib('nvg')->add_level($DOF->get_string('form_tag_edit', 'crm'),
                $DOF->url_im('crm','/tags/action.php'),$addvars);

        $customdata = new stdClass;
        $customdata->dof = $DOF;
        $customdata->departmentid = $depid;
        $customdata->tagid = $tagid;
        $customdata->tagclass = $tag->class;

        // подключаем методы вывода формы
        $form = new block_dof_im_crm_tag_form($DOF->url_im('crm','/tags/action.php', $addvars), $customdata);
        // обрабатываем пришедшие данные (если нужно)
        $form->process($addvars);
        // Устанавливаем данные по умолчанию
        $tag->about = [
            'text' => $tag->about ?? '',
            'format' => FORMAT_HTML
        ];
        $form->set_data($tag);

        $DOF->modlib('nvg')->print_header(NVG_MODE_PORTAL);

        $form->display();

        break;

     case 'deletetag' : // Удалить тег

        // Получаем GET параметры
        $success = optional_param('success', 0, PARAM_INT);
        $tagid = required_param('tagid', PARAM_INT);
        $confirmed = optional_param('confirm', 0, PARAM_INT);

        // Проверяем доступ
        $DOF->storage('tags')->require_access('delete');

        if ($confirmed) {
            // Пытаемся каскадно удалить тег
            if ( $DOF->im('crm')->delete_tag($tagid) )
            {// Удаление прошло успешно
                $addvars['success'] = 1;
            } else
            {
                $addvars['success'] = 0;
            }
            redirect ($DOF->url_im('crm','/tags/alltags.php', $addvars));
            $DOF->modlib('nvg')->print_header(NVG_MODE_PORTAL);
        } else
        {
            // Формируем GET параметры
            $somevars = $addvars;
            $addvars['tagid'] = $tagid;
            $addvars['action'] = $action;
            $addvars['confirm'] = 1;

            $DOF->modlib('nvg')->print_header(NVG_MODE_PORTAL);

            $confirm_delete_tag = $DOF->get_string('confirmation_delete_tag','crm');
            $linkyes = $DOF->url_im('crm', '/tags/action.php', $addvars);
            $linkno = $DOF->url_im('crm', '/tags/alltags.php', $somevars);
            $DOF->modlib('widgets')->notice_yesno($confirm_delete_tag, $linkyes, $linkno);
        }
        break;
     default: // Страница ошибки

        // Печатаем шапку
        $DOF->modlib('nvg')->print_header(NVG_MODE_PORTAL);
        // Печатаем ошибку - такая задача не найдена
        $DOF->print_error('invalid_task', $DOF->url_im('crm','/tags/alltags.php', $addvars), null, 'im', 'crm');
}

//печать подвала
$DOF->modlib('nvg')->print_footer(NVG_MODE_PORTAL);

?>