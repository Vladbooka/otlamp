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

namespace mod_event3kl;

use mod_event3kl\provider\base\abstract_provider;

defined('MOODLE_INTERNAL') || die();

class providers extends \ArrayObject {


    public function offsetSet($name, $value)
    {
        if (!is_object($value) || !($value instanceof abstract_provider))
        {
            throw new \InvalidArgumentException(sprintf('Only subclasses of abstract_provider allowed.'));
        }
        parent::offsetSet($name, $value);
    }

    /**
     * Получение списка имеющихся классов провайдеров в виде массива незаполненных инстансов
     * @return providers
     */
    public static function get_all_providers()
    {
        $providers = new self();

        foreach(glob(__DIR__ . '/provider/*', GLOB_NOSORT) as $providerfilename)
        {
            if (is_file($providerfilename)) {
                require_once($providerfilename);
                $providercode = basename($providerfilename, '.php');
                try {
                    $providers->append(self::instance($providercode));
                } catch(\Exception $ex) {
                    continue;
                }
            }
        }

        return $providers;
    }

    /**
     * @param string $providercode
     * @param event3kl $event3kl
     * @throws \Exception
     * @return abstract_provider
     */
     public static function instance($providercode, event3kl $event3kl=null) {
        $providerclass = '\\mod_event3kl\\provider\\'.$providercode;
        if (class_exists($providerclass)) {
            return new $providerclass($event3kl);
        }
        throw new \Exception('Provider class not found');
    }

    public function get_select_options() {
        $options = [];
        foreach($this as $provider) {
            $options[$provider->get_code()] = $provider->get_display_name();
        }
        return $options;
    }

    public function mod_form_definition(&$mform, &$form) {

        $mform->addElement('select', 'provider', get_string('providertype', 'mod_event3kl'), $this->get_select_options());
        $mform->setDefault('provider', 'external');

        foreach($this as $provider) {
            $provider->mod_form_definition($mform, $form);
        }
    }

    public function mod_form_validation($data, $files) {
        $errors = [];
        foreach($this as $provider) {
            $errors = array_merge($errors, $provider->mod_form_validation($data, $files));
        }
        return $errors;
    }
}