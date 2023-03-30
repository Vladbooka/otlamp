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
 * Панель управления дополнительными полями Деканата. Точка входа в плагин.
 *
 * @package    im
 * @subpackage customfields
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// Подключаем библиотеки
require_once('lib.php');
require_once('form.php');

// HTML-код старинцы
$html = '';
$changestatuserror = optional_param('changestatus_error', 0, PARAM_INT);
if( ! empty($changestatuserror) )
{
    $DOF->messages->add($DOF->get_string('changestatus_error', 'customfields'), 'error');
}
// Проверка базового права доступа к интерфейсу
$DOF->storage('customfields')->require_access('view');

// Добавление уровня навигации плагина
$DOF->modlib('nvg')->add_level(
    $DOF->get_string('page_list_name', 'customfields'),
    $DOF->url_im('customfields', '/list.php'),
    $addvars
);

// Вкладки
$html .= $DOF->im('customfields')->render_tabs('list', $addvars);

// Получение текущей персоны
$currentperson = $DOF->storage('persons')->get_bu(null, true);

// Проверка доступа к интерфейсу
if ( ! $DOF->storage('customfields')->is_access('view') )
{// Доступ к просмотру допполей закрыт
    $DOF->messages->add(
        $DOF->get_string('error_page_customfields_list_access_denied', 'customfields'),
        'error'
    );
} else
{// Доступ открыт
    
    // Создание нового дополнительного поля
    $cancreate = $DOF->storage('customfields')->is_access('create');
    if ( $cancreate )
    {// Пользователь может создавать новые дополнительные поля
        $url = $DOF->url_im('customfields', '/save.php', $addvars);
        $html .= dof_html_writer::link(
            $url,
            $DOF->get_string('create_customfield_link', 'customfields'),
            ['class' => 'btn btn-primary button dof_button']
        );
    }
    
    // Инициализация фильтра
    $url = $DOF->url_im('customfields','/list.php', $addvars);
    $customdata = new stdClass();
    $customdata->dof = $DOF;
    $customdata->addvars = $addvars;
    $filter = new dof_im_customfields_list($url, $customdata);
    // Обработчик фильтра
    $filter->process();
    // Отображение фильтра
    $html .= $filter->render();
    
    // Получение идентификаторов дополнительных полей на основе фильтра
    $itemids = (array)$filter->get_itemids();
    
    // Пагинация
    $pages = $DOF->modlib('widgets')->pages_navigation(
        'customfields',
        count($itemids),
        $addvars['limitnum'],
        $addvars['limitfrom']
    );
    
    // Добавление пагинации
    $html .= $pages->get_navpages_list('/list.php', $addvars);
}

// Печать шапки страницы
$DOF->modlib('nvg')->print_header(NVG_MODE_PORTAL);

print($html);

// Печать подвала страницы
$DOF->modlib('nvg')->print_footer(NVG_MODE_PORTAL);
?>