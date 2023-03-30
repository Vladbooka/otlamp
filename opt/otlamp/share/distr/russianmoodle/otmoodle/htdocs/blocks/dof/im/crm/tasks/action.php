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

// Отображение  задачи

require_once('lib.php');
require_once('form.php');

$action = required_param('action', PARAM_TEXT);
$depid = optional_param('departmentid', 0, PARAM_INT);

switch ($action)
{
    case 'create' : //  Создать новую задачу
        // Проверяем доступ
        $DOF->storage('tasks')->require_access('create');

        // Формируем массив для ссылок
        $addvars['action'] = $action;

        // Добавляем уровень навигации
        $DOF->modlib('nvg')->add_level($DOF->get_string('task_create', 'crm'),
                $DOF->url_im('crm','/tasks/action.php'),$addvars);

        // Инициализируем данные для формы
        $customdata = new stdClass();
        $customdata->dof = $DOF;
        $customdata->departmentid = $depid;
        $customdata->taskid = 0;

        // Создаем форму
        $form = new block_dof_im_crm_task_form($DOF->url_im('crm','/tasks/action.php', $addvars), $customdata);
        $form->process($addvars);

        // Шапка
        $DOF->modlib('nvg')->print_header(NVG_MODE_PORTAL);

        // Отобразить форму
        $form->display();
    break;
    case 'edit' :
        // Получаем параметры из GET
        $taskid = required_param('taskid', PARAM_INT);

        // Проверяем доступ
        $DOF->storage('tasks')->require_access('edit/owner', $taskid);

        // Получаем задачу
        $task = $DOF->storage('tasks')->get($taskid);

        // Формируем массив для ссылок
        $addvars['action'] = $action;
        $addvars['taskid'] = $taskid;
        // Добавляем уровень навигации
        $DOF->modlib('nvg')->add_level($DOF->get_string('task_edit', 'crm'),
                $DOF->url_im('crm','/tasks/action.php'),$addvars);

        // Инициализируем данные для формы
        $customdata = new stdClass();
        $customdata->dof = $DOF;
        $customdata->departmentid = $depid;
        $customdata->taskid = $taskid;

        // Создаем форму
        $form = new block_dof_im_crm_task_form($DOF->url_im('crm','/tasks/action.php', $addvars), $customdata);
        $form->process($addvars);
        // Устанавливаем данные по умолчанию
        $task->about = [
            'text' => $task->about ?? '',
            'format' => FORMAT_HTML
        ];
        $form->set_data($task);

        // Шапка
        $DOF->modlib('nvg')->print_header(NVG_MODE_PORTAL);

        // Отобразить форму
        $form->display();
    break;
    case 'delegate' : // Делегировать

        // Получаем параметры из GET
        $taskid = required_param('taskid', PARAM_INT);

        // Проверяем доступ
        $DOF->storage('tasks')->require_access('create', $taskid);

        // Массив параметров для ссылок
        $addvars['taskid'] = $taskid;
        $addvars['action'] = $action;

        // Получаем задачу - родителя
        $task = $DOF->storage('tasks')->get_record(array('id' => $taskid));
        if ( empty($task) )
        {
            $DOF->print_error(
                    $DOF->get_string('error_task_not_found', 'crm'),
                    $DOF->url_im('crm','/tasks/index.php', $addvars),
                    null,
                    'im',
                    'crm'
            );
        }
        $customdata = new stdClass();
        $customdata->dof = $DOF;
        $customdata->departmentid = $depid;
        $customdata->taskid = $taskid;

        $form = new block_dof_im_crm_delegatetask_form($DOF->url_im('crm','/tasks/action.php', $addvars), $customdata);
        $form->process($addvars);
        $DOF->modlib('nvg')->print_header(NVG_MODE_PORTAL);
        $form->display();
        $DOF->im('crm')->display_task($task, $addvars, false);
    break;
    case 'children_task' : // Создать подзадачу
        // Получаем параметры из GET
        $taskid = required_param('taskid', PARAM_INT);

        // Проверяем доступ
        $DOF->storage('tasks')->require_access('create', $taskid);

        $addvars['taskid'] = $taskid;
        $addvars['action'] = $action;

        // Получаем задачу - родителя
        $task = $DOF->storage('tasks')->get_record(array('id' => $taskid));
        if ( empty($task) )
        {
            $DOF->print_error(
                    $DOF->get_string('error_task_not_found', 'crm'),
                    $DOF->url_im('crm','/tasks/index.php', $addvars),
                    null,
                    'im',
                    'crm'
            );
        }

        $customdata = new stdClass();
        $customdata->dof = $DOF;
        $customdata->departmentid = $depid;
        $customdata->taskid = $taskid;

        $form = new block_dof_im_crm_childrentask_form($DOF->url_im('crm','/tasks/action.php', $addvars), $customdata);
        $form->process($addvars);
        $DOF->modlib('nvg')->print_header(NVG_MODE_PORTAL);
        $form->display();
        $DOF->im('crm')->display_delegatetask($task, $addvars);
    break;
    case 'delete' :
        // Получаем параметры из GET
        $taskid = required_param('taskid', PARAM_INT);

        // Проверяем доступ
        $DOF->storage('tasks')->require_access('delete', $taskid);

        // Массивы GET параметров
        $somevars = $addvars;
        $addvars['taskid'] = $taskid;
        $addvars['action'] = $action;

        $confirmed = optional_param('confirm', 0, PARAM_INT);
        if ( $confirmed ) {
            if ( $DOF->im('crm')->delete_task($taskid) )
            {// Обновление прошло успешно
                $somevars['success'] = 1;
            } else
            {// Обновление завершилось с ошибкой
                $somevars['success'] = 0;
            }
            redirect($DOF->url_im('crm','/tasks/index.php', $somevars));
            $DOF->modlib('nvg')->print_header(NVG_MODE_PORTAL);
        } else
        {
            // Шапка
            $DOF->modlib('nvg')->print_header(NVG_MODE_PORTAL);
            // Готовим данные для отображения страницы подтверждения действия
            $confirm_delete_task = $DOF->get_string('confirmation_delete_task','crm');
            $addvars['confirm'] = 1;
            $linkyes = $DOF->url_im('crm', '/tasks/action.php', $addvars );
            $somevars['taskid'] = $taskid;
            $linkno = $DOF->url_im('crm', '/tasks/task.php', $somevars );
            $DOF->modlib('widgets')->notice_yesno($confirm_delete_task, $linkyes, $linkno);
        }
    break;
    case 'solved' :
        // Получаем параметры из GET
        $taskid = required_param('taskid', PARAM_INT);

        // Проверяем доступ
        $DOF->workflow('tasks')->require_access('edit/owner', $taskid);

        $addvars['taskid'] = $taskid;
        $addvars['action'] = $action;

        // Получаем задачу - родителя
        $task = $DOF->storage('tasks')->get_record(array('id' => $taskid));
        if ( empty($task) )
        {
            $DOF->print_error(
                    $DOF->get_string('error_task_not_found', 'crm'),
                    $DOF->url_im('crm','/tasks/index.php', $addvars),
                    null,
                    'im',
                    'crm'
            );
        }

        $customdata = new stdClass();
        $customdata->dof = $DOF;
        $customdata->departmentid = $depid;
        $customdata->taskid = $taskid;

        $form = new block_dof_im_crm_task_complete($DOF->url_im('crm','/tasks/action.php', $addvars), $customdata);
        $form->process($addvars);
        $DOF->modlib('nvg')->print_header(NVG_MODE_PORTAL);
        $form->display();
        $DOF->im('crm')->display_delegatetask($task, $addvars);
        break;

    default:
        // Нет такой задачи
        $DOF->print_error(
            $DOF->get_string('error_action_not_defined', 'crm'),
            $DOF->url_im('crm','/tasks/index.php', $addvars),
            null,
            'im',
            'crm'
        );
    break;
}
//печать подвала
$DOF->modlib('nvg')->print_footer(NVG_MODE_PORTAL);

?>