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
 * Плагин информации о пользователе. Страница настроек.
 *
 * @package    im
 * @subpackage achievements
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// Подключаем библиотеки
require_once('lib.php');
require_once('form.php');

// Формирование url формы
$url = $DOF->url_im('achievements', '/plugins/userinfo/settings.php', $addvars);
// Формирование параметров для передачи в форму
$customdata = new stdClass();
$customdata->dof = $DOF;
$customdata->departmentid = $addvars['departmentid'];
$customdata->addvars = $addvars;


// Создание формы
$settingsform = new dof_im_achievements_userinfo_settingsform($url, $customdata);
// Обработчик настроек
$settingsform->process();

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

// Отображение формы
$settingsform->display();
// Печать подвала
$DOF->modlib('nvg')->print_footer(NVG_MODE_PORTAL);

?>