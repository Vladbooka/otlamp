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
 * Модуль Взаимная оценка. Класс взаимной оценки.
 *
 * @package    mod
 * @subpackage otmutualassessment
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_otmutualassessment\strategy;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/grouplib.php');
require_once($CFG->libdir . '/gradelib.php');

use mod_otmutualassessment\strategy\base;
use context_course;
use grade_scale;
use moodle_exception;
use html_writer;
use mod_otmutualassessment\moodlequickform_number;
use stdClass;
use core\notification;

class mutual extends base {
    /**
     * Код стратегии
     * @var string
     */
    protected static $code = 'mutual';
    
    /**
     * Подготовить и вернуть список пользователей для оценки
     * @return array
     */
    public function get_graded_users($groupid = null) {
        // Potential graders should be active users only.
        $potentialgradedusers = get_enrolled_users($this->get_context(), 'mod/otmutualassessment:begradedbyothers',
            $groupid, 'u.*', null, null, null, true);
        
        $gradedusers = [];
        if ($this->get_course_module()->effectivegroupmode == SEPARATEGROUPS) {
            if (!is_null($groupid) && groups_is_member($groupid, $this->get_grader()->id)) {
                if ($groups = groups_get_all_groups($this->get_course()->id, $this->get_grader()->id, $this->get_course_module()->groupingid)) {
                    foreach ($groups as $group) {
                        foreach ($potentialgradedusers as $graded) {
                            if ($graded->id == $this->get_grader()->id) {
                                // Do not send self.
                                continue;
                            }
                            if (groups_is_member($group->id, $graded->id)) {
                                $gradedusers[$graded->id] = $graded;
                            }
                        }
                    }
                }
            }
        } elseif ($this->get_course_module()->effectivegroupmode == VISIBLEGROUPS) {
            if (!is_null($groupid)) {
                if ($groups = groups_get_all_groups($this->get_course()->id, 0, $this->get_course_module()->groupingid)) {
                    foreach ($groups as $group) {
                        foreach ($potentialgradedusers as $graded) {
                            if ($graded->id == $this->get_grader()->id) {
                                // Do not send self.
                                continue;
                            }
                            $gradedusers[$graded->id] = $graded;
                        }
                    }
                }
            }
        } else {
            foreach ($potentialgradedusers as $graded) {
                if ($graded->id == $this->get_grader()->id) {
                    // Do not send self.
                    continue;
                }
                // Must be enrolled.
                $context = context_course::instance($this->get_course()->id);
                if (is_enrolled($context, $graded->id)) {
                    $gradedusers[$graded->id] = $graded;
                }
            }
        }
        return $gradedusers;
    }
    
    /**
     * Рассчитать и получить оценку пользователя
     * @param int $userid идентификатор пользователя
     * @throws moodle_exception
     * @return mixed|NULL|number
     */
    public function calculate_grade($userid) {
        switch ($this->get_instance()->gradingmode) {
            case self::ABSOLUTE:
                $userpoints = $this->get_user_points($userid);
                if ($this->get_instance()->grade > 0) {
                    // Значение
                    return grade_floatval(unformat_float($userpoints));
                } else {
                    // Шкала
                    $scale = new grade_scale(['id' => -($this->get_instance()->grade)]);
                    $scaleitems = $scale->load_items();
                    end($scaleitems);
                    $maxitem = key($scaleitems);
                    if ($userpoints > 0) {
                        return $maxitem + 1;
                    } else {
                        return 1;
                    }
                }
                break;
            case self::RELATIVE:
            default:
                $userpoints = $this->get_user_points($userid);
                $maxpoints = $this->get_max_points($userid);
                if ($maxpoints == 0) {
                    // Нет других участников группы
                    $ratio = 0;
                }
                if ($userpoints >= $maxpoints) {
                    // Например, пользователя с уже проставленной оценкой, переводим в новую группу
                    $ratio = 1;
                } else {
                    $ratio = $userpoints / $maxpoints;
                }
                if ($this->get_instance()->grade > 0) {
                    // Значение
                    return grade_floatval(unformat_float($ratio * $this->get_instance()->grade));
                } else {
                    // Шкала
                    $scale = new grade_scale(['id' => -($this->get_instance()->grade)]);
                    $scaleitems = $scale->load_items();
                    end($scaleitems);
                    $maxitem = key($scaleitems);
                    $step = 1 / $maxitem;
                    return round($ratio / $step) + 1;
                }
                break;
        }
    }
    
    /**
     * Получить максимально возможное кол-во баллов, которые может получить пользователь
     * Для разных пользователей в зависимости от участия в группах и группового режима может быть разное кол-во максимальных баллов
     * @param int $userid идентификатор пользователя
     * @return number
     */
    public function get_max_points($userid) {
        $maxpoints = 0;
        if (!empty($this->get_course_module()->effectivegroupmode)) {
            $groups = $this->get_user_groups($userid);
            if ($groups !== false) {
                // Включен групповой режим
                foreach ($groups as $group) {
                    $maxgrouppoints = 0;
                    $members = groups_get_members($group);
                    if ($members !== false && count($members) > 1) {
                        // Если членов группы больше 1
                        $maxgrouppoints = count($members) + 1;
                        $maxpoints += $maxgrouppoints * (count($members) - 1);
                    }
                }
            }
        } else {
            if (is_null($this->get_grader())) {
                $this->set_grader($userid);
            }
            if (is_null($this->get_gradedusers())) {
                $this->set_graded_users();
            }
            $maxpoints = count($this->get_gradedusers()) * $this->get_points_for_assessment();
        }
        return $maxpoints;
    }
    
    /**
     * Получить минимальное значение диапазона баллов, которое можно выставить одному участнику
     * @param int $groupid идентификатор группы
     * @return int
     */
    public function get_min_value($groupid = null) {
        return 0;
    }
    
    /**
     * Получить максимальное значение диапазона баллов, которое можно выставить одному участнику
     * @param int $groupid идентификатор группы
     * @return int
     */
    public function get_max_value($groupid = null) {
        if (!empty($groupid)) {
            if ($members = groups_get_members($groupid)) {
                return count($members) + 1;
            } else {
                return 1;
            }
        } else {
            if (is_null($this->get_gradedusers())) {
                $this->set_graded_users($groupid);
            }
            return count($this->get_gradedusers()) + 2;
        }
    }
    
    public function graderform_definition(& $mform, & $form) {
        $pointsleft = $form->otmutualassessment->get_points_for_assessment($form->groupid);
        
        $mform->addElement('hidden', 'pointsleft', $pointsleft);
        $mform->setType('pointsleft', PARAM_INT);
        
        $mform->addElement('static', 'visiblepointsleft', get_string('leftpoints', 'mod_otmutualassessment'),
            html_writer::div($pointsleft, 'leftpoints'));
        $mform->addElement('static', 'hr', '', html_writer::tag('hr', ''));
        
        foreach ($form->gradedusers as $gradeduser) {
            $element = new moodlequickform_number('graded_points_' . $gradeduser->id, fullname($gradeduser), [
                'min' => $form->otmutualassessment->get_min_value($form->groupid),
                'max' => $form->otmutualassessment->get_max_value($form->groupid),
            ]);
            $mform->addElement($element);
            $mform->setType('graded_points_' . $gradeduser->id, PARAM_INT);
        }
        
        $mform->addElement('submit', 'submit', get_string('save_grades', 'mod_otmutualassessment'));
        $mform->disabledIf('submit', 'pointsleft', 'noteq', 0);
        
        if ($records = $form->otmutualassessment->get_points($form->groupid)) {
            $form->reset_summ();
            $default_values = new stdClass();
            foreach ($records as $record) {
                if ($mform->elementExists('graded_points_' . $record->graded)) {
                    $default_values->{'graded_points_' . $record->graded} = $record->point;
                    $form->add_to_summ($record->point);
                }
            }
            $default_values->pointsleft = $form->otmutualassessment->get_points_for_assessment($form->groupid) - $form->get_summ();
            $form->set_data($default_values);
        }
    }
    
    public function graderform_definition_after_data(& $mform, & $form) {
        $summ = 0;
        foreach ($form->gradedusers as $gradeduser) {
            $summ += (int)$mform->getElementValue('graded_points_' . $gradeduser->id);
        }
        $mform->setDefault('visiblepointsleft', html_writer::div(
            $form->otmutualassessment->get_points_for_assessment($form->groupid) - $summ, 'leftpoints'));
    }
    
    public function graderform_validation($data, $files, & $form) {
        $error = [];
        foreach ($form->gradedusers as $gradeduser) {
            if ($data['graded_points_' . $gradeduser->id] < 0) {
                $error['graded_points_' . $gradeduser->id] = get_string('error_invalid_grade_must_be_greater_than_zero', 'mod_otmutualassessment');
            }
        }
        if ($form->is_submitted() && $data['pointsleft'] > 0) {
            $error['visiblepointsleft'] = get_string('error_all_points_are_not_distributed', 'mod_otmutualassessment');
        }
        return $error;
    }
    
    public function graderform_process(& $mform, & $form) {
        if ($formdata = $form->get_data()) {
            $grades = [];
            foreach ($form->gradedusers as $gradeduser) {
                $grades[$gradeduser->id] = $formdata->{'graded_points_' . $gradeduser->id} ?? 0;
            }
            if ($form->otmutualassessment->save_grades($form->grader, $grades, $form->groupid)) {
                notification::add(get_string('grades_saved_successfully', 'mod_otmutualassessment'), notification::SUCCESS);
            }
        }
        
        if ((!empty($form->groupid) && !groups_is_member($form->groupid, $form->grader->id))
            || $form->otmutualassessment->is_user_completed_assessment($form->grader->id, $form->groupid)) {
                // Если не член группы или выполнил обязательства оценщика
                $mform->freeze();
        }
    }
    
    /**
     * Подключение фронтенд-обработчика формы оценщика
     */
    public function graderform_js_call() {
        global $PAGE;
        $PAGE->requires->js_call_amd('mod_otmutualassessment/pointsleftcheck', 'init', [$this->get_points_for_assessment()]);
    }
    
    /**
     * Получить html-код инструкции для оценщика
     * @return string
     */
    public function get_instruction_for_grader($groupid = null) {
        $a = new stdClass();
        $a->points = $this->get_points_for_assessment($groupid);
        return html_writer::div($this->get_plural_string($a->points, 'instruction_for_grader_' . $this->get_code(), 'mod_otmutualassessment', $a), 'instruction-for-grader');
    }
    
    /**
     * Проверить, выполнил ли пользователь обязательства оценщика
     * @param int $userid идентификатор пользователя
     * @param int $groupid идентификатор группы
     * @return boolean
     */
    public function is_user_completed_assessment($userid, $groupid = null) {
        if (empty($this->get_gradedusers())) {
            // Некого оценивать
            return false;
        }
        $amountpoints = $this->get_amount_points_by_graded_users($userid, $groupid);
        $distributionpoints = $this->get_points_for_assessment($groupid);
        if ($amountpoints != $distributionpoints) {
            return false;
        }
        return true;
    }
}
