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
 * Базовый класс работы с попытками прохождения модулей курса
 *
 * @package    local_learninghistory
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_learninghistory\attempt;
use local_learninghistory\local\utilities;
use stdClass;

class attempt_base implements attempt_interface
{
    /**
     * Идентификатор пользователя
     * @var int
     */
    protected $userid;
    
    /**
     * Тип модуля курса assign|quiz|...
     * @var string
     */
    protected $modname;
    
    /**
     * Объект курса
     * @var stdClass
     */
    protected $course;
    
    /**
     * Объект модуля курса
     * @var stdClass
     */
    protected $cm;
    
    /**
     * Инстанс модуля курса
     * @var int
     */
    protected $instance;
    
    /**
     * Возможный номер первой попытки прохождения модуля курса
     * @var integer
     */
    protected $possiblefirstattemptnumber = 0;
    
    /**
     * Временный кеш
     * @var stdClass
     */
    protected $cache;
    
    /**
     * Коструктор
     * @param int $cmid идентификатор модуля курса
     * @param int $userid идентификатор пользователя
     */
    public function __construct($cmid, $userid)
    {
        $this->cmid = $cmid;
        $this->userid = $userid;
        $this->course = utilities::get_course_by_cmid($cmid);
        if( ! empty($this->course) )
        {
            $this->cm = utilities::get_module_from_cmid($cmid);
        }
        $this->cache = new stdClass();
    }
    
    /**
     * Получить номер текущей попытки прохождения модуля курса
     * {@inheritDoc}
     * @see \local_learninghistory\attempt\attempt_interface::get_current_attemptnumber()
     * @return int
     */
    public function get_current_attemptnumber()
    {
        // По умолчанию возвращает 0, если необходимо - нужно переопределить в дочернем классе
        return 0;
    }
    
    /**
     * Получить номер последней попытки прохождения модуля курса
     * {@inheritDoc}
     * @see \local_learninghistory\attempt\attempt_interface::get_last_attemptnumber()
     * @return int
     */
    public function get_last_attemptnumber()
    {
        // По умолчанию возвращает 0, если необходимо - нужно переопределить в дочернем классе
        return 0;
    }
    
    /**
     * Получить возможный номер первой попытки прохождения модуля курса
     * {@inheritDoc}
     * @see \local_learninghistory\attempt\attempt_interface::get_possible_first_attemptnumber()
     * @return int
     */
    public function get_possible_first_attemptnumber()
    {
        return $this->possiblefirstattemptnumber;
    }
    
    /**
     * Получить количество попыток прохождения модуля курса
     * @param unknown $lastattempt номер последней попытки из базы
     * @return int|sstring количество попыток прохождения модуля курса или '-'
     */
    public function get_attempts_count($lastattempt = null)
    {
        if( is_null($lastattempt) )
        {// Если не передан номер последней попытки из базы, получим его
            $ll = $this->get_learninghistory_snapshot_actual();
            if( ! empty($ll) )
            {
                $llcm = utilities::get_learninghistory_cm_snapshot_actual($this->cmid, $ll->id, $this->userid);
                if( ! empty($llcm) )
                {// Приведем результат к нужному формату с учетом особенностей модуля курса
                    return ((int)$llcm->attemptnumber + 1 - $this->possiblefirstattemptnumber);
                }
            }
            // Нет последней попытки
            return '-';
        } else
        {// Если передан номер последней попытки из базы, сразу вернем результат в нужном формате с учетом особенностей модуля курса
            return ((int)$lastattempt + 1 - $this->possiblefirstattemptnumber);
        }
    }
    
    /**
     * Получить актуальную подписку пользователя на курс, работает с кеш-хранилищем для оптимизации количества запросов к БД
     * @return stdClass|boolean объект актуальной подписки или false
     */
    protected function get_learninghistory_snapshot_actual()
    {
        if( ! empty($this->cache->ll[$this->course->id][$this->userid]) )
        {
            return $this->cache->ll[$this->course->id][$this->userid];
        } else
        {
            $ll = utilities::get_learninghistory_snapshot_actual($this->course->id, $this->userid);
            return $this->cache->ll[$this->course->id][$this->userid] = $ll;
        }
    }
    
    public function get_attempt_linked_log($logtimecreated)
    {
        return $this->get_possible_first_attemptnumber();
    }
}