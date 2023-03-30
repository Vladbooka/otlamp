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
use local_pprocessing\processor\condition;
use local_pprocessing\logger;

defined('MOODLE_INTERNAL') || die();

/**
 * Получить время в заданном формате
 *
 * @package local
 * @subpackage pprocessing
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class get_formatted_time extends base
{
    /**
     * {@inheritDoc}
     * @see \local_pprocessing\processor\handler\base::execute()
     */
    protected function execution_process(container $container)
    {
        // Значение (метка) времени, которое нужно преобразовать
        $time = $this->get_required_parameter('time');
        // Формат, к которому нужно преобразовать
        $format = $this->get_optional_parameter('format', 'timestamp');
        $this->debugging('', ['time' => $time, 'format' => $format]);
        // Получение основных данных на основе переданных параметров
        switch ($format) {
            case 'timestamp':
                return $time;
                break;
            case 'date':
                return date('Y-m-d', $time);
                break;
            case 'datetime':
                return date('Y-m-d H:i:s', $time);
                break;
            default:
                return date($format, $time);
                break;
        }
    }
}

