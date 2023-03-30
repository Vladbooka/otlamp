<?PHP
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

/*
 * Задачи, данные мной
 */

// Подключаем библиотеки
require_once('lib.php');

// Получаем пользователя
$person = $DOF->storage('persons')->get_bu();
// Получаем параметры для сортировки
$ordering = $DOF->im('crm')->get_sort_params();
// Какое количество строк таблицы выводить на экран
$limitnum = $DOF->modlib('widgets')->get_limitnum_bydefault();
$limitnum = (int)optional_param('limitnum', $limitnum, PARAM_INT);
// Начиная с какого номера записи показывать ее
$limitfrom    = (int)optional_param('limitfrom', '1', PARAM_INT);
// Формируем фрагмент sql для отображения не мусорных объектов
$sql = '';
$params = array();
// Добавляем фильтрацию по пользователю
$sql = 'purchaserpersonid = :purchaserpersonid';
$params['purchaserpersonid'] = $person->id;
// Получаем мусорные статусы для фильтрации
$junkstatuses = $DOF->workflow('tasks')->get_meta_list('junk');
// Если есть мусорные статусы - добавляем фильтрацию по ним
if (! empty($junkstatuses) )
{
    foreach ( $junkstatuses as $status => $name )
    {
        $sql .= ' AND ';
        // Добавляем статус счета
        $params['status'.$status] = $status;
        $sql .= 'status <> :status'.$status.'';
    }
}
// Получаем число задач для поддержки пагинации
$itemscount = count($DOF->storage('tasks')->get_records_select($sql, $params));

// Подключаем класс для вывода страниц
$pages = $DOF->modlib('widgets')->pages_navigation('crm', $itemscount, $limitnum, $limitfrom);

// Получаем список задач
$items = $DOF->storage('tasks')->get_records_select($sql, $params, $ordering, '*', $limitfrom - 1, $limitnum);

// Шапка
$DOF->modlib('nvg')->print_header(NVG_MODE_PORTAL);

// Печатаем вкладки
echo $DOF->im('crm')->print_tab($addvars, 'tasks', 'mytasks');

// Проверка доступа
if ( $DOF->storage('tasks')->is_access('create') )
{
    // Ссылка на создание новой задачи
    $svars = $addvars;
    $svars['action'] = 'create';
    $svars['taskid'] = 0;
    // Выводим ссылку на создание задачи
    echo html_writer::link(
        $DOF->url_im('crm','/tasks/action.php',$svars),
        $DOF->get_string('create_task', 'crm'),
        array(
                'title' => $DOF->get_string('create_task_title', 'crm'),
                'class' => 'create_link'
            )
        );
}
// Добавляем пагинацию к GET параметрам
$addvars['limitfrom'] = $limitfrom;
// Выводим пагинацию
echo $pages->get_navpages_list('/tasks/mytasks.php', $addvars).'<br />';

// Выводим таблицу
$DOF->im('crm')->print_table_task($items, 'mytasks', $addvars);

// Выводим пагинацию
echo '<br />'.$pages->get_navpages_list('/tasks/mytasks.php', $addvars);

// Печать подвала
$DOF->modlib('nvg')->print_footer(NVG_MODE_PORTAL);

?>