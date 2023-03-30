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
 * Интерфейс управления причинами отсутствия. Языковые переменные.
 *
 * @package    storage
 * @subpackage schabsenteeism
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// Подключение библиотек
require_once('lib.php');
require_once('form.php');

// Начальны параметры
$html = '';

// Получение GET-параметров
// Получение смещения
$addvars['limitfrom']  = optional_param('limitfrom', 1, PARAM_INT);
if ( $addvars['limitfrom'] < 1 )
{
    $addvars['limitfrom'] = 1;
}

// Проверка доступа
$DOF->storage('schabsenteeism')->require_access('viewdesk');

// Получение списка причин для пользователя
$person = $DOF->storage('persons')->get_bu(null, true);
$list = $DOF->modlib('journal')->get_manager('schabsenteeism')->
    get_list($person->id, $addvars['departmentid']);
    
// Генерация пагинации
$pages = $DOF->modlib('widgets')->pages_navigation(
    'schabsenteeism', count($list), $addvars['limitnum'], $addvars['limitfrom']);
$pagination = $pages->get_navpages_list('/index.php', $addvars);

// Текущая страница
$list = array_slice($list, $addvars['limitfrom'] - 1, $addvars['limitnum'], true);

// Ссылка на создание причины отсутствия
if ( $DOF->storage('schabsenteeism')->is_access('create', null, null, $addvars['departmentid']) )
{// Право на создание есть
    $link = dof_html_writer::link(
        $DOF->url_im('schabsenteeism', '/save.php', $addvars), 
        $DOF->get_string('create_link', 'schabsenteeism'), 
        ['class' => 'dof_button btn button']
    );
    $html .= html_writer::div($link);
}

$html .= $pagination;
$html .= $DOF->im('schabsenteeism')->show_table($list, $addvars);
$html .= $pagination;

// Печать шапки страницы
$DOF->modlib('nvg')->print_header(NVG_MODE_PORTAL);

echo $html;

// Печать подвала страницы
$DOF->modlib('nvg')->print_footer(NVG_MODE_PORTAL);
?>



