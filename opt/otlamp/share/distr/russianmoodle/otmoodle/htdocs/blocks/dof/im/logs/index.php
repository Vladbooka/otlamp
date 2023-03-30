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
 * Интерфейс логов
 *
 * @package    im
 * @subpackage logs
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// Подключение библиотек
require_once('lib.php');

GLOBAL $DOF;

// Начальны параметры
$html = '';

// Добавление уровня навигации
$DOF->modlib('nvg')->add_level(
        $DOF->get_string('title', 'logs'),
        $DOF->url_im('logs','/index.php', $addvars)
        );

// Получение GET-параметров
// Получение числа записей по умолчанию
$limitnumdefault = (int)$DOF->modlib('widgets')->get_limitnum_bydefault($addvars['departmentid']);
$limitnum = optional_param('limitnum', $limitnumdefault, PARAM_INT);
// Получение смещения
$limitfrom  = optional_param('limitfrom', '1', PARAM_INT);

// Формирование GET-параметров
$addvars['limitnum'] = $limitnum;
$addvars['limitfrom'] = $limitfrom;

// Проверка доступа
$DOF->storage('logs')->require_access('view');

// Получим объект для формирования пагинации
$pages = $DOF->modlib('widgets')->pages_navigation('logs', null, $limitnum, $limitfrom);

// Получим массив данных
$list = $DOF->storage('logs')->get_listing($pages->get_current_limitfrom() - 1, $pages->get_current_limitnum());

// Сформируем html код пагинации
$pages->count = count($DOF->storage('logs')->get_listing());
$pagination = $pages->get_navpages_list('/index.php', $addvars);

$html .= $DOF->im('logs')->show_table($list, $addvars);

// Печать шапки страницы
$DOF->modlib('nvg')->print_header(NVG_MODE_PORTAL);

echo $pagination;
echo $html;
echo $pagination;

// Печать подвала страницы
$DOF->modlib('nvg')->print_footer(NVG_MODE_PORTAL);
?>



