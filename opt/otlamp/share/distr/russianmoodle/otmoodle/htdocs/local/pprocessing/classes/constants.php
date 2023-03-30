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

namespace local_pprocessing;

defined('MOODLE_INTERNAL') || die();

/**
 * Класс-хелпер констант
 *
 * @package    local_pprocessing
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class constants
{
    /**
     * доступные источники
     *
     * @var array
     */
    const sources = [
        'dof' => 'dof',
        'otcourselogic' => 'otcourselogic',
        'moodle' => 'moodle'
    ];
    
    /**
     * доступные типы логов
     *
     * @var array
     */
    const log_types = [
        'scenario' => 'scenario',
        'processor' => 'processor',
        'executor' => 'executor',
    ];
    
    /**
     * доступные статусы логов
     *
     * @var array
     */
    const log_statuses = [
        'error' => 'error',
        'success' => 'success',
        'warning' => 'warning',
        'info' => 'info',
        'debug' => 'debug'
    ];
    
    /**
     * операторы сравнения в sql
     *
     * @var array
     */
    const sql_comparison_operators = [
        'equal_to' => '=',
        'greater_than' => '>',
        'less_than' => '<',
        'greater_than_or_equal_to' => '>=',
        'less_than_or_equal_to' => '<=',
        'not_equal_to' => '<>'
    ];
}

