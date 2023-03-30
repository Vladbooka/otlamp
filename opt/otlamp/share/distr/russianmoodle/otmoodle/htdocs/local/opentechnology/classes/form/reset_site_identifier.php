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
 * Форма сброса идентификатора инсталляции
 *
 * @package    local
 * @subpackage opentechnology
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_opentechnology\form;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/formslib.php');

use moodleform;
use core\hub\registration;
use core\notification;
use context_system;

class reset_site_identifier extends moodleform {
    
    protected function definition() {
        
        $mform =& $this->_form;
        
        $mform->addElement('header', 'resetheader', get_string('reset_site_identifier_title', 'local_opentechnology'));
        
        $mform->addElement('submit', 'reset', get_string('reset_form_submit', 'local_opentechnology'));
        
        $mform->applyFilter('__ALL__', 'trim');
    }
    
    public function process() {
        if ($this->get_data()) {
            global $CFG;
            if (has_capability('local/opentechnology:reset_site_identifier', context_system::instance())) {
                $oldid = $CFG->siteidentifier;
                $wasregistered = registration::is_registered();
                if (registration::unregister(true, true)) {
                    $CFG->siteidentifier = null;
                    get_site_identifier();
                }
                
                if ($wasregistered) {
                    if (!registration::is_registered()) {
                        notification::add(get_string('unregister_successfull', 'local_opentechnology'), notification::SUCCESS);
                    } else {
                        notification::add(get_string('unregister_failed', 'local_opentechnology'), notification::SUCCESS);
                    }
                }
                
                if ($oldid != $CFG->siteidentifier) {
                    notification::add(get_string('reset_site_identifier_successfull', 'local_opentechnology'), notification::SUCCESS);
                } else {
                    notification::add(get_string('reset_site_identifier_failed', 'local_opentechnology'), notification::ERROR);
                }
            } else {
                notification::add(get_string('nopermissions', 'local_opentechnology', get_capability_string('local/opentechnology:resetsiteidentifier')), notification::ERROR);
            }
        }
    }
}