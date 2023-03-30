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
 * @package    local_learninghistory
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_learninghistory;

require_once(dirname(realpath(__FILE__)).'/../../../config.php');
require_once($CFG->dirroot . '/local/learninghistory/lib.php');
require_once($CFG->dirroot . '/local/learninghistory/classes/attempt/attempt_base.php');
require_once($CFG->dirroot . '/local/learninghistory/classes/attempt/mod/attempt_mod_assign.php');
require_once($CFG->dirroot . '/local/learninghistory/classes/attempt/mod/attempt_mod_quiz.php');

defined('MOODLE_INTERNAL') || die();

use context;
use context_module;
use context_course;
use local_learninghistory\local\utilities;
use moodle_exception;

class activetime
{
    /**
     * Допустимая задержка между логами по умолчанию
     * @var integer
     */
    const DEFAULT_DELAY_BETWEEN_LOGS = 600;
    
    private $delaybetweenlogs;
    
    protected $courseid;
    
    protected static $mods_supported_attempts = [
        'quiz',
        'assign'
    ];
    
    protected $debug = false;
    
    public function __construct($courseid)
    {
        global $DB, $CFG;
        if( $DB->get_record('course', ['id' => $courseid], '*', IGNORE_MISSING) )
        {// Проверим, что курс существует
            $this->courseid = $courseid;
        } else
        {// Если нет - вернем false
            return false;
        }
        $mode = local_learninghistory_get_course_config($this->courseid, 'mode');
        $delay = local_learninghistory_get_course_config($this->courseid, 'delay');
        $delaybetweenlogs = local_learninghistory_get_course_config($this->courseid, 'delaybetweenlogs');
        switch($mode)
        {
            case 0:
                $this->delaybetweenlogs = self::DEFAULT_DELAY_BETWEEN_LOGS;
                break;
            case 1:
                if ($delaybetweenlogs) {
                    // Если указано максимально допустимое время между логами - используем его
                    $this->delaybetweenlogs = $delaybetweenlogs;
                } elseif ($delay) {
                    // Если не указано, но есть время между дополнительными проверками - используем его с дополнительным запасом
                    $this->delaybetweenlogs = $delay + 10;
                } else {
                    // Если так случилось, что ничего нет, по умолчанию время 5 минут
                    $this->delaybetweenlogs = self::DEFAULT_DELAY_BETWEEN_LOGS;
                }
                break;
            default:
                $this->delaybetweenlogs = self::DEFAULT_DELAY_BETWEEN_LOGS;
                break;
        }
        $this->debug = !empty($CFG->debugdeveloper);
    }
    
    /**
     * Добавление дополнительных логов
     * @param int $userid id пользователя
     * @param int $contextid id контекста
     */
    public function add_log($userid, $contextid)
    {
        $context = context::instance_by_id($contextid);
        $other = [];
        if( is_a($context, '\context_module') )
        {
            $modinfo = get_fast_modinfo($this->courseid);
            $cm = $modinfo->get_cm($context->instanceid)->get_course_module_record(true);
            if( in_array($cm->modname, self::get_mods_supported_attempts()) )
            {
                $classname = '\local_learninghistory\attempt\mod\attempt_mod_' . $cm->modname;
                
            } else
            {
                $classname = '\local_learninghistory\attempt\attempt_base';
            }
            $attemptmod = new $classname($context->instanceid, $userid);
            $attempt = $attemptmod->get_current_attemptnumber();
            if( $attempt === false )
            {
                $attempt = $attemptmod->get_last_attemptnumber();
                if( $attempt === false )
                {
                    $attempt = $attemptmod->get_possible_first_attemptnumber();
                }
            }

            $other = ['attempt' => $attempt];
        }
        
        // Формирование события об изменении состояния
        $eventdata = [
            'courseid' => $this->courseid,
            'context' => $context,
            'relateduserid' => $userid,
            'other' => $other
        ];
        $event = \local_learninghistory\event\activetime_updated::create($eventdata);
        $event->trigger();
    }

/**
 * Пересчитать время затраченное на изучение курса
 * @param boolean $refresh флаг обновления, если указано true - сделает полный пересчет времени с момента начала подписки
 *                                          если указано false - добавит не посчитанное время к последнему посчитанному значению
 * @throws moodle_exception
 */
    public function check_activetime($refresh = false, $fixtime = null)
    {
        global $DB;
        
        // Возможность на время дебага ограничить обработку логов одним курсом (указывается идентификатор)
        $debuglimitcourse = null;
        // Возможность на время дебага ограничить обработку логов одним пользователем (указывается идентификатор)
        $debuglimituser = null;
        
        if (!is_null($debuglimitcourse) && $this->debug && $debuglimitcourse != $this->courseid)
        {
            return;
        }
        
        $this->debug('Courseid: ' . $this->courseid, true);
        
        $atlastupdate = 0;
        $context = \context_course::instance($this->courseid, IGNORE_MISSING);
        if (!empty($context))
        {// Если нашли такой контекст
            
            //получение времени последнего лога курса на данный момент
            if (empty($this->courseid) || $this->courseid == SITEID) {
                throw new moodle_exception('courseid_not_set');
            }
            
            //Получение записанных на курс пользователей и обновление соответствующих им записей для курсов и для элементов курса
            $users = get_enrolled_users($context);
            if (!empty($users))
            {
                list($select, $params) = $DB->get_in_or_equal(array_keys($users));
                $sql = 'SELECT * FROM {local_learninghistory}
                         WHERE userid ' . $select . ' AND
                               courseid = ? AND
                               status = ?';
                array_push($params, $this->courseid, 'active');
                $records = $DB->get_records_sql($sql, $params);
                if (is_null($fixtime))
                {
                    $fixtime = time();// Фиксируем время
                }
                
                // количество обработанных пользователей
                $processedusers = 0;
                foreach($records as $record)
                {
                    if (!is_null($debuglimituser) && $this->debug && $debuglimituser != $record->userid)
                    {
                        continue;
                    }
                    
                    $this->debug('Userid: ' . $record->userid);
                    
                    $time = $fixtime;
                    if ($refresh) {
                        // Обнуление, требуется для случаев, когда в записях БД из-за старых ошибок были модули, которых быть не должно
                        $DB->set_field('local_learninghistory_cm', 'activetime', 0, ['llid' => $record->id]);
                        $atlastupdate = $record->begindate;
                        $record->activetime = 0;
                    } else {
                        $atlastupdate = ! empty($record->atlastupdate) ? $record->atlastupdate : $record->begindate;
                    }
                    $logs = $this->get_logs($record->userid, $this->courseid, $atlastupdate, $time, 'timecreated DESC', 0, 1);
                    if (!empty($logs)) {
                        // В качестве фиксации используем время последнего лога в курсе
                        $lastlog = array_shift($logs);
                        $time = $lastlog->timecreated;
                    }
                    $this->debug([
                        'Startdate: ' . $atlastupdate,
                        'Enddate: ' . $time,
                    ]);
                    // Отправляем $time + 1, т.к. при получении логов используется строгое сравнение, а последний лог нам нужен
                    list($courseactivetime, $modsactivetime) = $this->get_new_activetime($record->userid, $atlastupdate, $time + 1);
                    $this->debug('Courseactivetime: ' . $courseactivetime);
                    
                    if (!empty($courseactivetime))
                    {
                        $record->activetime += $courseactivetime;
                        $record->atlastupdate = $time;
                        $coursetimeupdres = $DB->update_record('local_learninghistory', $record);
                        $this->debug('Courseactivetime updated: '.var_export($coursetimeupdres, true));
                    }
                    
                    if (!empty($modsactivetime))
                    {
                        // количество попыток подлежащих обновлению (для дебага)
                        $modsactivetimenum = 0;
                        // сумма времени попыток подлежащих обновлению (для дебага)
                        $modsactivetimesum = 0;
                        // количество обновленных записей (для дебага)
                        $modstimeupd = 0;
                        $params = $selectarr = [];
                        $select = 'llid = ?';
                        $params[] = $record->id;
                        foreach($modsactivetime as $contextid => $attempts)
                        {
                            $params[] = $contextid;
                            list($insql, $attemptparams) = $DB->get_in_or_equal(array_keys($attempts));
                            $selectarr[] = '(contextid = ? AND attemptnumber ' . $insql . ')';
                            $params = array_merge($params, $attemptparams);
                            if ($this->debug)
                            {
                                $modsactivetimenum += count($attempts);
                                $modsactivetimesum += array_sum($attempts);
                            }
                        }
                        $this->debug([
                            'Number of modsactivetime: ' . $modsactivetimenum,
                            'Sum of modsactivetime: '.$modsactivetimesum
                        ]);
                        if ($modsactivetimesum > $courseactivetime)
                        {
                            $this->debug('ALARM! Sum of modsactivetime greater than courseactivetime!!!', true);
                        }
                        if( ! empty($selectarr) )
                        {
                            $select .= ' AND (' . implode(' OR ', $selectarr).')';
                        }
                        $cmrecords = $DB->get_records_select('local_learninghistory_cm', $select, $params);
                        
                        foreach($cmrecords as $cmrecord)
                        {
                            if( isset($modsactivetime[$cmrecord->contextid][$cmrecord->attemptnumber]) )
                            {
                                $cmrecord->activetime += $modsactivetime[$cmrecord->contextid][$cmrecord->attemptnumber];
                                $cmrecord->atlastupdate = $time;
                                $modstimeupd += (int)$DB->update_record('local_learninghistory_cm', $cmrecord);
                            }
                        }
                        $this->debug('Num of updated llcms: '.$modstimeupd);
                    }
                    
                    $processedusers++;
                    $this->debug('*****************************************');
                }
                $this->debug(['Course process finished','Users processed: '.$processedusers], true);
            }
        }
    }
    
    /**
     * Получение попытки, основываясь на логе
     *
     * @param object $userlog - лог пользователя
     * @return number
     */
    protected function get_attempt_by_userlog($userlog)
    {
        $attempt = 0;
        
        set_error_handler([$this, 'errorhandler']);
        try {
            $data = unserialize($userlog->other);
        } catch (moodle_exception $e) {
            /**
             * @todo сделать что-нибудь с логами скорма (\mod_scorm\event\sco_launched),
             *       которые не получается вернуть к php-значению из хранимого представления (unserialize)
             */
        }
        restore_error_handler();
        
        if (isset($data['attempt']))
        {
            $attempt = $data['attempt'];
            
        } else
        {
            try {
                $attemptmod = self::construct_attempt($this->courseid, $userlog->contextinstanceid, $userlog->userid);
                $attempt = $attemptmod->get_attempt_linked_log($userlog->timecreated);
            } catch(\moodle_exception $ex){
                // мы здесь потому что скорее всего переданный лог не принадлежит модулю
                $attempt = false;
            }
        }
        
        return $attempt;
    }
    
    /**
     * Получение логов по курсу и пользователю
     * @param int $userid - id пользователя
     * @param int $courseid - id курса, если не указано или интерпретируемо как false, то вернёт 0
     * @param int $begindate - c какого момента брать логи (по умолчанию с самого начала)
     * @param int $enddate - до какого момента брать логи (по умолчанию до конца)
     * @param string $sort -  порядок сортировки результата (валидный SQL ORDER BY параметр)
     * @param int $limitfrom - вернуть подмножество записей, начиная с указанной
     * @param int $limitnum - количество возвращаемых логов
     * @return mixed: array - массив логов (массив объектов) или int - 0
     */
    protected function get_logs($userid = null, $courseid = 0, $begindate = null, $enddate = null, $sort = 'timecreated ASC', $limitfrom = 0, $limitnum = 0)
    {
        global $CFG;
        
        $doflibpath = $CFG->dirroot . '/blocks/dof/locallib.php';
        if (!file_exists($doflibpath))
        {
            throw new moodle_exception('Recalculation of time spent on studying the course is performed only if "Dean`s office" is installed in the system.');
        }
        require_once($doflibpath);
        global $DOF;
        
        //Получение логов
        $userlogs = $DOF->modlib('ama')->course($this->courseid)->get_logs($userid, $courseid, $begindate, $enddate, $sort, $limitfrom, $limitnum);
        
        return (empty($userlogs) ? [] : $userlogs);
    }
    
    /**
     * Получение времени, потраченного на изучение курса и на изучение модулей.
     * @param int $userid - id пользователя
     * @param int $atlastupdate - c какого момента начинать считать(по умолчанию с самого начала)
     * @param int $enddate - до какого момента считать(по умолчанию до конца)
     * @throws moodle_exception
     * @return array - [время в курсе, время в модулях]
     */
    protected function get_new_activetime($userid, $atlastupdate, $enddate)
    {
        // Отображать ли дополнительные отладочные сообщения, содержащие доп.информацию по каждому логу
        $displayextendeddebug = false;
        $extendeddebugdata = [];
        $collectextendeddebug = function($message) use ($displayextendeddebug, &$extendeddebugdata) {
            if ($displayextendeddebug)
            {
                $extendeddebugdata[] = $message;
            }
        };
        
        $courseactivetime = 0;
        $modsactivetime = [];
        
        if (!empty($this->courseid) && $this->courseid != SITEID) {
            
            $prevlog = null;
            $prevattempt = null;
            
            $userlogs = $this->get_logs($userid, $this->courseid, $atlastupdate - 1, $enddate);
            $this->debug('Logs count: ' . count($userlogs));
            
            foreach($userlogs as $userlog)
            {
                $collectextendeddebug('Contextid: '.$userlog->contextid);
                
                if ($userlog->contextid == context_course::instance($this->courseid))
                {
                    // Контекст курса, не имеет попытки, это свойство модулей курса
                    $attempt = false;
                } else {
                    // Получение попытки прохождения модуля курса
                    // Модули не имеющие попыток вернут 0
                    // Если лог был между попытками, quiz вернет false (не будет относиться к изучению модуля)
                    $attempt = $this->get_attempt_by_userlog($userlog);
                }
                
                $collectextendeddebug('Attempt: '.var_export($attempt, true));
                
                if (!is_null($prevlog))
                {
                    // Разница между текущим логом из выборки и предыдущим логом
                    $logsdiff = $userlog->timecreated - $prevlog->timecreated;
                    
                    $collectextendeddebug('Logs diff: '.$logsdiff);
                    $collectextendeddebug('Less than delay: '.var_export(($logsdiff < $this->delaybetweenlogs), true));
                    
                    // Если меньше заданной настройками максимально допустимой разницы
                    if ($logsdiff < $this->delaybetweenlogs)
                    {
                        /////////////////////////////
                        // Подсчёт времени модулей //
                        /////////////////////////////
                        
                        // Лог будет браться в зачет только если для предыдущего лога определена попытка в модуле
                        // проверяется предыдущий, т.к. время в большинстве модулей фиксируется только однажды, в начале,
                        // а следующий лог фиксируется уже вне модуля и означает окончание пребывания в модуле
                        $collectextendeddebug('Previous attempt: '.var_export($prevattempt, true));
                        
                        if ($prevattempt !== false)
                        {
                            // Инициализация данных, если требуется
                            if (!isset($modsactivetime[$prevlog->contextid][$prevattempt]))
                            {
                                $modsactivetime[$prevlog->contextid][$prevattempt] = 0;
                            }
                            
                            $collectextendeddebug('Previous mod time: '.$modsactivetime[$prevlog->contextid][$prevattempt]);
                            
                            // ко времени непрерывного изучения модуля курса добавляется время между логами
                            $modsactivetime[$prevlog->contextid][$prevattempt] += $logsdiff;
                            
                            $collectextendeddebug('Current mod time: '.$modsactivetime[$prevlog->contextid][$prevattempt]);
                        }
                        
                        ///////////////////////////
                        // Подсчёт времени курса //
                        ///////////////////////////
                        
                        $collectextendeddebug('Previous course time: '.$courseactivetime);
                        
                        // к общему времени непрерывного изучения курса добавляется время между логами
                        $courseactivetime += $logsdiff;
                        
                        $collectextendeddebug('Current course time: '.$courseactivetime);
                        
                    }
                }
                if ($displayextendeddebug)
                {
                    $this->debug($extendeddebugdata, true);
                    $extendeddebugdata = [];
                }
                
                //Сохранение времени лога для использования в следующем цикле
                $prevlog = clone($userlog);
                $prevattempt = $attempt;
            }
            
            return [$courseactivetime, $modsactivetime];
            
        } else
        {
            throw new moodle_exception('courseid_not_set');
        }
    }
    
    public function get_current_activetime($userid)
    {
        global $DB;
        $activetime = 0;
        $firstrecord = $this->get_first_active($userid);
        if ($firstrecord !== false) {
            $activetime = (int)$firstrecord->activetime;
        }
        return $activetime;
    }
    
    public function get_first_active($userid) {
        
        global $DB;
        
        $params = [];
        $select = 'userid = ? AND
                    courseid = ? AND
                    status = ?';
        array_push($params, $userid, $this->courseid, 'active');
        $records = $DB->get_records_select('local_learninghistory', $select, $params);
        
        if( ! empty($records) )
        {
            return array_shift($records);
        } else {
            return false;
        }
    }
    
    public static function construct_attempt($courseorid, $cmid, $userid)
    {
        // Create an array to hold the cache
        static $attemptscache = array();
        
        if (isset($attemptscache[$cmid][$userid]))
        {
            return $attemptscache[$cmid][$userid];
        }
        
        $modinfo = get_fast_modinfo($courseorid, -1);
        $cm = $modinfo->get_cm($cmid);
        
        if (in_array($cm->modname, self::get_mods_supported_attempts()))
        {
            $classname = 'local_learninghistory\attempt\mod\attempt_mod_' . $cm->modname;
        } else
        {
            $classname = 'local_learninghistory\attempt\attempt_base';
        }
        $attemptscache[$cmid][$userid] = new $classname($cmid, $userid);
        
        return $attemptscache[$cmid][$userid];
    }
    
    public static function get_mods_supported_attempts()
    {
        return self::$mods_supported_attempts;
    }
    
    /**
     * Кастомный обработчик ошибок, который отлавливает замечания E_NOTICE и выбрасывает на них свое исключение
     * @param int $errno уровень ошибки в виде целого числа
     * @param string $errstr сообщение об ошибке в виде строки
     * @param string $errfile имя файла, в котором произошла ошибка, в виде строки
     * @param int $errline номер строки, в которой произошла ошибка, в виде целого числа
     * @throws moodle_exception
     * @return boolean
     */
    private function errorhandler($errno, $errstr, $errfile, $errline)
    {
        switch ($errno) {
            case E_NOTICE:
                // Ловим нотисы и выбрасываем свое исключение
                throw new moodle_exception('custom_e_notice');
                break;
                
            default:
                // Передаем обработку стандартному хендлеру php
                return false;
                break;
        }
    }
    
    private function debug($messages, $decorate=false)
    {
        if ($this->debug) {
            if (!is_array($messages))
            {
                $messages = [$messages];
            }
            $maxmessage = max(array_map('strlen', $messages));
            if ($decorate)
            {
                mtrace(str_pad('', ($maxmessage + 4), '*'));
            }
            foreach ($messages as $message)
            {
                if ($decorate)
                {
                    $message = '*' . str_pad($message, ($maxmessage + 2), ' ', STR_PAD_BOTH) . '*';
                }
                
                mtrace($message);
            }
            if ($decorate)
            {
                mtrace(str_pad('', ($maxmessage + 4), '*'));
            }
        }
    }
}