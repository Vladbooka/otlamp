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
class get_course_totals extends base
{
    /**
     * {@inheritDoc}
     * @see \local_pprocessing\processor\handler\base::execute()
     */
    protected function execution_process(container $container)
    {
        $courseid = $container->read('courseid');
        if ( empty($courseid) )
        {// обязательное поле
            return;
        }
        
        $userid = $container->read('userid');
        if ( empty($userid) )
        {// обязательное поле
            return;
        }
        
        
        $completion = new \completion_completion(['userid' => $userid, 'course' => $courseid]);
        $coursegradeitem = \grade_item::fetch_course_item($courseid);
        $coursegrade = \grade_grade::fetch_users_grades($coursegradeitem, [$userid])[$userid];
        $finalgradepct = grade_format_gradevalue_percentage(
            $coursegrade->finalgrade,
            $coursegradeitem,
            3,
            false
        );
        
        $coursetotals = [
            'courseid' => $courseid,
            'userid' => $userid,
            'is_complete' => $completion->is_complete(),
            'timecompleted' => $completion->timecompleted,
            'is_passed' => $coursegrade->is_passed(),
            'finalgrade' => $coursegrade->finalgrade,
            'finalgrade_pct' => (float)(substr($finalgradepct,0,-2))
        ];
        
        if (!empty($this->config['custom-scale']))
        {
            foreach($this->config['custom-scale'] as $scalepoint=>$conditions)
            {
                $meetconditions = true;
                foreach($conditions as $operator => $value)
                {
                    switch($operator)
                    {
                        case '>=':
                            $meetconditions = ($coursetotals['finalgrade_pct'] >= $value);
                            break;
                        case '>':
                            $meetconditions = ($coursetotals['finalgrade_pct'] > $value);
                            break;
                        case '==':
                            $meetconditions = ($coursetotals['finalgrade_pct'] == $value);
                            break;
                        case '<=':
                            $meetconditions = ($coursetotals['finalgrade_pct'] <= $value);
                            break;
                        case '<':
                            $meetconditions = ($coursetotals['finalgrade_pct'] < $value);
                            break;
                        default:
                            $meetconditions = false;
                            break;
                    }
                    if (!$meetconditions)
                    {
                        break;
                    }
                }
                if ($meetconditions)
                {
                    $coursetotals['finalgrade_custom_scale'] = $scalepoint;
                    break;
                }
            }
            if (!isset($coursetotals['finalgrade_custom_scale']))
            {
                $coursetotals['finalgrade_custom_scale'] = null;
            }
        }
        
        // кладем курс в пулл
        $container->write('course_totals', $coursetotals, true, true);
    }
}

