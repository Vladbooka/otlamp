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
use stdClass;
use moodleform;
use mod_otmutualassessment_mod_form;
use mod_otmutualassessment\moodlequickform_number;
use admin_settingpage;
use html_writer;
use core\notification;

class range extends base {
    
    /**
     * Минимальный балл для выставления другому участнику
     * @var integer
     */
    const DEFAULT_MIN_POINTS = 1;
    /**
     * Максимальный балл для выставления другому участнику
     * @var integer
     */
    const DEFAULT_MAX_POINTS = 10;
    
    /**
     * Код стратегии
     * @var string
     */
    protected static $code = 'range';
    
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
                        $maxgrouppoints = (count($members) - 1) * $this->get_max_value();
                        $maxpoints += $maxgrouppoints;
                    }
                }
            }
        } else {
            if (is_null($this->get_gradedusers())) {
                $this->set_graded_users();
            }
            $maxpoints = count($this->get_gradedusers()) * $this->get_max_value();
        }
        return $maxpoints;
    }
    
    /**
     * Добавление в форму настроек модуля курса кастомных настроек для конкретной стратегии
     * @param moodleform $mform указатель на объект moodleform
     * @param mod_otmutualassessment_mod_form $form указатель на объект mod_otmutualassessment_mod_form
     */
    public function add_custom_mod_form_elements(& $mform, & $form) {
        $min = get_config('mod_otmutualassessment', 'strategy_' . $this->get_code() . '_min');
        if ($min === false) {
            $min = self::DEFAULT_MIN_POINTS;
        }
        $max = get_config('mod_otmutualassessment', 'strategy_' . $this->get_code() . '_max');
        if ($max === false) {
            $max = self::DEFAULT_MAX_POINTS;
        }
        // Минимальный балл для участников
        $minpoints = new moodlequickform_number('minpoints', get_string('minpoints_label', 'mod_otmutualassessment'),
            ['min' => $min, 'max' => $max]);
        $mform->addElement($minpoints);
        $mform->setType('minpoints', PARAM_INT);
        $mform->setDefault('minpoints', $min);
        // Максимальный балл для участников
        $maxpoints = new moodlequickform_number('maxpoints', get_string('maxpoints_label', 'mod_otmutualassessment'),
            ['min' => $min, 'max' => $max]);
        $mform->addElement($maxpoints);
        $mform->setType('maxpoints', PARAM_INT);
        $mform->setDefault('maxpoints', $max);
    }
    
    /**
     * Подготовить данные из формы модуля курса для сохранения опций стратегии
     * @param stdClass $data объект с данными формы модуля курса
     * @param string строка (массив в json формате)
     */
    public function prepare_options_for_save($data) {
        $options = [];
        $min = get_config('mod_otmutualassessment', 'strategy_' . $this->get_code() . '_min');
        if ($min === false) {
            $min = self::DEFAULT_MIN_POINTS;
        }
        $max = get_config('mod_otmutualassessment', 'strategy_' . $this->get_code() . '_max');
        if ($max === false) {
            $max = self::DEFAULT_MAX_POINTS;
        }
        if (property_exists($data, 'minpoints')) {
            $options['minpoints'] = $data->minpoints;
        } else {
            $options['minpoints'] = $min;
        }
        if (property_exists($data, 'maxpoints')) {
            $options['maxpoints'] = $data->maxpoints;
        } else {
            $options['maxpoints'] = $max;
        }
        return json_encode($options);
    }
    
    /**
     * Подготовить опции стратегии по умолчанию для сохранения
     * @return string
     */
    public function get_default_options_for_save() {
        $options = [];
        $options['minpoints'] = get_config('mod_otmutualassessment', 'strategy_' . $this->get_code() . '_min');
        if ($options['minpoints'] === false) {
            $options['minpoints'] = self::DEFAULT_MIN_POINTS;
        }
        $options['maxpoints'] = get_config('mod_otmutualassessment', 'strategy_' . $this->get_code() . '_max');
        if ($options['maxpoints'] === false) {
            $options['maxpoints'] = self::DEFAULT_MAX_POINTS;
        }
        if ($options['minpoints'] >= $options['maxpoints']) {
            // Такого быть не должно, используем значения по умолчанию
            $options['minpoints'] = self::DEFAULT_MIN_POINTS;
            $options['maxpoints'] = self::DEFAULT_MAX_POINTS;
        }
        return json_encode($options);
    }
    
    /**
     * Добавить кастомные настройки для стратегий
     * @param admin_settingpage $settings
     */
    public function add_custom_settings(& $settings) {
        // Минимальное значение по умолчанию для модуля курса
        $pluginname = 'mod_otmutualassessment';
        $configname = 'strategy_' . $this->get_code() . '_min';
        $name = $pluginname . '/' . $configname;
        $visiblename = get_string('setting_' . $configname, $pluginname);
        $description = get_string('setting_' . $configname . '_desc', $pluginname);
        $defaultsetting = self::DEFAULT_MIN_POINTS;
        $setting = new \local_opentechnology\admin_setting\number($name, $visiblename, $description, $defaultsetting);
        $settings->add($setting);
        // Максимальное значение по умолчанию для модуля курса
        $pluginname = 'mod_otmutualassessment';
        $configname = 'strategy_' . $this->get_code() . '_max';
        $name = $pluginname . '/' . $configname;
        $visiblename = get_string('setting_' . $configname, $pluginname);
        $description = get_string('setting_' . $configname . '_desc', $pluginname);
        $defaultsetting = self::DEFAULT_MAX_POINTS;
        $setting = new \local_opentechnology\admin_setting\number($name, $visiblename, $description, $defaultsetting);
        $settings->add($setting);
    }
    
    /**
     * Валидация кастомных полей стратегий формы редактирования модуля курса
     * @param array $data
     * @param array $files
     * @param moodleform $mform
     */
    public function validation_custom_mod_form_elements($data, $files, & $mform) {
        $errors = [];
        $min = get_config('mod_otmutualassessment', 'strategy_' . $this->get_code() . '_min');
        if ($min === false) {
            $min = self::DEFAULT_MIN_POINTS;
        }
        $max = get_config('mod_otmutualassessment', 'strategy_' . $this->get_code() . '_max');
        if ($max === false) {
            $max = self::DEFAULT_MAX_POINTS;
        }
        if ($data['minpoints'] < $min) {
            $errors['minpoints'] = get_string('error_mod_form_invalid_min_value', 'mod_otmutualassessment');
        }
        if ($data['maxpoints'] > $max) {
            $errors['maxpoints'] = get_string('error_mod_form_invalid_max_value', 'mod_otmutualassessment');
        }
        if ($data['minpoints'] >= $data['maxpoints']) {
            $errors['maxpoints'] = get_string('error_mod_form_min_must_be_less_max', 'mod_otmutualassessment');
        }
        if (!empty($this->get_grades())) {
            $options = json_decode($this->get_instance()->options, true);
            $changemin = $data['minpoints'] != $options['minpoints'];
            $changemax = $data['maxpoints'] != $options['maxpoints'];
            if ($changemin) {
                if (!empty($errors['minpoints'])) {
                    $errors['minpoints'] .= html_writer::tag('br', '') . get_string('error_mod_form_min_value_can_not_be_changed', 'mod_otmutualassessment');
                } else {
                    $errors['minpoints'] = get_string('error_mod_form_min_value_can_not_be_changed', 'mod_otmutualassessment');
                }
            }
            if ($changemax) {
                if (!empty($errors['maxpoints'])) {
                    $errors['maxpoints'] .= html_writer::tag('br', '') . get_string('error_mod_form_max_value_can_not_be_changed', 'mod_otmutualassessment');
                } else {
                    $errors['maxpoints'] = get_string('error_mod_form_max_value_can_not_be_changed', 'mod_otmutualassessment');
                }
            }
        }
        return $errors;
    }
    
    /**
     * Предобработка дефолтных значений кастомных полей стратегий формы редактирования модуля курса
     *
     * @param array $default_values passed by reference
     */
    public function data_preprocessing_custom_mod_form_elements(&$default_values) {
        
        if (array_key_exists('options', $default_values))
        {
            $options = json_decode($default_values['options'], true);
            if (array_key_exists('minpoints', $options))
            {
                $default_values['minpoints'] = $options['minpoints'];
            }
            if (array_key_exists('maxpoints', $options))
            {
                $default_values['maxpoints'] = $options['maxpoints'];
            }
        }
    }
    
    /**
     * Получить минимальное значение диапазона баллов, которое можно выставить одному участнику
     * @param int $groupid идентификатор группы
     * @return int
     */
    public function get_min_value($groupid = null) {
        $options = json_decode($this->get_instance()->options, true);
        if (empty($options['minpoints']) && !(is_scalar($options['minpoints']) && strlen($options['minpoints']))) {
            $mindefault = get_config('mod_otmutualassessment', 'strategy_' . $this->get_code() . '_min');
            if ($mindefault === false) {
                $mindefault = self::DEFAULT_MIN_POINTS;
            }
            return $mindefault;
        }
        return $options['minpoints'];
    }
    
    /**
     * Получить максимальное значение диапазона баллов, которое можно выставить одному участнику
     * @param int $groupid идентификатор группы
     * @return int
     */
    public function get_max_value($groupid = null) {
        $options = json_decode($this->get_instance()->options, true);
        if (empty($options['maxpoints']) && !(is_scalar($options['maxpoints']) && strlen($options['maxpoints']))) {
            $maxdefault = get_config('mod_otmutualassessment', 'strategy_' . $this->get_code() . '_max');
            if ($maxdefault === false) {
                $maxdefault = self::DEFAULT_MAX_POINTS;
            }
            return $maxdefault;
        }
        return $options['maxpoints'];
    }
    
    /**
     * Представление формы оценщика
     * {@inheritDoc}
     * @see \mod_otmutualassessment\strategy\base::graderform_definition()
     */
    public function graderform_definition(& $mform, & $form) {
        foreach ($form->gradedusers as $gradeduser) {
            $element = new moodlequickform_number('graded_points_' . $gradeduser->id, fullname($gradeduser),
                ['min' => $form->otmutualassessment->get_min_value($form->groupid), 'max' => $form->otmutualassessment->get_max_value($form->groupid)]);
            $mform->addElement($element);
            $mform->setType('graded_points_' . $gradeduser->id, PARAM_INT);
        }
        
        $mform->addElement('submit', 'submit', get_string('save_grades', 'mod_otmutualassessment'));
        foreach ($form->gradedusers as $gradeduser) {
            // Нужно поставить баллы всем участникам
            $mform->disabledIf('submit', 'graded_points_' . $gradeduser->id, 'eq', '');
        }
        
        if ($records = $form->otmutualassessment->get_points($form->groupid)) {
            $default_values = new stdClass();
            foreach ($records as $record) {
                if ($mform->elementExists('graded_points_' . $record->graded)) {
                    $default_values->{'graded_points_' . $record->graded} = $record->point;
                }
            }
            $form->set_data($default_values);
        }
    }
    
    /**
     * Валидация формы оценщика
     * {@inheritDoc}
     * @see \mod_otmutualassessment\strategy\base::graderform_validation()
     */
    public function graderform_validation($data, $files, & $form) {
        $error = [];
        foreach ($form->gradedusers as $gradeduser) {
            if ($data['graded_points_' . $gradeduser->id] < $form->otmutualassessment->get_min_value($form->groupid)) {
                $error['graded_points_' . $gradeduser->id] = get_string('error_invalid_grade_must_be_grater_than_min_value', 'mod_otmutualassessment', $form->otmutualassessment->get_min_value($form->groupid));
            }
            if ($data['graded_points_' . $gradeduser->id] > $form->otmutualassessment->get_max_value($form->groupid)) {
                $error['graded_points_' . $gradeduser->id] = get_string('error_invalid_grade_must_be_less_than_max_value', 'mod_otmutualassessment', $form->otmutualassessment->get_max_value($form->groupid));
            }
            if (empty($data['graded_points_' . $gradeduser->id]) && !(is_scalar($data['graded_points_' . $gradeduser->id]) && strlen($data['graded_points_' . $gradeduser->id]))) {
                $error['graded_points_' . $gradeduser->id] = get_string('error_invalid_grade_must_be_not_empty', 'mod_otmutualassessment');
            }
        }
        return $error;
    }
    
    /**
     * Процесс обработки формы оценщика
     * {@inheritDoc}
     * @see \mod_otmutualassessment\strategy\base::graderform_process()
     */
    public function graderform_process(& $mform, & $form) {
        if ($formdata = $form->get_data()) {
            $grades = [];
            foreach ($form->gradedusers as $gradeduser) {
                $grades[$gradeduser->id] = $formdata->{'graded_points_' . $gradeduser->id} ?? $form->otmutualassessment->get_min_value($form->groupid);
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
     * Есть ли у стратегии настройки
     * {@inheritDoc}
     * @see \mod_otmutualassessment\strategy\base::has_config()
     */
    public function has_config() {
        return true;
    }
    
    /**
     * Получить html-код инструкции для оценщика
     * @return string
     */
    public function get_instruction_for_grader($groupid = null) {
        $a = new stdClass();
        $a->min = $this->get_min_value($groupid);
        $a->max = $this->get_max_value($groupid);
        return html_writer::div($this->get_plural_string($a->max, 'instruction_for_grader_' . $this->get_code(), 'mod_otmutualassessment', $a), 'instruction-for-grader');
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
        $gradedsfromdb = array_keys($this->get_gradeds_from_db_by_grader($userid, $groupid));
        asort($gradedsfromdb);
        $gradedusers = array_keys($this->get_gradedusers());
        asort($gradedusers);
        if (!empty(array_diff($gradedusers, $gradedsfromdb))) {
            return false;
        }
        return true;
    }
}
