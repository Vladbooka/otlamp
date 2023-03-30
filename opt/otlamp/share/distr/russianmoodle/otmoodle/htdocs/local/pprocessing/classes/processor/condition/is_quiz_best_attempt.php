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

namespace local_pprocessing\processor\condition;
use local_pprocessing\container;

require_once($CFG->dirroot . '/mod/quiz/locallib.php');
defined('MOODLE_INTERNAL') || die();

/**
 * Условие проверки, что переданная попытка - лучшая
 *
 * @package     local_pprocessing
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class is_quiz_best_attempt extends base
{
    /**
     * {@inheritDoc}
     * @see \local_pprocessing\processor\handler\base::execute()
     */
    protected function execution_process(container $container)
    {
        $attempt = $this->get_required_parameter('attempt');
        if (is_array($attempt)) {
            $attempt = (object)$attempt;
        }
        $quiz = $this->get_required_parameter('quiz');
        if (is_array($quiz)) {
            $quiz = (object)$quiz;
        }
        if ($quiz->grademethod == QUIZ_GRADEAVERAGE) {
            // Для средней оценки каждая попытка лучшая
            return true;
        }
        // Лучшая попытка может быть только среди завершенных
        $attempts = quiz_get_user_attempts($quiz, $attempt->userid);
        $bestattempt = quiz_calculate_best_attempt($quiz, $attempts);
        $this->debugging('attemptid, bestattemptid, quiz', [$attempt->id, $bestattempt->id, $quiz]);
        
        return $attempt->id == $bestattempt->id;
    }
}

