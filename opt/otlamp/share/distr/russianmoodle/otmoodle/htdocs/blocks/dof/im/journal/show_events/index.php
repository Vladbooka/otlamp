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
 * Список событий. Точка входа в сабинтерфейс.
 *
 * @package    im
 * @subpackage journal
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// Подключение библиотек
require_once('lib.php');

// Проверка прав доступа
$DOF->require_access('view');

// Уведомление о часовой зоне пользователя
$usertimezone = $DOF->storage('persons')->get_usertimezone_as_number();
$DOF->messages->add(
    $DOF->modlib('ig')->igs('you_from_timezone', dof_usertimezone($usertimezone)),
    'notice'
);

// Шапка страницы
$DOF->modlib('nvg')->print_header(NVG_MODE_PORTAL);

// Получение списка секций, отображаемых на текущей странице
$pagesectionscfg = $DOF->plugin_path('im', 'journal', '/cfg/main_events.php');
// Отображение секций в соответствие с файлом конфигурации интерфейса
$DOF->modlib('nvg')->print_sections($pagesectionscfg);

// Подвал страницы
$DOF->modlib('nvg')->print_footer(NVG_MODE_PORTAL);
?>