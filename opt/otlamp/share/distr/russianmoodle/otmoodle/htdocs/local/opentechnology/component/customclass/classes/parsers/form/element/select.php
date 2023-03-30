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
 * Настраиваемые формы
 *
 * @package    local_opentechnology
 * @subpackage otcomponent_customclass
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace otcomponent_customclass\parsers\form\element;


class select extends \otcomponent_customclass\parsers\form\element
{
    
    protected function get_display_value($n)
    {
        $formelement = $this->get();
        
        $values = $this->get_value($n);
        if (!is_array($values))
        {
            $values = [$values];
        }
        
        foreach ($values as $n => $value) {
            $found = false;
            for ($i = 0; $i < count($formelement->_options); $i++) {
                if ((string)$value == (string)$formelement->_options[$i]['attr']['value']) {
                    $values[$n] = $formelement->_options[$i]['text'];
                    $found = true;
                    break;
                }
            }
            if (is_null($value) && !$found)
            {
                unset($values[$n]);
            }
        }
        
        return empty($values) ? null : join(', ', $values);
        
    }
}