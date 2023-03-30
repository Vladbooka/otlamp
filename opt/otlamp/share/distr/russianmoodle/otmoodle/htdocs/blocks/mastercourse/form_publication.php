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
 * Блок согласования мастеркурса, классы форм
 *
 * @package    block_mastercourse
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require_once($CFG->libdir . '/formslib.php');

class block_mastercourse_form_publication extends moodleform
{
    protected $serviceinstance;
    
    protected function get_form_identifier() {
        $class = get_class($this);
        $serviceshortname = $this->_customdata->serviceinstance->get_service_shortname();
        return preg_replace('/[^a-z0-9_]/i', '_', $class) . '__' . $serviceshortname;
    }
    
    protected function definition()
    {
        $this->serviceinstance = $this->_customdata->serviceinstance;
        
        $this->serviceinstance->form_publication_definition($this->_form);
        
        // кнопки "сохранить"
        $this->add_action_buttons(false, get_string('form_publication__field__submit', 'block_mastercourse'));
    }
    
    /**
     * записывает новые статусы
     *
     * @throws moodle_exception
     */
    public function process() {
        // редактирование сообщения
        if ($data = $this->get_data())
        {
            $this->serviceinstance->form_publication_process($data);
        }
    }
}