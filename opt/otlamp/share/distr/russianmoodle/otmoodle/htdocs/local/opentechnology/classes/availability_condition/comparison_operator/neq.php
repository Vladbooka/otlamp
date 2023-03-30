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
 * Оператор сравнения. Абстрактный класс
 *
 * @package    local_opentechnology
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_opentechnology\availability_condition\comparison_operator;

use local_opentechnology\availability_condition\abstract_comparison_operator;
use local_opentechnology\availability_condition\arg;

class neq extends abstract_comparison_operator {

    public static function define_args() : array {
        $args = [];
        $args[] = new arg('arg1');
        $args[] = new arg('arg2');
        return $args;
    }

    public function execute() {
        $arg1 = $this->args[0];
        $arg2 = $this->args[1];
        return $arg1->getValue() != $arg2->getValue();
    }

}