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

use mod_event3kl\datemodifier\base\abstract_datemodifier;

defined('MOODLE_INTERNAL') || die();

class datemodifiers extends \ArrayObject {


    public function offsetSet($name, $value)
    {
        if (!is_object($value) || !($value instanceof abstract_datemodifier))
        {
            throw new \InvalidArgumentException(sprintf('Only subclasses of abstract_datemodifier allowed.'));
        }
        parent::offsetSet($name, $value);
    }

    /**
     * Получение списка имеющихся классов модификаторов дат в виде массива незаполненных инстансов
     * @return formats
     */
    public static function get_all_datemodifiers()
    {
        $datemodifiers = new self();

        foreach(glob(__DIR__ . '/datemodifier/*', GLOB_NOSORT) as $datemodifierfilename)
        {
            if (is_file($datemodifierfilename)) {
                require_once($datemodifierfilename);
                $datemodifiercode = basename($datemodifierfilename, '.php');
                try {
                    $datemodifiers->append(self::instance($datemodifiercode));
                } catch(\Exception $ex) {
                    continue;
                }
            }
        }

        return $datemodifiers;
    }

    /**
     * @param string $datemodecode
     * @throws \Exception
     * @return abstract_datemodifier
     */
    public static function instance($datemodifiercode, $datemodifierconfig) {
        $datemodifierclass = '\\mod_event3kl\\datemodifier\\'.$datemodifiercode;
        if (class_exists($datemodifierclass)) {
            return $datemodifierclass::instance_from_config($datemodifierconfig);
        }
        throw new \Exception('Date mode class not found');
    }

    public function get_select_options() {
        $options = [];
        foreach($this as $datemode) {
            $options[$datemode->get_code()] = $datemode->get_display_name();
        }
        return $options;
    }

    public function mod_form_definition(&$mform, &$form) {
        foreach($this as $datemode) {
            $datemode->mod_form_definition($mform, $form);
        }
    }
}