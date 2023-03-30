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
 * Страница редактирования занятия
 *
 * @package    im
 * @subpackage journal
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

global $addvars, $DOF;

// Подключение библиотек
require_once('../lib.php');
require_once($DOF->plugin_path('im','journal','/group_journal/libform.php'));

// ID предмето-класса
$csid = $addvars['csid'] = optional_param('csid', 0, PARAM_INT);
$eventid = $addvars['eventid'] = optional_param('eventid', 0, PARAM_INT);
$planid = $addvars['planid'] = optional_param('planid', 0, PARAM_INT);
$departmentid = $addvars['departmentid'] = optional_param('departmentid', 0, PARAM_RAW_TRIMMED);
$layout = $addvars['page_layout'] = optional_param('page_layout', 'NVG_MODE_PORTAL', PARAM_RAW_TRIMMED);

// Добавление стилей
$DOF->modlib('nvg')->add_css('im', 'journal', '/styles.css');
$DOF->modlib('nvg')->add_js('im', 'journal', '/group_journal/js/lessonedit.js?v=2021030203', false);

// Проверка прав доступа
if ( ! $DOF->im('journal')->is_access('view_journal/own', $csid) &&
        $DOF->im('journal')->require_access('view_journal', $csid) )
{// Нет прав для просмотра журнала
    $DOF->messages->add(
            $DOF->get_string('error_grpjournal_view_access_denied', 'journal'),
            DOF_MESSAGE_ERROR
            );
}

// Определение шаблона страницы
switch ( $layout )
{
    case 'popup':
        $layout = NVG_MODE_POPUP;
        break;

    default:
        $layout = NVG_MODE_PAGE;
        break;
}

// HTML код
$html = '';

// Получение занятий учебного процесса
$lesson = $DOF->modlib('journal')->get_manager('lessonprocess')->get_lesson($csid, $eventid, $planid);

// Форма посещаемости
$url = $DOF->url_im('journal', '/group_journal/forms/lessonedit.php', $addvars);

// Данные для формы
$customdata = new stdClass();
$customdata->dof = $DOF;
$customdata->lesson = $lesson;
$customdata->addvars = $addvars;
$customdata->cstreamid = $csid;
$customdata->planid = $planid;
$customdata->eventid = $eventid;
$customdata->departmentid = $departmentid;
$customdata->editdate = dof_im_journal_is_editdate($planid, $csid);

$status = $DOF->storage('schevents')->get_field($eventid, 'status');
$flag = true;
if ( $status == 'replaced' || $status == 'canceled' || $status == 'implied' )
{
    $flag = false;
}

// Форма заполнения информации о занятии
$formtopic = new dof_im_journal_lesson_edit(
        $url,
        $customdata,
        'post',
        '',
        null,
        $flag
        );

// Обработка формы
$formtopic->process();

$html .= dof_html_writer::div($formtopic->render(), 'lessonedit-form');

// Печать шапки страницы
$DOF->modlib('nvg')->print_header($layout);

// Отображение сообщений
$DOF->messages->display();

echo $html;

// Печать подвала
$DOF->modlib('nvg')->print_footer($layout);
