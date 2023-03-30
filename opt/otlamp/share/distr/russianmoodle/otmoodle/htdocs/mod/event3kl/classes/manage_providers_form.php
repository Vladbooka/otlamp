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

use mod_event3kl\provider\external;
use Exception;

require_once($CFG->libdir . '/formslib.php');

defined('MOODLE_INTERNAL') || die();

/**
 * Класс формы управления провайдерами
 *
 */
class manage_providers_form extends \moodleform {
    
    /**
     * Объект для работы с провайдером
     * @var external|null
     */
    private $provider = null;
    
    protected function definition() {
        $mform = & $this->_form;
        $this->provider = new external();
        $this->provider->set_customdata($this->_customdata);
        $this->provider->settings_definition($mform, $this);
    }
    
    public function validation($data, $files) {
        $errors = parent::validation($data, $files);
        return array_merge($errors, $this->provider->settings_validation($data, $files));
    }
    
    public function process() {
        $mform = & $this->_form;
        $this->provider->settings_processing($mform, $this);
    }
}