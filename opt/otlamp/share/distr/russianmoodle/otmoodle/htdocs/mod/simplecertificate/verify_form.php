<?php
if (!defined('MOODLE_INTERNAL')) {
    die('Direct access to this script is forbidden.');    ///  It must be included from a Moodle page
}

require_once($CFG->dirroot.'/lib/formslib.php');


class verify_form extends moodleform {

    // Define the form
    function definition () {
        global $CFG;

        $mform =& $this->_form;
        $mform->addElement('text', 'code', get_string('code', 'simplecertificate'), array('size'=>'36'));
        $mform->setType('code', PARAM_ALPHANUMEXT);
        $mform->addRule('code', null, 'required', null, 'client');

        //Add recaptcha if enabeld
        if ($this->is_recaptcha_enabled()) {
            $mform->addElement('recaptcha', 'recaptcha_element', get_string('recaptcha', 'auth'));
            $mform->addHelpButton('recaptcha_element', 'recaptcha', 'auth');
        }


        $this->add_action_buttons(false, get_string('verifycertificate','simplecertificate'));
    }

    function validation($data, $files) {
        $errors = parent::validation($data, $files);
        if ($this->is_recaptcha_enabled()) {
            $recaptcha_element = $this->_form->getElement('recaptcha_element');
            if (!empty($this->_form->_submitValues['g-recaptcha-response'])) {
                $response = $this->_form->_submitValues['g-recaptcha-response'];
                if (!$recaptcha_element->verify($response)) {
                    $errors['recaptcha_element'] = get_string('incorrectpleasetryagain', 'auth');
                }
            } else {
                $errors['recaptcha_element'] = get_string('missingrecaptchachallengefield');
            }
        }
        return $errors;
    }

    function is_recaptcha_enabled() {
        global $CFG;
        return (!empty($CFG->recaptchapublickey) && !empty($CFG->recaptchaprivatekey));
    }

}