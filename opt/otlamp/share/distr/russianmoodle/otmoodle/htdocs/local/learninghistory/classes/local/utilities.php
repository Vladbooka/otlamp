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
 * Functions and classes for learninghistory plugin
 *
 * @package    local_learninghistory
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_learninghistory\local;

use local_learninghistory\local\enrol_manager;
use local_learninghistory\local\completion_tracker;
use local_learninghistory\local\grades_manager;
use local_learninghistory\activetime;
use local_learninghistory\attempt\attempt_base;
use local_learninghistory\attempt\mod\attempt_mod_assign;
use local_learninghistory\attempt\mod\attempt_mod_quiz;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/group/lib.php');
require_once($CFG->libdir . '/accesslib.php');

/**
 * Утилиты для плагина истории обучения
 *
 * @package   local_learninghistory
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class utilities {

    /**
     * Получить историю обучения студента
     *
     * Метод возвращает историю обучения студента,
     * производя фильтрацию по курсу, а также дополнительным полям
     *
     * @param int $courseid  - ID курса, по которому необходимо получить историю.
     *                         Все курсы, если не указано
     * @param int $userid    - ID пользователя.
     *                         Текущий пользователь, если не указано.
     * @param array $options - дополнительные опции:
     *        status - Фильтрация по статусу =>
     *                  'active' по умолчанию
     *                          string | фильтрация по одному статусу
     *                          array  | фильтрация по нескольким
     *                          NULL   | без фильтрации
     *
     * @return array - массив учебных процессов пользователя
     */
    public static function get_learninghistory_snapshots($courseid = NULL, $userid = NULL , $options = array() )
    {
        global $DB;

        if ( empty($userid) )
        {// Установим текущего пользователя, если не указано
            global $USER;
            $userid = $USER->id;
        }

        // Формируем параметры фильтрации
        $params = [
            'userid' => $userid
        ];
        if ( ! empty($courseid) )
        {// Добавим фильтрацию по курсу
            $params['courseid'] = $courseid;
        }
        if ( isset($options['status']) )
        {
            if ( ! empty($options['status']) )
            {// Добавим фильтрацию по статусу
                $params['status'] = $options['status'];
            }
        }

        // Вернем данные
        return $DB->get_records('local_learninghistory', $params);
    }

    /**
     * Получить текущую активную подписку студента на курс.
     *
     * @param int $courseid
     * @param int $userid
     */
    public static function get_learninghistory_snapshot_actual($courseid, $userid = 0) {
        global $DB;
        if (empty($userid)) {
            global $USER;
            $userid = $USER->id;
        }
        $params = [
                'status' => 'active',
                'courseid' => $courseid,
                'userid' => $userid,
        ];
        $sort = 'id DESC';
        // Достанем самую первую активную запись.
        $lastactive = $DB->get_records('local_learninghistory', $params, $sort, '*', 0, 1);
        if (empty($lastactive)) {
            return false;
        }
        $lastactive = array_shift($lastactive);
        return $lastactive;
    }

    /**
     * Получить предыдущую подписку студента на курс.
     *
     * @param int $courseid
     * @param int $userid
     */
    public static function get_learninghistory_snapshot_previous($courseid, $userid = 0) {
        global $DB;
        if (empty($userid)) {
            global $USER;
            $userid = $USER->id;
        }
        $params = [
            'courseid' => $courseid,
            'userid' => $userid,
        ];
        $sort = 'previd DESC';
        // Достанем самую первую активную запись.
        $lastprevious = $DB->get_records('local_learninghistory', $params, $sort, '*', 0, 2);
        if (empty($lastprevious)) {
            return false;
        }
        $lastprevious = array_pop($lastprevious);
        // Нам нужна история.
        if ($lastprevious->status == 'active') {
            return false;
        }
        return $lastprevious;
    }

    /**
     * Сохранить учебный процесс студента по курсу
     *
     * В зависимости от наличия записи обновляет, или создает учебный процесс
     *
     * @param int $courseid
     * @param int $userid
     * @param array $parameters
     */
    public static function set_learninghistory_snapshot($courseid, $userid = 0, $parameters = []) {
        global $DB;
        if (empty($userid)) {
            global $USER;
            $userid = $USER->id;
        }
        $course = get_course($courseid);
        $snapshot = self::get_learninghistory_snapshot_actual($courseid, $userid);
        foreach ($parameters as $key => $value) {
            switch ($key) {
                // Эти поля задавать нельзя (автоматические)
                case 'id':
                case 'userid':
                case 'courseid':
                case 'previd':
                case 'lastupdate':
                    unset($parameters[$key]);
                    break;
            }
        }
        // Актуальной подписки не найдено, создаём новую.
        if (empty($snapshot))
        {
            $snapshot = new \stdClass();
            // Основные поля.
            $snapshot->userid = $userid;
            $snapshot->courseid = $courseid;
            $snapshot->coursefullname = $course->fullname;
            $snapshot->courseshortname = $course->shortname;
            $snapshot->lastupdate = time();
            foreach ($parameters as $key => $value) {
                $snapshot->{$key} = $value;
            }
            // Определим предыдущую запись, если есть. 0 - начальная запись.
            $previous = self::get_learninghistory_snapshot_previous($courseid, $userid);
            $previd = 0;
            if (!empty($previous)) {
                $previd = $previous->id;
            }
            $snapshot->previd = $previd;
            return $DB->insert_record('local_learninghistory', $snapshot, true);
        } else {
            $updateneeded = false;
            // Актуальной подписка есть, изменяем её.
            foreach ($parameters as $key => $value) {
                if ($snapshot->{$key} != $value) {
                    $updateneeded = true;
                    $snapshot->{$key} = $value;
                }
            }
            if ($updateneeded)
            {
                $snapshot->lastupdate = time();
                if( $DB->update_record('local_learninghistory', $snapshot) )
                {
                    return $snapshot->id;
                } else
                {
                    return false;
                }
            }
        }
        return false;
    }

    /**
     * Обновить информацию по активным учебным процессам
     *
     */
    public static function update_active_snapshots()
    {
        global $DB;

        $params = [
            'status' => 'active'
        ];
        // Получим активные учебные процессы
        $snapshots = $DB->get_records('local_learninghistory', $params);
        if ( ! empty($snapshots) )
        {// Есть записи для обновления
            // Начинаем процесс обновления информации
            foreach ( $snapshots as $snapshot )
            {
                // Обновим итоговую оценку
                $parameters = [];
                $parameters['finalgrade'] = grades_manager::get_user_finalgrade($snapshot->courseid, $snapshot->userid);
                // Запишем данные
                self::set_learninghistory_snapshot($snapshot->courseid, $snapshot->userid, $parameters);
            }
        }
        return true;
    }

    /**
     * Создание/обновление записи в local_learninghistory_cm
     * @param int $cmid
     * @param int $llid
     * @param int $userid
     * @param array $parameters
     * @return boolean|int
     */
    public static function set_learninghistory_cm_snapshot($cmid, $llid, $userid = 0, $parameters = [])
    {
        global $DB;
        if( empty($userid) )
        {
            global $USER;
            $userid = $USER->id;
        }
        $snapshot = self::get_learninghistory_cm_snapshot_actual($cmid, $llid, $userid);
        foreach($parameters as $key => $value)
        {
            switch($key)
            {
                // Эти поля задавать нельзя (автоматические)
                case 'id':
                case 'llid':
                case 'cmid':
                case 'contextid':
                case 'userid':
                case 'timecreated':
                case 'timemodified':
                    unset($parameters[$key]);
                    break;
            }
        }

        $cm = self::get_module_from_cmid($cmid);

        // Актуальной подписки не найдено, создаём новую.
        if( empty($snapshot) )
        {
            $snapshot = new \stdClass();
            // Основные поля.
            $snapshot->llid = $llid;
            $snapshot->cmid = $cmid;
            $snapshot->contextid = $cm->contextid;
            $snapshot->userid = $userid;
            $snapshot->activetime = 0;
            foreach($parameters as $key => $value)
            {
                $snapshot->{$key} = $value;
            }
            $snapshot->timecreated = $snapshot->timemodified = $snapshot->atlastupdate = time();
            return $DB->insert_record('local_learninghistory_cm', $snapshot, true);
        } else
        {
            $updateneeded = false;
            // Актуальной подписка есть, изменяем её.
            foreach($parameters as $key => $value)
            {
                if( $snapshot->{$key} != $value )
                {
                    $updateneeded = true;
                    $snapshot->{$key} = $value;
                }
            }

            if( $updateneeded )
            {
                $snapshot->timemodified = time();
                $updateresult = $DB->update_record('local_learninghistory_cm', $snapshot);
                if ($updateresult) {
                    return $snapshot->id;
				}
            }
        }
		return false;
    }

    /**
     * Получение последней записи cm пользователя в learninghistory
     * @param int $cmid - идентификатор cm
     * @param int|null $userid - идентификатор пользователя, если не указывать - текущий
     * @param string|null $status - если указать статус, выборка отфильтруется по статусу (напр. active), если нет - без фильтрации
     * @return \stdClass|false - одна запись результата выборки или false если ничего не найдено
     */
    public static function get_last_user_cm($cmid, $userid=null, $status=null)
    {
        global $DB, $USER;

        if (is_null($userid)) {
            $userid = $USER->id;
        }

        $params = [
            'cmid' => $cmid,
            'userid' => $userid,
        ];

        if (!is_null($status)) {
            $params['status'] = $status;
        }

        $records = $DB->get_records('local_learninghistory_cm', $params, 'attemptnumber DESC, timemodified DESC', '*', 0, 1);
        return array_shift($records);
    }

    public static function get_learninghistory_cm_snapshot_actual($cmid, $llid, $userid = 0)
    {
        global $DB;
        if( empty($userid) )
        {
            global $USER;
            $userid = $USER->id;
        }
        $params = [
            'status' => 'active',
            'cmid' => $cmid,
            'llid' => $llid,
            'userid' => $userid,
        ];
        $sort = 'attemptnumber DESC, timemodified DESC';
        // Достанем самую первую активную запись.
        $lastattempt = $DB->get_records('local_learninghistory_cm', $params, $sort, '*', 0, 1);
        if( empty($lastattempt) )
        {
            return false;
        }
        $lastattempt = array_shift($lastattempt);
        return $lastattempt;
    }

    public static function get_learninghistory_cm_all_actual($llid, $userid = 0)
    {
        global $DB;
        if( empty($userid) )
        {
            global $USER;
            $userid = $USER->id;
        }
        $params = [
            'status' => 'active',
            'llid' => $llid,
            'userid' => $userid,
        ];
        return $DB->get_records('local_learninghistory_cm', $params);
    }

    public static function set_learninghistory_module_snapshot($cmid, $parameters = [])
    {
        global $DB;

        if (isset($parameters['status']) && $parameters['status'] == 'archive') {
            /**
             * @todo это вообще какой-то костыль, от которого лучше избавится,
             * странной непонятно логики в этом методе не должно быть
             */
            $snapshot = self::get_learninghistory_module_snapshot_actual($cmid);
            if ($snapshot === false) {
                // Удаляем модуль, записей по которому нет, поэтому просто пропустим его
                return true;
            }
            $snapshot->status = 'archive';
            $snapshot->timemodified = time();
            return $DB->update_record('local_learninghistory_module', $snapshot);
        }else{
        $cm = self::get_module_from_cmid($cmid);

        $snapshot = self::get_learninghistory_module_snapshot_actual($cm->id);
        foreach($parameters as $key => $value)
        {
                if (in_array(
                    $key,
                    ['id','cmid','courseid','name','modname','timecreated','timemodified']
                    ))
            {
                    unset($parameters[$key]);
            }
        }

        // Актуальной подписки не найдено, создаём новую.
        if( empty($snapshot) )
        {
            $snapshot = new \stdClass();
            // Основные поля.
            $snapshot->cmid = $cm->id;
            $snapshot->courseid = $cm->course;
            $snapshot->section = $cm->section;
            $snapshot->name = $cm->name;
                $snapshot->status = 'active';
            $snapshot->modname = $cm->modname;
            $snapshot->timecreated = $snapshot->timemodified = time();
            foreach ($parameters as $key => $value)
            {
                    if (!empty($value)){
                $snapshot->{$key} = $value;
            }
                }
            return $DB->insert_record('local_learninghistory_module', $snapshot);
        } else
        {
            $updateneeded = false;
            // Актуальной подписка есть, изменяем её.
            foreach($parameters as $key => $value)
            {
                    if( $snapshot->{$key} != $value && !empty($value) )
                {
                    $updateneeded = true;
                    $snapshot->{$key} = $value;
                }
            }

            if( $snapshot->name != $cm->name )
            {
                $snapshot->status = 'renamed';
                $snapshot->timemodified = time();
                $DB->update_record('local_learninghistory_module', $snapshot);
                $snapshot->name = $cm->name;
                $snapshot->status = 'active';
                $snapshot->timecreated = $snapshot->timemodified;
                return $DB->insert_record('local_learninghistory_module', $snapshot);
            } else
            {
                if( $updateneeded )
                {
                    $snapshot->timemodified = time();
                    return $DB->update_record('local_learninghistory_module', $snapshot);
                }
            }
        return true;
    }
        }
    }

    public static function get_learninghistory_module_snapshot_actual($cmid)
    {
        global $DB;
        $params = [
            'status' => 'active',
            'cmid' => $cmid
        ];
        $sort = 'id DESC';
        // Достанем самую первую активную запись.
        $lastactive = $DB->get_records('local_learninghistory_module', $params, $sort, '*', 0, 1);
        if( empty($lastactive) )
        {
            return false;
        }
        $lastactive = array_shift($lastactive);
        return $lastactive;
    }

    public static function get_learninghistory_module_snapshot_last($cmid)
    {
        global $DB;
        $params = [
            'cmid' => $cmid
        ];
        $sort = 'id DESC';
        // Достанем самую первую активную запись.
        $last = $DB->get_records('local_learninghistory_module', $params, $sort, '*', 0, 1);
        if( empty($last) )
        {
            return false;
        }
        $last = array_shift($last);
        return $last;
    }

    public static function get_course_mods($courseid, $cmid = null, $ignorevisibility = true) {
        global $DB;

        if (empty($courseid)) {
            return false; // avoid warnings
        }
        $params[] = $courseid;
        if( ! is_null($cmid) )
        {
            $cmselect = ' AND cm.id = ?';
            $params[] = $cmid;
        } else
        {
            $cmselect = '';
        }
        if( ! $ignorevisibility )
        {
            $visibilityselect = ' AND m.visible = ?';
            $params[] = 1;
        } else
        {
            $visibilityselect = '';
        }

        return $DB->get_records_sql("SELECT cm.*, m.name as modname
                                   FROM {modules} m, {course_modules} cm
                                  WHERE cm.course = ? AND cm.module = m.id" . $cmselect . $visibilityselect,
            $params); // no disabled mods
    }

    public static function get_course_by_cmid($cmid)
    {
        global $DB;
        return $DB->get_record_sql("
                    SELECT c.*
                      FROM {course_modules} cm
                      JOIN {course} c ON c.id = cm.course
                     WHERE cm.id = ?", [$cmid], IGNORE_MISSING);
    }

    public static function get_module_from_cmid($cmid) {
        global $DB;
        if (!$cmrec = $DB->get_record_sql("SELECT cm.*, md.name as modname, c.id as contextid
                               FROM {course_modules} cm,
                                    {modules} md,
                                    {context} c
                               WHERE cm.id = ? AND
                                     md.id = cm.module AND
                                     c.instanceid=cm.id AND
                                     c.contextlevel = ?", [$cmid, CONTEXT_MODULE])){
                                         print_error('invalidcoursemodule');
        } elseif (!$modrec = $DB->get_record($cmrec->modname, ['id' => $cmrec->instance])) {
            print_error('invalidcoursemodule');
        }

        $modrec->instance = $modrec->id;
        $modrec->cmid = $cmrec->id;
        $cmrec->name = $modrec->name;

        return $cmrec;
    }
}