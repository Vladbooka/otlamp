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

use local_crw\popularity\base;
use logstore_legacy\log\store as logstore;
use moodle_exception;

/**
 * Класс расчета популярности курса по уникальным просмотрам
 * @package local_crw
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class unique_course_view extends base {
    
    /**
     * Запрос на получение значения популярности
     * @var string
     */
    private $sql = '';
    
    /**
     * Параметры для запроса на получение популярности
     * @var array
     */
    private $params = [];
    
    /**
     * Флаг подготовки запроса и параметров
     * @var bool
     */
    private $prepared = false;
    
    /**
     * Период, за который требуется получить значение популярности
     * @var int
     */
    protected $period = 60 * 60 * 24 * 30;
    
    /**
     * Имя типа популярности
     * @var string
     */
    protected $name = 'unique_course_view';
    
    /**
     * Получить значение популярности для
     * {@inheritDoc}
     * @see \local_crw\popularity\base::get_course_popularity()
     */
    public function get_course_popularity($courseid) {
        global $DB;
        $result = 0;
        if ($this->prepared) {
            $this->params['course'] = $courseid;
            try {
                $result = $DB->get_record_sql($this->sql, $this->params);
                $result = $result->count;
            } catch (moodle_exception $e) {
                $result = 0;
            }
        }
        $this->set_sort_value($result);
        return $result;
    }

    /**
     * Установить конфиг
     * {@inheritDoc}
     * @see \local_crw\popularity\base::set_config()
     */
    public function set_config() {
        global $CFG;
        if (!is_null($this->dof)) {
            list($this->sql, $this->params) = $this->preparesql();
            if (!empty($this->sql) && !empty($this->params)) {
                $this->prepared = true;
            }
        }
    }
    
    /**
     * Подготовить запрос и параметры для получение значения популярности курса
     * @return string[]|number[]
     */
    private function preparesql() {
        if ($logreader = $this->dof->modlib('ama')->course(false)->get_logreader()) {
            if ($logreader instanceof logstore) {
                $table = 'log';
                $select = 'action = :action AND module = :module AND time > :time AND course = :course';
                $params = ['action' => 'view', 'module' => 'course'];
            } else {
                $table = $logreader->get_internal_log_table_name();
                $select = 'eventname = :eventname AND timecreated > :time AND courseid = :course';
                $params = ['eventname' => '\\core\\event\\course_viewed'];
            }
            $params['time'] = $this->period;
            $sql = 'SELECT COUNT(DISTINCT userid) count FROM {' . $table . '} WHERE ' . $select;
            return [$sql, $params];
        }
    }
}