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

namespace block_dof;

use local_opentechnology\otserial_base;
use stdClass;

class otserial extends otserial_base
{
    protected function setup_plugin_cfg()
    {
        $this->component = 'block_dof';
        $this->tariffcodes = array(
            'free',
            'Д-1', 'Д-2', 'Д-3', 'Д-Люкс',
            'Р',
            'П-Ла', 'П-Ко', 'П-Ве', 'П-Ун',
        );
    }
    
    public function __construct($upgrade=false)
    {
        global $CFG;
        $plugin = new stdClass();
        require($CFG->dirroot . '/blocks/dof/version.php');
        //URL приложения
        $purl = $CFG->wwwroot;
    
        parent::__construct($plugin->component, $plugin->version, $purl,
            array('upgrade'=>$upgrade));
    }
}