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
 * Панель управления достижениями
 *
 * @package    im
 * @subpackage achievements
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// Подключаем библиотеки
require_once('lib.php');
require_once(dirname(realpath(__FILE__)) . '/sortachievementcats/form.php');

// Получение GET-параметров
$parentcat = optional_param('parentcat', 0, PARAM_INT);

// Добавление уровня навигации
$DOF->modlib('nvg')->add_level(
        $DOF->get_string('admin_panel_title', 'achievements'),
        $DOF->url_im('achievements', '/admin_panel.php'),
        $addvars
);

// Права доступа к административной части
$DOF->im('achievements')->require_access('admnistration');

// Формирование URL формы
$somevars = array_merge(
    $addvars, ['parentcat' => $parentcat]
);
$url = $DOF->url_im('achievements', '/admin_panel.php', $somevars);
// Формирование дополнительных данных
$customdata = new stdClass;
$customdata->dof = $DOF;
$customdata->addvars = $somevars;
// Получение формы сортировки разделов
$sortform = new dof_im_achievementcats_sort_form($url, $customdata);
// Обработка данных сортировки
$sortform->process();

// Опции формирования таблицы разделов достижений
$options = [
    'addvars' => $addvars,
    'sortform' => $sortform->render()
];
// Таблица разделов 
$achievementcatstable = $DOF->im('achievements')->get_achievementcatstable($options);

// Таблица шаблонов достижений
$achievementstable = $DOF->im('achievements')->get_achievementstable($options);

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

// Панель редактирования разделов достижений
print($achievementcatstable);
// Панель редактирования шаблонов достижений
print($achievementstable);

// Печать подвала
$DOF->modlib('nvg')->print_footer(NVG_MODE_PORTAL);

?>