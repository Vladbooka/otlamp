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

// Подключение библиотек
require_once('../lib.php');

// ID предмето-класса
$csid = optional_param('csid', null, PARAM_INT);
$planid = optional_param('planid', null, PARAM_INT);
$eventid = optional_param('eventid', null, PARAM_INT);
$cpassedid = optional_param('cpassedid', null, PARAM_INT);
$layout = optional_param('page_layout', 'NVG_MODE_PORTAL', PARAM_RAW_TRIMMED);

// Добавление стилей
$DOF->modlib('nvg')->add_css('im', 'journal', '/styles.css');
$DOF->modlib('nvg')->add_js('im', 'journal',
    '/group_journal/js/gradeitem.js', false);

// !!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
// !!!!!!!!!!!!!!!!!!!! Проверка прав доступа
// !!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!

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



// Получение занятие учебного процесса
/**
 * @var dof_lesson $lesson
 */
$lesson = $DOF->modlib('journal')->get_manager('lessonprocess')->get_lesson($csid, $eventid, $planid);
$coursecatid=null;
$courseid=null;
$gradeitemid=null;
if ($lesson->plan_exists())
{
    $plan = $lesson->get_plan();
    if (!empty($plan->mdlgradeitemid))
    {
        $amagradeitem = $DOF->modlib('ama')->grade_item($plan->mdlgradeitemid)->get();
        if (!empty($amagradeitem->courseid))
        {
            $courseid = $amagradeitem->courseid;
            $course = $DOF->modlib('ama')->course($courseid)->get();
            if (!empty($course->category))
            {
                $coursecatid = $course->category;
            }
        }
        if (!empty($amagradeitem->id))
        {
            $gradeitemid = $amagradeitem->id;
        }
    }
}

// HTML код
$html = dof_html_writer::div('', 'in-process', [
    'id' => 'gradeitem_selector',
    'data-csid' => (string)$csid,
    'data-planid' => (string)$planid,
    'data-eventid' => (string)$eventid,
    'data-cpassedid' => (string)$cpassedid,
    'data-coursecatid' => (string)$coursecatid,
    'data-courseid' => (string)$courseid,
    'data-gradeitemid' => (string)$gradeitemid,
]);



// Печать шапки страницы
$DOF->modlib('nvg')->print_header($layout);

// Отображение сообщений
$DOF->messages->display();

echo $html;

// Печать подвала
$DOF->modlib('nvg')->print_footer($layout);
