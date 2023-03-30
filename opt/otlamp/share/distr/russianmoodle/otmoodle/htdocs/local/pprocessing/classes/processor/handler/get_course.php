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

namespace local_pprocessing\processor\handler;

use moodle_url;
use local_pprocessing\container;

defined('MOODLE_INTERNAL') || die();

/**
 * Получение данных курса по идентификатору
 *
 * @package    local_pprocessing
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class get_course extends base
{
    /**
     * {@inheritDoc}
     * @see \local_pprocessing\processor\handler\base::execute()
     */
    protected function execution_process(container $container)
    {
        $courseid = $container->read('courseid');
        if ( empty($courseid) )
        {
            // обязательное поле
            return;
        }
        
        $url = new moodle_url('/course/view.php', ['id' => $courseid]);
        
        // получить объект курса
        $course = get_course($courseid);
        $course->url = $url->out(true);
        
        // кладем курс в пулл
        $container->write('course', $course, true, true);
        return $course;
    }
}

