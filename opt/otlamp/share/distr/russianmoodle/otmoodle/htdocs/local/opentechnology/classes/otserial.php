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

namespace local_opentechnology;

use stdClass;

class otserial extends otserial_base
{
    protected function setup_plugin_cfg()
    {
        $this->code_cfg = '';
        $this->code_str = 'local_opentechnology';
        $this->code_param = '';
        $this->component = 'local_opentechnology';
        $this->tariffcodes = array(
            'free',
            'М-1', 'М-2', 'М-3', 'М-Люкс',
            'Д-1', 'Д-2', 'Д-3', 'Д-Люкс',
            'А', 'Р',
            'П-Ре', 'П-Ин', 'П-Ла', 'П-Ко', 'П-Ве', 'П-Ун',
            'Серверная лицензия', 'Облачная лицензия', 
        );
    }


    public function __construct($upgrade=false)
    {
        global $CFG;
        $plugin = new stdClass();
        require($CFG->dirroot . '/local/opentechnology/version.php');

        parent::__construct('moodle', $plugin->version, $CFG->wwwroot,
            array('upgrade'=>$upgrade));
    }

    public function get_bproduct_data()
    {
        // Moodle и есть базовый продукт, так что возвращаем false
        return false;
    }
}