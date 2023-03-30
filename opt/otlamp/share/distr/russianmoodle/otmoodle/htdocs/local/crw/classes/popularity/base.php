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

namespace local_crw\popularity;
use dof_control;
use stdClass;

/**
 * Базовый класс расчета популярности курса
 * @package local_crw
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
abstract class base {
    /**
     * Получить значение популярности курса
     * @param int $courseid идентификатор курса
     */
    abstract public function get_course_popularity($courseid);
    
    /**
     * Период, за который требуется получить значение популярности. По умолчанию не ограничен.
     * @var integer
     */
    protected $period = 0;
    
    /**
     * Значение сортировки для показателя популярности
     * @var mixed
     */
    protected $sortvalue = null;
    
    /**
     * Имя типа популярности
     * @var string
     */
    protected $name = '';
    
    /**
     * Контроллер ЭД
     * @var dof_control
     */
    protected $dof = null;
    
    /**
     * Конструктор
     */
    public function __construct() {
        $this->set_dof();
        $this->set_config();
    }
    
    /**
     * Установка конфига
     */
    public function set_config() {
        
    }
    
    /**
     * Получить имя типа популярности
     * @return string
     */
    public function get_name() {
        return $this->name;
    }
    
    /**
     * Получить локализованное имя типа популярности
     * @return string
     */
    public function get_local_string_name() {
        return get_string('popularity_' . $this->get_name(), 'local_crw');
    }
    
    /**
     * Установить значение сортировки для переданного значения популярности
     * @param mixed $value
     * @return int
     */
    protected function set_sort_value($value) {
        $this->sortvalue = (int)$value;
    }
    
    /**
     * Получить значение сортировки для показателя популярности
     * @return mixed
     */
    public function get_sort_value() {
        return $this->sortvalue;
    }
    
    /**
     * Установить контроллер ЭД
     */
    public function set_dof() {
        global $CFG;
        
        if (is_null($this->dof)) {
            $doflibpath = $CFG->dirroot . '/blocks/dof/locallib.php';
            if (file_exists($doflibpath))
            {
                require_once($doflibpath);
                global $DOF;
                $this->dof = $DOF;
            }
        }
    }
    
    /**
     * Сохранить значение популярности курса
     * @param int $courseid идентификатор курса
     * @param string|number $value значение популярности
     */
    public function save($courseid, $value) {
        global $DB;
        if ($find = $DB->get_record('crw_course_properties', [
            'courseid' => $courseid,
            'name' => 'course_popularity'
        ], 'id, name, value')) {
            if ($find->value != $value) {
                $find->svalue = $value;
                $find->value = $value;
                $find->sortvalue = $this->get_sort_value();
                $DB->update_record('crw_course_properties', $find);
            }
        } else {
            $dataobject = new stdClass();
            $dataobject->courseid = $courseid;
            $dataobject->name = 'course_popularity';
            $dataobject->svalue = $value;
            $dataobject->value = $value;
            $dataobject->sortvalue =$this->get_sort_value();
            $DB->insert_record('crw_course_properties', $dataobject);
        }
    }
}