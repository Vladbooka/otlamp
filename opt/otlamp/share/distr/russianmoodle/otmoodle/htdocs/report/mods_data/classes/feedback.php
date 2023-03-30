<?php
///////////////////////////////////////////////////////////////////////////
//                                                                       //
// This file is part of Moodle - http://moodle.org/                      //
// Moodle - Modular Object-Oriented Dynamic Learning Environment         //
//                                                                       //
// Moodle is free software: you can redistribute it and/or modify        //
// it under the terms of the GNU General Public License as published by  //
// the Free Software Foundation, either version 3 of the License, or     //
// (at your option) any later version.                                   //
//                                                                       //
// Moodle is distributed in the hope that it will be useful,             //
// but WITHOUT ANY WARRANTY; without even the implied warranty of        //
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the         //
// GNU General Public License for more details.                          //
//                                                                       //
// You should have received a copy of the GNU General Public License     //
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.       //
//                                                                       //
///////////////////////////////////////////////////////////////////////////

/**
 * Блок объединения отчетов. Класс формирования данных по модулю теста.
 * 
 * @package    block
 * @subpackage reports_union
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot.'/mod/feedback/lib.php');

class report_mods_data_feedback
{
    /**
     * Конструктор
     */
    public function __construct() {}
    
    /**
     * Добавить данные в результирующий массив
     * 
     * @param int $cmid - ID модуля курса
     * @param array $report - Результирующий массив
     */
    public function add_xls_data($cmid, &$report)
    {
        global $DB;
        if ( ! $cm = get_coursemodule_from_id('feedback', $cmid))
        {// Экземпляр модуля не найден
            return $report;
        }
        if ( ! $feedbackmodule = $DB->get_record('feedback', ['id' => $cm->instance]))
        {// Экземпляр ответа не найден
            return $report;
        }
        
        // Получение полей формы
        $params = ['feedback' => $feedbackmodule->id, 'hasvalue' => 1];
        if ( ! $items = $DB->get_records('feedback_item', $params, 'position'))
        {
            return $report;
        }
        // Получить завершенные ответы
        $completeds = feedback_get_completeds_group($feedbackmodule);
        
        if ( ! empty($completeds) )
        {// Есть завершенные ответы
            $cmdata = [];
            
            // Добавление шапки
            if ( ! isset($report['header1'][$cmid]) )
            {// Добавление названия экземпляра в заголовок
                 $report['header1'][$cmid] = $cm->name;
            }
            // Подготовка массива строк для полей
            $report['header2'][$cmid] = [];
            
            foreach ( $completeds as $completeitem )
            {// Обработка каждого ответа
                $userfeedbackmodule = $DB->get_record('feedback', ['id' => $completeitem->feedback]);
                $data = [];
            
                // Данные пользователя
                $data['idnumber'] = NULL;
                $data['fullname'] = NULL;
                // Анонимный ответ
                $is_anonimous = true;
                if ($user = $DB->get_record('user', ['id' => $completeitem->userid]))
                {
                    if ($completeitem->anonymous_response == FEEDBACK_ANONYMOUS_NO)
                    {// Неанонимный ответ
                        $is_anonimous = false;
                        $data['idnumber'] = $user->idnumber;
                        $data['fullname'] = fullname($user);
                    }
                }
                
                // Добавление имени поля idnumber и fullname
                if ( ! isset ($report['header2'][$cmid]['idnumber']) )
                {
                    $report['header2'][$cmid]['idnumber'] = get_string('idnumber');
                }
                if ( ! isset ($report['header2'][$cmid]['fullname']) )
                {
                    $report['header2'][$cmid]['fullname'] = get_string('fullnameuser');
                }
                
                // Данные полей формы
                foreach ( $items as $item )
                {
                    $data[$item->id] = NULL;
                    
                    // Добавление имени поля
                    if ( ! isset ($report['header2'][$item->id]) )
                    {
                        $report['header2'][$cmid][$item->id] = $item->name;
                    }
                    // Получение ответа по полю
                    $params = ['item' => $item->id, 'completed' => $completeitem->id];
                    $value = $DB->get_record('feedback_value', $params);
                    $itemobj = feedback_get_item_class($item->typ);
                    $printval = $itemobj->get_printval($item, $value);
                    $printval = trim($printval);
                    if ( ! empty($printval) )
                    {
                        $data[$item->id] = $printval;
                    }
                
                    // Получение курса
                    $courseid = isset($value->course_id) ? $value->course_id : 0;
                    if ($courseid == 0)
                    {   
                        $courseid = $userfeedbackmodule->course;
                    }
                }
                
                // Добавление имени поля courseid и courseshortname
                if ( ! isset ($report['header2'][$cmid]['courseid']) )
                {
                    $report['header2'][$cmid]['courseid'] = get_string('courseid', 'feedback');
                }
                if ( ! isset ($report['header2'][$cmid]['courseshortname']) )
                {
                    $report['header2'][$cmid]['courseshortname'] = get_string('course');
                }
                
                // Данные курса
                $data['courseid'] = NULL;
                $data['courseshortname'] = NULL;
                if (isset($courseid) AND $course = $DB->get_record('course', array('id' => $courseid)))
                {
                    $coursecontext = context_course::instance($courseid);
                    $shortname = format_string($course->shortname, true, array('context' => $coursecontext));
                    $data['courseid'] = $course->id;
                    $data['courseshortname'] = $shortname;
                }
            
                if ( $is_anonimous )
                {// Анонимный пользователь
                    $userid = 0;
                } else
                {// Неанонимный пользователь
                    $userid = $completeitem->userid;
                }
                if ( ! isset($report['users'][$userid]) )
                {// Не создан слот для пользователя
                    $report['users'][$userid] = [];
                }
                if ( ! isset($report['users'][$userid][$cmid]) )
                {// Не создан слот для модуля
                    $report['users'][$userid][$cmid] = [];
                }
                // Добавление данных по ответу пользователя
                $report['users'][$userid][$cmid][$completeitem->id] = $data;
            }
        } 
        
        return $report;
    }
}