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
 * Найти блок "Комментарий преподавателя" в контексте модуля
 *
 * @param context_module $contextmodule
 * @return context_block
 */
function find_block_in_quiz($contextmodule)
{
    global $DB;
    
    // поиск курса, добавленного в контекст курса
    $blockinstancerecord = $DB->get_record(
            'block_instances',
            [
                'blockname' => 'quiz_teacher_feedback',
                'parentcontextid' => $contextmodule->id
            ],
            'id',
            IGNORE_MULTIPLE
            );
    
    if ( empty($blockinstancerecord) )
    {
        return false;
    }
    
    return context_block::instance($blockinstancerecord->id);
}