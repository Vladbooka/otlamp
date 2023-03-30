<?php
use mod_event3kl\datemodes;

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
 * This file contains the forms to create and edit an instance of this module
 *
 * @package   mod_event3kl
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die('Direct access to this script is forbidden.');
require_once ($CFG->dirroot . '/course/moodleform_mod.php');
require_once ($CFG->dirroot . '/mod/event3kl/classes/provider/base/abstract_provider.php');
require_once ($CFG->dirroot . '/mod/event3kl/classes/format/base/abstract_format.php');

/**
 * Event3kl settings form.
 *
 * @package   mod_event3kl
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class mod_event3kl_mod_form extends moodleform_mod {
    
    /**
     * Модификаторы даты для подстановки в форму
     * 
     * @var array
     */
    public $datemodifiers = [];
    
    /**
     * Called to define this moodle form
     *
     * @return void
     */
    public function definition() {

        global $CFG, $DB;

        $mform = $this->_form;

        $mform->addElement('header', 'general', get_string('general', 'form'));

        $mform->addElement('text', 'name', get_string('event3klname', 'mod_event3kl'), ['size' => '64']);
        if (!empty($CFG->formatstringstriptags)) {
            $mform->setType('name', PARAM_TEXT);
        } else {
            $mform->setType('name', PARAM_CLEANHTML);
        }
        $mform->addRule('name', null, 'required', null, 'client');
        $mform->addRule('name', get_string('maximumchars', '', 255), 'maxlength', 255, 'client');

        $this->standard_intro_elements(get_string('description', 'mod_event3kl'));
        
        $defaultvalues = new \stdClass();
        if (! empty($this->_instance)){
            $defaultvalues = $DB->get_record('event3kl', ['id' => $this->_instance]);
        }
        
        // Подготовка данных
        $this->data_preprocessing($defaultvalues);

        // добавление провайдеров и их настроек на форму
        $providers = \mod_event3kl\providers::get_all_providers();
        $providers->mod_form_definition($mform, $this);

        // добавление форматов и их настроек на форму
        $formats = \mod_event3kl\formats::get_all_formats();
        $formats->mod_form_definition($mform, $this);

        // добавление способов указания даты и времени занятия и их настроек на форму
        $datemodes = datemodes::get_all_datemodes();
        $datemodes->mod_form_definition($mform, $this);

        $this->standard_grading_coursemodule_elements();
        $this->standard_coursemodule_elements();
        $this->apply_admin_defaults();

        $this->add_action_buttons();
        
        $this->set_data($defaultvalues);

    }

    /**
     * Perform minimal validation on the settings form
     * @param array $data
     * @param array $files
     */
    public function validation($data, $files) {
        $errors = [];

        // валидация провайдеров
        $providers = \mod_event3kl\providers::get_all_providers();
        $errors = array_merge($errors, $providers->mod_form_validation($data, $files));

        // валидация форматов
        $formats = \mod_event3kl\formats::get_all_formats();
        $errors = array_merge($errors, $formats->mod_form_validation($data, $files));

        // валидация способов указания даты и времени занятия
        $datemodes = datemodes::get_all_datemodes();
        $errors = array_merge($errors, $datemodes->mod_form_validation($data, $files));

        // проверяем возможность указания дэйтмода для выбранного формата занятия
        if (! in_array($data['format'], datemodes::instance($data['datemode'])::get_suitable_formats()) ){
            $errors['datemode'] = get_string('error_datemode_not_suitable','mod_event3kl');
        }
        
//         var_dump($errors); exit;

        return $errors;
    }

    /**
     * Any data processing needed before the form is displayed
     * (needed to set up draft areas for editor and filemanager elements)
     * @param array $defaultvalues
     */
    public function data_preprocessing(&$defaultvalues) {
        
        if (! empty($defaultvalues->providerdata)){
            $providerdata = json_decode($defaultvalues->providerdata, true);
            if (array_key_exists('providerinstance', $providerdata)){
                $defaultvalues->providerinstance = $providerdata['providerinstance'];
            }
        }
        
        if (! empty($defaultvalues->datemodedata)){
            $datemodedata = json_decode($defaultvalues->datemodedata, true);
            foreach ($datemodedata['datemodifiers'] as $num => $datemodifier){
                $datemode = $defaultvalues->datemode;
                $code = $datemodifier['code'];
                $selectname = $datemode . '_mcode[' . $num . ']';
                $defaultvalues->$selectname = $code;
                switch ($code){
                    case 'set_date':
                        $name = $datemode . '_modifiers[' . $num . ']' .'[' . $code . ']' . '[set_date]';
                        $defaultvalues->$name = $datemodifier['config']['timestamp'];
                        continue 2;
                    case 'set_time':
                        $name = 'set_time[set_time][hours]';
                        $defaultvalues->$name = $datemodifier['config']['hours'];
                        $name = 'set_time[set_time][minutes]';
                        $defaultvalues->$name = $datemodifier['config']['minutes'];
                        continue 2;
                }
                foreach ($datemodifier['config'] as $key =>$value){
                    $name = $datemode . '_modifiers[' . $num . ']' . '[' . $code . ']' . '[' . $key . ']';
                    $defaultvalues->$name = $value;
                }
                
            }
            $this->datemodifiers = $datemodedata['datemodifiers'];
        }

    }

//     /**
//      * Add any custom completion rules to the form.
//      *
//      * @return array Contains the names of the added form elements
//      */
//     public function add_completion_rules() {

//     }

//     /**
//      * Determines if completion is enabled for this module.
//      *
//      * @param array $data
//      * @return bool
//      */
//     public function completion_rule_enabled($data) {

//     }
}