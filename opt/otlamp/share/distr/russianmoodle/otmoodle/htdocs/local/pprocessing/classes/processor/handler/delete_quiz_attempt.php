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

require_once($CFG->dirroot . '/local/pprocessing/locallib.php');
require_once($CFG->dirroot . '/mod/quiz/locallib.php');
defined('MOODLE_INTERNAL') || die();

/**
 * Хендлер удаления попыток тестирования
 *
 * @package     local_pprocessing
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class delete_quiz_attempt extends base
{
    /**
     * {@inheritDoc}
     * @see \local_pprocessing\processor\handler\base::execute()
     */
    protected function execution_process(container $container)
    {
        $attempt = $this->get_required_parameter('attempt');
        $quiz = $this->get_required_parameter('quiz');
        $needupdategrades = $this->get_optional_parameter('needupdategrades', true);
        
        if (is_array($attempt)) {
            $attempt = (object)$attempt;
        }
        if (is_array($quiz)) {
            $quiz = (object)$quiz;
        }
        
        // Если после удаления попытки не нужно обновлять оценки, делаем это через локальный метод
        if ($needupdategrades) {
            quiz_delete_attempt($attempt, $quiz);
        } else {
            local_pprocessing_quiz_delete_attempt_without_update_grades($attempt, $quiz);
        }
        
        return true;
    }
}

