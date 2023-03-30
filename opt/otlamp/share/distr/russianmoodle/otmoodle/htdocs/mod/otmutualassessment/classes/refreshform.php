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
 * Модуль Взаимная оценка. Класс формы для пересчета оценок.
 *
 * @package    mod
 * @subpackage otmutualassessment
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_otmutualassessment;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/formslib.php');

use moodleform;
use core\notification;

class refreshform extends moodleform {
    
    /**
     * Объект для работы модулем курса
     * @var mixed
     */
    private $otmutualassessment = null;
    
    protected function definition() {
        
        $mform =& $this->_form;
        
        $this->otmutualassessment = $this->_customdata->otmutualassessment;
        
        $mform->addElement('header', 'refresh', get_string('refresh_form_header', 'mod_otmutualassessment'));
        
        $mform->addElement('submit', 'submit', get_string('refresh_form_submit', 'mod_otmutualassessment'));
        
        $mform->applyFilter('__ALL__', 'trim');
    }
    
    public function process() {
        if ($this->get_data()) {
            $event = \mod_otmutualassessment\event\refresh_grades::create_from_otmutualassessment(
                $this->otmutualassessment->get_instance(), $this->otmutualassessment->get_context());
            $event->trigger();
            $this->otmutualassessment->process_refresh(['full_refresh'], null, true);
        }
    }
}