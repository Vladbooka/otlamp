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

global $CFG;
require_once($CFG->dirroot . '/config.php');
require_once($CFG->dirroot . '/mod/otmutualassessment/locallib.php');

/**
 * Does otmutualassessment support requested feature?
 *
 * @param $feature
 */
function otmutualassessment_supports($feature)
{
    switch ($feature) {
        case FEATURE_GROUPS:
            return true;
            break;
        case FEATURE_GROUPINGS:
            return true;
            break;
        case FEATURE_MOD_INTRO:
            return true;
            break;
        case FEATURE_COMPLETION_TRACKS_VIEWS:
            return true;
            break;
        case FEATURE_COMPLETION_HAS_RULES:
            return true;
            break;
        case FEATURE_GRADE_HAS_GRADE:
            return true;
            break;
        case FEATURE_BACKUP_MOODLE2:
            return true;
            break;
        case FEATURE_SHOW_DESCRIPTION:
            return true;
            break;
        default:
            return null;
            break;
    }
}

/**
 * Add otmutualassessment instance
 *
 * @param stdClass $otmutualassessment
 */
function otmutualassessment_add_instance($data)
{
    global $DB;
    $context = context_module::instance($data->coursemodule);
    $strategylist = mod_otmutualassessment_get_strategy_list();
    $otmutualassessment = new $strategylist[$data->strategy]($context, null, null);
    $instance = new stdClass();
    $instance->course = $data->course;
    $instance->name = $data->name;
    $instance->intro = $data->intro;
    $instance->introformat = $data->introformat;
    $instance->timecreated = $instance->timemodified = time();
    $instance->strategy = $data->strategy;
    $instance->grade = $data->grade;
    $instance->gradingmode = $data->gradingmode;
    $instance->completionsetgrades = $data->completionsetgrades ?? 0;
    $instance->options = $otmutualassessment->get_default_options_for_save();
    $instance->id = $DB->insert_record('otmutualassessment', $instance);
    $otmutualassessment->set_instance($instance);
    $otmutualassessment->update_gradebook(false, $data->coursemodule);
    return $instance->id;
}

/**
 * Update otmutualassessment instance
 *
 * @param stdClass $otmutualassessment
 */
function otmutualassessment_update_instance($data)
{
    global $DB;
    $context = context_module::instance($data->coursemodule);
    $strategylist = mod_otmutualassessment_get_strategy_list();
    $otmutualassessment = new $strategylist[$data->strategy]($context, null, null);
    $instance = new stdClass();
    $instance->id = $data->instance;
    $instance->course = $data->course;
    $instance->name = $data->name;
    $instance->intro = $data->intro;
    $instance->introformat = $data->introformat;
    $instance->timemodified = time();
    $instance->strategy = $data->strategy;
    $instance->grade = $data->grade;
    $instance->gradingmode = $data->gradingmode;
    $instance->completionsetgrades = $data->completionsetgrades ?? 0;
    $instance->options = $otmutualassessment->prepare_options_for_save($data);
    $result = $DB->update_record('otmutualassessment', $instance);
    $otmutualassessment->update_gradebook(false, $otmutualassessment->get_course_module()->id);
    return $result;
}

/**
 * Delete otmutualassessment instance
 *
 * @param integer $id
 */
function otmutualassessment_delete_instance($id)
{
    $cm = get_coursemodule_from_instance('otmutualassessment', $id, 0, false, MUST_EXIST);
    $context = context_module::instance($cm->id);
    $strategylist = mod_otmutualassessment_get_strategy_list();
    $instance = mod_otmutualassessment_get_instance($id);
    $otmutualassessment = new $strategylist[$instance->strategy]($context, null, null);
    return $otmutualassessment->delete_instance();
}

/**
 * Определить завершение элемента курса на основе условий
 *
 * @param object $course - Объект курса
 * @param object $cm - Объект элемента курса
 * @param int    $userid - ID пользователя, для которго проверяется завершение элемента
 * @param bool   $type - Тип проверки (и/или)
 *
 * @return bool - True, если элемент завершен и false, если нет
 */
function otmutualassessment_get_completion_state($course, $cm, $userid, $type)
{
    $context = context_module::instance($cm->id);
    $strategylist = mod_otmutualassessment_get_strategy_list();
    $instance = mod_otmutualassessment_get_instance($cm->instance);
    $otmutualassessment = new $strategylist[$instance->strategy]($context, $cm, $course);
    if ($otmutualassessment->get_instance()->completionsetgrades) {
        $status = $otmutualassessment->get_grader_cm_status($userid);
        return ($status == $otmutualassessment::COMPLETED);
    } else
    {
        return $type;
    }
}

/**
 * Обновление оценок
 * @param stdClass $otmutualassessment объект инстанса
 * @param array $grades массив оценок или флаг сброса оценок
 * @return int результат GRADE_UPDATE_OK|GRADE_UPDATE_FAILED|GRADE_UPDATE_MULTIPLE|GRADE_UPDATE_ITEM_LOCKED
 */
function otmutualassessment_grade_item_update($otmutualassessment, $grades = null)
{
    global $CFG;
    require_once($CFG->libdir.'/gradelib.php');
    
    $params['itemname'] = $otmutualassessment->name;
    if( $otmutualassessment->grade > 0 ) {
        $params['gradetype'] = GRADE_TYPE_VALUE;
        $params['grademax']  = $otmutualassessment->grade;
        $params['grademin']  = 0;
    } elseif( $otmutualassessment->grade < 0 ) {
        $params['gradetype'] = GRADE_TYPE_SCALE;
        $params['scaleid']   = -$otmutualassessment->grade;
    } else
    {
        $params['gradetype'] = GRADE_TYPE_NONE;
    }
    if( $grades  === 'reset' )
    {
        $params['reset'] = true;
        $grades = null;
    }
    return grade_update('mod/otmutualassessment', $otmutualassessment->course, 'mod', 'otmutualassessment',
        $otmutualassessment->id, 0, $grades, $params);
}

/**
 * Удаление оценок
 * @param stdClass $otmutualassessment объект инстанса
 * @return int результат GRADE_UPDATE_OK|GRADE_UPDATE_FAILED|GRADE_UPDATE_MULTIPLE|GRADE_UPDATE_ITEM_LOCKED
 */
function otmutualassessment_grade_item_delete($otmutualassessment)
{
    global $CFG;
    require_once($CFG->libdir . '/gradelib.php');

    return grade_update('mod/otmutualassessment', $otmutualassessment->course, 'mod', 'otmutualassessment',
        $otmutualassessment->id, 0, null, ['deleted' => 1]);
}

/**
 * Update activity grades.
 *
 * @param stdClass $otmutualassessment объект инстанса
 * @param int $userid specific user only, 0 means all
 * @param bool $nullifnone - not used
 */
function otmutualassessment_update_grades($otmutualassessment, $userid = 0) {
    global $CFG;
    require_once($CFG->libdir.'/gradelib.php');

    if( $otmutualassessment->grade == 0 )
    {
        otmutualassessment_grade_item_update($otmutualassessment);

    } elseif( $grades = otmutualassessment_get_user_grades($otmutualassessment, $userid) )
    {
        foreach($grades as $k => $v)
        {
            if( $v['rawgrade'] == -1 )
            {
                $grades[$k]['rawgrade'] = null;
            } elseif( $v['rawgrade'] > 100 )
            {
                $grades[$k]['rawgrade'] = 100;
            }
        }
        otmutualassessment_grade_item_update($otmutualassessment, $grades);

    } else
    {
        otmutualassessment_grade_item_update($otmutualassessment);
    }
}

/**
 * Получает оценки пользователей
 * @param int|stdClass $comdi объект вебинара bли идентификатор
 * @param int $userid ID пользователя (если нужно получить оценки конкретного пользователя)
 * @return array ['user ID'] => ['userid' => user ID, 'rawgrade' => user grade]
 */
function otmutualassessment_get_user_grades($otmutualassessment, $userid = 0)
{
    global $DB;
    // Нормализация
    if (is_number($otmutualassessment)) {
        $id = $otmutualassessment;
    } elseif (is_object($otmutualassessment)) {
        if (!empty($otmutualassessment->id)) {
            $id = $otmutualassessment->id;
        }
    } else {
        throw new moodle_exception('error_undefined_otmutualassessment_id', 'mod_otmutualassessment');
    }
    $grades = [];
    
    if ((int) $userid > 0) {
        $otmutualassessmentgrades = $DB->get_record('otmutualassessment_grades', [
            'otmutualassessmentid' => $id,
            'userid' => $userid
        ]);
        if (empty($otmutualassessmentgrades)) {
            return $grades;
        }
        $grades[$otmutualassessmentgrades->userid] = [
            'userid' => $otmutualassessmentgrades->userid,
            'rawgrade' => $otmutualassessmentgrades->grade
        ];
    } elseif ((int) $userid == 0) {
        $otmutualassessmentgrades = $DB->get_records('otmutualassessment_grades', [
            'otmutualassessmentid' => $id
        ]);
        if (! empty($otmutualassessmentgrades)) {
            foreach ($otmutualassessmentgrades as $otmutualassessmentgrade) {
                $grades[$otmutualassessmentgrade->userid] = [
                    'userid' => $otmutualassessmentgrade->userid,
                    'rawgrade' => $otmutualassessmentgrade->grade
                ];
            }
        }
    }
    
    return $grades;
}

/**
 * Rescale all grades for this activity and push the new grades to the gradebook.
 *
 * @param stdClass $course Course db record
 * @param stdClass $cm Course module db record
 * @param float $oldmin
 * @param float $oldmax
 * @param float $newmin
 * @param float $newmax
 */
function otmutualassessment_rescale_activity_grades($course, $cm, $oldmin, $oldmax, $newmin, $newmax)
{
    global $DB;
    
    if ($oldmax <= $oldmin) {
        // Grades cannot be scaled.
        return false;
    }
    $scale = ($newmax - $newmin) / ($oldmax - $oldmin);
    if (($newmax - $newmin) <= 1) {
        // We would lose too much precision, lets bail.
        return false;
    }
    $params = array(
        'p1' => $oldmin,
        'p2' => $scale,
        'p3' => $newmin,
        'a' => $cm->instance
    );
    
    // Only rescale grades that are greater than or equal to 0. Anything else is a special value.
    $sql = 'UPDATE {otmutualassessment_grades}
               SET grade = (((grade - :p1) * :p2) + :p3)
             WHERE otmutualassessmentid = :a and grade >= 0';
    $dbupdate = $DB->execute($sql, $params);
    if (!$dbupdate) {
        return false;
    }
    
    // Now re-push all grades to the gradebook.
    $dbparams = ['id' => $cm->instance];
    $otmutualassessment = $DB->get_record('otmutualassessment', $dbparams);
    $otmutualassessment->cmidnumber = $cm->idnumber;
    
    otmutualassessment_update_grades($otmutualassessment);
    
    return true;
}

/**
 * Implementation of the function for printing the form elements that control
 * whether the course reset functionality affects the otmutualassessment.
 * @param moodleform $mform form passed by reference
 */
function otmutualassessment_reset_course_form_definition(&$mform) {
    $mform->addElement('header', 'otmutualassessmentheader', get_string('modulenameplural', 'mod_otmutualassessment'));
    $mform->addElement('advcheckbox', 'reset_otmutualassessment_points',
        get_string('deletepoints', 'mod_otmutualassessment'));
    $mform->addElement('advcheckbox', 'reset_otmutualassessment_statuses',
        get_string('deletestatuses', 'mod_otmutualassessment'));
}

/**
 * This function is used by the reset_course_userdata function in moodlelib.
 * This function will remove all otmutualassessment points and statuses in the database
 * and clean up any related data.
 *
 * @param stdClass $data the data submitted from the reset course.
 * @return array
 */
function otmutualassessment_reset_userdata($data) {
    global $CFG, $DB;
    require_once($CFG->dirroot . '/mod/otmutualassessment/locallib.php');
    $strategylist = mod_otmutualassessment_get_strategy_list();
    
    $status = [];
    $params = ['courseid' => $data->courseid];
    $sql = "SELECT ma.* FROM {otmutualassessment} ma WHERE ma.course = :courseid";
    $course = $DB->get_record('course', ['id' => $data->courseid], '*', MUST_EXIST);
    if ($otmutualassessments = $DB->get_records_sql($sql, $params)) {
        foreach ($otmutualassessments as $otmutualassessment) {
            $cm = get_coursemodule_from_instance('otmutualassessment',
                $otmutualassessment->id,
                $data->courseid,
                false,
                MUST_EXIST);
            $context = context_module::instance($cm->id);
            $assessment = new $strategylist[$otmutualassessment->strategy]($context, $cm, $course);
            $status = array_merge($status, $assessment->reset_userdata($data));
        }
    }
    return $status;
}

/**
 * Removes all grades from gradebook
 *
 * @param int $courseid The ID of the course to reset
 * @param string $type Optional type of otmutualassessment to limit the reset to a particular otmutualassessment type
 */
function otmutualassessment_reset_gradebook($courseid, $type='') {
    global $CFG, $DB;
    
    $params = ['moduletype' => 'otmutualassessment', 'courseid' => $courseid];
    $sql = 'SELECT ma.*, cm.idnumber as cmidnumber, ma.course as courseid
            FROM {otmutualassessment} ma, {course_modules} cm, {modules} m
            WHERE m.name=:moduletype AND m.id=cm.module AND cm.instance=ma.id AND ma.course=:courseid';
    
    if ($assessments = $DB->get_records_sql($sql, $params)) {
        foreach ($assessments as $assessment) {
            otmutualassessment_grade_item_update($assessment, 'reset');
        }
    }
}

/**
 * Course reset form defaults.
 * @param  object $course
 * @return array
 */
function otmutualassessment_reset_course_form_defaults($course) {
    return [
        'reset_otmutualassessment_points' => 1,
        'reset_otmutualassessment_statuses' => 1,
    ];
}

/**
 * This function extends the settings navigation block for the site.
 *
 * It is safe to rely on PAGE here as we will only ever be within the module
 * context when this is called
 *
 * @param settings_navigation $settings
 * @param navigation_node $assessmentnode
 * @return void
 */
function otmutualassessment_extend_settings_navigation($settings, $assessmentnode) {
    global $PAGE;
    
    // We want to add these new nodes after the Edit settings node, and before the
    // Locally assigned roles node. Of course, both of those are controlled by capabilities.
    $keys = $assessmentnode->get_children_key_list();
    $beforekey = null;
    $i = array_search('modedit', $keys);
    if ($i === false and array_key_exists(0, $keys)) {
        $beforekey = $keys[0];
    } else if (array_key_exists($i + 1, $keys)) {
        $beforekey = $keys[$i + 1];
    }
    
    $context = context_module::instance($PAGE->cm->id);
    list($course, $cm) = get_course_and_cm_from_cmid($PAGE->cm->id);
    $instance = mod_otmutualassessment_get_instance($cm->instance);
    $strategylist = mod_otmutualassessment_get_strategy_list();
    $otmutualassessment = new $strategylist[$instance->strategy]($context, $cm, $course);
    
    if ($otmutualassessment->can_view_grades()) {
        $params = ['cmid' => $PAGE->cm->id];
        $groupid = optional_param('group', null, PARAM_INT);
        $activegroup = $otmutualassessment->get_active_group();
        if (!is_null($groupid)) {
            $params['group'] = $groupid;
        } elseif (!empty($activegroup)) {
            $params['group'] = $activegroup;
        } elseif (!empty($otmutualassessment->get_course_module()->effectivegroupmode)
            && $firstgroup = $otmutualassessment->get_first_course_group()) {
                $params['group'] = $firstgroup;
        }
        $node = navigation_node::create(get_string('report', 'otmutualassessment'),
            new moodle_url('/mod/otmutualassessment/report.php', $params),
            navigation_node::TYPE_CUSTOM, null, 'mod_otmutualassessment_report');
        $assessmentnode->add_node($node, $beforekey);
    }
    
    if (has_capability('mod/otmutualassessment:refreshgrades', $PAGE->cm->context)) {
        $node = navigation_node::create(get_string('refresh', 'otmutualassessment'),
            new moodle_url('/mod/otmutualassessment/refresh.php', ['cmid' => $PAGE->cm->id]),
            navigation_node::TYPE_SETTING, null, 'mod_otmutualassessment_refreshgrades',
            new pix_icon('t/edit', ''));
        $assessmentnode->add_node($node, $beforekey);
    }
}
