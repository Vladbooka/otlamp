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

namespace otcomponent_otlogger;

defined('MOODLE_INTERNAL') || die();

/**
 * Родительский класс получателя логов
 * 
 * @package    local_opentechnology
 * @subpackage log
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

class receiver_base {
    
    /**
     * Тип логируемых данных 
     * 
     * @var string
     */
    
    protected $type;
    
    /**
     * Данные, которые нужно залогировать, чаще всего массив
     * 
     * @var mixed
     */
    
    protected $messagedata;
    
    function __construct($type,$messagedata){
  
        $this->type = $type;
        $this->messagedata = $messagedata;
    }
}