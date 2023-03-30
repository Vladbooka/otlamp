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
 * Form for user's email input
 *
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once($CFG->libdir . '/formslib.php');

class block_email_form extends moodleform {
    public function definition() {
        global $USER;
        
        $mform = $this->_form;
        
        $attributes = array('size' => 20);
        
        $mform->addElement('text', 'email', get_string('enteremail', 'block_linksocial'), $attributes);
        $mform->setType('email', PARAM_EMAIL);
        $mform->setDefault('email', $USER->email);
        
        $this->add_action_buttons(true, get_string('resend', 'block_linksocial'));
    }
    
    public function validation($data, $files) {
        $errors = parent::validation($data, $files);
        
        if (!validate_email($data['email'])) {
            $errors['email'] = get_string('wrongemail', 'block_linksocial');
        } 
        return $errors;
    }
    
    function process() {
        global $DB, $USER, $CFG;
        
        $emaildata = $this->get_emaildata($USER->id);
        if ($this->is_cancelled()) {
            redirect(new moodle_url("$CFG->wwwroot/my"));
        } else if ($fromform = $this->get_data()) {
            $emaildata = $this->get_emaildata($USER->id, $fromform->email);
            $DB->update_record('user', $emaildata);
        }
        if (!empty($USER->email) && $USER->confirmed == 0) {
            auth_otoauth_send_confirmation_email($USER->id);
        }
    }
    
    /**
     * Возвращает объект emaildata
     * 
     * @param string $email
     * @return object
     */
    private function get_emaildata($userid, $email = null) {
        global $DB;
        
        $emaildata     = new stdClass();
        $emaildata->id = $userid;
        
        if (!is_null($email)) {
            $emaildata->email = $email;
        } else {
            $emaildata->email = $DB->get_field(
                    'user', 
                    'email', 
                    array('id' => $userid)
            );
        }
        return $emaildata;
    }
}