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
 * Панель управления дополнительными полями Деканата. Сохранение допполей.
 *
 * @package    im
 * @subpackage customfields
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// Подключаем библиотеки
require_once('lib.php');
require_once('form.php');

// Разбираем get-параметры
$id = optional_param('id', 0, PARAM_INT);
$type = optional_param('type', '', PARAM_ALPHA);

// HTML-код старинцы
$html = '';

$listurl = $DOF->url_im('customfields', '/list.php', $addvars);
// Добавление уровня навигации плагина
$DOF->modlib('nvg')->add_level(
    $DOF->get_string('page_list_name', 'customfields'),
    $listurl,
    $addvars
);

// Текущий URL
$addvars['id'] = $id;
$addvars['type'] = $type;
$currenturl = $DOF->url_im('customfields', '/save.php', $addvars);

// Добавление уровня навигации плагина
$DOF->modlib('nvg')->add_level(
    $DOF->get_string('page_save_name', 'customfields'),
    $currenturl
);

if ( $id > 0 )
{// Редактирование имеющегося поля
    if ( ! $DOF->storage('customfields')->is_access('edit', $id) )
    {
        $DOF->messages->add(
            $DOF->get_string('error_save_edit_access_denied', 'customfields'),
            'error'
        );
    }
} else 
{// Создание поля
    if ( ! $DOF->storage('customfields')->is_access('create') )
    {
        $DOF->messages->add(
            $DOF->get_string('error_save_create_access_denied', 'customfields'),
            'error'
        );
    }
}

// Проверка на наличие ошибок
if ( $DOF->messages->errors_exists() )
{
    // Печать шапки страницы
    $DOF->modlib('nvg')->print_header(NVG_MODE_PORTAL);
    // Печать подвала страницы
    $DOF->modlib('nvg')->print_footer(NVG_MODE_PORTAL);
    die;
}

// Формириование дополнительных данных для формы сохранения
$customdata = new stdClass;
$customdata->dof = $DOF;
$customdata->id = $id;
$customdata->addvars = $addvars;
$customdata->returnurl = $currenturl;
$customdata->cancelurl = $listurl;
$form = new dof_im_customfields_save_form($currenturl, $customdata);

// Обработка формы
$form->process();
// Отображение формы
$html .= $form->render();

// Печать шапки страницы
$DOF->modlib('nvg')->print_header(NVG_MODE_PORTAL);

print($html);
// Печать подвала страницы
$DOF->modlib('nvg')->print_footer(NVG_MODE_PORTAL);