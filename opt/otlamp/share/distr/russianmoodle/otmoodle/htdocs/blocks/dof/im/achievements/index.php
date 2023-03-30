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
 * Точка входа в плагин
 *
 * @package    im
 * @subpackage achievements
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// Подключаем библиотеки
require_once('lib.php');

// Печать шапки страницы
$DOF->modlib('nvg')->print_header(NVG_MODE_PORTAL);

if ( $DOF->im('achievements')->is_access('admnistration') )
{// Доступ к панели управления достижениями
    // Добавление ссылки на панель администрирования
    $link = dof_html_writer::link(
            $DOF->url_im('achievements', '/admin_panel.php', $addvars),
            $DOF->get_string('admin_panel_title', 'achievements')
    );
    echo dof_html_writer::tag('h4', $link);
    
    // Добавление ссылки на панель управления плагинами портфолио
    $link = dof_html_writer::link(
            $DOF->url_im('achievements', '/plugins/index.php', $addvars),
            $DOF->get_string('page_plugins_name', 'achievements')
    );
    echo dof_html_writer::tag('h4', $link);
}

if ( $DOF->im('achievements')->is_access('control_panel') )
{// Доступ к панели управления портфолио пользователей
    // Добавление ссылки на панель
    $link = dof_html_writer::link(
            $DOF->url_im('achievements', '/moderator_panel.php', $addvars),
            $DOF->get_string('moderator_panel_title', 'achievements')
    );
    echo dof_html_writer::tag('h4', $link);
}

if ( $DOF->im('achievements')->is_access('view_reports') )
{// Доступ к панели управления портфолио пользователей
    // Добавление ссылки на панель
    $link = dof_html_writer::link(
        $DOF->url_im('achievements', '/reports.php', $addvars),
        $DOF->get_string('reports_title', 'achievements')
        );
    echo dof_html_writer::tag('h4', $link);
}

if ( $DOF->im('achievements')->is_access('my') )
{// Доступ к панели добавления своих достижений
    // Добавление ссылки на панель своих достижений
    $link = dof_html_writer::link(
            $DOF->url_im('achievements', '/my.php', $addvars),
            $DOF->get_string('my_portfolio', 'achievements')
    );
    echo dof_html_writer::tag('h4', $link);
}

// Рейтинг
$currentperson = $DOF->storage('persons')->get_bu(NULL, true);
$system_rating_enabled = $DOF->storage('config')->get_config_value(
    'system_rating_enabled', 
    'im', 
    'achievements', 
    $addvars['departmentid']
);
if ( $system_rating_enabled )
{// Рейтинг включен в подразделении
    if ( $DOF->im('achievements')->is_access('user_rating_view', $currentperson->id, $currentperson->mdluser) )
    {// Текущий пользователь может видеть рейтинг текущего пользователя
        // Добавление ссылки на панель своих достижений
        $link = dof_html_writer::link(
            $DOF->url_im('achievements', '/rating.php', $addvars),
            $DOF->get_string('user_rating_pagelink', 'achievements')
        );
        echo dof_html_writer::tag('h4', $link);
    }
}

// Печать подвала
$DOF->modlib('nvg')->print_footer(NVG_MODE_PORTAL);

?>