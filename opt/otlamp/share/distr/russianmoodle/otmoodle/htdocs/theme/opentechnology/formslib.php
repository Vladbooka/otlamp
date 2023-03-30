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
 * Form to edit a users editor preferences.
 *
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @package core_user
 */

if (!defined('MOODLE_INTERNAL')) {
    die('Direct access to this script is forbidden.');    //  It must be included from a Moodle page.
}

require_once($CFG->dirroot.'/lib/formslib.php');

/**
 * Class user_edit_editor_form.
 *
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class theme_opentechnology_gradereportsettings_form extends moodleform {

    /**
     * Define the form.
     */
    public function definition () {
        global $PAGE;
        
        $mform = $this->_form;

        $yesno = [
            1 => get_string('yes'),
            0 => get_string('no')
        ];
        
        $mform->addElement('select', 'preference_verticaldisplay', get_string('verticaldisplay', 'theme_opentechnology'), $yesno);
        $mform->addHelpButton('preference_verticaldisplay', 'verticaldisplay', 'theme_opentechnology');
        $mform->setDefault('preference_verticaldisplay', 0);
        
        // Инициализация менеджера профилей
        $manager = theme_opentechnology\profilemanager::instance();
        // Получение профиля текущей страницы
        $profile = $manager->get_current_profile();
        // Получение настроки темы ориентации текста в шапке отчета по оценкам
        $verticaldisplay = (int)$manager->get_theme_setting('gradereport_table', $profile);
        if( $verticaldisplay != 2 )
        {// Если в теме запрещено - фризим личную настройку
            $mform->freeze('preference_verticaldisplay');
        }

        $this->add_action_buttons(true, get_string('savechanges'));
    }
    
    /**
     * Extend the form definition after the data has been parsed.
     */
    public function definition_after_data() {
        global $CFG, $DB, $OUTPUT;
    
        $mform = $this->_form;
        $verticaldisplay = $mform->getElement('preference_verticaldisplay');
        $verticaldisplay->setValue(get_user_preferences('verticaldisplay', 0));
    }
}