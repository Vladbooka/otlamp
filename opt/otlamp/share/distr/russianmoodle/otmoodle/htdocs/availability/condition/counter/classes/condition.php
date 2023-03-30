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
 * Условие показа по числу выполненных условий. Класс условия.
 *
 * @package    availability_counter
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace availability_counter;

defined('MOODLE_INTERNAL') || die();

class condition extends \core_availability\condition 
{
    private $gradeitemsid;

    private $counter;

    /**
     * Constructor.
     *
     * @param \stdClass $structure Data structure from JSON decode
     */
    public function __construct($structure) 
    {

        $this->counter = 0;
        if ( isset($structure->counter) )
        {// Передана информация о счетчике достаточных условий
            $counter = intval($structure->counter);
            if ( $counter > 0 )
            {// Счетчик положительный
                $this->counter = $counter;
            } 
        } 
        
        $this->gradeitemsid = [];
        if ( isset($structure->elements) && ! empty($structure->elements) )
        {// Есть элементы курса для простановки условий
            $this->gradeitemsid = $structure->elements;
        }
    }

    /**
     * Сохранить данные
     */ 
    public function save() 
    {
        $result = (object)[
                        'type' => 'counter', 
                        'elements' => $this->gradeitemsid, 
                        'counter' => $this->counter
        ];
        
        return $result;
    }

    public function is_available($not, \core_availability\info $info, $grabthelot, $userid) 
    {
        $course = $info->get_course();
        
        $countcomplete = 0;
        if ( ! empty($this->gradeitemsid) )
        {// Есть условия по элементам курса
            foreach ( $this->gradeitemsid as $item )
            {// Проверим каждый имеющийся элемент
                // Оценка по элементу
                if ( ! isset($item->id) )
                {// ID элемента не передан
                    continue;
                }
                // Оценка
                $score = $this::get_cached_grade_score($item->id, $course->id, $grabthelot, $userid);
                
                // Наличие оценки у пользователя
                if ( is_float($score) )
                {// Условие выполнено
                    $allow = true;
                } else 
                {// Условие не выполнено
                    $allow = false;
                }
                
                // Ограничение по минимальному уровню оценки
                if ( isset($item->min) )
                {
                    $allow = $allow && ($score >= $item->min);
                }
                // Ограничение по максимальному уровню оценки
                if ( isset($item->max) )
                {
                    $allow = $allow && ($score < $item->max);
                }
                
                if ( $allow )
                {
                    $countcomplete++;
                }
            }
        }
        
        // Подсчет числа успехов
        if ( $countcomplete >= $this->counter )
        {
            $result = true;
        } else 
        {
            $result = false;
        }
        
        // Инверсия результата 
        if ($not)
        {
            $result = !$result;
        }
        return $result;
    }

    /**
     * Описание блокировки
     * @see \core_availability\condition::get_description()
     */
    public function get_description($full, $not, \core_availability\info $info) 
    {
        return get_string('description_not_available', 'availability_counter');
    }

    protected function get_debug_string() 
    {
        return null;
    }

    protected static function get_cached_grade_score($gradeitemid, $courseid,
            $grabthelot=false, $userid=0) {
        global $USER, $DB;
        if (!$userid) {
            $userid = $USER->id;
        }
        $cache = \cache::make('availability_grade', 'scores');
        if (($cachedgrades = $cache->get($userid)) === false) {
            $cachedgrades = array();
        }
        if (!array_key_exists($gradeitemid, $cachedgrades)) {
            if ($grabthelot) {
                // Get all grades for the current course.
                $rs = $DB->get_recordset_sql('
                        SELECT
                            gi.id,gg.finalgrade,gg.rawgrademin,gg.rawgrademax
                        FROM
                            {grade_items} gi
                            LEFT JOIN {grade_grades} gg ON gi.id=gg.itemid AND gg.userid=?
                        WHERE
                            gi.courseid = ?', array($userid, $courseid));
                foreach ($rs as $record) {
                    if (is_null($record->finalgrade)) {
                        // No grade = false.
                        $cachedgrades[$record->id] = false;
                    } else {
                        // Otherwise convert grade to percentage.
                        $cachedgrades[$record->id] =
                                (($record->finalgrade - $record->rawgrademin) * 100) /
                                ($record->rawgrademax - $record->rawgrademin);
                    }
                }
                $rs->close();
                // And if it's still not set, well it doesn't exist (eg
                // maybe the user set it as a condition, then deleted the
                // grade item) so we call it false.
                if (!array_key_exists($gradeitemid, $cachedgrades)) {
                    $cachedgrades[$gradeitemid] = false;
                }
            } else {
                // Just get current grade.
                $record = $DB->get_record('grade_grades', array(
                    'userid' => $userid, 'itemid' => $gradeitemid));
                if ($record && !is_null($record->finalgrade)) {
                    $score = (($record->finalgrade - $record->rawgrademin) * 100) /
                        ($record->rawgrademax - $record->rawgrademin);
                } else {
                    // Treat the case where row exists but is null, same as
                    // case where row doesn't exist.
                    $score = false;
                }
                $cachedgrades[$gradeitemid] = $score;
            }
            $cache->set($userid, $cachedgrades);
        }
        return $cachedgrades[$gradeitemid];
    }

    /**
     * Восстановление из бэкапа
     * {@inheritDoc}
     * @see \core_availability\tree_node::update_after_restore()
     */
    public function update_after_restore($restoreid, $courseid, \base_logger $logger, $name)
    {
        global $DB;
        
        // Фомирование массива данных для новых идентификаторов
        $new_itemsid = [];
        
        if ( ! empty($this->gradeitemsid) && is_array($this->gradeitemsid) )
        {
            foreach ( $this->gradeitemsid as $item )
            {
                if ( is_object($item) )
                {
                    $rec = \restore_dbops::get_backup_ids_record($restoreid, 'grade_item', $item->id);
                    if ( ! empty($rec->newitemid) )
                    {
                        $new_item_obj = new \stdClass();
                        $new_item_obj->min = $item->min;
                        $new_item_obj->max = $item->max;
                        $new_item_obj->id = $rec->newitemid;
                        $new_itemsid[] = $new_item_obj;
                    }
                }
            }
        }
        
        $this->gradeitemsid = $new_itemsid;
        
        return true;
    }

    /**
     * Обновление модуля
     * {@inheritDoc}
     * @see \core_availability\condition::update_dependency_id()
     */
    public function update_dependency_id($table, $oldid, $newid) 
    {
        if ( $table === 'grade_items' )
        {
            foreach ( $this->gradeitemsid as &$item )
            {
                if ( $item->id === (int)$oldid )
                {
                    $item->id = $newid;
                }
            }
            return true;
        } else
        {
            return false;
        }
    }
}
