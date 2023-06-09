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
 * Базовые функции всех интерфейсов Деканата
 *
 * @package    im
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// Загрузка библиотек верхнего уровня
require_once(dirname(realpath(__FILE__))."/../locallib.php");

// Определение глобальных переменных
global $PAGE, $DOF;

// Установка контекста страницы
if ( $DOF->context == null )
{// Контекст страницы еще не был установлен
    $context = context_system::instance();
    $PAGE->set_context($context);
}

// Глобальная проверка доступа на просмотр интерфейсов
$DOF->require_access('view');

// Инициализация генератора HTML
$DOF->modlib('widgets')->html_writer();

// Добавление общих GET параметров для всех интерфейсов
$depid = optional_param('departmentid', 0, PARAM_INT);
$addvars = [];
$addvars['departmentid'] = $depid;

// Добавление уровня навигации плагина
$DOF->modlib('nvg')->add_level(
    $DOF->get_string('title'),
    $DOF->url_im('standard','/index.php', $addvars)
);

// Уведомление об использовании акаунта с правами доступа администратора
if ( $DOF->is_access('datamanage') ||
     $DOF->is_access('manage') || 
     $DOF->is_access('admin') 
   )
{// Достаточно прав для обхода бизнес-логики Деканата
    if ( ! $DOF->messages->get_stack_messages('notice_datamanage_access', DOF_MESSAGE_WARNING) )
    {
        $DOF->messages->add($DOF->get_string('notice_datamanage_access'), DOF_MESSAGE_WARNING, 'notice_datamanage_access');
    }
}

if ( isset($_SERVER['REQUEST_URI']) )
{
    // Добавление URL текущей страницы
    $PAGE->set_url($_SERVER['REQUEST_URI']);
}

?>