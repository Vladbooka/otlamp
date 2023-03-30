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

namespace local_pprocessing\processor\filter;

use local_pprocessing\container;
use local_pprocessing\processor\base as base_processor;


defined('MOODLE_INTERNAL') || die();

/**
 * Базовый класс фильтра
 *
 * @package    local_pprocessing
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
abstract class base extends base_processor
{
    /**
     * Формирует условия для выборки в виде sql
     *
     * @param container $container - Контейнер переменных
     *
     * @return stdClass со свойствами sql (строка с sql-условиями) и parameters (массив параметров, используемых в sql) 
     */
    protected function execution_process(container $container)
    {
    }
    
    public function get_type()
    {
        return 'filter';
    }
}

