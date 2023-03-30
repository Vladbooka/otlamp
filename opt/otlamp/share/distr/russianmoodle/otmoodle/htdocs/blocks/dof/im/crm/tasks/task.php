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
// Получение id задачи
$id = required_param('taskid', PARAM_INT);

// Добавление уровня навигации
$DOF->modlib('nvg')->add_level($DOF->get_string('tasksforme', 'crm'), $DOF->url_im('crm','/tasks/index.php',$addvars));
$addvars['taskid'] = $id;
$DOF->modlib('nvg')->add_level($DOF->get_string('task_view', 'crm'), $DOF->url_im('crm','/tasks/task.php',$addvars));

// Проверяем, существует ли задача
if ( ! $item = $DOF->storage('tasks')->get($id) )
{
    $DOF->print_error('task_not_found', $DOF->url_im('tasks'), null, 'im', 'crm');
}

// Печать шапки страницы
$DOF->modlib('nvg')->print_header(NVG_MODE_PORTAL);

$DOF->storage('tasks')->require_access('view/owner',$id);

// Показываем таблицу со всеми сведениями о задаче
$DOF->im('crm')->display_task($item, $addvars);

// Подсчитываем число дочерних задач
$itemscount = $DOF->storage('tasks')->count_list(array('parentid' => $item->id));

// Если дочерние задачи есть - рисуем таблицу
if ($itemscount)
{
    /* Пагинация */
    // Получить число записей для вывода на странице
    $limitnum = $DOF->modlib('widgets')->get_limitnum_bydefault();
    $limitnum = (int)optional_param('limitnum', $limitnum, PARAM_INT);
    // Получить начальное смещение
    $limitfrom    = (int)optional_param('limitfrom', '1', PARAM_INT);
    
    // Подключаем класс для вывода страниц
    $pages = $DOF->modlib('widgets')->pages_navigation('crm', $itemscount, $limitnum, $limitfrom);
    
    /* Получение записей */
    // Получаем параметры для сортировки
    $ordering = $DOF->im('crm')->get_sort_params();
    
    // Получаем список задач из бд
    $childrentasks = $DOF->storage('tasks')->
        get_records(array('parentid' => $item->id), $ordering, '*', $limitfrom-1, $limitnum);
    // Печатаем заголовок
    echo $DOF->modlib('widgets')->print_heading(
            $DOF->get_string('children_tasks', 'crm'), '', 2, 'main', true);
    // Печатаем таблицу
    $DOF->im('crm')->print_table_task($childrentasks, 'task', $addvars);
}

// Печать подвала
$DOF->modlib('nvg')->print_footer(NVG_MODE_PORTAL);

?>