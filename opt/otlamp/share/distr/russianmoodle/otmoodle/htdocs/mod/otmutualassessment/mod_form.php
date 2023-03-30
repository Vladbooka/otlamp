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
 * Модуль Взаимная оценка. Класс формы добавления элемента.
 *
 * @package    mod
 * @subpackage otmutualassessment
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once ($CFG->dirroot . '/course/moodleform_mod.php');
require_once ($CFG->libdir . '/formslib.php');
require_once ($CFG->dirroot . '/mod/otmutualassessment/locallib.php');

class mod_otmutualassessment_mod_form extends moodleform_mod
{
    /**
     * Объект управления модулем
     * @var mixed
     */
    private $otmutualassessment = null;
    /**
     * Define the form
     */
    function definition()
    {
        global $CFG;
        $mform = & $this->_form;
        $gradesexists = false;
        $strategylist = mod_otmutualassessment_get_strategy_list();
        if (!empty($this->_instance)) {
            $instance = mod_otmutualassessment_get_instance($this->_instance);
            if ($this->current && $this->current->coursemodule) {
                $cm = get_coursemodule_from_instance('otmutualassessment', $this->current->id, 0, false, MUST_EXIST);
                $ctx = context_module::instance($cm->id);
            }
            if (!empty($strategylist[$instance->strategy])) {
                $this->otmutualassessment = new $strategylist[$instance->strategy]($ctx, null, null);
                $gradesexists = !empty($this->otmutualassessment->get_grades());
            }
        }

        // Custom elements
        $mform->addElement('header', 'general', get_string('general', 'form'));
        $mform->addElement('text', 'name', get_string('title', 'mod_otmutualassessment'), ['size'=>'64']);
        if (!empty($CFG->formatstringstriptags)) {
            $mform->setType('name', PARAM_TEXT);
        } else {
            $mform->setType('name', PARAM_CLEANHTML);
        }
        $mform->addRule('name', null, 'required', null, 'client');
        $mform->addRule('name', get_string('maximumchars', '', 255), 'maxlength', 255, 'client');
        
        $this->standard_intro_elements(get_string('description', 'mod_otmutualassessment'));
        
        // Стратегия
        foreach ($strategylist as $code => $classname) {
            $select[$code] = get_string('strategy_' . $code, 'mod_otmutualassessment');
        }
        $mform->addElement('select', 'strategy', get_string('strategy', 'mod_otmutualassessment'), $select);
        $mform->setType('strategy', PARAM_TEXT);
        $mform->setDefault('strategy', 'mutual_absolute');
        $mform->addHelpButton('strategy', 'strategy', 'mod_otmutualassessment');
        
        // Общие настройки для всех стратегий
        \mod_otmutualassessment\strategy\base::add_common_mod_form_elements($mform, $this);
        // Кастомные настройки для конкретной стратегии
        if (!is_null($this->otmutualassessment)) {
            $this->otmutualassessment->add_custom_mod_form_elements($mform, $this);
        }

        // Standard elements and buttons
        $this->standard_coursemodule_elements();
        $this->standard_grading_coursemodule_elements();
        if ($gradesexists) {
            // Если по модулю уже есть оценки, то менять стратегию нельзя, групповой режим менять нельзя
            $mform->freeze('strategy');
            $mform->freeze('groupmode');
            $mform->freeze('gradingmode');
        }
        $this->add_action_buttons();
    }

    /**
     * (non-PHPdoc)
     *
     * @see moodleform_mod::data_preprocessing()
     */
    function data_preprocessing(&$default_values)
    {
        parent::data_preprocessing($default_values);
        if (!is_null($this->otmutualassessment)) {
            $this->otmutualassessment->data_preprocessing_custom_mod_form_elements($default_values);
        }
    }

    /**
     * Perform minimal validation on the settings form
     *
     * @param array $data
     * @param array $files
     */
    public function validation($data, $files)
    {
        $mform = & $this->_form;
        $errors = parent::validation($data, $files);
        
        if (!is_null($this->otmutualassessment)) {
            $errors += $this->otmutualassessment->validation_common_mod_form_elements($data, $files, $mform);
            $errors += $this->otmutualassessment->validation_custom_mod_form_elements($data, $files, $mform);
        }

        return $errors;
    }

    /**
     * Set default values
     */
    function set_data($default_values)
    {
        parent::set_data($default_values);
    }

    /**
     * Добавляем кастомное условие выполнение элемента - Выставление оценки другим участникам
     * @return string - Возвращаем название созданной группы элементов
     */
    function add_completion_rules()
    {
        $mform = & $this->_form;
        $mform->addElement('checkbox', 'completionsetgrades', '', get_string('completionsetgrades', 'mod_otmutualassessment'));
        return ['completionsetgrades'];
    }
    
    /**
     * Проверять элемент на доп условие, если чекбокс активен
     * @return bool - возвращаем статус доп условия (проверять/нет)
     */
    function completion_rule_enabled($data) {
        return ( ! empty($data['completionsetgrades']) );
    }
}