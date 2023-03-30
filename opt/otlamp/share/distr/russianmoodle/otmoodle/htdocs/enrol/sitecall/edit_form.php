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
 * Плагин подписки через форму связи с менеджером.
 * Форма добавления способа подписки для курса
 *
 * @package    enrol
 * @subpackage sitecall
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

// Подключим библиотеки
require_once($CFG->libdir.'/formslib.php');

// Класс формы добавления экемплятра подписки
class enrol_sitecall_edit_form extends moodleform 
{
    /**
     * Объявление формы
     */
    function definition() 
    {
        $mform = $this->_form;
        
        //Получаем переданые данные
        list($instance, $plugin, $context) = $this->_customdata;
        
        // Заголовок формы
        $mform->addElement('header', 'header', get_string('pluginname', 'enrol_sitecall'));
        
        // Поле ID курса
        $mform->addElement('hidden', 'courseid');
        $mform->setType('courseid', PARAM_INT);
        
        // Поле статуса плагина    
        $options = array(ENROL_INSTANCE_ENABLED  => get_string('yes'),
                         ENROL_INSTANCE_DISABLED => get_string('no'));
        $mform->addElement('select', 'status', get_string('status', 'enrol_sitecall'), $options);
        $mform->setDefault('status', $plugin->get_config('status'));
        
        $yesno = [
                        0 => get_string('no'),
                        1 => get_string('yes')
        ];
        $editor_options = [
                        'subdirs' => true,
                        'maxfiles' => EDITOR_UNLIMITED_FILES
        ];
        
        // Уведомлять преподавателей
        $mform->addElement('select', 'messageteacher_send', get_string('messageteacher_send', 'enrol_sitecall'), $yesno);
        // Сообщение преподавателям
        $mform->addElement('editor', 'messageteacher', get_string('messageteacher', 'enrol_sitecall'), $editor_options);
        $mform->addHelpButton('messageteacher', 'messageteacher', 'enrol_sitecall');
        
        // Уведомлять студента
        $mform->addElement('select', 'messagestudent_send', get_string('messagestudent_send', 'enrol_sitecall'), $yesno);
        // Сообщение студенту
        $mform->addElement('editor', 'messagestudent', get_string('messagestudent', 'enrol_sitecall'), $editor_options);
        $mform->addHelpButton('messagestudent', 'messagestudent', 'enrol_sitecall');
        
        // Добавим кнопки действия
        $this->add_action_buttons(true, ($instance->id ? null : get_string('addinstance', 'enrol')));
        
        // Заполним поля значениями 
        $this->set_data($instance);
    }

    /**
     * Проверка переданных данных
     */
    public function validation($data, $files) 
    {
        $errors = parent::validation($data, $files);
        return $errors;
    }
    
    /**
     * Заполнение данными
     * 
     * @see moodleform::set_data()
     */
    public function set_data($instance) 
    {
        if ( is_object($instance) ) 
        {
            $instance = (array) $instance;
        }
        
        if ( ! empty($instance['customint1']) ) 
        {
            $instance['messageteacher_send'] = $instance['customint1'];
        } else {
            $instance['messageteacher_send'] = 0;
        }
        if ( ! empty($instance['customint2']) )
        {
            $instance['messagestudent_send'] = $instance['customint2'];
        } else {
            $instance['messagestudent_send'] = 0;
        }
        
        if ( ! empty($instance['customtext1']) )
        {
            $data = unserialize($instance['customtext1']);
            $instance['messageteacher'] = [
                            'text' => $data['text'],
                            'infoformat' => $data['format']
            ];
        } else {
            $instance['messageteacher'] = [
                            'text' => '',
                            'infoformat' => FORMAT_HTML
            ];
        }
        if ( ! empty($instance['customtext2']) )
        {
            $data = unserialize($instance['customtext2']);
            $instance['messagestudent'] = [
                            'text' => $data['text'],
                            'infoformat' => $data['format']
            ];
        } else {
            $instance['messagestudent'] = [
                            'text' => '',
                            'infoformat' => FORMAT_HTML
            ];
        }
        parent::set_data($instance);
    }
}
