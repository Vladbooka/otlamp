<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Обозреватель событий для плагина local_learninghistory
 * 
 * @package    local_learninghistory
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_learninghistory;

use completion_completion;
use completion_info;
use local_learninghistory\local\enrol_manager;
use local_learninghistory\local\utilities;
use local_learninghistory\local\grades_manager;
use local_learninghistory\activetime;
use local_learninghistory\attempt\attempt_base;
use local_learninghistory\attempt\mod\attempt_mod_assign;
use local_learninghistory\attempt\mod\attempt_mod_quiz;
use context;
use context_course;
use context_module;
use cache;
use cache_store;
use grade_item;
use grade_scale;

require_once($CFG->libdir . '/datalib.php');
require_once($CFG->libdir . '/grade/grade_item.php');
require_once($CFG->libdir . '/grade/constants.php');

defined('MOODLE_INTERNAL') || die();

/**
 * Event observer for mod_forum.
 */
class observer {

    /**
     * Triggered via user_enrolment_deleted event.
     *
     * @param \core\event\user_enrolment_deleted $event
     */
    public static function user_enrolment_deleted(\core\event\user_enrolment_deleted $event) 
    {
        $parameters = [
            'status' => 'archive',
            'enddate' => time()
        ];
        // Итоговая оценка уже в истории, достаем ее оттуда
        $parameters['finalgrade'] = grades_manager::get_user_finalgrade(
                $event->courseid, 
                $event->relateduserid, 
                array(
                        'startdate' => $event->other['userenrolment']['timestart'], 
                        'allowhistory' => true
                ));
        
        // Сохранение итоговой оценки за курс
        $parameters['coursefinalgrade'] = grades_manager::get_max_course_finalgrade($event->courseid);
        
        // Сохранение статуса прохождения курса пользователем
        $ccompletion = new completion_completion(['userid' => $event->relateduserid, 'course' => $event->courseid]);
        if ( $ccompletion->is_complete() )
        {
            $parameters['coursecompletion'] = 1;
        } else 
        {
            $parameters['coursecompletion'] = 0;
        }
        
        // Если у пользователя два userenrolment, и в таблице нет записей по курсу, то запишется только последний
        if ($event->other['userenrolment']['lastenrol'] == true) {
            $llid = utilities::set_learninghistory_snapshot($event->courseid, $event->relateduserid, $parameters);
            if( is_numeric($llid) && $llid > 0 )
            {
                $llcms = utilities::get_learninghistory_cm_all_actual($llid, $event->relateduserid);
                if( ! empty($llcms) )
                {
                    $params = [
                        'status' => 'archive',
                    ];
                    foreach($llcms as $llcm)
                    {
                        utilities::set_learninghistory_cm_snapshot($llcm->cmid, $llid, $event->relateduserid, $params);
                    }
                }
            }
        }
    }

    /**
     * Triggered via user_enrolment_created event.
     *
     * @param \core\event\user_enrolment_created $event
     */
    public static function user_enrolment_created(\core\event\user_enrolment_created $event) 
    {
        // Сначала достанем информацию о подписке.
        $ue = enrol_manager::get_user_enrol_info($event->objectid);
        
        $parameters = [
            'status' => 'active',
            'enroltype' => $event->other['enrol'],
            'begindate' => $ue->timestart,
            'enddate' => $ue->timeend,
            'activetime' => 0,
            'atlastupdate' => time()
        ];
        
        // @todo: понять, каким образом определять, что оценки восстановлены.
        $graderestored = false; // Пока что оценки не восстанавливаются
        if ( $graderestored )
        {// Оценки восстановились
            $parameters['graderestored'] = 1;
            $parameters['finalgrade'] = grades_manager::get_user_finalgrade($event->courseid, $event->relateduserid);
        }
        
        $snapshot = utilities::get_learninghistory_snapshot_actual($event->courseid, $event->relateduserid);
        if (empty($snapshot)) {
            $llid = utilities::set_learninghistory_snapshot($event->courseid, $event->relateduserid, $parameters);
            if( is_numeric($llid) && $llid > 0 )
            {
                $course = get_course($event->courseid);
                $modinfo = get_fast_modinfo($course, 0, true);
                if( ! empty($modinfo) )
                {
                    $cms = array_keys($modinfo->cms);
                    if( ! empty($cms) )
                    {
                        $params = [
                            'status' => 'active',
                            'activetime' => 0,
                            'atlastupdate' => time()
                        ];
                        foreach($cms as $cmid)
                        {
                            $cm = utilities::get_module_from_cmid($cmid);
                            $cm_info = $modinfo->get_cm($cmid);
                            $completion = new completion_info($course);
                            $current = $completion->get_data($cm_info, false, $event->relateduserid);
                            $params['completion'] = $current->completionstate;
                            /**
                             * @TODO на текущий момент оценки при записи на ставятся, т.к. восстановление оценок, 
                             * если оно затребовано, происходит после события записи на курс
                             */
//                             $mparams = [
//                                 'courseid' => $event->courseid,
//                                 'itemtype' => 'mod',
//                                 'itemmodule' => $cm->modname,
//                                 'iteminstance' => $cm->instance,
//                                 'itemnumber' => 0
//                             ];
//                             $grade_item = new grade_item($mparams);
//                             if( ! empty($grade_item) )
//                             {
//                                 $final = $grade_item->get_final($event->relateduserid);
//                                 if( ! empty($final) )
//                                 {
//                                     $params['finalgrade'] = $final->finalgrade;
//                                 }
//                             }
                            
                            // Получаем текущую попытку элемента
                            if( in_array($cm->modname, activetime::get_mods_supported_attempts()) )
                            {
                                $classname = 'local_learninghistory\attempt\mod\attempt_mod_' . $cm->modname;
                            } else
                            {
                                $classname = 'local_learninghistory\attempt\attempt_base';
                            }
                            $attemptmod = new $classname($cmid, $event->relateduserid);
                            $attempt = $attemptmod->get_current_attemptnumber();
                            if( $attempt === false )
                            {
                                $attempt = $attemptmod->get_last_attemptnumber();
                                if( $attempt === false )
                                {
                                    $attempt = $attemptmod->get_possible_first_attemptnumber();
                                }
                            }
                            $params['attemptnumber'] = $attempt;
                            
                            utilities::set_learninghistory_cm_snapshot($cmid, $llid, $event->relateduserid, $params);
                        }
                    }
                }
            }
        }
    }

    /**
     * Triggered via user_enrolment_updated event.
     *
     * @param \core\event\user_enrolment_updated $event
     */
    public static function user_enrolment_updated(\core\event\user_enrolment_updated $event) {
        global $DB;
        
        // Сначала достанем информацию о подписке.
        $ue = enrol_manager::get_user_enrol_info($event->objectid);
        
        $parameters = [
            'begindate' => $ue->timestart,
            'enddate' => $ue->timeend
        ];
        
        utilities::set_learninghistory_snapshot($event->courseid, $event->relateduserid, $parameters);
    }

    /**
     * Triggered via course_completed event.
     *
     * @param \core\event\course_completed $event
     */
    public static function course_completed(\core\event\course_completed $event) 
    {
        global $DB;
        
        $parameters = [];
        
        // Сохранение статуса прохождения курса пользователем
        $ccompletion = new completion_completion(['userid' => $event->relateduserid, 'course' => $event->courseid]);
        if ( $ccompletion->is_complete() )
        {
            $parameters['coursecompletion'] = 1;
        } else
        {
            $parameters['coursecompletion'] = 0;
        }
        
        // Сохранение итоговой оценки за курс
        $parameters['coursefinalgrade'] = grades_manager::get_max_course_finalgrade($event->courseid);
        
        // Если у пользователя два userenrolment, и в таблице нет записей по курсу, то запишется только последний
        utilities::set_learninghistory_snapshot($event->courseid, $event->relateduserid, $parameters);
    }
    
    public static function attempt_started(\mod_quiz\event\attempt_started $event)
    {
        $course = get_course($event->courseid);
        // Получаем все необходимые данные про модуль курса
        $modinfo = get_fast_modinfo($course);
        $cm_info = $modinfo->get_cm($event->contextinstanceid);
        $cm = $cm_info->get_course_module_record(true);
        $completion = new completion_info($course);
        $current = $completion->get_data($cm_info, false, $event->userid);
        $context = context_module::instance($cm->id, IGNORE_MISSING);
        if( ! empty($context) )
        {
            // Получаем активную подписку пользователя
            $ll = utilities::get_learninghistory_snapshot_actual($event->courseid, $event->userid);
            if( ! empty($ll) )
            {
                // Получаем текущую активную попытку прохождения модуля
                $cmsnapshotactual = utilities::get_learninghistory_cm_snapshot_actual($context->instanceid, $ll->id, $event->userid);
                if( ! empty($cmsnapshotactual) )
                {
                    // Получаем текущую попытку теста
                    $attemptmod = new attempt_mod_quiz($cm->id, $event->userid);
                    $currentattempt = $attemptmod->get_current_attemptnumber();
                    if( $cmsnapshotactual->attemptnumber < $currentattempt )
                    {// Если следующая попытка - архивируем текущую активную попытку прохождения модуля
                        $params = [
                            'status' => 'archive'
                        ];
                        utilities::set_learninghistory_cm_snapshot($context->instanceid, $ll->id, $event->userid, $params);
                        
                        // Получаем текущую оценку за модуль
                        $finalgrade = null;
                        $grade_item = new grade_item([
                            'courseid' => $event->courseid,
                            'itemtype' => 'mod',
                            'itemname' => $cm->name,
                            'itemmodule' => $cm->modname,
                            'iteminstance' => $cm->instance
                        ]);
                        if( ! empty($grade_item) )
                        {
                            $final = $grade_item->get_final($event->userid);
                            if( ! empty($final) )
                            {
                                $finalgrade = $final->finalgrade;
                            }
                        }
                        $params = [
                            'status' => 'active',
                            'attemptnumber' => $currentattempt,
                            'finalgrade' => $finalgrade,
                            'completion' => $current->completionstate
                        ];
                        utilities::set_learninghistory_cm_snapshot($context->instanceid, $ll->id, $event->userid, $params);
                    }
                }
            }
        }
    }
    
    public static function add_attempt(\mod_assign\event\add_attempt $event)
    {
        $course = get_course($event->courseid);
        // Получаем все необходимые данные про модуль курса
        $modinfo = get_fast_modinfo($course);
        $cm_info = $modinfo->get_cm($event->contextinstanceid);
        $cm = $cm_info->get_course_module_record(true);
        $completion = new completion_info($course);
        $current = $completion->get_data($cm_info, false, $event->relateduserid);
        $context = context_module::instance($cm->id, IGNORE_MISSING);
        if( ! empty($context) )
        {
            // Получаем активную подписку пользователя
            $ll = utilities::get_learninghistory_snapshot_actual($event->courseid, $event->relateduserid);
            if( ! empty($ll) )
            {
                // Архивируем последнюю активную попытку прохождения модуля
                $params = [
                    'status' => 'archive'
                ];
                utilities::set_learninghistory_cm_snapshot($context->instanceid, $ll->id, $event->relateduserid, $params);
                
                // Получаем текущую попытку задания
                $attemptmod = new attempt_mod_assign($cm->id, $event->relateduserid);
                $currentattempt = $attemptmod->get_current_attemptnumber();
                // Получаем текущую оценку за модуль
                $finalgrade = null;
                $grade_item = new grade_item([
                    'courseid' => $event->courseid,
                    'itemtype' => 'mod',
                    'itemname' => $cm->name,
                    'itemmodule' => $cm->modname,
                    'iteminstance' => $cm->instance
                ]);
                if( ! empty($grade_item) )
                {
                    $final = $grade_item->get_final($event->relateduserid);
                    if( ! empty($final) )
                    {
                        $finalgrade = $final->finalgrade;
                    }
                }
                
                // Создаем новую активную попытку прохождения модуля
                $params = [
                    'status' => 'active',
                    'attemptnumber' => $currentattempt,
                    'finalgrade' => $finalgrade,
                    'completion' => $current->completionstate
                ];
                utilities::set_learninghistory_cm_snapshot($context->instanceid, $ll->id, $event->relateduserid, $params);
            }
        }
    }
    
    public static function course_module_created(\core\event\course_module_created $event)
    {
        $params = [
            'status' => 'active'
        ];
        utilities::set_learninghistory_module_snapshot($event->contextinstanceid, $params);
    }
    
    public static function course_module_updated(\core\event\course_module_updated $event)
    {
        utilities::set_learninghistory_module_snapshot($event->contextinstanceid);
    }
    
    public static function course_module_deleted(\core\event\course_module_deleted $event)
    {
        $params = [
            'status' => 'archive'
        ];
        utilities::set_learninghistory_module_snapshot($event->contextinstanceid, $params);
    }
    
    public static function user_graded(\core\event\user_graded $event)
    {
        $data = $event->get_data();
        $itemid = $data['other']['itemid'];
        $grade_item = new grade_item(['id' => $itemid]);
        // Получим запись grade_grades по пользователю из базы
        $final = $grade_item->get_final($event->relateduserid);
        if ($grade_item->itemtype == 'mod') {
            // Обновление оценки за модуль
            list($course, $cm) = get_course_and_cm_from_instance($grade_item->iteminstance, $grade_item->itemmodule);
            $ll = utilities::get_learninghistory_snapshot_actual($event->courseid, $event->relateduserid);
            if( ! empty($ll) )
            {
                $scale = null;
                if (!is_null($final->rawscaleid)) {
                    $gradescale = new grade_scale(['id' => $final->rawscaleid]);
                    $scale = $gradescale->scale;
                }
                $params = [
                    'status' => 'active',
                    'finalgrade' => $final->finalgrade,
                    'rawgrade' => $final->rawgrade,
                    'rawgrademin' => $final->rawgrademin ?? 0.00000,
                    'rawgrademax' => $final->rawgrademax ?? 100.00000,
                    'rawscaleid' => $final->rawscaleid,
                    'scalesnapshot' => $scale
                ];
                $id = utilities::set_learninghistory_cm_snapshot($cm->id, $ll->id, $event->relateduserid, $params);
                if ($id) {
                    // Формирование события об изменении истории оценки
                    $eventdata = [
                        'objectid' => $id,
                        'courseid' => $event->courseid,
                        'contextid' => $event->contextid,
                        'relateduserid' => $event->relateduserid,
                        'other' => array_merge($params, 
                            [
                                'itemtype' => $grade_item->itemtype, 
                                'itemmodule' => $grade_item->itemmodule, 
                                'iteminstance' => $grade_item->iteminstance,
                                'cmid' => $cm->id,
                                'action' => 'update'
                            ])
                    ];
                    $event = \local_learninghistory\event\cm_grade_history_updated::create($eventdata);
                    $event->trigger();
                }
            }
        } elseif ($grade_item->itemtype == 'course') {
            // Изменилась оценка за курс
            $params['coursefinalgrade'] = grades_manager::get_max_course_finalgrade($event->courseid);
            $params['finalgrade'] = $final->finalgrade;
            $id = utilities::set_learninghistory_snapshot($event->courseid, $event->relateduserid, $params);
            if ($id) {
                // Формирование события об изменении истории оценки
                $eventdata = [
                    'objectid' => $id,
                    'courseid' => $event->courseid,
                    'contextid' => $event->contextid,
                    'relateduserid' => $event->relateduserid,
                    'other' => array_merge($params,
                        [
                            'itemtype' => $grade_item->itemtype, 
                            'itemmodule' => $grade_item->itemmodule, 
                            'iteminstance' => $grade_item->iteminstance,
                            'cmid' => null,
                            'action' => 'update'
                        ])
                ];
                $event = \local_learninghistory\event\course_grade_history_updated::create($eventdata);
                $event->trigger();
            }
        }
    }
    
    public static function attempt_submitted(\mod_quiz\event\attempt_submitted $event) {
        $data = $event->get_data();
        $params = [
            'courseid' => $event->courseid, 
            'itemtype' => 'mod', 
            'itemmodule' => 'quiz', 
            'iteminstance' => $data['other']['quizid'],
            'itemnumber' => 0
        ];
        $grade_item = new grade_item($params);
        list($course, $cm) = get_course_and_cm_from_instance($grade_item->iteminstance, $grade_item->itemmodule);
        $ll = utilities::get_learninghistory_snapshot_actual($event->courseid, $event->relateduserid);
        if( ! empty($ll) )
        {
            // Получим запись grade_grades по пользователю из базы
            $final = $grade_item->get_final($event->relateduserid);
            if( ! empty($final) )
            {
                $params = [
                    'status' => 'active',
                    'finalgrade' => $final->finalgrade,
                    'rawgrade' => $final->rawgrade,
                    'rawgrademin' => $final->rawgrademin ?? 0.00000,
                    'rawgrademax' => $final->rawgrademax ?? 100.00000,
                    'rawscaleid' => $final->rawscaleid,
                    'scalesnapshot' => null
                ];
                $id = utilities::set_learninghistory_cm_snapshot($cm->id, $ll->id, $event->relateduserid, $params);
                if ($id) {
                    // Формирование события об изменении истории оценки
                    $eventdata = [
                        'objectid' => $id,
                        'courseid' => $event->courseid,
                        'contextid' => $event->contextid,
                        'relateduserid' => $event->relateduserid,
                        'other' => array_merge($params,
                            [
                                'itemtype' => $grade_item->itemtype,
                                'itemmodule' => $grade_item->itemmodule,
                                'iteminstance' => $grade_item->iteminstance,
                                'cmid' => $cm->id,
                                'action' => 'update'
                            ])
                    ];
                    $event = \local_learninghistory\event\cm_grade_history_updated::create($eventdata);
                    $event->trigger();
                }
            }
        }
    }
    
    /**
     * Observe all events.
     *
     * @param \core\event\base $event The event.
     * @return void
     */
    public static function catch_all(\core\event\base $event) {
        if( strpos($event->eventname, '\event\course_module_viewed') === false &&
            $event->eventname !== '\mod_assign\event\submission_status_viewed' )
        {// Обрабатываем, только просмотры элементов курса
            return;
        }

        // Получаем активную подписку пользователя
        $ll = utilities::get_learninghistory_snapshot_actual($event->courseid, $event->userid);
        if( ! empty($ll) )
        {
            $llcm = utilities::get_learninghistory_cm_snapshot_actual($event->contextinstanceid, $ll->id, $event->userid);
            if( empty($llcm) )
            {
                $course = get_course($event->courseid);
                // Получаем все необходимые данные про модуль курса
                $modinfo = get_fast_modinfo($course);
                $cm_info = $modinfo->get_cm($event->contextinstanceid);
                $cm = $cm_info->get_course_module_record(true);
                // Получаем информацию о выполнении элемента
                $completion = new completion_info($course);
                $current = $completion->get_data($cm_info, false, $event->userid);
                // Получаем текущую попытку элемента
                if( in_array($cm->modname, activetime::get_mods_supported_attempts()) )
                {
                    $classname = 'local_learninghistory\attempt\mod\attempt_mod_' . $cm->modname;
                } else
                {
                    $classname = 'local_learninghistory\attempt\attempt_base';
                }
                $attemptmod = new $classname($cm->id, $event->userid);
                $attempt = $attemptmod->get_current_attemptnumber();
                if( $attempt === false )
                {
                    $attempt = $attemptmod->get_last_attemptnumber();
                    if( $attempt === false )
                    {
                        $attempt = $attemptmod->get_possible_first_attemptnumber();
                    }
                }
                // Получаем текущую оценку за модуль
                $finalgrade = null;
                $grade_item = new grade_item([
                    'courseid' => $event->courseid,
                    'itemtype' => 'mod',
                    'itemname' => $cm->name,
                    'itemmodule' => $cm->modname,
                    'iteminstance' => $cm->instance
                ]);
                if( ! empty($grade_item) )
                {
                    $final = $grade_item->get_final($event->userid);
                    if( ! empty($final) )
                    {
                        $finalgrade = $final->finalgrade;
                    }
                }
                
                // Создаем новую активную попытку прохождения модуля
                $params = [
                    'status' => 'active',
                    'attemptnumber' => $attempt,
                    'finalgrade' => $finalgrade,
                    'completion' => $current->completionstate
                ];
                
                $llcm = utilities::set_learninghistory_cm_snapshot($cm->id, $ll->id, $event->userid, $params);
            }
        }
    }
    
    public static function course_module_completion_updated(\core\event\course_module_completion_updated $event)
    {
        $ll = utilities::get_learninghistory_snapshot_actual($event->courseid, $event->relateduserid);
        if( ! empty($ll) )
        {
            $course = get_course($event->courseid);
            $modinfo = get_fast_modinfo($course);
            $cm_info = $modinfo->get_cm($event->contextinstanceid);
            $completion = new completion_info($course);
            $current = $completion->get_data($cm_info, false, $event->relateduserid);
            $params = [
                'status' => 'active',
                'completion' => $current->completionstate
            ];
            utilities::set_learninghistory_cm_snapshot($event->contextinstanceid, $ll->id, $event->relateduserid, $params);
        }
    }
    
    /**
     * Обработчик события удаления оценки
     * Событие удаления оценки выбрасывается только тогда, когда оценка действительно удаляется
     * Это происходит при удалении пользователя, отписке пользователя от курса, удалении модуля курса, удалении курса
     * В остальных случаях, например, удаление попытки теста, оценка обновляется
     * Процесс удаления оценки - это часть каскада событий, которые вызваны удалением какой-либо сущности
     * История обучения отслеживает конечные события каскада (удаление модуля, удаление подписки) и обрабатывает их, внося данные в хранилища
     * Сценарий выгрузки оценок во внешнюю базу данных реализует принцип "есть оценка - есть запись, нет оценки - нет записи"
     * Т.к. в самой истории мы оставляет конечные оценки и они там остаются после удаления оценок из мудла,
     * мы реализовали фиктивный способ отслеживания удаления: мы кидаем событие изменения истории оценки заранее, до обработки
     * последнего основного события в каскаде, не внося реальных изменений в запись (это сделают за нас другие обработчики,
     * после того, как будет выброшено финальное событие каскада)
     * @param \core\event\grade_deleted $event
     */
    public static function grade_deleted(\core\event\grade_deleted $event)
    {
        $data = $event->get_data();
        $itemid = $data['other']['itemid'];
        $grade_item = new grade_item(['id' => $itemid]);
        if ($grade_item->itemtype == 'mod') {
            // Обновление оценки за модуль
            list($course, $cm) = get_course_and_cm_from_instance($grade_item->iteminstance, $grade_item->itemmodule);
            $ll = utilities::get_learninghistory_snapshot_actual($event->courseid, $event->relateduserid);
            if( ! empty($ll) )
            {
                $llhcm = utilities::get_learninghistory_cm_snapshot_actual($cm->id, $ll->id, $event->relateduserid);
                if ($llhcm) {
                    // Формирование события об изменении истории оценки
                    $eventdata = [
                        'objectid' => $llhcm->id,
                        'courseid' => $event->courseid,
                        'contextid' => $event->contextid,
                        'relateduserid' => $event->relateduserid,
                        'other' => [
                            'itemtype' => $grade_item->itemtype,
                            'itemmodule' => $grade_item->itemmodule,
                            'iteminstance' => $grade_item->iteminstance,
                            'cmid' => $cm->id,
                            'action' => 'delete'
                        ]
                    ];
                    $event = \local_learninghistory\event\cm_grade_history_updated::create($eventdata);
                    $event->trigger();
                }
            }
        } elseif ($grade_item->itemtype == 'course') {
            $llh = utilities::get_learninghistory_snapshot_actual($event->courseid, $event->relateduserid);
            if ($llh) {
                // Формирование события об изменении истории оценки
                $eventdata = [
                    'objectid' => $llh->id,
                    'courseid' => $event->courseid,
                    'contextid' => $event->contextid,
                    'relateduserid' => $event->relateduserid,
                    'other' => [
                        'itemtype' => $grade_item->itemtype,
                        'itemmodule' => $grade_item->itemmodule,
                        'iteminstance' => $grade_item->iteminstance,
                        'cmid' => null,
                        'action' => 'delete'
                    ]
                ];
                $event = \local_learninghistory\event\course_grade_history_updated::create($eventdata);
                $event->trigger();
            }
        }
    }
}
