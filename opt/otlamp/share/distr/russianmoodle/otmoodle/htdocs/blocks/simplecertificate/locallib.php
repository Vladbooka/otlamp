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
 * Сертификаты. Локальная библиотека.
 *
 * @package    block
 * @subpackage simplecertificate
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

use block_simplecertificate\local\utilities;

require_once($CFG->libdir . '/formslib.php');

class certificates_filter_form extends moodleform 
{
    var $username;
    /**
     * Объявление формы
     */
    function definition() 
    {
        global $CFG, $PAGE;
        
        // Языковые переменные
        $str_filter_header = get_string('form_certificates_filter_header', 'block_simplecertificate');
        $str_filter_username = get_string('form_certificates_filter_username', 'block_simplecertificate');
        $str_filter_courses = get_string('form_certificates_filter_courses', 'block_simplecertificate');
        $str_filter_submit = get_string('form_certificates_filter_submit', 'block_simplecertificate');
        $str_filter_cancel = get_string('form_certificates_filter_cancel', 'block_simplecertificate');
        
        // Получим данные
        $mform = $this->_form;
        
        $this->username  = $this->_customdata['username']; 
        $this->courseid = $this->_customdata['courseid'];

        // Заголовок формы
        $mform->addElement(
                'header', 
                'certificates_filter_header', 
                $str_filter_header
        );
        
        // ФИО содержит
        $mform->addElement(
                'text',
                'certificates_filter_username',
                $str_filter_username
        );
        $mform->setType('certificates_filter_username', PARAM_TEXT);
        
        // Курс
        $courses = utilities::get_courses_select();
        $mform->addElement(
                'select',
                'certificates_filter_courses',
                $str_filter_courses,
                $courses
        );

        // Кнопки
        $buttonarray = [];
        $buttonarray[] = &$mform->createElement('submit', 'submitbutton', $str_filter_submit);
        $buttonarray[] = &$mform->createElement('cancel', 'cancel', $str_filter_cancel);
        $mform->addGroup($buttonarray, 'buttonar', '', [' '], false);
        
        // Применим фильтр
        $mform->applyFilter('__ALL__', 'trim');
        
        $this->set_data();
    }

    /**
     * Значения по умолчанию для формы
     */
    function set_data($default_values = []) 
    {
       
        if ( ! empty($this->username) )
        {// Указана часть ФИО пользователей
            $default_values['certificates_filter_username'] = $this->username;
        }
        if ( ! empty($this->courseid) )
        {// Указана часть ФИО пользователей
            $default_values['certificates_filter_courses'] = $this->courseid;
        }
        // Заполняем форму данными
        parent::set_data($default_values);
    }
    
    /**
     * Обработчик формы
     */
    function process()
    {

        if ( $this->is_cancelled() )
        {// Отменили фильтрацию
            $url = new moodle_url('/blocks/simplecertificate/view.php');
            redirect($url);
        }
        
        if ( $this->is_submitted() AND confirm_sesskey() AND 
             $this->is_validated() AND $formdata = $this->get_data()
           )
        {// Форма отправлена и проверена
            
            $params = [];
            if ( ! empty($formdata->certificates_filter_username) )
            {
                $params['username'] = $formdata->certificates_filter_username;
            }
            if ( ! empty($formdata->certificates_filter_courses) )
            {
                $params['courseid'] = $formdata->certificates_filter_courses;
            }
            $url = new moodle_url('/blocks/simplecertificate/view.php', $params);
            redirect($url);
        }
    }
}