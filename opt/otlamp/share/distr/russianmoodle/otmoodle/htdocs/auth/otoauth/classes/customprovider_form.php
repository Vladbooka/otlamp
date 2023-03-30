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
 * Настраиваемый провайдер авторизации. Форма создания
 *
 * @package    auth
 * @subpackage otoauth
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace auth_otoauth;

use otcomponent_yaml\Yaml;

require_once($CFG->libdir . '/formslib.php');

class customprovider_form extends \moodleform
{
    protected function definition()
    {
        $mform = &$this->_form;

        $mform->addElement('text', 'cp_code', get_string('custom_provider_property_code', 'auth_otoauth'));
        $mform->setType('cp_code', PARAM_ALPHANUMEXT);

        $mform->addElement('text', 'cp_name', get_string('custom_provider_property_name', 'auth_otoauth'));
        $mform->setType('cp_name', PARAM_RAW);

        $mform->addElement('textarea', 'cp_description', get_string('custom_provider_property_description', 'auth_otoauth'), [
            'class' => 'custom_provider_textarea',
            'rows' => '10',
            'cols' => '80',
        ]);
        $mform->setType('cp_description', PARAM_RAW);

        $mform->addElement('textarea', 'cp_config', get_string('custom_provider_property_config', 'auth_otoauth'), [
            'class' => 'custom_provider_textarea',
            'rows' => '10',
            'cols' => '80',
        ]);
        $mform->setType('cp_config', PARAM_RAW);
        $mform->addHelpButton('cp_config', 'custom_provider_property_config', 'auth_otoauth');

        $statuses = \auth_otoauth\customprovider::get_status_list();
        $mform->addElement('select', 'cp_status', get_string('custom_provider_property_status', 'auth_otoauth'), $statuses);

        $this->add_action_buttons();
    }

    function validation($data, $files) {

        $errors = [];

        $customproviders = \auth_otoauth\customprovider::get_custom_providers(['code' => $data['cp_code']]);
        if (!empty($customproviders))
        {
            if (count($customproviders) == 1 && array_key_exists('id', $this->_customdata))
            {
                $customprovider = array_shift($customproviders);
                if ($customprovider->id != $this->_customdata['id'])
                {
                    $errors['cp_code'] = get_string('custom_provider_error_code_not_unique', 'auth_otoauth');
                }
            } else
            {
                $errors['cp_code'] = get_string('custom_provider_error_code_not_unique', 'auth_otoauth');
            }
        }

        $statuses = \auth_otoauth\customprovider::get_status_list();
        if (!array_key_exists($data['cp_status'], $statuses))
        {
            $errors['cp_status'] = get_string('custom_provider_error_unknown_status', 'auth_otoauth');
        }


        try {
            \auth_otoauth\customprovider::parse_config($data['cp_config']);
        } catch (customprovider_exception $ex) {
            $errors['cp_config'] = $ex->getMessage();
        }


        return $errors;
    }

    public function process()
    {
        if ($this->is_cancelled())
        {
            redirect($this->_customdata['baseurl']);
        }
        if ($data = $this->get_data())
        {
            $customprovider = new \stdClass();
            $customprovider->code = $data->cp_code;
            $customprovider->name = $data->cp_name;
            $customprovider->description = $data->cp_description;
            $customprovider->config = $data->cp_config;
            $customprovider->status = $data->cp_status;
            if (array_key_exists('id', $this->_customdata))
            {
                $customprovider->id = $this->_customdata['id'];
                \auth_otoauth\customprovider::edit_custom_provider($customprovider);

            } else
            {
                \auth_otoauth\customprovider::add_custom_provider($customprovider);
            }

            redirect($this->_customdata['baseurl']);
        }
    }
}
