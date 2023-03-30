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
require_once($CFG->dirroot . '/report/mods_data/locallib.php');

class report_mods_data_feedback
{
    protected $startdate;
    protected $enddate;
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
    public function add_subreport($cmid, &$report, $exportformat, $users = [], $completion = 'all', $attempts = 'all', $startdate = null, $enddate = null, $attemptsinperiod = null)
    {
        global $DB;
        if ( ! $cm = get_coursemodule_from_id('feedback', $cmid))
        {// Экземпляр модуля не найден
            return $report;
        }
        if ( ! $course = $DB->get_record('course', array('id' => $cm->course))) {
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
        
        $this->startdate = $startdate;
        $this->enddate = $enddate;
        
        // Получить завершенные ответы
        $completeds = $this->feedback_get_completeds_group($feedbackmodule, false, false, $startdate, $enddate);
        
        if ( ! empty($completeds) )
        {// Есть завершенные ответы
            $cmdata = [];
            
            // Добавление шапки
            if ( ! isset($report['header1'][$cmid]) )
            {// Добавление названия экземпляра в заголовок
                 $report['header1'][$cmid] = [
                    'coursename' => $course->fullname,
                    'name' => $cm->name
                ];
            }
            // Подготовка массива строк для полей
            $report['header2'][$cmid] = [];
            
            foreach ( $completeds as $completeitem )
            {// Обработка каждого ответа

                list($course, $cm) = get_course_and_cm_from_cmid($cmid);
                $completionstate = report_mods_data_get_attempt_completion($completeitem->id, 'feedback', $course, $cm, $completeitem->userid);
                switch($completionstate)
                {
                    case ATTEMPT_COMPLETION_COMPLETE:
                        if( $completion == 'notcompleted' )
                        {
                            continue;
                        }
                        break;
                    case ATTEMPT_COMPLETION_UNKNOWN:
                        break;
                    case ATTEMPT_COMPLETION_IGNORE:
                        continue;
                        break;
                    case ATTEMPT_COMPLETION_INCOMPLETE:
                    default:
                        if( $completion == 'completed' )
                        {
                            continue;
                        }
                        break;
                }
                
                if( ! empty($users) && in_array($completeitem->userid, $users) || empty($users))
                {//если указан userid, то только по нему собираем данные
                    
                    $userfeedbackmodule = $DB->get_record('feedback', ['id' => $completeitem->feedback]);
                    $data = [];
                
                    // Данные пользователя
                    $data['idnumber'] = NULL;
                    $data['fullname'] = NULL;
                    // Анонимный ответ
                    $anonymous = true;
                    if ($user = $DB->get_record('user', ['id' => $completeitem->userid]))
                    {
                        if ($completeitem->anonymous_response == FEEDBACK_ANONYMOUS_NO)
                        {// Неанонимный ответ
                            $anonymous = false;
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
                
                    if ( ! empty($users) && in_array($completeitem->userid, $users) && $anonymous )
                    {//для отчета по пользователю не отображаем анонимные данные
                        continue;
                    } else if($anonymous)
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
                    if ( ! isset($report['users'][$userid][$course->id]) )
                    {// Не создан слот для пользователя
                        $report['users'][$userid][$course->id] = [
                            'info'=>$course,
                            'cms'=>[]
                        ];
                    }
                    // Добавление данных по ответу пользователя
                    switch($completionstate)
                    {
                        case ATTEMPT_COMPLETION_COMPLETE:
                            $report['users'][$userid][$course->id]['cms']['completed'][$cmid]['all'][$completeitem->id] = $data;
                            $report['users'][$userid][$course->id]['cms']['completed'][$cmid]['best'][$completeitem->id] = $data;
                            $report['users'][$userid][$course->id]['cms']['all'][$cmid]['all'][$completeitem->id] = $data;
                            $report['users'][$userid][$course->id]['cms']['all'][$cmid]['best'][$completeitem->id] = $data;
                            break;
                        case ATTEMPT_COMPLETION_INCOMPLETE:
                            $report['users'][$userid][$course->id]['cms']['notcompleted'][$cmid]['all'][$completeitem->id] = $data;
                            $report['users'][$userid][$course->id]['cms']['notcompleted'][$cmid]['best'][$completeitem->id] = $data;
                            $report['users'][$userid][$course->id]['cms']['all'][$cmid]['all'][$completeitem->id] = $data;
                            $report['users'][$userid][$course->id]['cms']['all'][$cmid]['best'][$completeitem->id] = $data;
                            break;
                        case ATTEMPT_COMPLETION_UNKNOWN:
                            $report['users'][$userid][$course->id]['cms']['completed'][$cmid]['all'][$completeitem->id] = $data;
                            $report['users'][$userid][$course->id]['cms']['completed'][$cmid]['best'][$completeitem->id] = $data;
                            $report['users'][$userid][$course->id]['cms']['notcompleted'][$cmid]['all'][$completeitem->id] = $data;
                            $report['users'][$userid][$course->id]['cms']['notcompleted'][$cmid]['best'][$completeitem->id] = $data;
                            $report['users'][$userid][$course->id]['cms']['all'][$cmid]['all'][$completeitem->id] = $data;
                            $report['users'][$userid][$course->id]['cms']['all'][$cmid]['best'][$completeitem->id] = $data;
                            break;
                    }
                }
            }
        } 
        
        return $report;
    }
    
    /**
     * get the completeds depending on the given groupid.
     *
     * @global object
     * @global object
     * @param object $feedback
     * @param int $groupid
     * @param int $courseid
     * @return mixed array of found completeds otherwise false
     */
    protected function feedback_get_completeds_group($feedback, $groupid = false, $courseid = false, $startdate = null, $enddate = null) 
    {
        global $CFG, $DB;
    
        if (intval($groupid) > 0) {
            $query = "SELECT fbc.*
                    FROM {feedback_completed} fbc, {groups_members} gm
                   WHERE fbc.feedback = ?
                         AND gm.groupid = ?
                         AND fbc.userid = gm.userid";
            $params = [$feedback->id, $groupid];
            if( ! is_null($startdate) )
            {
                $query .= ' AND fbc.timemodified >= ?';
                $params[] = $startdate;
            }
            if( ! is_null($enddate) )
            {
                $query .= ' AND fbc.timemodified <= ?';
                $params[] = $enddate;
            }
            if ($values = $DB->get_records_sql($query, $params)) {
                return $values;
            } else {
                return false;
            }
        } else {
            $params = [];
            if ($courseid) {
                $startdateselect = $enddateselect = '';
                if( ! is_null($startdate) )
                {
                    $startdateselect = ' AND fbc.timemodified >= ?';
                    $params[] = $startdate;
                }
                if( ! is_null($enddate) )
                {
                    $enddateselect = ' AND fbc.timemodified <= ?';
                    $params[] = $enddate;
                }
                $query = "SELECT DISTINCT fbc.*
                        FROM {feedback_completed} fbc, {feedback_value} fbv
                        WHERE fbc.id = fbv.completed
                            $startdateselect
                            $enddateselect
                            AND fbc.feedback = ?
                            AND fbv.course_id = ?
                        ORDER BY random_response";
                array_push($params, $feedback->id, $courseid);
                if ($values = $DB->get_records_sql($query, $params)) {
                    return $values;
                } else {
                    return false;
                }
            } else {
                $select = 'feedback = ?';
                $params[] = $feedback->id;
                if( ! is_null($startdate) )
                {
                    $select .= ' AND timemodified >= ?';
                    $params[] = $startdate;
                }
                if( ! is_null($enddate) )
                {
                    $select .= ' AND timemodified <= ?';
                    $params[] = $enddate;
                }
                if ($values = $DB->get_records_select('feedback_completed', $select, $params)) {
                    return $values;
                } else {
                    return false;
                }
            }
        }
    }
}