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
 * Панель управления плагинами портфолио
 * 
 * @package    im
 * @subpackage achievements
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// Подключение библиотек
require_once('lib.php');

// Требуются права администратора
$DOF->im('achievements')->require_access('admnistration');

// Печать шапки страницы
$DOF->modlib('nvg')->print_header(NVG_MODE_PORTAL);

// Вывод системных сообщений
$messages = $DOF->im('achievements')->messages();
if ( ! empty($messages) )
{
    foreach ( $messages as $message )
    {
        echo $DOF->modlib('widgets')->success_message($message);
    }
}
// Ссылка на плагин фильтра
$link = dof_html_writer::link(
            $DOF->url_im('achievements', '/plugins/usersfilter/settings.php', $addvars),
            $DOF->get_string('plugin_userfilter', 'achievements')
    );
echo dof_html_writer::tag('h4', $link);

// Ссылка на плагин пользовательской информации
$link = dof_html_writer::link(
        $DOF->url_im('achievements', '/plugins/userinfo/settings.php', $addvars),
        $DOF->get_string('plugin_userinfo', 'achievements')
        );
echo dof_html_writer::tag('h4', $link);

// Печать подвала
$DOF->modlib('nvg')->print_footer(NVG_MODE_PORTAL);

?>