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
 * Класс формы resource library
 */
if (!defined('MOODLE_INTERNAL')) {
    die('Direct access to this script is forbidden.');    ///  It must be included from a Moodle page
}

require_once($CFG->dirroot . '/course/moodleform_mod.php');

class mod_otresourcelibrary_mod_form extends moodleform_mod {

    public function definition() {
        global $PAGE, $CFG;
        
        // Запись модуля курса
        $course = $this->get_course();
        
        $PAGE->requires->js_call_amd('mod_otresourcelibrary/mod_form_modal', 'init', [
            'id' => $course->id
        ]);
        
        $mform =& $this->_form;
        
        // Скрытый элемент настроек из модалки
        $mform->addElement('hidden', 'khipu_setting', '');
        $mform->setType('khipu_setting', PARAM_RAW);
        
        //General options
        $mform->addElement('header', 'general', get_string('general', 'form'));

        // Название
        $mform->addElement('text', 'name', get_string('library_elemenrt_name', 'otresourcelibrary'), array('size' => '56'));
        
        if (!empty($CFG->formatstringstriptags)) {
            $mform->setType('name', PARAM_TEXT);
        } else {
            $mform->setType('name', PARAM_CLEANHTML);
        }
        
        $mform->addRule('name', null, 'required', null, 'client');
        $mform->addRule('name', get_string('maximumchars', '', 255), 'maxlength', 255, 'client');
        
        // Краткое описание
        $mform->addElement('textarea', 'description', get_string('short_description', 'otresourcelibrary'),
            ['rows' => 5, 'cols' => '56']);
        $mform->setType('description', PARAM_RAW);
        
        // Кнопка открытия модального окна
        $button = html_writer::div(get_string('otresourcelibrary_settings_button', 'otresourcelibrary'), 'btn btn-primary otresourcelibrary_settings_button disabled');
        $mform->addElement('static', 'otresourcelibrary_settings_button', '', $button);
        
        // Информация о материале
        $this->get_current_material_info($mform);
        
       
        // Добавление дополнительного класса форме для JS манипуляций
        $mform->updateAttributes(['class' => "{$mform->getAttribute('class')} mod_otresourcelibrary-js"]);
        
        // Add standard elements.
        $this->standard_coursemodule_elements();
        
        // Добавляем кнопки действия
        $this->add_action_buttons();
    }
    /**
     *
     * {@inheritDoc}
     * @see moodleform_mod::validation()
     */
    public function validation($data, $files) {
        $errors = parent::validation($data, $files);
        if( empty($jsondata = $data['khipu_setting'])) {
            $errors['otresourcelibrary_settings_button'] = get_string('empty_khipu_setting', 'otresourcelibrary');
        } else {
            $params = json_decode($jsondata);
            if (!isset($params->sourcename) || !isset($params->resourceid) ||
                !isset($params->pagenum) || !isset($params->chapter) || !isset($params->fragment)) {
                    $errors['otresourcelibrary_settings_button'] = get_string('wrong_param_khipu_setting', 'otresourcelibrary');
                }
        }
        return $errors;
    }
    
    private function get_current_material_info(&$mform) {
        global $DB;
        $sourcename = $title = get_string('no_selected_material', 'otresourcelibrary');
        $otresourcelibrary = $DB->get_record('otresourcelibrary', ['id'=> $this->get_instance()]);
        if (! empty($otresourcelibrary->khipu_setting)) {
            $config = json_decode($otresourcelibrary->khipu_setting);
            if (isset($config->resourceid) && isset($config->sourcename)) {
                list($resourcesslice, ) = otresourcelibrary_find_resources($config->resourceid, null, $config->sourcename);
                if (! empty($resourcesslice)) {
                    $sourcename = $resourcesslice[0]['properties']['sourcename'];
                    $title = $resourcesslice[0]['properties']['title']??'';
                }
            }
        }
        $mform->addElement(
            'static',
            'otresourcelibrary_sourcename_info',
            get_string('sourcename', 'otresourcelibrary'),
            html_writer::div($sourcename, 'otrl_sourcename_info')
            );
        $mform->addElement(
            'static',
            'otresourcelibrary_resource_info',
            get_string('resource', 'otresourcelibrary'),
            html_writer::div($title, 'otrl_resource_info')
            );
    }
}