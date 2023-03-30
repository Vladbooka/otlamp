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
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle. If not, see <http://www.gnu.org/licenses/>.
namespace local_pprocessing\processor\handler;

use local_pprocessing\container;
use local_pprocessing\logger;

defined('MOODLE_INTERNAL') || die();

/**
 * Отрезать переданную часть строки от начала или конца строки
 *
 * @package local
 * @subpackage pprocessing
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class cut extends base
{
    protected function validate_parameter($name, $value) {
        switch ($name) {
            case 'string':
            case 'part':
                return is_string($value);
                break;
            case 'tail':
                return is_bool($value);
                break;
            default:
                return false;
                break;
        }
    }
    
    /**
     * {@inheritDoc}
     * @see \local_pprocessing\processor\handler\base::execute()
     */
    protected function execution_process(container $container)
    {
        $string = $this->get_required_parameter('string');
        $part = $this->get_required_parameter('part');
        $tail = $this->get_optional_parameter('tail', true);
        if (strpos($string, $part) !== false) {
            if ($tail) {
                if (strpos($string, $part) + strlen($part) == strlen($string)) {
                    return substr($string, 0, strpos($string, $part));
                }
            } else {
                if (strpos($string, $part) === 0) {
                    return substr($string, strlen($part));
                }
            }
        }
        // Если не найдена подстрока или подстрока не является началом или концом строки, вернем без преобразований
        return $string;
    }
}

