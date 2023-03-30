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
 * Интерфейс управления причинами отсутствия
 *
 * @package    im
 * @subpackage schabsenteeism
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// Подключение библиотек
require_once('lib.php');
require_once('form.php');

// Начальные параметры
$html = '';

// ID выбранной причины
$id = optional_param('id', 0, PARAM_INT);

if ( empty($id) )
{// Проверка на возможность создания причины
    if ( ! $DOF->storage('schabsenteeism')->is_access('create') )
    {
        $DOF->messages->add(
            $DOF->get_string('error_interface_save_access_create_denied', 'schabsenteeism'),
            'error'
        );
    }
} else 
{// Проверка на возможность редактирования подразделения
    if ( ! $DOF->modlib('journal')->get_manager('schabsenteeism')->can_edit($id) )
    {
        $DOF->messages->add(
            $DOF->get_string('error_interface_save_access_edit_denied', 'schabsenteeism'),
            'error'
        );
    }
    
    // Добавление GET-параметров
    $addvars['id'] = $id;
}

// Добавление уровня навигации
$DOF->modlib('nvg')->add_level(
    $DOF->get_string('form_save_header', 'schabsenteeism'),
    $DOF->url_im('schabsenteeism', '/save.php', $addvars)
);

// Проверка на наличие ошибок
if ( $DOF->messages->errors_exists() )
{
    // Печать шапки страницы
    $DOF->modlib('nvg')->print_header(NVG_MODE_PORTAL);
    // Печать подвала страницы
    $DOF->modlib('nvg')->print_footer(NVG_MODE_PORTAL);
    die;
}

// Сформируем url формы
$url = $DOF->url_im('schabsenteeism', '/save.php', $addvars);
    
// Сформируем дополнительные данные
$customdata = new stdClass;
$customdata->dof = $DOF;
$customdata->id = $id;
$customdata->addvars = $addvars;
$customdata->returnurl = $DOF->url_im('schabsenteeism', '/index.php', $addvars);

// Форма сохранения подразделения
$form = new dof_im_schabsenteeism_save($url, $customdata);
// Обработчик формы
$form->process();
// Рендерим форму
$html .= $form->render();

// Печать шапки страницы
$DOF->modlib('nvg')->print_header(NVG_MODE_PORTAL);

// Печать формы
echo $html;

// Печать подвала
$DOF->modlib('nvg')->print_footer(NVG_MODE_PORTAL);

?>