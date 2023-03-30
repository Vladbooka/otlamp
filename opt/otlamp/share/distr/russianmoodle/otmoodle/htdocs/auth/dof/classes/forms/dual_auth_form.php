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
 * Плагин аутентификации Деканата. Форма двухфакторной авторизации.
 *
 * @package    auth_dof
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace auth_dof\forms;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir.'/formslib.php');

use moodleform;

class dual_auth_form extends moodleform
{
    /**
     * Объявление полей формы
     */
    public function definition() {
        
        $mform = $this->_form;
        
        // Поле код
        $mform->addElement(
            'text',
            'code',
            get_string('dual_auth_text', 'auth_dof')
        );
        $mform->setType('code', PARAM_NOTAGS);
        // Подтверждение формы
        $this->add_action_buttons(true, get_string('confirm', 'auth_dof'));
    }
}
