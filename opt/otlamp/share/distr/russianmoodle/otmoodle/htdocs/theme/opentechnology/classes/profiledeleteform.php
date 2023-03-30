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
 * Тема СЭО 3KL. Удаление профиля.
 *
 * @package    theme
 * @subpackage opentechnology
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace theme_opentechnology;

global $CFG;
require_once($CFG->libdir.'/formslib.php');

use moodleform;
use html_writer;
use moodle_url;

class profiledeleteform extends moodleform 
{   
    /**
     * Профиль
     * 
     * @var base
     */
    private $profile = null;
    
    /**
     * Обьявление полей формы
     */
    public function definition()
    {
        // Создание ссылки на HTML_QuickForm
        $mform =& $this->_form;
        
        // Добавление свойств формы
        $this->profile = $this->_customdata->profile;
        
        $dialog = html_writer::div(get_string('profile_delete_dialog', 'theme_opentechnology'));
        $mform->addElement(
            'html',
            $dialog
        );
        
        // Поле для сохранения base64 фотографии
        $group = [];
        $group[] = $mform->createElement(
            'submit', 
            'submit', 
            get_string('profile_delete_confirm', 'theme_opentechnology')
        );
        $group[] = $mform->createElement(
            'submit',
            'cancel',
            get_string('profile_delete_cancel', 'theme_opentechnology')
        );
        $mform->addGroup($group, 'submit', '', '', false);
    }
    
    /**
     * Обработка данных формы
     *
     * @return bool
     */
    public function process()
    {
        
        if ( $this->is_submitted() && confirm_sesskey() &&
             $this->is_validated() && $formdata = $this->get_data()
           )
        {
            $returnurl = new moodle_url('/theme/opentechnology/profiles/index.php');
            if ( ! empty($formdata->submit) )
            {// Удаление профиля
                
                if ( ! profilemanager::instance()->delete_profile($this->profile) )
                {// Удаление завершилось с ошибками
                    print_error('profile_delete_error', 'theme_opentechnology', $returnurl);
                }
                
                $returnurl = new moodle_url(
                    '/theme/opentechnology/profiles/index.php'
                );
            }
            redirect($returnurl);
        }
    }
}