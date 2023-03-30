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

use mod_event3kl\format\base\abstract_format;

defined('MOODLE_INTERNAL') || die();

class formats extends \ArrayObject {


    public function offsetSet($name, $value)
    {
        if (!is_object($value) || !($value instanceof abstract_format))
        {
            throw new \InvalidArgumentException(sprintf('Only subclasses of abstract_format allowed.'));
        }
        parent::offsetSet($name, $value);
    }

    /**
     * Получение списка имеющихся классов форматов в виде массива незаполненных инстансов
     * @return formats
     */
    public static function get_all_formats()
    {
        $formats = new self();

        foreach(glob(__DIR__ . '/format/*', GLOB_NOSORT) as $formatfilename)
        {
            if (is_file($formatfilename)) {
                require_once($formatfilename);
                $formatcode = basename($formatfilename, '.php');
                try {
                    $formats->append(self::instance($formatcode));
                } catch(\Exception $ex) {
                    continue;
                }
            }
        }

        return $formats;
    }

    /**
     * @param string $formatcode
     * @throws \Exception
     * @return abstract_format
     */
    public static function instance($formatcode) {
        $formatclass = '\\mod_event3kl\\format\\'.$formatcode;
        if (class_exists($formatclass)) {
            return new $formatclass();
        }
        throw new \Exception('Format class not found');
    }

    public function get_select_options() {
        $options = [];
        foreach($this as $format) {
            $options[$format->get_code()] = $format->get_display_name();
        }
        return $options;
    }

    public function mod_form_definition(&$mform, &$form) {

        $mform->addElement('select', 'format', get_string('formattype', 'mod_event3kl'), $this->get_select_options());
        $mform->setDefault('format', 'common');

        foreach($this as $format) {
            $format->mod_form_definition($mform, $form);
        }
    }

    public function mod_form_validation($data, $files) {
        $errors = [];
        foreach($this as $format) {
            $errors = array_merge($errors, $format->mod_form_validation($data, $files));
        }
        return $errors;
    }
}