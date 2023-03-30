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
 * Модуль Взаимная оценка. Класс формы для выставления оценок.
 *
 * @package    mod
 * @subpackage otmutualassessment
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_otmutualassessment;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/formslib.php');

use moodleform;
use stdClass;

class graderform extends moodleform {
    
    /**
     * Массив оцениваемых пользователей
     * @var array
     */
    private $gradedusers = [];
    
    /**
     * Объект оценщика
     * @var stdClass
     */
    private $grader = null;
    
    /**
     * Объект для работы модулем курса
     * @var mixed
     */
    private $otmutualassessment = null;

    /**
     * Идунтификатор группы
     * @var int
     */
    private $groupid = null;
    
    /**
     * Сумма баллов в форме
     * @var integer
     */
    private $summ = 0;
    
    public function __get($property) {
        if (property_exists($this, $property)) {
            return $this->$property;
        }
        return null;
    }
    
    public function __isset($property) {
        return isset($this->$property);
    }
    
    protected function definition() {
        
        $mform =& $this->_form;
        // Инициализация свойств формы
        $this->gradedusers = $this->_customdata->gradedusers;
        $this->grader = $this->_customdata->grader;
        $this->otmutualassessment = $this->_customdata->otmutualassessment;
        $this->groupid = $this->_customdata->groupid;
        $this->summ = 0;
        $this->otmutualassessment->graderform_definition($mform, $this);
        
        $mform->applyFilter('__ALL__', 'trim');
    }
    
    public function definition_after_data() {
        $mform =& $this->_form;
        $this->otmutualassessment->graderform_definition_after_data($mform, $this);
    }
    
    public function validation($data, $files) {
        $error = parent::validation($data, $files);
        $error += $this->otmutualassessment->graderform_validation($data, $files, $this);
        return $error;
    }
    
    public function process() {
        $mform =& $this->_form;
        // Обработка формы
        $this->otmutualassessment->graderform_process($mform, $this);
    }
    
    public function reset_summ()
    {
        $this->summ = 0;
    }
    
    public function add_to_summ($add)
    {
        $this->summ += $add;
    }
    
    public function get_summ()
    {
        return $this->summ;
    }
}