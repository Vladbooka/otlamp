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
 * Юнит-тесты сценариев
 *
 * @package    local
 * @subpackage pprocessing
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

define('TIME_PASSIVE', 0);
define('TIME_ACTIVE', 1);

define('DURATION_TINY', 1);
define('DURATION_SHORT', 2);
define('DURATION_LONG', 3);

// ПЛАН ТЕСТИРОВАНИЯ
// https://docs.google.com/spreadsheets/d/1BV2J3F-HJUkeAFQyXLkEjfJqVK5eLVv13-FJ3jy4X_c/edit?usp=sharing
// над горизонтальной чертой вертикальные черточки - время лога (лог1, лог2 и т.д.)
// под горизонтальной чертой пунктирные вертикальные - время расчетов времени затраченного на обучение (чек1, чек2 и т.д.)
// цветовые обозначения имеются в документе по ссылке


class local_learninghistory_activetime_course_testcase extends advanced_testcase
{
    const debug = false;
    const mode = 1;
    const delay = 60;
    const delaybetweenlogs = 70;
    
    private $time = 0;
    
    private $course = null;
    private $user = null;
    private $context = null;
    
    //active time object
    private $ATO = null;
    
    private $active = 0;
    private $passive = 0;
    
    /**
     * Setup testcase.
     */
    public function setUp() {
        $this->resetAfterTest();
        
        // Включение стандартного лога и обеспечение записи в базу без буферизации
        $this->preventResetByRollback();
        set_config('enabled_stores', 'logstore_standard', 'tool_log');
        set_config('buffersize', 0, 'logstore_standard');
    }
    
    /**
     * Получение стандартного хранилища логов
     *
     * @return logstore_standard\log\store
     */
    private function get_logreader()
    {
        global $CFG;
        
        require_once($CFG->libdir . '/datalib.php');
        $logmanager = get_log_manager();
        $readers = $logmanager->get_readers();
        if (array_key_exists('logstore_standard', $readers)) {
            return $readers['logstore_standard'];
        }
        $this->fail('Fail on getting standard logstore');
    }
    
    /**
     * Поиск записи лога в БД по данным события
     *
     * @param \local_learninghistory\event\activetime_updated $event
     * @return mixed
     */
    private function find_events($eventdata, $sort, $limitnum)
    {
        $conditions = [];
        $parameters = [];
        foreach($eventdata as $field=>$value)
        {
            $conditions[] = $field.'=:'.$field;
            if ($field == 'other')
            {
                $parameters[$field] = serialize($value);
                
            } else
            {
                $parameters[$field] = $value;
            }
        }
        
        $reader = $this->get_logreader();
        return $reader->get_events_select(implode(' AND ', $conditions), $parameters, $sort, 0, $limitnum);
    }
    
    /**
     * Добавление лога о времени, проведенном в системе с указанием даты создания
     *
     * @param int $timecreated
     */
    private function add_log()
    {
        global $DB;
        
        $this->ATO->add_log($this->user->id, $this->context->id);
        
        $eventdata = [
            'eventname' => '\\local_learninghistory\\event\\activetime_updated',
            'component' => 'local_learninghistory',
            'action' => 'updated',
            'target' => 'activetime',
            'contextid' => $this->context->id,
            'courseid' => $this->course->id,
            'relateduserid' => $this->user->id,
            'other' => []
        ];
        
        // найдем событие по его свойствам
        $foundevents = $this->find_events($eventdata, 'timecreated DESC, id DESC', 1);
        $this->assertCount(1, $foundevents);
        
        // теперь у нас есть идентификатор лога
        reset($foundevents);
        $logid = key($foundevents);
        
        // получим лог для изменения
        $logrecord = $DB->get_record($this->get_logreader()->get_internal_log_table_name(), ['id' => $logid]);
        
        // внесем нужные правки
        $logrecord->timecreated = $this->current_time();
        $DB->update_record(
            $this->get_logreader()->get_internal_log_table_name(),
            $logrecord
        );
    }
    
    private function reset_atlastupdate()
    {
        global $DB;
        
        if (empty($this->user))
        {
            throw new Exception('user should be specified');
        }
        
        if (empty($this->course))
        {
            throw new Exception('course should be specified');
        }
        
        $query = 'userid = :userid AND courseid = :courseid AND status = "active"';
        $params = [
            'userid' => $this->user->id,
            'courseid' => $this->course->id
        ];
        $activetimerecords = $DB->get_records_select('local_learninghistory', $query, $params);
        $activetimerecord = array_shift($activetimerecords);
        $activetimerecord->lastupdate = $activetimerecord->begindate;
        $activetimerecord->atlastupdate = $activetimerecord->begindate;
        $DB->update_record('local_learninghistory', $activetimerecord);
    }
    
    private function get_learninghistory_record($userid, $courseid)
    {
        global $DB;
        
        $query = 'userid = :userid AND courseid = :courseid AND status = "active"';
        $params = [
            'userid' => $userid,
            'courseid' => $courseid
        ];
        
        return $DB->get_records_select('local_learninghistory', $query, $params);
    }
    
    
    private function get_user_logs($userid, $courseid, $contextid)
    {
        global $DB;
        
        $eventdata = [
            'eventname' => '\\local_learninghistory\\event\\activetime_updated',
            'component' => 'local_learninghistory',
            'target' => 'activetime',
            'contextid' => $contextid,
            'courseid' => $courseid,
            'userid' => $userid,
        ];
        
        // найдем событие по его свойствам
        $logrecords = [];
        $foundevents = $this->find_events($eventdata, 'timecreated DESC, id DESC', 0);
        if (!empty($foundevents))
        {
            // получим лог для изменения
            $logrecords = $DB->get_records_select(
                $this->get_logreader()->get_internal_log_table_name(),
                'id IN ('.implode(',', array_keys($foundevents)).')'
            );
        }
        
        return $logrecords;
    }
    
    private function set_activetime_config()
    {
        global $DB;
        
        if (empty($this->course))
        {
            throw new Exception('course should be specified');
        }
        
        $cfg = [
            'mode' => $this::mode,
            'delay' => $this::delay,
            'delaybetweenlogs' => $this::delaybetweenlogs
        ];
        
        foreach($cfg as $prop => $val)
        {
            $DB->insert_record('llhistory_properties', [
                'courseid' => $this->course->id,
                'name' => $prop,
                'value' => $val,
            ]);
        }
    }
    
    private function inc_duration($durationtype, $timetype)
    {
        switch($durationtype)
        {
            case DURATION_TINY:
                $duration = floor(rand(1, $this::delaybetweenlogs-1) / 2);
                break;
            case DURATION_SHORT:
                $duration = rand(1, $this::delaybetweenlogs-1);
                break;
            case DURATION_LONG:
                $duration = rand($this::delaybetweenlogs+1, $this::delaybetweenlogs*3);
                break;
            default:
                $duration = 0;
                break;
        }
        
        switch($timetype)
        {
            case TIME_ACTIVE:
                $this->active += $duration;
                break;
            case TIME_PASSIVE:
                $this->passive += $duration;
                break;
        }
        
        return $duration;
    }
    
    private function current_time()
    {
        return $this->time + $this->active + $this->passive;
    }
    
    private function check($assertduration, $refresh = false, $message = '')
    {
        // выносим дебаг в функцию, чтобы сделать покороче запись при вызове :)
        $debug = function() {
            $debug = '';
            if ($this::debug)
            {
                $lhr = $this->get_learninghistory_record($this->user->id, $this->course->id);
                $logs = $this->get_user_logs($this->user->id, $this->course->id, $this->context->id);
                $debug .= PHP_EOL . var_export([
                    'activetime' => $lhr,
                    'curtime' => $this->current_time(),
                    'logs' => $logs
                ], true);
            }
            return $debug;
        };
        
        $this->ATO->check_activetime($refresh, $this->current_time());
        $checkresult = $this->ATO->get_current_activetime($this->user->id);
        
        $this->assertEquals($assertduration, $checkresult, $message.PHP_EOL.$debug());
    }
    
    public function test_course_activetime_test()
    {
        // тестовые данные генерируются начиная с даты годовалой давности
        $this->time = time() - 365 * 24 * 60 * 60;
        
        // генерируем пользователя
        $usertmpl = ['timecreated' => $this->time-3];
        $this->user = $this->getDataGenerator()->create_user($usertmpl);
        
        // генерируем курс
        $coursetmpl = ['timecreated' => $this->time-3, 'startdate' => $this->time-2];
        $this->course = $this->getDataGenerator()->create_course($coursetmpl);
        $this->context = context_course::instance($this->course->id);
        
        // генерируем подписку пользователя на курс
        $this->getDataGenerator()->enrol_user($this->user->id, $this->course->id, 'student', 'manual', $this->time-1);
        
        // генерируем настройки activetime для курса
        $this->set_activetime_config();
        
        // сбрасываем atlastupdate, так как при подписке пользователя на курс, ему прописывается текущее время в atlastupdate
        $this->reset_atlastupdate();
        
        
        // начинаем проверку
        $this->ATO = new \local_learninghistory\activetime($this->course->id);
        
        // сразу установим пользователя как текущего, чтобы сгенерированные логи были сгенерированы им
        $this->setUser($this->user);
        
        // подписка началась 1 год и 1 секунду назад
        
        // чек1
        // спустя длительное время без логов (только пассивное время), не должно быть зафиксировано никакой активности
        $this->inc_duration(DURATION_LONG, TIME_PASSIVE);
        $this->check(0, false, 'чек1 failed');
        
        // лог1
        // добавим первый лог (в момент последней проверки)
        $this->add_log();
        
        // лог2
        // Этот лог идёт следом за предыдущим и время между ними попадет в зачёт
        $log1log2 = $this->inc_duration(DURATION_SHORT, TIME_ACTIVE);
        $this->add_log();
        
        // чек2
        // в момент лога при пересчете активность еще не поменялась и соответствует предыдущему значению
        $this->check(0, false, 'чек2 failed');
        
        // чек3
        // но при последующем пересчете реально потраченное время уже должно засчитаться
        // в зачёт должно попасть всё активное время (нет неподтвержденного активного, которое в зачёт не попадет)
        $this->inc_duration(DURATION_SHORT, TIME_PASSIVE);
        $this->check($this->active, false, 'чек3 failed');
        
        // лог3
        // После длительного перерыва добавляем лог, время перед ним - пассивное и в зачёт не попадёт
        $this->inc_duration(DURATION_LONG, TIME_PASSIVE);
        $this->add_log();
        
        // чек4
        // длительный перерыв не должен засчитываться, текущая активность (до появления следующего лога) - тоже
        $log3log4 = $this->inc_duration(DURATION_TINY, TIME_ACTIVE);
        $this->check($log1log2, false, 'чек4 failed');
        
        // лог4
        // Этот лог идёт следом за предыдущим и время между ними попадет в зачёт
        $log3log4 += $this->inc_duration(DURATION_TINY, TIME_ACTIVE);
        $this->add_log();
        
        // чек5
        // время между прошедшими логами 3 и 4 теперь должно попасть в зачёт, а активное время, начавшееся после 4 лога - нет
        $log4log5 = $this->inc_duration(DURATION_TINY, TIME_ACTIVE);
        $this->check($log1log2 + $log3log4, false, 'чек5 failed');
        
        // лог5
        // Этот лог идёт следом за предыдущим и время между ними попадет в зачёт
        $log4log5 += $this->inc_duration(DURATION_TINY, TIME_ACTIVE);
        $this->add_log();
        
        // лог6
        // Этот лог идёт следом за предыдущим и время между ними попадет в зачёт
        $log5log6 = $this->inc_duration(DURATION_SHORT, TIME_ACTIVE);
        $this->add_log();
        
        // чек6
        // проверка выполняется одновременно с логом6 и время между логами 5 и 6 в зачёт не попадёт
        // а время между логами 1-2 и 3-5 должо попасть
        $this->check($log1log2 + $log3log4 + $log4log5, false, 'чек6 failed');
        
        
        // лог7
        // Этот лог идёт следом за предыдущим и время между ними попадет в зачёт
        $log6log7 = $this->inc_duration(DURATION_SHORT, TIME_ACTIVE);
        $this->add_log();
        
        // чек7
        // проверка выполняется после длительного перерыва и всё активное время должно попасть в зачёт
        // (нет неподтвержденного активного, которое в зачёт не попадет)
        $this->inc_duration(DURATION_LONG, TIME_PASSIVE);
        $this->check($this->active, false, 'чек7 failed');
        
        // чек8
        // проверка пересчета времени без изменения параметров (результат должен остаться тот же)
        $this->check($this->active, true, 'чек8 failed');
        
        /**
         * @todo добавить тесты:
         *        - пересчет времени с изменением параметров (результат должен измениться)
         */
    }
}