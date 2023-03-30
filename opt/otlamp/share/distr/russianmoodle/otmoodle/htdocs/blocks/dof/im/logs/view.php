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

// Идентификатор лога
$logid = optional_param('id', 0, PARAM_INT);

// Проверка прав
$DOF->storage('logs')->require_access('view');

// Данные для формы выгрузки отчета
$custom_data = new stdClass();
$custom_data->dof = $DOF;
$custom_data->id = $logid;

// Начальны параметры
$html = '';

if ( ! empty($logid) )
{
    // Форма выгрузки отчета
    $form = new dof_im_logs_report(new moodle_url('view.php', ['id' => $logid]), $custom_data);
    $form->process();
    
    // Рендер формы
    $html .= $form->render();
}

// Добавление уровня навигации
$DOF->modlib('nvg')->add_level(
        $DOF->get_string('title', 'logs'),
        $DOF->url_im('logs','/index.php', $addvars)
        );

// Печать шапки страницы
$DOF->modlib('nvg')->print_header(NVG_MODE_PORTAL);

echo $html;

// Печать подвала страницы
$DOF->modlib('nvg')->print_footer(NVG_MODE_PORTAL);
?>
