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

namespace block_mylearninghistory\form;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/formslib.php');
require_once($CFG->dirroot . '/local/crw/locallib.php');

use moodleform;

class courses_filter_rules extends moodleform {
    
    private $rules = [];
    
    protected function definition() {
        
        $mform =& $this->_form;
        
        $this->config = $this->_customdata->config;
        
        $this->rules = [
            'equal' => get_string('equal_label', 'block_mylearninghistory'),
            'notequal' => get_string('notequal_label', 'block_mylearninghistory'),
            'like' => get_string('like_label', 'block_mylearninghistory'),
            'graterorequal' => get_string('graterorequal_label', 'block_mylearninghistory'),
            'lessorequal' => get_string('lessorequal_label', 'block_mylearninghistory'),
            'grater' => get_string('grater_label', 'block_mylearninghistory'),
            'less' => get_string('less_label', 'block_mylearninghistory'),
            'in' => get_string('in_label', 'block_mylearninghistory'),
            'notin' => get_string('notin_label', 'block_mylearninghistory'),
        ];
        
        if ($fields = local_crw_get_custom_fields()) {
            foreach($fields as $fieldname => $cffield) {
                $type = $cffield['type'];
                if ($type == 'submit') {
                    // Грязный уродский костыль для пропуска кнопки сохранения кастомных полей
                    continue;
                }
                if ($type == 'select' && array_key_exists('multiple', $cffield) &&
                    $cffield['multiple'] == 'multiple')
                {
                    $type = 'multiple_select';
                }
                $rulesselect = $this->get_rules($type);
                $group = [];
                $group[] = $mform->createElement(
                    'checkbox', 'filter_field_' . $fieldname, $cffield['label']);
                $group[] = $mform->createElement(
                    'select', 'filter_rule_' . $fieldname, get_string('rule_label', 'block_mylearninghistory'), $rulesselect);
                
                $mform->addGroup($group, 'group_' . $fieldname, '', null, false);
            }
            $mform->addElement('submit', 'submit', get_string('save', 'block_mylearninghistory'));
        } else {
            $mform->addElement('static', 'noccf', '', get_string('noccf_value', 'block_mylearninghistory'));
        }
        $mform->applyFilter('__ALL__', 'trim');
    }
    
    private function get_rules($fieldtype) {
        $rules = $this->rules;
        switch ($fieldtype) {
            case 'multiple_select':
                foreach(['graterorequal', 'lessorequal', 'grater', 'less'] as $rule) {
                    unset($rules[$rule]);
                }
                break;
            case 'text':
            case 'textarea':
            case 'checkbox':
            case 'select':
                foreach(['graterorequal', 'lessorequal', 'grater', 'less', 'in', 'notin'] as $rule) {
                    unset($rules[$rule]);
                }
                break;
            default:
                break;
        }
        return $rules;
    }
}