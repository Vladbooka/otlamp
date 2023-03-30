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
 * Класс формы
 *
 * @package    tool
 * @subpackage removedeletedusersfromcohorts
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once("$CFG->libdir/formslib.php");
require_once("$CFG->dirroot/admin/tool/removedeletedusersfromcohorts/lib.php");

class tool_removedeletedusersfromcohorts_form extends moodleform 
{
    protected function definition() {
        global $CFG, $DB;

        $mform = $this->_form;
        
        $mform->addElement('static', 'description', '', get_string('form_description', 'tool_removedeletedusersfromcohorts'));

        $this->add_action_buttons(false, get_string('form_doit', 'tool_removedeletedusersfromcohorts'));
    }
    
    public function process()
    {
        if( $formdata = $this->get_data() )
        {
            tool_removedeletedusersfromcohorts_execute();
        }
    }
}
