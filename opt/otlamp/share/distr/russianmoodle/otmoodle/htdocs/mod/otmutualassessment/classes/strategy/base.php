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
 * Модуль Взаимная оценка. Основной класс для работы с модулем.
 *
 * @package    mod
 * @subpackage otmutualassessment
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_otmutualassessment\strategy;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/mod/otmutualassessment/lib.php');
require_once($CFG->dirroot . '/mod/otmutualassessment/locallib.php');
require_once($CFG->libdir . '/gradelib.php');
require_once($CFG->libdir . '/grouplib.php');

use context_module;
use stdClass;
use coding_exception;
use context_course;
use mod_otmutualassessment\graderform;
use html_writer;
use core\notification;
use grade_scale;
use moodle_exception;
use mod_otmutualassessment\event\grade_updated;
use mod_otmutualassessment\event\grader_status_updated;
use html_table;
use html_table_row;
use html_table_cell;
use moodle_url;
use grade_item;
use cm_info;
use completion_info;
use mod_otmutualassessment\refreshform;
use mod_otmutualassessment\refreshtaskform;
use moodleform;
use mod_otmutualassessment_mod_form;
use admin_settingpage;
use single_button;
use Exception;
use mod_otmutualassessment\task\full_refresh;
use mod_otmutualassessment\task\refresh_grades;
use mod_otmutualassessment\task\reset_grades;
use mod_otmutualassessment\task\refresh_statuses;
use mod_otmutualassessment\task\reset_statuses;
use mod_otmutualassessment\task\refresh_completion_states;
use mod_otmutualassessment\task\delete_history;
use core\task\manager as taskmanager;
use core\lock\lock;
use gradereport_singleview\local\ui\empty_element;

abstract class base {
    
    /**
     * Статус завершенной оценки участников
     * @var string
     */
    const COMPLETED = 'completed';
    
    /**
     * Статус незавершенной оценки участников
     * @var string
     */
    const NOTCOMPLETED = 'notcompleted';
    
    /**
     * Статус незавершенной оценки участников
     * @var string
     */
    const NOTREQUIRED = 'notrequired';
    
    /**
     * Расчет оценки как суммы баллов
     * @var integer
     */
    const RELATIVE = 1;
    
    /**
     * Расчет оценки в концепции Moodle
     * @var integer
     */
    const ABSOLUTE = 2;
    
    /**
     * Объект инстанса модуля курса
     * @var stdClass|null
     */
    private $instance = null;
    
    /**
     * Объект курса
     * @var stdClass
     */
    private $course = null;
    
    /**
     * Контекст модуля курса
     * @var context_module|null
     */
    private $context = null;
    
    /**
     * Список стратегий оценки
     * @var array
     */
    private $strategies = [];
    
    /**
     * Объект оценщика
     * @var stdClass|null
     */
    private $grader = null;
    
    /**
     * Пользователи, которых можно оценить
     * @var array|null
     */
    private $gradedusers = null;
    
    /**
     * Хранилище инстансов элементов курса
     * @var string
     */
    private $table = 'otmutualassessment';
    
    /**
     * Хранилище оценок участников
     * @var string
     */
    private $gradestable = 'otmutualassessment_grades';
    
    /**
     * Хранилище баллов участников
     * @var string
     */
    private $pointstable = 'otmutualassessment_points';
    
    /**
     * Хранилище статусов оценщика
     * @var string
     */
    private $statusestable = 'otmutualassessment_statuses';
    
    /**
     * Поле вторичного ключа
     * @var string
     */
    private $fk = 'otmutualassessmentid';
    
    /**
     * The grade_item record
     * @var grade_item
     */
    private $gradeitem = null;
    
    /**
     * Уникальный код стратегии
     * @var string
     */
    protected static $code = null;
    
    /**
     * Базовый урл-адрес отчета
     * @var string
     */
    private $reportbaseurl = '/mod/otmutualassessment/report.php';
    
    /**
     * Режим обновления данных в модуле: в реальном времени (live), в фоновом режиме (cron)
     * @var string
     */
    private $efficiencyofrefresh = 'cron';
    
    /**
     * Этапы обновления данных
     * @var array
     */
    private $refreshactions = [
        'refresh_grades', 
        'reset_grades', 
        'refresh_statuses', 
        'reset_statuses', 
        'refresh_completion_states',
        'delete_history'
    ];
    
    /**
     * Рассчитать и получить оценку пользователя
     * @param int $userid идентификатор пользователя
     */
    abstract public function calculate_grade($userid);
    
    /**
     * Подготовить и вернуть список пользователей для оценки
     * @param int $groupid идентификатор группы
     */
    abstract public function get_graded_users($groupid = null);
    
    /**
     * Получить максимально возможное кол-во баллов, которые может получить пользователь
     * Для разных пользователей в зависимости от участия в группах и группового режима может быть разное кол-во максимальных баллов
     * Метод должен быть определен в дочерних классах
     * @param int $userid идентификатор пользователя
     * @return number
     */
    abstract public function get_max_points($userid);
    
    /**
     * Проверить, выполнил ли пользователь обязательства оценщика
     * @param int $userid идентификатор пользователя
     * @param int $groupid идентификатор группы
     * @return boolean
     */
    abstract public function is_user_completed_assessment($userid, $groupid = null);
    
    /**
     * Constructor for the base assign class.
     *
     * Note: For $coursemodule you can supply a stdclass if you like, but it
     * will be more efficient to supply a cm_info object.
     *
     * @param mixed $coursemodulecontext context|null the course module context
     *                                   (or the course context if the coursemodule has not been
     *                                   created yet).
     * @param mixed $coursemodule the current course module if it was already loaded,
     *                            otherwise this class will load one from the context as required.
     * @param mixed $course the current course  if it was already loaded,
     *                      otherwise this class will load one from the context as required.
     */
    public function __construct($coursemodulecontext, $coursemodule, $course) {
        $this->context = $coursemodulecontext;
        $this->course = $course;
        $this->coursemodule = cm_info::create($coursemodule);
        if (is_null($this->context) && $this->coursemodule instanceof cm_info)
        {
            $this->context = $this->coursemodule->context;
        }
        $this->strategies = mod_otmutualassessment_get_strategy_list();
        $this->efficiencyofrefresh = get_config('mod_otmutualassessment', 'efficiencyofrefresh');
        if (empty($this->efficiencyofrefresh)) {
            $this->efficiencyofrefresh = 'cron';
        }
    }
    
    /**
     * Получить код стратегии
     * @return string
     */
    public static function get_code() {
        return static::$code;
    }
    
    /**
     * Получить список режимов расчета оценки
     * @return string[]
     */
    public static function get_gradingmods_list() {
        return [
            self::ABSOLUTE => get_string('absolute_gradingmode', 'mod_otmutualassessment'),
            self::RELATIVE => get_string('relative_gradingmode', 'mod_otmutualassessment'),
        ];
    }
    
    /**
     * Добавление в форму настроек модуля курса общих настроек для всех стратегий
     * @param moodleform $mform указатель на объект moodleform
     * @param mod_otmutualassessment_mod_form $form указатель на объект mod_otmutualassessment_mod_form
     */
    public static function add_common_mod_form_elements(& $mform, & $form) {
        $select = self::get_gradingmods_list();
        $mform->addElement('select', 'gradingmode', get_string('gradingmode', 'mod_otmutualassessment'), $select);
        $mform->setType('gradingmode', PARAM_INT);
        $mform->setDefault('gradingmode', self::ABSOLUTE);
        $mform->addHelpButton('gradingmode', 'gradingmode', 'mod_otmutualassessment');
    }
    
    /**
     * Сохранить оценки, выставленные оценщиком
     * @param stdClass $grader объект оценщика
     * @param array $points массив оценок [gradedid => grade,...]
     * @param int $groupid группа, для которой выставляются оценки
     */
    public function save_grades($grader, $points, $groupid = null) {
        global $DB;
        $result = false;
        if (empty($points)) {
            return $result;
        }
        $usersfrompoints = array_unique(array_keys($points));
        $realusers = array_unique(array_keys($this->gradedusers));
        if (empty(array_diff($usersfrompoints, $realusers)) && empty(array_diff($realusers, $usersfrompoints))) {
            // Получили все баллы по переданным пользователям - сохраняем
            $result = true;
            $transaction = $DB->start_delegated_transaction();
            // Сохраняем выставленные баллы
            $result = $result && $this->save_points($grader->id, $points, $groupid);
            
            // Проверяем, допустимо ли ставить оценку оценщику
            if (has_capability('mod/otmutualassessment:begradedbyothers', $this->get_context(), $grader->id))
            {
                // Ставим оценку оценщику
                $result = $result && $this->set_grade($grader->id);
            }
            
            // Выставляем оценки остальным участникам
            foreach ($this->gradedusers as $gradeduser) {
                $result = $result && $this->set_grade($gradeduser->id);
            }
            // Выставляем статус оценщика
            $result = $result && $this->set_status($grader->id, self::COMPLETED, $groupid);
            if ($result) {
                // Если все прошло удачно, коммитим транзакцию
                $DB->commit_delegated_transaction($transaction);
                // Посчитаем выполнение элемента для оценщика
                $completion = new completion_info($this->get_course());
                if ($completion->is_enabled($this->get_course_module()) && $this->get_instance()->completionsetgrades) {
                    $completion->update_state($this->get_course_module(), COMPLETION_UNKNOWN, $grader->id);
                }
            } else {
                $DB->force_transaction_rollback($transaction);
            }
        }
        return $result;
    }
    
    /**
     * Сохранение выставленных оценщиком баллов
     * @param int $graderid иденификатор оценщика
     * @param array $points массив выставленных баллов вида [gradedid => point,...]
     * @return boolean
     */
    public function save_points($graderid, $points, $groupid = null) {
        global $DB;
        if (is_null($groupid)) {
            $groupid = 0;
        }
        $result = false;
        if (!empty($points)) {
            $result = true;
            $time = time();
            foreach ($points as $gradedid => $point) {
                if ($row = $DB->get_record($this->pointstable, [
                    $this->fk => $this->get_instance()->id,
                    'grader' => $graderid,
                    'graded' => $gradedid,
                    'groupid' => $groupid
                ])) {
                    if ($row->point != $point) {
                        $row->point = $point;
                        $row->timemodified = $time;
                        $result = $result && $DB->update_record($this->pointstable, $row);
                    };
                } else {
                    $row = new stdClass();
                    $row->{$this->fk} = $this->get_instance()->id;
                    $row->grader = $graderid;
                    $row->graded = $gradedid;
                    $row->groupid = $groupid;
                    $row->point = $point;
                    $row->timecreated = $row->timemodified = $time;
                    $result = $result && $DB->insert_record($this->pointstable, $row, false);
                }
            }
        }
        if (!$result) {
            notification::add(get_string('error_failed_to_save_points', 'mod_otmutualassessment'), notification::ERROR);
        }
        return $result;
    }
    
    /**
     * Выставить оценку пользователю
     * @param int $userid идентификатор пользователя
     * @return bool
     */
    public function set_grade($userid) {
        if (!$this->is_user_deleted($userid)
            && is_enrolled(context_course::instance($this->course->id), $userid)
            && has_capability('mod/otmutualassessment:begradedbyothers', $this->get_context(), $userid)) {
            // Не выставляем оценки удаленным, не подписанным, не имеющим права быть оцененным пользователям
            if ($grade = $this->get_user_grade($userid, true)) {
                try {
                    $grade->grade = $this->calculate_grade($userid);
                } catch (moodle_exception $e) {
                    notification::add($e->getMessage() . '<br/>' . format_backtrace($e->getTrace()), notification::ERROR);
                    return false;
                }
                return $this->update_grade($grade);
            }
        }
        return false;
    }
    
    /**
     * Сбросить оценку пользователя
     * @param int $userid идентификатор пользователя
     * @return bool
     */
    public function reset_grade($userid) {
        if (!$this->is_user_deleted($userid) && is_enrolled(context_course::instance($this->course->id), $userid)) {
            // Не сбрасываем оценки удаленным или не подписанным пользователям
            if ($grade = $this->get_user_grade($userid, true)) {
                $grade->grade = -1;
                return $this->update_grade($grade);
            }
        }
        return false;
    }
    
    /**
     * Получить сумму выставленных пользователю баллов другими участниками (с учетом группы, если передана группа)
     * @param int $userid идентификатор пользователя
     * @param int $groupid идентификатор группы, в которой проставлялись баллы
     * @return number
     */
    public function get_user_points($userid, $groupid = null) {
        global $DB;
        $userpoints = 0;
        if ($graders = $this->get_graders($userid, $groupid)) {
            if (empty($groupid)) {
                if (!empty($this->get_course_module()->effectivegroupmode)) {
                    $usergroups = groups_get_user_groups($this->get_course()->id, $userid);
                    if (empty($usergroups[$this->get_course_module()->groupingid])) {
                        return $userpoints;
                    } else {
                        list($sqlingroups, $paramsgroups) = $DB->get_in_or_equal(array_values($usergroups[$this->get_course_module()->groupingid]), SQL_PARAMS_NAMED);
                        $groupsselect = ' AND p.groupid ' . $sqlingroups;
                        $groupsjoin = ' LEFT JOIN {groups_members} gm
                                               ON gm.groupid = p.groupid AND gm.userid = p.grader';
                        $groupsjoinselect = ' AND gm.userid IS NOT NULL';
                    }
                } else {
                    $groupsselect = ' AND p.groupid = :groupid';
                    $paramsgroups['groupid'] = 0;
                    $groupsjoin = $groupsjoinselect = '';
                }
            } else {
                $groupsselect = ' AND p.groupid = :groupid';
                $paramsgroups['groupid'] = $groupid;
                $groupsjoin = ' LEFT JOIN {groups_members} gm
                                       ON gm.groupid = p.groupid AND gm.userid = p.grader';
                $groupsjoinselect = ' AND gm.userid IS NOT NULL';
            }
            list($sqlingraders, $paramsgraders) = $DB->get_in_or_equal(array_keys($graders), SQL_PARAMS_NAMED);
            $params = array_merge($paramsgroups, $paramsgraders);
            $sql = 'SELECT SUM(p.point) AS userpoints
                      FROM {' . $this->pointstable . '} p' . $groupsjoin . '
                     WHERE p.graded = :graded
                       AND p.grader ' . $sqlingraders . '
                       AND p.' . $this->fk . ' = :instanceid' . $groupsselect . $groupsjoinselect;
            $params['graded'] = $userid;
            $params['instanceid'] = $this->get_instance()->id;
            if ($row = $DB->get_record_sql($sql, $params)) {
                $userpoints = (int)$row->userpoints;
            }
        }
        return $userpoints;
    }
    
    /**
     * Получить оценщиков пользователя
     * @param int $userid идентификатор пользователя, для которого ищем оценщиков
     * @param int $groupid идентификатор группы, если нужно получить оценщиков в группе
     * @return array
     */
    public function get_graders($userid, $groupid = null) {
        $potentialgraders = $this->get_graders_by_capability();
        $graders = [];
        if (!empty($this->get_course_module()->effectivegroupmode)) {
            if (empty($groupid)) {
                if ($groups = groups_get_all_groups($this->get_course()->id, $userid, $this->get_course_module()->groupingid)) {
                    foreach ($groups as $group) {
                        foreach ($potentialgraders as $grader) {
                            if ($grader->id == $userid) {
                                // Do not send self.
                                continue;
                            }
                            // При групповом режиме пользователя могут оценивать только те,
                            // кто находится с ним в одной группе
                            if (groups_is_member($group->id, $grader->id)) {
                                $graders[$grader->id] = $grader;
                            }
                        }
                    }
                }
            } else {
                foreach ($potentialgraders as $grader) {
                    if ($grader->id == $userid) {
                        // Do not send self.
                        continue;
                    }
                    // При групповом режиме пользователя могут оценивать только те,
                    // кто находится с ним в одной группе
                    if (groups_is_member($groupid, $grader->id)) {
                        $graders[$grader->id] = $grader;
                    }
                }
            }
        } else {
            foreach ($potentialgraders as $grader) {
                if ($grader->id == $userid) {
                    // Do not send self.
                    continue;
                }
                // Must be enrolled.
                $context = context_course::instance($this->get_course()->id);
                if (is_enrolled($context, $grader->id)) {
                    $graders[$grader->id] = $grader;
                }
            }
        }
        return $graders;
    }
    
    /**
     * Получить сумму выставленных пользователем баллов другим участникам
     * @param int $userid идентификатор пользователя (оценщика)
     * @param int $groupid идентификатор группы, в которой проставлялись баллы
     * @return number
     */
    public function get_amount_points($userid, $groupid = null) {
        global $DB;
        $userpoints = 0;
        if (is_null($groupid)) {
            $groupid = 0;
        }
        $sql = 'SELECT SUM(point) AS userpoints
                  FROM {' . $this->pointstable . '}
                 WHERE grader = :grader
                   AND graded != :graded
                   AND ' . $this->fk . ' = :instanceid
                   AND p.groupid = :groupid';
        $params = [
            'graded' => $userid,
            'grader' => $userid,
            'instanceid' => $this->get_instance()->id,
            'groupid' => $groupid
        ];
        if ($raw = $DB->get_record_sql($sql, $params)) {
            $userpoints = (int)$raw->userpoints;
        }
        return $userpoints;
    }
    
    /**
     * Получить форму для выставления оценок
     * @param int $groupid идентификатор группы
     * @return graderform объект формы оценщика
     */
    public function get_grades_form($groupid = null) {
        global $PAGE;
        if (!empty($this->get_grader()) && !is_null($this->get_gradedusers())) {
            $this->graderform_js_call();
            $customdata = new stdClass();
            $customdata->grader = $this->get_grader();
            $customdata->groupid = $groupid;
            $customdata->gradedusers = $this->gradedusers;
            $customdata->otmutualassessment =& $this;
            return new graderform($PAGE->url->out(false), $customdata, 'post', '', ['class' => 'otmutualassessment-grades-form']);
        } else {
            return false;
        }
    }
    
    /**
     * Получить таблицу с результатами оценивания
     * @param int $groupid идентификатор группы, если требуется получить только часть таблицы для конкретной группы участников
     */
    public function get_grades_table($groupid = null) {
        $html = '';
        if ($table = $this->prepare_grades_table($groupid)) {
            $html .= html_writer::table($table);
        } else {
            $html .= get_string('empty_report', 'mod_otmutualassessment');
        }
        return $html;
    }
    
    /**
     * Does this user have view grade or grade permission for this assignment?
     *
     * @param mixed $groupid int|null when is set to a value, use this group instead calculating it
     * @return bool
     */
    public function can_view_grades($groupid = null) {
        // Permissions check.
        if (!has_capability('mod/otmutualassessment:viewgrades', $this->context)) {
            return false;
        }
        // Checks for the edge case when user belongs to no groups and groupmode is sep.
        if ($this->get_course_module()->effectivegroupmode == SEPARATEGROUPS) {
            if ($groupid === null) {
                $groupid = groups_get_activity_allowed_groups($this->get_course_module());
            }
            $groupflag = has_capability('moodle/site:accessallgroups', $this->context);
            $groupflag = $groupflag || !empty($groupid);
            return (bool)$groupflag;
        }
        return true;
    }
    
    /**
     * Получить список оценщиков из базы (пользователи, которые реально выставили оценки)
     * @param int $groupid идентификатор группы
     * @return array
     */
    public function get_graders_from_db($groupid = null) {
        global $DB;
        if (is_null($groupid)) {
            $groupid = 0;
            $groupjoin = '';
            $where = ' WHERE p.groupid = :groupid AND p.' . $this->fk . ' = :fk';
        } else {
            $groupjoin = ' LEFT JOIN {groups_members} gm
                                  ON gm.userid = p.grader AND gm.groupid = p.groupid
                           LEFT JOIN {groups} g
                                  ON gm.groupid = g.id';
            $where = ' WHERE p.groupid = :groupid AND p.' . $this->fk . ' = :fk AND gm.groupid IS NOT NULL';
        }
        $params = [
            'fk' => $this->get_instance()->id,
            'groupid' => $groupid
        ];
        $sql = 'SELECT DISTINCT p.grader AS id
                           FROM {' . $this->pointstable . '} p' . $groupjoin . $where;
        return $DB->get_records_sql($sql, $params);
    }
    
    /**
     * Получить список оцененных пользователей из базы (пользователей, которые реально получили оценки от других участников)
     * @param int $groupid идентификатор группы
     * @return array
     */
    public function get_gradeds_from_db($groupid = null) {
        global $DB;
        if (is_null($groupid)) {
            $groupid = 0;
            $groupjoin = '';
            $where = ' WHERE p.groupid = :groupid AND p.' . $this->fk . ' = :fk';
        } else {
            $groupjoin = ' LEFT JOIN {groups_members} gm
                                  ON gm.userid = p.graded AND gm.groupid = p.groupid
                           LEFT JOIN {groups} g
                                  ON gm.groupid = g.id';
            $where = ' WHERE p.groupid = :groupid AND p.' . $this->fk . ' = :fk AND gm.groupid IS NOT NULL';
        }
        $params = [
            'fk' => $this->get_instance()->id,
            'groupid' => $groupid
        ];
        $sql = 'SELECT DISTINCT p.graded AS id
                           FROM {' . $this->pointstable . '} p' . $groupjoin . $where;
        return $DB->get_records_sql($sql, $params);
    }
    
    /**
     * Получить список пользователей, которые потенциально могут быть оценены (по праву)
     * @param int $groupid идентификатор группы
     */
    public function get_gradeds_by_capability($groupid = null) {
        global $DB;
        return get_enrolled_users($this->context, 'mod/otmutualassessment:begradedbyothers',
            $groupid, 'u.id, ' . $DB->sql_fullname('u.firstname', 'u.lastname') . ' AS fullname', null, null, null, true);
    }
    
    /**
     * Получить список пользователей, которые потенциально могут выставить оценки (по праву)
     * @param int $groupid идентификатор группы
     */
    public function get_graders_by_capability($groupid = null) {
        global $DB;
        return get_enrolled_users($this->context, 'mod/otmutualassessment:gradeothers',
            $groupid, 'u.id, ' . $DB->sql_fullname('u.firstname', 'u.lastname') . ' AS fullname', null, null, null, true);
    }
    
    /**
     * Получить список оцененных пользователей для отчета (уже оцененные + те, кого потенциально можно оценить)
     * @param int $groupid идентификатор группы
     * @return array массив идентификаторов пользователей
     */
    public function get_gradeds_list($groupid = null) {
        global $DB;
        $users = [];
        $real = $this->get_gradeds_from_db($groupid);
        $potential = $this->get_gradeds_by_capability($groupid);
        $keys = array_unique(array_merge(array_keys($real), array_keys($potential)));
        if (!empty($keys)) {
            list($sqlin, $params) = $DB->get_in_or_equal($keys);
            $where = ' WHERE id ' . $sqlin;
            $sql = 'SELECT id, ' . $DB->sql_fullname('firstname', 'lastname') . ' AS fullname
                  FROM {user}' . $where . '
                       ORDER BY fullname ASC, id ASC';
            $users = $DB->get_records_sql($sql, $params);
        }
        return $users;
        
    }
    
    /**
     * Получить список оценщиков для отчета (те, кто проставили оценки + те, кто потенциально может проставить оценки)
     * @param int $groupid идентификатор группы
     * @return array массив идентификаторов пользователей
     */
    public function get_graders_list($groupid = null) {
        global $DB;
        $users = [];
        $real = $this->get_graders_from_db($groupid);
        $potential = $this->get_graders_by_capability($groupid);
        $keys = array_unique(array_merge(array_keys($real), array_keys($potential)));
        if (!empty($keys)) {
            list($sqlin, $params) = $DB->get_in_or_equal($keys);
            $where = ' WHERE id ' . $sqlin;
            $sql = 'SELECT id, ' . $DB->sql_fullname('firstname', 'lastname') . ' AS fullname
                  FROM {user}' . $where . '
                       ORDER BY fullname ASC, id ASC';
            $users = $DB->get_records_sql($sql, $params);
        }
        return $users;
    }
    
    /**
     * Исполнил ли пользователь все возложенные на него в элементе курса обязательства оценщика
     *
     * @param int $userid - идентификатор пользователя (оценщика)
     * @return string|boolean
     */
    public function get_grader_cm_status($userid)
    {
        // все оценщики для элемента курса
        // в разрезе групп проверять нет смысла, так как переопределить права в группе нельзя,
        // а принадлежность группе проверяется через groups_get_user_groups ниже при необходимости
        $potentialgraders = $this->get_graders_by_capability();
        if (!array_key_exists($userid, $potentialgraders))
        {// не является оценщиком, статус - оценивание от пользователя не требуется
            return self::NOTREQUIRED;
        }
        
        if (empty($this->get_course_module()->effectivegroupmode)) {
            // Групповой режим не включен
            $status = $this->get_status($userid);
            if ($status === false) {
                $status = self::NOTCOMPLETED;
            }
        } else {
            $usergroups = groups_get_user_groups($this->get_course()->id, $userid);
            $result = false;
            if (!empty($usergroups[$this->get_course_module()->groupingid])) {
                $result = true;
                foreach ($usergroups[$this->get_course_module()->groupingid] as $gid) {
                    $result = $result && ($this->get_status($userid, $gid) === self::COMPLETED);
                }
            }
            if ($result) {
                $status = self::COMPLETED;
            } else {
                $status = self::NOTCOMPLETED;
            }
        }
        return $status;
    }
    
    /**
     * Подготовить таблицу для отображения по переданной матрице оценок
     * @param array $matrix
     * @return html_table|false
     */
    public function prepare_grades_table($groupid = null) {
        global $PAGE;
        $showcontrols = false;
        if (!empty($this->get_course_module()->effectivegroupmode)
            && empty(groups_get_all_groups($this->get_course()->id, 0, $this->get_course_module()->groupingid))) {
            // Если включен групповой режим и нет групп - нечего показывать
            return false;
        }
        if ($PAGE->user_is_editing()) {
            $showcontrols = true;
        }
        $gradeds = $this->get_gradeds_by_capability($groupid);
        if (!empty($gradeds)) {
            $graders = $this->get_graders_by_capability($groupid);
            $PAGE->requires->js_call_amd('mod_otmutualassessment/verticaldisplay', 'init');
            $table = new html_table();
            $table->attributes = ['class' => 'generaltable otmutualassessment-grades-table'];
            $cell = new html_table_cell($this->get_first_cell());
            $cell->attributes = ['class' => 'caption first'];
            $cell->style = 'width: 150px';
            $table->head[] = $cell;
            $fullhead = false;
            foreach ($gradeds as $graded) {
                $row = new html_table_row();
                $cell = new html_table_cell($graded->fullname);
                $cell->attributes = ['class' => 'firstcol'];
                $row->cells[] = $cell;
                foreach ($graders as $grader) {
                    if ($graded->id == $grader->id) {
                        $point = '-';
                    } else {
                        $point = $this->get_set_point($grader->id, $graded->id, $groupid);
                        if ($point === false) {
                            $point = '-';
                        }
                    }
                    $cell = new html_table_cell($point);
                    $row->cells[] = $cell;
                    if (!$fullhead) {
                        $graderstatus = $this->get_grader_cm_status($grader->id);
                        $cell = new html_table_cell($grader->fullname . 
                            ($showcontrols && $this->get_status($grader->id, $groupid) == self::COMPLETED ? 
                                $this->get_report_controls($grader, $groupid) : ''));
                        $cell->attributes = [
                            'class' => 'caption grader status_'.$graderstatus,
                            'title' => get_string('status_' . $graderstatus, 'mod_otmutualassessment')
                        ];
                        $table->head[] = $cell;
                    }
                }
                $fullhead = true;
                $cell = new html_table_cell($this->get_user_points($graded->id, $groupid));
                $row->cells[] = $cell;
                $gradevalue = $this->get_grade($graded->id);
                try {
                    $gradeitem = $this->get_grade_item();
                    $grade = grade_format_gradevalue($gradevalue, $gradeitem);
                } catch (coding_exception $e) {
                    $grade = get_string('error_cannot_load_the_grade_item', 'mod_otmutualassessment');
                }
                $cell = new html_table_cell($grade);
                $row->cells[] = $cell;
                $status = $this->get_grader_cm_status($graded->id);
                $cell = new html_table_cell(get_string('status_' . $status, 'mod_otmutualassessment'));
                $row->cells[] = $cell;
                $table->data[] = $row;
            }
            $cell = new html_table_cell(get_string('points_summ', 'mod_otmutualassessment'));
            $cell->attributes = ['class' => 'caption summ'];
            $table->head[] = $cell;
            $cell = new html_table_cell(get_string('grade', 'mod_otmutualassessment'));
            $cell->attributes = ['class' => 'caption grade'];
            $table->head[] = $cell;
            $cell = new html_table_cell(get_string('status', 'mod_otmutualassessment'));
            $cell->attributes = ['class' => 'caption status'];
            $table->head[] = $cell;
            
            return $table;
        }
        return false;
    }
    
    /**
     * Получить выставленный оценщиком балл
     * @param int $grader идентификатор оценщика
     * @param int $graded идентификатор оцененного пользователя
     * @param int $groupid идентификатор группы
     * @return mixed|boolean
     */
    public function get_set_point($grader, $graded, $groupid = null) {
        global $DB;
        if (is_null($groupid)) {
            $groupid = 0;
        }
        return $DB->get_field($this->pointstable, 'point', [
            $this->fk => $this->get_instance()->id,
            'grader' => $grader,
            'graded' => $graded,
            'groupid' => $groupid
        ]);
    }
    
    /**
     * Получить выставленную пользователю оценку за модуль курса
     * @param int $userid идентификатор пользователя
     * @return mixed|boolean
     */
    public function get_grade($userid) {
        global $DB;
        return $DB->get_field($this->gradestable, 'grade', [
            $this->fk => $this->get_instance()->id,
            'userid' => $userid
        ]);
    }
    
    /**
     * Получить список стратегий оценивания
     * @param boolean $withlocalizedstrings с локализованными переводами или только ключи
     * @return array
     */
    public function get_strategy_list($withlocalizedstrings = false) {
        if ($withlocalizedstrings) {
            return $this->strategies;
        } else {
            return array_keys($this->strategies);
        }
    }
    
    /**
     * Delete all grades from the gradebook for this instance.
     *
     * @return bool
     */
    protected function delete_grades() {
        $result = grade_update('mod/otmutualassessment',
            $this->get_course()->id,
            'mod',
            'otmutualassessment',
            $this->instance->id,
            0,
            null,
            ['deleted' => 1]);
        return $result == GRADE_UPDATE_OK;
    }
    
    /**
     * Get the current course.
     * @return mixed stdClass|null The course
     */
    public function get_course() {
        global $DB;
        
        if ($this->course) {
            return $this->course;
        }
        
        if (!$this->context) {
            return null;
        }
        $params = ['id' => $this->get_course_context()->instanceid];
        $this->course = $DB->get_record('course', $params, '*', MUST_EXIST);
        
        return $this->course;
    }
    
    /**
     * Get the context of the current course.
     * @return mixed context|null The course context
     */
    protected function get_course_context() {
        if (!$this->context && !$this->course) {
            throw new coding_exception('Improper use of the assignment class. ' .
                'Cannot load the course context.');
        }
        if ($this->context) {
            return $this->context->get_course_context();
        } else {
            return context_course::instance($this->course->id);
        }
    }
    
    /**
     * Выполнить процесс удаления инстанса модуля курса
     * @return boolean
     */
    public function delete_instance() {
        global $DB;
        
        $DB->delete_records($this->pointstable, [$this->fk => $this->get_instance()->id]);
        $DB->delete_records($this->gradestable, [$this->fk => $this->get_instance()->id]);
        $DB->delete_records($this->statusestable, [$this->fk => $this->get_instance()->id]);
        
        // Delete items from the gradebook.
        if (! $this->delete_grades()) {
            return false;
        }
        
        // Delete the instance.
        $DB->delete_records('otmutualassessment', ['id' => $this->get_instance()->id]);
        
        return true;
    }
    
    /**
     * Получить объект инстанса модуля курса
     * @return stdClass|null
     */
    public function get_instance() {
        global $DB;
        if ($this->instance) {
            return $this->instance;
        }
        if ($this->get_course_module()) {
            $params = ['id' => $this->get_course_module()->instance];
            $this->instance = $DB->get_record('otmutualassessment', $params, '*', MUST_EXIST);
        }
        if (!$this->instance) {
            throw new coding_exception('Improper use of the otmutualassessment class. ' .
                'Cannot load the otmutualassessment record.');
        }
        return $this->instance;
    }
    
    /**
     * Получить html-код инструкции для оценщика
     * @return string
     */
    public function get_instruction_for_grader($groupid = null) {
        return '';
    }
    
    /**
     * Получить кол-во баллов для распределения
     * @return number
     */
    public function get_points_for_assessment($groupid = null) {
        if (!empty($groupid)) {
            if ($members = groups_get_members($groupid)) {
                return count($members) + 1;
            } else {
                return 1;
            }
        } else {
            return count($this->gradedusers) + 2;
        }
    }
    
    /**
     * Установить список оцениваемых пользователей
     * @param int $groupid идентификатор группы
     * Если не передали конкретную группу, выставляем всех возможных пользователей для оценки
     */
    public function set_graded_users($groupid = null) {
        $this->gradedusers = [];
        if (!empty($this->get_grader())) {
            $this->gradedusers = $this->get_graded_users($groupid);
        }
    }
    
    /**
     * Установить оценщика
     * @param int $userid
     */
    public function set_grader($userid) {
        global $DB;
        $this->grader = $DB->get_record('user', ['id' => $userid]);
    }
    
    /**
     * Получить установленный список пользователей для оценки
     * @return array|null
     */
    public function get_gradedusers() {
        return $this->gradedusers;
    }
    
    /**
     * Выставить статус оценщику
     * @param int $userid идентификатор пользователя
     * @param string $status статус, который нужно выставить
     * @return boolean|number
     */
    public function set_status($userid, $status, $groupid = null) {
        global $DB;
        if (is_null($groupid)) {
            $groupid = 0;
        }
        $result = false;
        if (!$this->is_user_deleted($userid) && is_enrolled(context_course::instance($this->course->id), $userid)) {
            // Не выставляем статус удаленным или не подписанным пользователям
            if ($row = $DB->get_record($this->statusestable,
                [
                    $this->fk => $this->get_instance()->id,
                    'userid' => $userid,
                    'groupid' => $groupid
                ])) {
                $oldstatus = $row->status;
                $row->status = $status;
                $row->timemodified = time();
                $result = $DB->update_record($this->statusestable, $row);
            } else {
                $oldstatus = null;
                $row = new stdClass();
                $row->{$this->fk} = $this->get_instance()->id;
                $row->userid = $userid;
                $row->groupid = $groupid;
                $row->status = $status;
                $row->timecreated = $row->timemodified = time();
                $id = $DB->insert_record($this->statusestable, $row);
                $row->id = $id;
                $result = (bool)$id;
            }
            if (!$result) {
                $a = new stdClass();
                $a->userid = $userid;
                $a->status = $status;
                notification::add(get_string('error_failed_to_set_status', 'otmutualassessment', $a), notification::ERROR);
            } else {
                $event = grader_status_updated::create_from_otmutualassessment($row, $oldstatus, $this->context);
                $event->trigger();
            }
        }
        
        return $result;
    }
    
    /**
     * Update a grade in the grade table for the otmutualassessment and in the gradebook.
     * @param stdClass $grade a grade record keyed on id
     * @return bool true for success
     */
    public function update_grade($grade) {
        global $DB;
        
        $grade->timemodified = time();
        
        if ($grade->grade && $grade->grade != -1) {
            if ($this->get_instance()->grade > 0) {
                if (!is_numeric($grade->grade)) {
                    return false;
                } else if ($grade->grade > $this->get_instance()->grade) {
                    return false;
                } else if ($grade->grade < 0) {
                    return false;
                }
            } else {
                // This is a scale.
                if ($scale = $DB->get_record('scale', ['id' => -($this->get_instance()->grade)])) {
                    $scaleoptions = make_menu_from_list($scale->scale);
                    if (!array_key_exists((int) $grade->grade, $scaleoptions)) {
                        return false;
                    }
                }
            }
        }
        
        $DB->update_record($this->gradestable, $grade);
        
        $event = grade_updated::create_from_otmutualassessment($grade, $this->context);
        $event->trigger();
        
        $this->gradebook_item_update($grade);
        
        return true;
    }
    
    /**
     * Update grades in the gradebook.
     * @param mixed $grade stdClass
     * @return bool
     */
    protected function gradebook_item_update($grade) {
        $gradebookgrade = $this->convert_grade_for_gradebook($grade);
        // Grading is disabled, return.
        if ($this->grading_disabled($gradebookgrade['userid'])) {
            return false;
        }
        $otmutualassessment = clone $this->get_instance();
        return otmutualassessment_grade_item_update($otmutualassessment, $gradebookgrade) == GRADE_UPDATE_OK;
    }
    
    /**
     * Determine if this users grade can be edited.
     * @param int $userid - The student userid
     * @return bool $gradingdisabled
     */
    public function grading_disabled($userid) {
        $gradinginfo = grade_get_grades($this->get_course()->id,
            'mod',
            'otmutualassessment',
            $this->get_instance()->id,
            [$userid]);
        if (!$gradinginfo) {
            return false;
        }
        
        if (!isset($gradinginfo->items[0]->grades[$userid])) {
            return false;
        }
        $gradingdisabled = $gradinginfo->items[0]->grades[$userid]->locked ||
                           $gradinginfo->items[0]->grades[$userid]->overridden;
        return $gradingdisabled;
    }
    
    /**
     * Convert the final raw grade(s) in the grading table for the gradebook.
     * @param stdClass $grade
     * @return array
     */
    protected function convert_grade_for_gradebook(stdClass $grade) {
        $gradebookgrade = [];
        if ($grade->grade >= 0) {
            $gradebookgrade['rawgrade'] = $grade->grade;
        }
        // Allow "no grade" to be chosen.
        if ($grade->grade == -1) {
            $gradebookgrade['rawgrade'] = null;
        }
        $gradebookgrade['userid'] = $grade->userid;
        $gradebookgrade['usermodified'] = $grade->userid;
        $gradebookgrade['datesubmitted'] = null;
        $gradebookgrade['dategraded'] = $grade->timemodified;
        if (isset($grade->feedbackformat)) {
            $gradebookgrade['feedbackformat'] = $grade->feedbackformat;
        }
        if (isset($grade->feedbacktext)) {
            $gradebookgrade['feedback'] = $grade->feedbacktext;
        }
        
        return $gradebookgrade;
    }
    
    /**
     * This will retrieve a grade object from the db, optionally creating it if required.
     * @param int $userid The user we are grading
     * @param bool $create If true the grade will be created if it does not exist
     * @return stdClass The grade record
     */
    public function get_user_grade($userid, $create) {
        global $DB, $USER;
        
        // If the userid is not null then use userid.
        if (!$userid) {
            $userid = $USER->id;
        }
        
        $params = [$this->fk => $this->get_instance()->id, 'userid' => $userid];
        
        $grade = $DB->get_record($this->gradestable, $params);
        
        if ($grade) {
            return $grade;
        }
        if ($create) {
            $grade = new stdClass();
            $grade->{$this->fk} = $this->get_instance()->id;
            $grade->userid      = $userid;
            $grade->timecreated = $grade->timemodified  = time();
            $grade->grade       = -1;
            
            $gid = $DB->insert_record($this->gradestable, $grade);
            $grade->id = $gid;
            return $grade;
        }
        return false;
    }
    
    /**
     * Получить статус оценщика из БД
     * @param int $userid идентификатор пользователя
     * @return string|boolean
     */
    public function get_status($userid, $groupid = null) {
        global $DB;
        if (is_null($groupid)) {
            $groupid = 0;
        }
        return $DB->get_field($this->statusestable, 'status',
            [$this->fk => $this->get_instance()->id, 'userid' => $userid, 'groupid' => $groupid]);
    }
    
    /**
     * Получить баллы, проставленные оценщиком другим участникам
     * @return mixed|stdClass|false
     */
    public function get_points($groupid = null) {
        global $DB;
        if (is_null($groupid)) {
            $groupid = 0;
        }
        return $DB->get_records($this->pointstable,
            [$this->fk => $this->get_instance()->id, 'grader' => $this->get_grader()->id, 'groupid' => $groupid]);
    }
    
    /**
     * Удалить оценки пользователя
     * @param int $userid идентификатор пользователя
     * @return boolean
     */
    public function delete_user_grade($userid) {
        global $DB;
        $result = true;
        $transaction = $DB->start_delegated_transaction();
        $result = $result && $DB->delete_records($this->pointstable, [$this->fk => $this->get_instance()->id, 'grader' => $userid]);
        $result = $result && $DB->delete_records($this->pointstable, [$this->fk => $this->get_instance()->id, 'graded' => $userid]);
        $result = $result && $DB->delete_records($this->gradestable, [$this->fk => $this->get_instance()->id, 'userid' => $userid]);
        $result = $result && $DB->delete_records($this->statusestable, [$this->fk => $this->get_instance()->id, 'userid' => $userid]);
        if ($result) {
            // Если все прошло удачно, коммитим транзакцию
            $DB->commit_delegated_transaction($transaction);
        } else {
            $DB->force_transaction_rollback($transaction);
        }
        return $result;
    }
    
    /**
     * Проверить удален ли пользователь
     * @param int $userid идентификатор пользователя
     * @return boolean
     */
    public function is_user_deleted($userid) {
        global $DB;
        if ($DB->get_record('user', ['id' => $userid, 'deleted' => 1]) === false) {
            return false;
        }
        return true;
    }
    
    /**
     * Получить html-код ссылки на отчет по выставленным участниками баллам
     * @return string
     */
    public function get_report_link() {
        $link = html_writer::link($this->get_report_url(),
            get_string('report_link_text', 'mod_otmutualassessment'), ['class' => 'btn btn-primary']);
        return html_writer::div($link, 'report-link');
    }
    
    /**
     * Получить ссылку на отчет
     * @return moodle_url
     */
    public function get_report_url($userid = null) {
        if (is_null($userid)) {
            $userid = 0;
        }
        $params = ['cmid' => $this->get_course_module()->id];
        if (!empty($this->get_course_module()->effectivegroupmode)) {
            $usergroups = groups_get_user_groups($this->get_course()->id, $userid);
            if (!empty($usergroups[$this->get_course_module()->groupingid])) {
                $group = array_shift($usergroups[$this->get_course_module()->groupingid]);
                $params['group'] = $group;
            }
        }
        return new moodle_url($this->get_report_base_url(), $params);
    }
    
    /**
     * Получить первую группу пользователя из списка групп, в которых он состоит
     * @param int $userid
     * @return int|boolean
     */
    public function get_first_user_group($userid) {
        $usergroups = groups_get_user_groups($this->get_course()->id, $userid);
        if (!empty($usergroups[$this->get_course_module()->groupingid])) {
            $group = array_shift($usergroups[$this->get_course_module()->groupingid]);
            return $group;
        }
        return false;
    }
    
    /**
     * Получить первую группу в курсе
     * @return int|boolean
     */
    public function get_first_course_group() {
        global $USER;
        if (has_capability('moodle/site:accessallgroups', $this->get_context())) {
            if ($groups = groups_get_all_groups($this->get_course()->id, 0, $this->get_course_module()->groupingid, 'g.id')) {
                $group = array_shift($groups);
                return $group->id;
            }
        } else {
            return $this->get_first_user_group($USER->id);
        }
        return false;
    }
    
    /**
     * Получить html-код информации предназначенной для отображения по окончанию оценивания
     * @return string
     */
    public function get_info_after_grading() {
        $html = get_string('grades_already_set', 'mod_otmutualassessment') . '<br/>';
        $html .= $this->get_plural_string($this->get_user_points($this->get_grader()->id), 'your_total_points', 'mod_otmutualassessment', $this->get_user_points($this->get_grader()->id));
        return html_writer::div($html, 'info-after-grading');
    }
    
    /**
     * Get the primary grade item for this otmutualassessment instance.
     *
     * @return grade_item The grade_item record
     */
    public function get_grade_item() {
        if ($this->gradeitem) {
            return $this->gradeitem;
        }
        $instance = $this->get_instance();
        $params = ['itemtype' => 'mod',
            'itemmodule' => 'otmutualassessment',
            'iteminstance' => $instance->id,
            'courseid' => $instance->course,
            'itemnumber' => 0];
        $this->gradeitem = grade_item::fetch($params);
        if (!$this->gradeitem) {
            throw new coding_exception('Improper use of the assignment class. ' .
                'Cannot load the grade item.');
        }
        return $this->gradeitem;
    }
    
    /**
     * Get the current course module.
     *
     * @return cm_info|null The course module or null if not known
     */
    public function get_course_module() {
        if ($this->coursemodule) {
            return $this->coursemodule;
        }
        if (!$this->context) {
            return null;
        }
        
        if ($this->context->contextlevel == CONTEXT_MODULE) {
            $modinfo = get_fast_modinfo($this->get_course());
            $this->coursemodule = $modinfo->get_cm($this->context->instanceid);
            return $this->coursemodule;
        }
        return null;
    }
    
    /**
     * Узнать, выставлял ли пользователь баллы
     * @param int $userid идентификатор пользователя
     * @param int $groupid идентификатор группы (0 - без групп, null - вообще кому-либо)
     * @return boolean
     */
    public function is_user_set_points($userid, $groupid = null) {
        global $DB;
        $params = [];
        if (!is_null($groupid)) {
            $params['groupid'] = $groupid;
        }
        $params[$this->fk] = $this->get_instance()->id;
        $params['grader'] = $userid;
        if ($DB->get_records($this->pointstable, $params)) {
            return true;
        }
        return false;
    }
    
    /**
     * Получить сумму выставленных пользователем баллов другим участникам
     * @param int $userid идентификатор пользователя (оценщика)
     * @param int $groupid идентификатор группы
     * @return number
     */
    public function get_amount_points_by_graded_users($userid, $groupid = null) {
        global $DB;
        $userpoints = 0;
        if (empty($this->get_gradedusers())) {
            return $userpoints;
        }
        if (is_null($groupid)) {
            $groupid = 0;
        }
        list($sqlin, $params) = $DB->get_in_or_equal(array_keys($this->get_gradedusers()), SQL_PARAMS_NAMED);
        $sql = 'SELECT SUM(point) AS userpoints
                  FROM {' . $this->pointstable . '}
                 WHERE grader = :userid
                   AND ' . $this->fk . ' = :instanceid
                   AND graded ' . $sqlin . '
                   AND groupid = :groupid';
        $params['userid'] = $userid;
        $params['instanceid'] = $this->get_instance()->id;
        $params['groupid'] = $groupid;
        if ($row = $DB->get_record_sql($sql, $params)) {
            $userpoints = (int)$row->userpoints;
        }
        return $userpoints;
    }

    /**
     * Проверить, оценил ли пользователь участника в рамках группы
     * @param int $userid идентификатор пользователя
     * @param int $participantid идентификатор участника
     * @param int $groupid идентификатор группы
     * @return boolean
     */
    public function is_user_set_points_for_participant($userid, $participantid, $groupid = null) {
        global $DB;
        if (is_null($groupid)) {
            $groupid = 0;
        }
        if ($DB->get_record($this->pointstable, [$this->fk => $this->get_instance()->id,
            'grader' => $userid, 'graded' => $participantid, 'groupid' => $groupid])) {
            return true;
        }
        return false;
    }
    
    /**
     * Получить контекст модуля курса
     * @return context_module|NULL
     */
    public function get_context() {
        return $this->context;
    }
    
    /**
     * Update the gradebook information for this assignment.
     *
     * @param bool $reset If true, will reset all grades in the gradbook for this assignment
     * @param int $coursemoduleid This is required because it might not exist in the database yet
     * @return bool
     */
    public function update_gradebook($reset, $coursemoduleid) {
        $otmutualassessment = clone $this->get_instance();
        $otmutualassessment->cmidnumber = $coursemoduleid;
        
        $param = null;
        if ($reset) {
            $param = 'reset';
        }
        
        return otmutualassessment_grade_item_update($otmutualassessment, $param);
    }
    
    /**
     * Получить html-код меню выбора группы
     * @param string|moodle_url $url адрес для перенаправления после выбора группы
     * @return string html-код меню выбора группы, если группы в курсе есть
     */
    public function get_group_menu($url, $hideallparticipants = false) {
        global $USER;
        if (groups_get_all_groups($this->get_course()->id, 0, $this->get_course_module()->groupingid)) {
            if ($this->get_course_module()->effectivegroupmode == SEPARATEGROUPS) {
                $usergroups = groups_get_user_groups($this->get_course()->id, $USER->id);
                $aag = has_capability('moodle/site:accessallgroups', $this->get_context());
                if (empty($usergroups[$this->get_course_module()->groupingid]) && !$aag) {
                    // Если изолированные группы и пользователь не в группе
                    return html_writer::div('', 'groupselector-wrapper');
                }
            }
            return html_writer::div(groups_print_activity_menu($this->get_course_module(), $url, true, $hideallparticipants), 'groupselector-wrapper');
        } else {
            return html_writer::div('', 'groupselector-wrapper');
        }
    }
    
    /**
     * Получить адрес страницы просмотра модуля курса
     * @return moodle_url
     */
    public function get_view_url() {
        return new moodle_url('/mod/otmutualassessment/view.php', ['id' => $this->get_course_module()->id]);
    }
    
    /**
     * Получить массив объектов групп пользователя, к которым у него есть доступ
     * @param int $userid идентификатор пользователя
     * @return array|false если групповой режим не включен, то false
     */
    public function get_allowed_user_groups($userid) {
        $groupmode = groups_get_activity_groupmode($this->get_course_module());
        if (!$groupmode) {
            return false;
        }
        $aag = has_capability('moodle/site:accessallgroups', $this->get_context());
        if ($groupmode == VISIBLEGROUPS or $aag) {
            $allowedgroups = groups_get_all_groups($this->get_course()->id, 0, $this->get_course_module()->groupingid); // any group in grouping
        } else {
            $allowedgroups = groups_get_all_groups($this->get_course()->id, $userid, $this->get_course_module()->groupingid); // only assigned groups
        }
        return $allowedgroups;
    }
    
    /**
     * Получить группы, в которых состоит пользователь
     * @param int $userid идентификатор пользователя
     * @return array
     */
    public function get_user_groups($userid) {
        $groups = groups_get_user_groups($this->get_course()->id, $userid);
        return $groups[$this->get_course_module()->groupingid];
    }
    
    /**
     * Получить активную группу в модуле
     * @return mixed|boolean
     */
    public function get_active_group() {
        global $USER;
        $groupmode = groups_get_activity_groupmode($this->get_course_module());
        $aag = has_capability('moodle/site:accessallgroups', $this->get_context());
        if ($groupmode == VISIBLEGROUPS or $aag) {
            $allowedgroups = groups_get_all_groups($this->get_course()->id, 0, $this->get_course_module()->groupingid); // any group in grouping
        } else {
            $allowedgroups = groups_get_all_groups($this->get_course()->id, $USER->id, $this->get_course_module()->groupingid); // only assigned groups
        }
        return groups_get_activity_group($this->get_course_module(), true, $allowedgroups);
    }
    
    /**
     * Получить массив оценок пользователей
     * @return array
     */
    public function get_grades() {
        global $DB;
        return $DB->get_records($this->gradestable, [$this->fk => $this->get_instance()->id]);
    }
    
    /**
     * Получить объект оценщика
     * @return stdClass|NULL
     */
    public function get_grader() {
        return $this->grader;
    }
    
    /**
     * Set the submitted form data.
     * @param stdClass $data The form data (instance)
     */
    public function set_instance(stdClass $data) {
        $this->instance = $data;
    }
    
    /**
     * Update the module completion status (set it viewed).
     *
     * @since Moodle 3.2
     */
    public function set_module_viewed() {
        $completion = new completion_info($this->get_course());
        $completion->set_module_viewed($this->get_course_module());
    }
    
    /**
     * Actual implementation of the reset course functionality, delete all the
     * otmutualassessment points and statuses for course $data->courseid.
     *
     * @param stdClass $data the data submitted from the reset course.
     * @return array status array
     */
    public function reset_userdata($data) {
        global $CFG, $DB;
        
        $componentstr = get_string('modulenameplural', 'mod_otmutualassessment');
        $status = [];
        
        $assessments = $DB->get_records('otmutualassessment', ['course' => $data->courseid], '', 'id');
        list($sql, $params) = $DB->get_in_or_equal(array_keys($assessments));
        
        // Remove points.
        if (!empty($data->reset_otmutualassessment_points)) {
            $DB->delete_records_select($this->pointstable, "$this->fk $sql", $params);
            $DB->delete_records_select($this->statusestable, "$this->fk $sql", $params);
            
            $status[] = [
                'component' => $componentstr,
                'item' => get_string('deletepoints', 'mod_otmutualassessment'),
                'error' => false
            ];
            
            if (!empty($data->reset_gradebook_grades)) {
                $DB->delete_records_select($this->gradestable, "$this->fk $sql", $params);
                // Remove all grades from gradebook.
                require_once($CFG->dirroot . '/mod/otmutualassessment/lib.php');
                otmutualassessment_reset_gradebook($data->courseid);
            }
        }
        
        // Remove statuses.
        if (!empty($data->reset_otmutualassessment_statuses) && empty($data->reset_otmutualassessment_points)) {
            $DB->delete_records_select($this->statusestable, "$this->fk $sql", $params);
            $status[] = [
                'component' => $componentstr,
                'item' => get_string('deletestatuses', 'mod_otmutualassessment'),
                'error' => false
            ];
        }
        
        return $status;
    }
    
    /**
     * Запустить процесс обновления данных с учетом заданного режима
     * @param array $actions массив требуемых действий
     * @param int $groupid идентификатор группы
     * @param boolean $shownotification необходимость показать уведомление о процессе 
     */
    public function process_refresh($actions = ['full_refresh'], $groupid = null, $shownotification = false) {
        if (empty($this->get_grades())) {
            // Если оценок нет - нечего делать
            if ($shownotification) {
                notification::add(get_string('process_refresh_not_required', 'mod_otmutualassessment'), notification::WARNING);
            }
            return;
        }
        switch ($this->get_efficiencyofrefresh()) {
            case 'live':
                // Обновлять нужно в реальном времени
                $this->refresh($actions, $groupid, $shownotification);
                if ($shownotification) {
                    notification::add(get_string('process_refresh_live_ended', 'mod_otmutualassessment'), notification::INFO);
                }
                break;
            case 'cron':
            default:
                foreach ($actions as $action) {
                    // Обновлять нужно в фоновом режиме, добавим задачу
                    $this->add_task($action, $groupid);
                }
                if ($shownotification) {
                    notification::add(get_string('process_refresh_cron_started', 'mod_otmutualassessment'), notification::INFO);
                }
                break;
        }
    }
    
    /**
     * Пересчитать оценки, статусы и выполнение элемента для всех пользователей
     * @param array $actions требуемые действия (full_refresh|refresh_grades|reset_grades|refresh_statuses|reset_statuses|refresh_completion_states|delete_history)
     * @param int $groupid идентификатор локальной группы
     */
    public function refresh($actions = ['full_refresh'], $groupid = null) {
        if (empty($this->get_grades())) {
            // Если оценок нет - нечего делать
            return;
        }
        $savegraderhistory = get_config('mod_otmutualassessment', 'savegraderhistory');
        if ($savegraderhistory === false) {
            $savegraderhistory = 1;
        }
        if (is_null($groupid)) {
            $groupid = 0;
        }
        $users = get_enrolled_users($this->get_context(), 'mod/otmutualassessment:begradedbyothers', $groupid);
        if (in_array('full_refresh', $actions) || in_array('refresh_grades', $actions)) {
            // Пересчитываем оценки для всех, у кого есть право быть оцененными
            $this->refresh_grades($users, $groupid);
        }
        
        if (in_array('full_refresh', $actions) || in_array('reset_grades', $actions)) {
            // Сбрасываем оценки для всех, кого оценивали, но у кого нет права быть оцененными
            $this->reset_grades_for_lost_capability_users($users, $groupid);
        }
        
        $users = get_enrolled_users($this->get_context(), 'mod/otmutualassessment:gradeothers', $groupid);
        if (in_array('full_refresh', $actions) || in_array('refresh_statuses', $actions)) {
            // Выставляем статусы всем, кто имеет право оценивать других
            $this->refresh_statuses($users, $groupid);
        }
        
        if (in_array('full_refresh', $actions) || in_array('reset_statuses', $actions)) {
            // Для всех, у кого есть статус о выполненных обязательствах, но нет права оценивать, сбросим статусы
            $this->reset_statuses_for_lost_capability_users($users, $groupid);
        }
        
        if (in_array('full_refresh', $actions) || in_array('delete_history', $actions)) {
            // Очистка истории оценщиков
            if (!$savegraderhistory) {
                $this->delete_history($users, $groupid);
            }
        }
        
        if (in_array('full_refresh', $actions) || in_array('refresh_completion_states', $actions)) {
            // Пересчитаем для всех выполнение элемента
            $this->refresh_completion_states($groupid);
        }
    }
    
    /**
     * Есть ли у пользователя оценка?
     * @param int $userid идентификатор пользователя
     * @return boolean
     */
    public function has_grade($userid) {
        global $DB;
        if ($row = $DB->get_record($this->gradestable, [$this->fk => $this->get_instance()->id, 'userid' => $userid])) {
            if ($row->grade != -1) {
                return true;
            }
        }
        return false;
    }
    
    /**
     * Получить список идентификаторов пользователей, которым выставлял оценки переданный пользователь
     * @param int $graderid идентификатор оценщика
     * @return array
     */
    public function get_gradeds_from_db_by_grader($graderid, $groupid = null) {
        global $DB;
        if (is_null($groupid)) {
            $groupid = 0;
            $groupjoin = '';
            $where = ' AND p.groupid = :groupid';
        } else {
            $groupjoin = ' LEFT JOIN {groups_members} gm
                                  ON gm.userid = p.grader AND gm.groupid = p.groupid
                           LEFT JOIN {groups} g
                                  ON gm.groupid = g.id';
            $where = ' AND p.groupid = :groupid AND gm.groupid IS NOT NULL';
        }
        $params = [
            'fk' => $this->get_instance()->id,
            'grader' => $graderid,
            'groupid' => $groupid
        ];
        $sql = 'SELECT DISTINCT p.graded AS id
                           FROM {' . $this->pointstable . '} p' . $groupjoin . '
                          WHERE p.grader = :grader AND p.' . $this->fk . ' = :fk' . $where;
        return $DB->get_records_sql($sql, $params);
    }
    
    /**
     * Сбросить список пользователей для оценки
     */
    public function reset_graded_users() {
        $this->gradedusers = null;
    }
    
    /**
     * Получить html-код первой ячейки таблицы отчета
     * @return string
     */
    protected function get_first_cell() {
        $gradersspan = html_writer::span(get_string('graders_caption', 'mod_otmutualassessment'));
        $gradedsspan = html_writer::span(get_string('gradeds_caption', 'mod_otmutualassessment'));
        $gradersdiv = html_writer::div($gradersspan);
        $gradedsdiv = html_writer::div($gradedsspan);
        return html_writer::div($gradedsdiv . $gradersdiv, 'wrap');
    }
    
    /**
     * Получить форму обновления оценок
     * @return \mod_otmutualassessment\refreshform
     */
    public function get_refresh_form() {
        global $PAGE;
        $customdata = new stdClass();
        $customdata->otmutualassessment =& $this;
        return new refreshform($PAGE->url->out(false), $customdata, 'post', '', ['class' => 'otmutualassessment-refresh-grades-form']);
    }
    
    /**
     * Получить форму добавления таска на обновления оценок
     * @return \mod_otmutualassessment\refreshtaskform
     */
    public function get_refresh_task_form() {
        global $PAGE;
        $customdata = new stdClass();
        $customdata->otmutualassessment =& $this;
        return new refreshtaskform($PAGE->url->out(false), $customdata, 'post', '', ['class' => 'otmutualassessment-refresh-grades-task-form']);
    }
    
    /**
     * Вернуть строку с учетом числа, если она существует
     * @param int $number число, для которого подбирается строка
     * @param string $identifier базовый идентификатор строки
     * @param string $component компонент, где искать строку
     * @param string|object|array $a объект, строка или число, которые могут быть использованы в строке
     * @param bool $lazyload Если установлено значение true, вместо самой строки возвращается строковый объект. Строка не вычисляется до тех пор, пока не будет использована впервые.
     * @return string
     */
    public function get_plural_string($number, $identifier, $component = '', $a = null, $lazyload = false) {
        if ($number % 10 == 1 && $number % 100 != 11) {
            $prefix = '_0';
        } elseif ($number % 10 >= 2 && $number % 10 <= 4 && ($number % 100 < 11 || $number % 100 > 14)) {
            $prefix = '_1';
        } else {
            $prefix = '_2';
        }
        if (get_string_manager()->string_exists($identifier . $prefix, $component)) {
            return get_string($identifier . $prefix, $component, $a, $lazyload);
        } else {
            return get_string($identifier, $component, $a, $lazyload);
        }
    }
    
    /**
     * Удалить историю голосования пользователя (если передана группа, то в группе, если не передана, то всю)
     * @param int $userid идентификатор пользователя
     * @param int $groupid идентификатор группы
     */
    public function delete_grader_history($userid, $groupid = null) {
        global $DB;
        // Удаляем баллы
        $params = [
            'grader' => $userid,
            $this->fk => $this->get_instance()->id,
        ];
        if (!is_null($groupid)) {
            $params['groupid'] = $groupid;
        }
        $DB->delete_records($this->pointstable, $params);
        // Удаляем статусы
        $params = [
            'userid' => $userid,
            $this->fk => $this->get_instance()->id,
        ];
        if (!is_null($groupid)) {
            $params['groupid'] = $groupid;
        }
        $DB->delete_records($this->statusestable, $params);
    }
    
    /**
     * Добавление в форму настроек модуля курса кастомных настроек для конкретной стратегии
     * @param moodleform $mform указатель на объект moodleform
     * @param mod_otmutualassessment_mod_form $form указатель на объект mod_otmutualassessment_mod_form
     */
    public function add_custom_mod_form_elements(& $mform, & $form) {
        return;
    }
    
    /**
     * Подготовить данные из формы модуля курса для сохранения опций стратегии
     * По умолчанию сохраняются пустые опции, если нужно сохранить нужные данные - нужно переопределить в классе стратегии
     * @param stdClass $data объект с данными формы модуля курса
     */
    public function prepare_options_for_save($data) {
        return null;
    }
    
    /**
     * Подготовить опции стратегии по умолчанию для сохранения
     * @return string
     */
    public function get_default_options_for_save() {
        return null;
    }
    
    /**
     * Добавить общие настройки для стратегий
     * @param admin_settingpage $settings
     */
    public function add_common_settings(& $settings) {
        return;
    }
    
    /**
     * Добавить кастомные настройки для стратегий
     * @param admin_settingpage $settings
     */
    public function add_custom_settings(& $settings) {
        return;
    }
    
    /**
     * Валидация общих полей стратегий формы редактирования модуля курса
     * @param array $data
     * @param array $files
     * @param moodleform $mform
     */
    public function validation_common_mod_form_elements($data, $files, & $mform) {
        $errors = [];
        if (!empty($this->get_grades())) {
            $changestrategy = $data['strategy'] != $this->get_instance()->strategy;
            $changegradingmode = $data['gradingmode'] != $this->get_instance()->gradingmode;
            $changegroupmode = $data['groupmode'] != $this->get_course_module()->effectivegroupmode;
            if ($changestrategy) {
                $errors['strategy'] = get_string('error_mod_form_strategy_can_not_be_changed', 'mod_otmutualassessment');
            }
            if ($changegradingmode) {
                $errors['gradingmode'] = get_string('error_mod_form_gradingmode_can_not_be_changed', 'mod_otmutualassessment');
            }
            if ($changegroupmode) {
                $errors['groupmode'] = get_string('error_mod_form_groupmode_can_not_be_changed', 'mod_otmutualassessment');
            }
        }
        return $errors;
    }
    
    /**
     * Валидация кастомных полей стратегий формы редактирования модуля курса
     * @param array $data
     * @param array $files
     * @param moodleform $mform
     */
    public function validation_custom_mod_form_elements($data, $files, & $mform) {
        return [];
    }
    
    /**
     * Предобработка дефолтных значений кастомных полей стратегий формы редактирования модуля курса
     *
     * @param array $default_values passed by reference
     */
    public function data_preprocessing_custom_mod_form_elements(&$default_values) {
        ;
    }
    
    /**
     * Представление формы оценивания
     * @param MoodleQuickForm $mform MoodleQuickForm quickform object definition
     * @param graderform $form graderform object
     */
    public function graderform_definition(& $mform, & $form) {
        ;
    }
    
    /**
     * Представление формы оценивания после отправки формы
     * @param MoodleQuickForm $mform MoodleQuickForm quickform object definition
     * @param graderform $form graderform object
     */
    public function graderform_definition_after_data(& $mform, & $form) {
        ;
    }
    
    /**
     * Валидация формы оценивания
     * @param array $data массив данных, отправленных формой
     * @param array $files массив файлов, отправленных формой
     * @param graderform $form graderform object
     */
    public function graderform_validation($data, $files, & $form) {
        ;
    }
    
    /**
     * Обработка формы оценивания
     * @param MoodleQuickForm $mform MoodleQuickForm quickform object definition
     * @param graderform $form graderform object
     */
    public function graderform_process(& $mform, & $form) {
        ;
    }
    
    /**
     * Есть у стратегии настройки (по умолчанию - нет, если есть - нужно переопределить в классе стратегии)
     * @return boolean
     */
    public function has_config() {
        return false;
    }
    
    /**
     * Подключение фронтенд-обработчика формы оценщика
     */
    public function graderform_js_call() {
        ;
    }
    
    /**
     * Получить элементы управления голосованием участника
     * @param stdClass $user объект участника, над которым совершается действие (property id, fullname must exists)
     * @param int $groupid идентификатор локальной группы
     * @return string html-код блока с элементами управления
     */
    public function get_report_controls($user, $groupid = null) {
        global $OUTPUT;
        $buttons = [];
        if (has_capability('mod/otmutualassessment:deletevotes', $this->get_context())) {
            $params = ['delete' => $user->id, 'sesskey' => sesskey()];
            $a = new stdClass();
            $a->fullname = $user->fullname;
            $alt = get_string('deletevote', 'mod_otmutualassessment', $a);
            if (!is_null($groupid)) {
                $params['group'] = $groupid;
                $a->groupname = groups_get_group_name($groupid);
                $alt = get_string('deletegroupvote', 'mod_otmutualassessment', $a);
            }
            $url = new moodle_url($this->get_report_url(), $params);
            $buttons[] = html_writer::link($url, $OUTPUT->pix_icon('t/delete', $alt));
        }
        return html_writer::div(implode(' ', $buttons), 'otmutualassessment-vote-controls');
    }
    
    /**
     * Получить базовый урл-алрес отчета
     * @return string
     */
    public function get_report_base_url() {
        return $this->reportbaseurl;;
    }
    
    /**
     * Обработка процесса удаления голосования участника
     * @param int $delete идентификатор участника, голосование которого удаляется
     * @param string $confirm флаг подтверждения удаления голосования участника
     */
    public function process_delete_vote($delete, $confirm, $groupid = null) {
        global $OUTPUT, $DB, $CFG;
        require_login($this->get_course(), true, $this->get_course_module());
        require_capability('mod/otmutualassessment:deletevotes', $this->get_context());
        if ($user = $DB->get_record('user', ['id' => $delete, 'mnethostid' => $CFG->mnet_localhost_id], '*')) {
            $a = new stdClass();
            $a->fullname = fullname($user, true);
            $groupflag = '';
            if (!is_null($groupid)) {
                $a->groupname = groups_get_group_name($groupid);
                $groupflag = 'group';
            }
            $heading = get_string('delete' . $groupflag . 'vote', 'mod_otmutualassessment', $a);
            $failstring = get_string('delete' . $groupflag . 'vote_failed', 'mod_otmutualassessment', $a);
            if ($confirm != md5($delete)) {
                echo $OUTPUT->header();
                echo $OUTPUT->heading($heading);
                
                $optionsyes = ['delete' => $delete, 'confirm' => md5($delete), 'sesskey'=>sesskey()];
                if (!is_null($groupid)) {
                    $optionsyes['group'] = $groupid;
                }
                $deleteurl = new moodle_url($this->get_report_url(), $optionsyes);
                $deletebutton = new single_button($deleteurl, get_string('delete'), 'post');
                
                echo $OUTPUT->confirm(get_string('deletevote_desc', 'mod_otmutualassessment'), $deletebutton, $this->get_report_url($delete));
                echo $OUTPUT->footer();
                die;
            } else if (data_submitted()) {
                try {
                    // Удалим историю оценщика
                    $this->delete_grader_history($delete, $groupid);
                    // Сбрасываем статус оценщика
                    $this->set_status($delete, self::NOTCOMPLETED, $groupid);
                    // Обновим оценки участников и пересчитаем для всех выполнение элемента
                    $this->process_refresh(['refresh_grades', 'refresh_completion_states'], $groupid, true);
                } catch (Exception $e) {
                    notification::add($failstring, notification::ERROR);
                }
                // Перенаправим на страницу отчета
                redirect($this->get_report_url());
            }
        }
    }
    
    /**
     * Обновить оценки участников
     * @param array $users массив записанных на курс пользователей с правом mod/otmutualassessment:begradedbyothers
     * @param int $groupid идентификатор локальной группы
     */
    protected function refresh_grades($users = null, $groupid = null) {
        if (is_null($groupid)) {
            $groupid = 0;
        }
        if (is_null($users)) {
            $users = get_enrolled_users($this->get_context(), 'mod/otmutualassessment:begradedbyothers', $groupid);
        }
        // Пересчитываем оценки для всех, у кого есть право быть оцененными
        foreach ($users as $user) {
            $this->set_grade($user->id);
            $this->reset_graded_users();
        }
    }
    
    /**
     * Сбросить оценки участников, потерявших право быть оцененными (mod/otmutualassessment:begradedbyothers)
     * @param array $users массив записанных на курс пользователей с правом mod/otmutualassessment:begradedbyothers
     * @param int $groupid идентификатор локальной группы
     */
    protected function reset_grades_for_lost_capability_users($users = null, $groupid = null) {
        global $DB;
        if (is_null($groupid)) {
            $groupid = 0;
        }
        if (is_null($users)) {
            $users = get_enrolled_users($this->get_context(), 'mod/otmutualassessment:begradedbyothers', $groupid);
        }
        // Сбрасываем оценки для всех, кого оценивали, но у кого нет права быть оцененными
        if ($users) {
            list($sqlin, $params) = $DB->get_in_or_equal(array_keys($users), SQL_PARAMS_NAMED, 'param', false);
            $sql = 'SELECT DISTINCT graded AS id
                               FROM {' . $this->pointstable . '}
                              WHERE graded ' . $sqlin . '
                                AND ' . $this->fk . ' = :instanceid';
        } else {
            $sql = 'SELECT DISTINCT graded AS id
                               FROM {' . $this->pointstable . '}
                              WHERE ' . $this->fk . ' = :instanceid';
        }
        $params['instanceid'] = $this->get_instance()->id;
        if ($userstoreset = $DB->get_records_sql($sql, $params)) {
            foreach ($userstoreset as $user) {
                $this->reset_grade($user->id);
            }
        }
    }
    
    /**
     * Обновить статусы участников с правом оцененивать других участников (mod/otmutualassessment:gradeothers)
     * @param array $users массив записанных на курс пользователей с правом mod/otmutualassessment:gradeothers
     * @param int $groupid идентификатор локальной группы
     */
    protected function refresh_statuses($users = null, $groupid = null) {
        global $DB;
        if (is_null($groupid)) {
            $groupid = 0;
        }
        if (is_null($users)) {
            $users = get_enrolled_users($this->get_context(), 'mod/otmutualassessment:gradeothers', $groupid);
        }
        // Выставляем статусы всем, кто имеет право оценивать других
        foreach ($users as $user) {
            $this->set_grader($user->id);
            if (!empty($this->get_course_module()->effectivegroupmode)) {
                // Если включен групповой режим, выставляем статусы для каждой группы, в которой есть пользователь
                $usergroups = [];
                if (!empty($groupid)) {
                    $usergroups = [$this->get_course_module()->groupingid => [$groupid => $groupid]];
                } else {
                    $usergroups = groups_get_user_groups($this->get_course()->id, $user->id);
                }
                $usergroups = !empty($usergroups[$this->get_course_module()->groupingid]) ? $usergroups[$this->get_course_module()->groupingid] : [];
                if (!empty($usergroups)) {
                    foreach ($usergroups as $groupid) {
                        $this->set_graded_users($groupid);
                        if ($this->is_user_completed_assessment($user->id, $groupid)) {
                            $status = self::COMPLETED;
                        } else {
                            $status = self::NOTCOMPLETED;
                        }
                        $this->set_status($user->id, $status, $groupid);
                    }
                    list($sqlin, $params) = $DB->get_in_or_equal(array_values($usergroups), SQL_PARAMS_NAMED, 'param', false);
                    $sql = 'SELECT *
                              FROM {' . $this->statusestable . '}
                             WHERE ' . $this->fk . ' = :instanceid
                               AND userid = :userid
                               AND status = :status
                               AND groupid ' . $sqlin;
                } else {
                    $sql = 'SELECT *
                              FROM {' . $this->statusestable . '}
                             WHERE ' . $this->fk . ' = :instanceid
                               AND userid = :userid
                               AND status = :status';
                }
                $params['userid'] = $user->id;
                $params['instanceid'] = $this->get_instance()->id;
                $params['status'] = self::COMPLETED;
                // Для всех групп, в которых пользователь не состоит, но имеет завершенные статусы,
                // сбросим статусы
                if ($rows = $DB->get_records_sql($sql, $params)) {
                    foreach ($rows as $row) {
                        $this->set_status($user->id, $status = self::NOTCOMPLETED, $row->groupid);
                    }
                }
            } else {
                $this->set_graded_users();
                if ($this->is_user_completed_assessment($user->id)) {
                    $status = self::COMPLETED;
                } else {
                    $status = self::NOTCOMPLETED;
                }
                $this->set_status($user->id, $status);
            }
        }
    }
    
    /**
     * Сбросить статусы участников, потерявших право оцененивать других участников (mod/otmutualassessment:gradeothers)
     * @param array $users массив записанных на курс пользователей с правом mod/otmutualassessment:gradeothers
     * @param int $groupid идентификатор локальной группы
     */
    protected function reset_statuses_for_lost_capability_users($users = null, $groupid = null) {
        global $DB;
        if (is_null($groupid)) {
            $groupid = 0;
        }
        if (is_null($users)) {
            $users = get_enrolled_users($this->get_context(), 'mod/otmutualassessment:gradeothers', $groupid);
        }
        // Для всех, у кого есть статус о выполненных обязательствах, но нет права оценивать, сбросим статусы
        if ($users) {
            list($sqlin, $params) = $DB->get_in_or_equal(array_keys($users), SQL_PARAMS_NAMED, 'param', false);
            $sql = 'SELECT *
                      FROM {' . $this->statusestable . '}
                     WHERE userid ' . $sqlin . '
                       AND ' . $this->fk . ' = :instanceid
                       AND status = :status';
        } else {
            $sql = 'SELECT *
                      FROM {' . $this->statusestable . '}
                     WHERE ' . $this->fk . ' = :instanceid
                       AND status = :status';
        }
        $params['instanceid'] = $this->get_instance()->id;
        $params['status'] = self::COMPLETED;
        if ($recordstoreset = $DB->get_records_sql($sql, $params)) {
            foreach ($recordstoreset as $record) {
                $this->set_status($record->userid, self::NOTCOMPLETED, $record->groupid);
            }
        }
    }
    
    /**
     * Пересчитать статусы выполнения элемента курса для всех участников
     */
    protected function refresh_completion_states() {
        // Пересчитаем для всех выполнение элемента
        $completion = new completion_info(get_course($this->get_course()->id));
        $completion->reset_all_state($this->get_course_module());
    }
    
    /**
     * Удалить историю голосования участников, потерявших право оцененивать других участников (mod/otmutualassessment:gradeothers)
     * @param array $users массив записанных на курс пользователей с правом mod/otmutualassessment:gradeothers
     * @param int $groupid идентификатор локальной группы
     */
    protected function delete_history($users = null, $groupid = null) {
        global $DB;
        if (is_null($groupid)) {
            $groupid = 0;
        }
        if (is_null($users)) {
            $users = get_enrolled_users($this->get_context(), 'mod/otmutualassessment:gradeothers', $groupid);
        }
        // Сначала чистим историю по оценщикам без права оценивать (сюда же попадут отчисленные)
        if ($users) {
            list($sqlin, $params) = $DB->get_in_or_equal(array_keys($users), SQL_PARAMS_NAMED, 'param', false);
            $sql = 'SELECT DISTINCT grader
                               FROM {' . $this->pointstable . '}
                              WHERE grader ' . $sqlin . '
                                AND ' . $this->fk . ' = :instanceid';
        } else {
            $sql = 'SELECT DISTINCT grader
                               FROM {' . $this->pointstable . '}
                              WHERE ' . $this->fk . ' = :instanceid';
        }
        $params['instanceid'] = $this->get_instance()->id;
        if ($records = $DB->get_records_sql($sql, $params)) {
            foreach ($records as $record) {
                $this->delete_grader_history($record->grader, $groupid);
            }
        }
        // Теперь чистим историю по оценщикам, которых удалили из групп при включенном групповом режиме
        if (!empty($this->get_course_module()->effectivegroupmode)) {
            $sql = 'SELECT DISTINCT p.grader, p.groupid
                               FROM {' . $this->pointstable . '} p
                          LEFT JOIN {groups_members} gm
                                 ON p.grader = gm.userid AND p.groupid = gm.groupid
                              WHERE gm.groupid IS NULL
                                AND p.groupid > 0
                                AND ' . $this->fk . ' = :instanceid';
            $params['instanceid'] = $this->get_instance()->id;
            if ($records = $DB->get_records_sql($sql, $params)) {
                foreach ($records as $record) {
                    $this->delete_grader_history($record->grader, $record->groupid);
                }
            }
        }
    }
    
    /**
     * Добавить задачу на пересчет данных по модулю
     * @return bool результат добавления задачи (true|false)
     */
    public function add_task($action = 'full_refresh', $groupid = null) {
        global $USER;
        $result = false;
        $classname = '\mod_otmutualassessment\task\\' . $action;
        if (class_exists($classname)) {
            if (!$this->is_task_added($action, $groupid)) {
                // Let's set up the adhoc task.
                $task = new $classname;
                $customdata = new stdClass();
                $customdata->cmid = $this->get_course_module()->id;
                $customdata->groupid = $groupid;
                $task->set_custom_data($customdata);
                $task->set_userid($USER->id);
                $task->set_component('mod_otmutualassessment');
                // Queue it.
                $result = taskmanager::queue_adhoc_task($task);
                if ($result && $action == 'full_refresh') {
                    // Если добавили задачу на полное обновление, уберем уже добавленные задачи на частичное обновление
                    if (is_null($groupid)) {
                        $this->remove_partial_tasks();
                        // Полное обновление по всем группам
                        $groups = groups_get_all_groups($this->get_course()->id);
                        foreach ($groups as $group) {
                            $this->remove_partial_tasks($group->id);
                            $this->remove_task('full_refresh', $group->id);
                        }
                    } else {
                        // Полное обновление только по конкретной группе
                        $this->remove_partial_tasks($groupid);
                    }
                }
            }
        }
        return $result;
    }
    
    /**
     * Проверить, имеется ли задача на пересчет данных по модулю в очереди
     * @return bool результат проверки (true|false)
     */
    public function is_task_added($action = 'full_refresh', $groupid = null) {
        global $DB;
        $customdata = new stdClass();
        $customdata->cmid = $this->get_course_module()->id;
        $customdata->groupid = $groupid;
        if ($action != 'full_refresh') {
            $actions = ['full_refresh', $action];
        } else {
            $actions = ['full_refresh'];
        }
        $classname = array_map(function($val) {return '\mod_otmutualassessment\task\\' . $val;}, $actions);
        list($sqlin, $params) = $DB->get_in_or_equal($classname, SQL_PARAMS_NAMED);
        $sql = 'SELECT *
                  FROM {task_adhoc}
                 WHERE classname ' . $sqlin;
        if ($records = $DB->get_records_sql($sql, $params)) {
            foreach ($records as $record) {
                $customdata = json_decode($record->customdata);
                // Если добавлена непосредственно нужная задачи или задача на полный пересчет
                if (!empty($customdata)
                    && ($customdata->cmid == $this->get_course_module()->id
                        && $customdata->groupid == $groupid)
                    || ($record->classname == '\mod_otmutualassessment\task\full_refresh'
                        && (is_null($customdata->groupid)
                            || $customdata->groupid == $groupid))) {
                    return true;
                }
            }
        }
        return false;
    }
    
    /**
     * Получить режим пересчета данных в модуле
     * @return string
     */
    public function get_efficiencyofrefresh() {
        return $this->efficiencyofrefresh;
    }
    
    /**
     * Получить предупреждающую информацию о неактуальности данных в отчете по выставленным баллам
     * @return string
     */
    public function get_warning_info() {
        if (!$this->is_report_actual()) {
            return html_writer::div(get_string('warning_info', 'mod_otmutualassessment'), 'alert alert-error alert-block fade in');
        } else {
            return '';
        }
    }
    
    /**
     * Узнать, актуальны ли данные в отчете по выставленным баллам
     * @return boolean
     */
    public function is_report_actual() {
        global $DB;
        return !$DB->record_exists_sql(
            'SELECT * FROM {task_adhoc} WHERE ' . $DB->sql_like('classname', ':classname'), 
            ['classname' => $DB->sql_like_escape('\\\\mod_otmutualassessment\\\\task\\\\').'%']
        );
    }
    
    /**
     * Удалить поставленные задачи на частичное обновление данных
     * @param int $groupid идентификатор группы
     */
    public function remove_partial_tasks($groupid = null) {
        foreach ($this->get_refresh_actions() as $action) {
            $this->remove_task($action, $groupid);
        }
    }
    
    /**
     * Удалить запланированную задачу на обновление данных
     * @param string $action запланированное действие
     * @param int $groupid идентификатор группы
     */
    public function remove_task($action, $groupid) {
        global $DB;
        $customdata = new stdClass();
        $customdata->cmid = $this->get_course_module()->id;
        $customdata->groupid = $groupid;
        $params = [
            'classname' => '\\mod_otmutualassessment\\task\\' . $action,
        ];
        $select = 'classname = :classname';
        $records = $DB->get_recordset_select('task_adhoc', $select, $params);
        if ($records->valid()) {
            $cronlockfactory = \core\lock\lock_config::get_lock_factory('cron');
            foreach ($records as $record) {
                $customdata = json_decode($record->customdata);
                if (!empty($customdata) 
                    && $customdata->cmid == $this->get_course_module()->id 
                    && $customdata->groupid == $groupid) {
                    if ($lock = $cronlockfactory->get_lock('adhoc_' . $record->id, 0)) {
                        // Safety check, see if the task has been already processed by another cron run.
                        $record = $DB->get_record('task_adhoc', array('id' => $record->id));
                        if (!$record) {
                            $lock->release();
                            continue;
                        }
                        
                        $task = taskmanager::adhoc_task_from_record($record);
                        // Safety check in case the task in the DB does not match a real class (maybe something was uninstalled).
                        if (!$task) {
                            $lock->release();
                            continue;
                        }
                        
                        $task->set_lock($lock);
                        if (!$task->is_blocking()) {
                            $lock->release();
                        } else {
                            continue;
                        }
                        
                        taskmanager::adhoc_task_complete($task);
                    }
                }
            }
        }
        $records->close();
    }
    
    /**
     * Получить массив этапов обновления данных
     * @return string[]
     */
    public function get_refresh_actions() {
        return $this->refreshactions;
    }
    
    /**
     * Получить урл интерфейса пересчета оценок
     * @return moodle_url
     */
    public function get_refresh_url() {
        return new moodle_url('/mod/otmutualassessment/refresh.php', ['cmid' => $this->get_course_module()->id]);
    }
}