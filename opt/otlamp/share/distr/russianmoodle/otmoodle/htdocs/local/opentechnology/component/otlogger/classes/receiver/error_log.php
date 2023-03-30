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

namespace otcomponent_otlogger\receiver;

use otcomponent_otlogger\receiver_base;

defined('MOODLE_INTERNAL') || die();



/**
 * Класс получателя логов error_log
 * 
 * @package    local_opentechnology
 * @subpackage log
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

class error_log extends receiver_base {
   
   /**
    * Метод записи лога в error.log
    */
    
    public function create_log(){
        
        if (! empty($this->type)){
            $message = 'OTlogger: ' . $this->type . ': ' . json_encode($this->messagedata);
            error_log($message);
        }
        else {
            error_log('Trying to create unknown type log');
        }
    }
    
    
}

