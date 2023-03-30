<?php
////////////////////////////////////////////////////////////////////////////
//                                                                        //
// NOTICE OF COPYRIGHT                                                    //
//                                                                        //
// Dean`s Office for Moodle                                               //
// Электронный деканат                                                    //
// <http://deansoffice.ru/>                                               //
//                                                                        //
// This program is free software: you can redistribute it and/or modify   //
// it under the terms of the GNU General Public License as published by   //
// the Free Software Foundation, either version 3 of the Licensen.        //
//                                                                        //
// This program is distributed in the hope that it will be useful,        //
// but WITHOUT ANY WARRANTY; without even the implied warranty of         //
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the          //
// GNU General Public License for more details.                           //
//                                                                        //
// You should have received a copy of the GNU General Public License      //
// along with this program.  If not, see <http://www.gnu.org/licenses/>.  //
//                                                                        //
////////////////////////////////////////////////////////////////////////////

/**
 * Класс для работы с модулем assign
 *
 * @package    modlib
 * @subpackage ama
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
 
global $CFG;

// Подключение библиотек
require_once(dirname(realpath(__FILE__)).'/class.ama_course_instance.php');
require_once(dirname(realpath(__FILE__)) . '/../../../../../mod/assign/lib.php');
require_once(dirname(realpath(__FILE__)) . '/../../../../../mod/assign/externallib.php');
require_once(dirname(realpath(__FILE__)) . '/../../../../../mod/assign/locallib.php');
require_once(dirname(realpath(__FILE__)) . '/../../../../../mod/assign/submission/onlinetext/locallib.php');
require_once(dirname(realpath(__FILE__)) . '/../../../../../mod/assign/submission/file/locallib.php');
require_once(dirname(realpath(__FILE__)) . '/../../../../../mod/assign/feedback/comments/locallib.php');
require_once(dirname(realpath(__FILE__)) . '/../../../../../mod/assign/feedback/file/locallib.php');
require_once(dirname(realpath(__FILE__)) . '/../../../../../lib/datalib.php');
require_once(dirname(realpath(__FILE__)) . '/../../../../../lib/gradelib.php');

class ama_course_instance_assign extends ama_course_instance
{
    /**
     * Конструктор класса
     * stdClass $cm объект модуля курса
     * @access public
     */
    public function __construct($cm)
    {
        $this->cm = $cm;
        if( isset($this->cm->id) )
        {
            $this->context = context_module::instance($this->cm->id);
            $this->assignrecord = $this->get_assign_record();
            $this->assign = new assign($this->context, $this->cm, $this->assignrecord->course);
        } else
        {
            $this->context = null;
            $this->assignrecord = null;
            $this->assign = null;
        }
        if( empty($this->courseid) )
        {
            $this->courseid = $this->cm->course;
        }
        $this->groupid = $this->get_current_group();
        $this->feedbackfilearea = ASSIGNFEEDBACK_FILE_FILEAREA;
        $this->submissionfilearea = ASSIGNSUBMISSION_FILE_FILEAREA;
    }
    
    public function get_instance_name()
    {
        return 'assign';
    }
    
    /**
     * Получить оценку за задание
     * @param int $userid идентификатор пользователя
     * @return array массив оценок
     */
    public function get_user_grades($userid)
    {
        if( (int)$userid <= 0 )
        {
            return [];
        }
        return $this->assignrecord ? assign_get_user_grades($this->assignrecord, (int)$userid) : [];
    }
    
    /**
     * Получить оценку за задание в процентах
     * @param int $userid идентификатор пользователя
     * @return string
     */
    public function get_grade_percentage($userid)
    {
        if( (! isset($this->cm->course) && ! isset($this->cm->instance)) || (int)$userid <= 0 )
        {
            return '';
        }
        $grade_item = grade_get_grades($this->cm->course, 'mod', $this->cm->modname, $this->cm->instance, (int)$userid);
        $item = new grade_item();
        $itemproperties = reset($grade_item->items);
        foreach ($itemproperties as $key => $value) {
            $item->$key = $value;
        }
        $grade = $item->grades[$userid]->grade;
        $item->gradetype = GRADE_TYPE_VALUE;
        $item->courseid = $this->cm->course;
        
        return grade_format_gradevalue($grade, $item, true, GRADE_DISPLAY_TYPE_PERCENTAGE, 2);
    }
    
    /**
     * Получить запись задания из БД
     * @return array массив из полей записи
     */
    public function get_assign_record()
    {
        if( ! isset($this->cm->instance) )
        {
            return [];
        }
        global $DB;
        return $DB->get_record($this->cm->modname, ['id' => $this->cm->instance]);
    }
    
    /**
     * Получить файловые зоны
     * @return array
     */
    public function get_file_areas()
    {
        if( ! isset($this->cm->course) )
        {
            return [];
        }
        $course = get_course($this->cm->course);
        return assign_get_file_areas($course, $this->cm, $this->context);
    }
    
    /**
     * Получить файлы из файловой зоны
     * @param string $filearea файловая зона
     * @return stored_file[] массив объектов файлов, отсортированный по хешу файлов
     */
    public function get_area_files($itemid, $component = 'assignsubmission_file', $filearea = 'submission_files')
    {
        if( ! isset($this->context) )
        {
            return [];
        }
        $fs = get_file_storage();
        return $fs->get_area_files($this->context->id, $component, $filearea, $itemid);
    }
    
    /**
     * Получить массив хешей пути файлов в ответе задания
     * @return array
     */
    public function get_pathnamehashes($itemid)
    {
        $pathnamehashes = [];
        $files = $this->get_area_files($itemid);
        if( $files )
        {
            foreach ( $files as $file )
            {
                if ( $file->is_directory() )
                {// Пропуск директорий
                    continue;
                }
                // Формирование ссылки на файл
                $pathnamehash = $file->get_pathnamehash();
                $pathnamehashes[$pathnamehash] = $pathnamehash;
            }
        }
        return $pathnamehashes;
    }
    
    /**
     * Получить файлы пользователя, сохраненные в задании
     * @param int $userid идентификатор пользователя
     * @return array массив файлов
     */
    public function get_files_by_userid($userid)
    {
        $userfiles = [];
        if( ! isset($this->assign) || (int)$userid <= 0 )
        {
            return $userfiles;
        }
        $submission = $this->get_submission((int)$userid);
        $files = $this->get_area_files($submission->id);
        if( $files )
        {
            foreach($files as $file)
            {
                if( !$file->is_directory() && $file->get_userid() == (int)$userid )
                {
                    $userfiles[] = $file;
                }
            }
        }
        return $userfiles;
    }
    
    /**
     * Получить объект отправки задания
     * @param int $userid идентификатор пользователя
     * @return stdClass объект отправки задания
     */
    public function get_submission($userid)
    {
        if( ! isset($this->assign) || (int)$userid <= 0 )
        {
            return false;
        }
        return $this->assign->get_user_submission((int)$userid, false);
    }
    
    /**
     * Получить текст, отправленный пользователем в задании
     * @param int $userid идентификатор пользователя
     * @return string текст, отправленный в задании
     */
    public function get_text_from_submission($userid)
    {
        if( ! isset($this->assign) || (int)$userid <= 0 )
        {
            return false;
        }
        $submission = $this->get_submisson((int)$userid);
        if( $submission )
        {
            $assign_submission_onlinetext = new assign_submission_onlinetext($this->assign, 'assignsubmission');
            return $assign_submission_onlinetext->get_editor_text('onlinetext', $submission->id);
        } else
        {
            return false;
        }
    }
    
    /**
     * Получить текст, отправленный пользователем в задании, по идентификатору отправки задания
     * @param int $submissionid идентификатор отправки задания
     * @return string текст, отправленный в задании
     */
    public function get_text_by_submissionid($submissionid)
    {
        if( ! isset($this->assign) )
        {
            return false;
        }
        $assign_submission_onlinetext = new assign_submission_onlinetext($this->assign, 'assignsubmission');
        return $assign_submission_onlinetext->get_editor_text('onlinetext', $submissionid);
    }
    
    /**
     * Возвращает последний файл, сформированный из текста задания, добавленный в очередь на отправку в Антиплагиат
     * @param int $userid идентификатор пользователя
     * @param string $component компонент, сохранивший файл
     * @param string $filearea файловая зона
     * @param int $submissionid идентификатор отправки задания
     */
    public function get_text_from_apru_queue($userid, $component, $filearea, $submissionid)
    {
        global $DB;
        if( (int)$userid <= 0 )
        {
            return false;
        }
        $sql = 'SELECT *
                    FROM {files}
                    WHERE contextid=?
                    AND component=?
                    AND filearea=?
                    AND itemid=?
                    AND filename LIKE \'' . $this->cm->id . '_' . (int)$userid . '_text_%\'
                    ORDER BY timemodified DESC';
        return $DB->get_records_sql($sql, [
            $this->context->id,
            $component,
            $filearea,
            $submissionid
        ], 0, 1);
    }
    
    /**
     * Возвращает записи из таблицы assign с указанными id
     * @param array $ids массив идентификаторов заданий 
     */
    public function get_assing($ids)
    {
        if( empty($ids) )
        {
            return [];
        }
        global $DB;
        $sql = 'SELECT * FROM {assign} a WHERE a.id IN (' . implode(',', (array)$ids) . ')';
        return $DB->get_records_sql($sql);
    }
    
    /**
     * Получить пользователей, которые могут добавить достижение
     * @param array $assignids массив идентификаторов заданий
     */
    public function get_users_which_can_add_achievement($assignids)
    {
        if( empty($assignids) )
        {
            return [];
        }
        global $DB;
        $sql = 'SELECT agr.id as id, agr.userid, agr.assignment, agr.grade as grade 
                  FROM {assign_grades} agr 
                  JOIN (
                         SELECT ag.assignment, ag.userid, MAX(ag.attemptnumber) AS attemptnumber
                           FROM {assign_grades} ag
                          WHERE ag.grade IS NOT NULL AND ag.assignment IN (' . implode(',', (array)$assignids) . ')
                          GROUP BY ag.assignment, ag.userid
                       ) prev 
                    ON (prev.userid = agr.userid AND prev.assignment = agr.assignment AND prev.attemptnumber = agr.attemptnumber)
        ';
              
        return $DB->get_records_sql($sql);
    }
    
    /**
     * Получить список заданий в системе
     */
    public function get_assigns()
    {
        global $DB;
        return $DB->get_records('assign');
    }
    
    /**
     * Получает рецензию (файл) на задание
     * @param int $userid идентификатор пользователя
     */
    public function get_feedback_file($userid)
    {
        if( ! isset($this->assign) || (int)$userid <= 0 )
        {
            return false;
        }
        $grade = $this->assign->get_user_grade((int)$userid, false);
        $feedbackfile = new assign_feedback_file($this->assign, 'assignfeedback');
        return $feedbackfile->get_file_feedback($grade->id);
    }
    
    /**
     * Получает рецензию (комметарий) на задание
     * @param int $userid идентификатор пользователя
     */
    public function get_feedback_comments($userid)
    {
        if( ! isset($this->assign) || (int)$userid <= 0 )
        {
            return '';
        }
        $grade = $this->assign->get_user_grade((int)$userid, false);
        $feedbackcomments = new assign_feedback_comments($this->assign, 'assignfeedback');
        $commentsfeedback = $feedbackcomments->get_feedback_comments($grade->id);
        return format_text($commentsfeedback->commenttext, $commentsfeedback->commentformat);
    }
    
    /**
     * Получает ссылку на задание
     */
    public function get_assign_link()
    {
        return isset($this->cm->id) ? '/mod/assign/view.php?id=' . $this->cm->id : '';
    }
    
    /**
     * Получить отзыв плагина (comments|file|editpdf|offline)
     * @param int $userid идентификатор пользователя
     * @param string $pluginname имя плагина (comments|file|editpdf|offline)
     */
    public function get_feedbackplugin($userid, $pluginname)
    {
        if( ! isset($this->cm->id) || (int)$userid <= 0 )
        {
            return '';
        }
        $plugin = $this->get_feedback_plugin_by_name($pluginname);
        $grade = $this->assign->get_user_grade((int)$userid, false);
        if ( empty($grade) )
        {
            return false;
        }
        $view = assign_feedback_plugin_feedback::FULL;
        $coursemoduleid = $this->cm->id;
        $returnaction = '';
        $returnparams = [];
        return new assign_feedback_plugin_feedback($plugin, $grade, $view, $coursemoduleid, $returnaction, $returnparams);
    }
    
    /**
     * Получить результат рендера плагина
     * @param int $userid идентификатор пользователя
     * @param string $pluginname имя плагина (comments|file|editpdf|offline)
     */
    public function render_assign_feedback_plugin_feedback($userid, $pluginname)
    {
        if( (int)$userid <= 0 )
        {
            return '';
        }
        $feedbackplugin = $this->get_feedbackplugin((int)$userid, $pluginname);
        return $feedbackplugin === false ? '' : $this->assign->get_renderer()->render_assign_feedback_plugin_feedback($this->get_feedbackplugin((int)$userid, $pluginname));
    }
    
    /**
     * Получить объект класса плагина
     * @param string $pluginname имя плагина (comments|file|editpdf|offline)
     */
    public function get_feedback_plugin_by_name($pluginname)
    {
        $classname = 'assign_feedback_' . $pluginname;
        return new $classname($this->assign, 'assignfeedback');
    }
    
    /**
     * Получить результат рендера файлов задания
     * @param int $userid идентификатор пользователя
     */
    public function render_assign_files($userid)
    {
        if( ! isset($this->assign) || (int)$userid <= 0 )
        {
            return '';
        }
        return $this->assign->get_renderer()->assign_files(
            $this->context,
            $this->get_submission((int)$userid)->id,
            $this->submissionfilearea,
            'assignsubmission_file'
        );
    }
    
    /**
     * Получить все задания в курсах
     * @return array массив объектов полученных заданий, содержащий assignid, assignname, courseid, coursename, categoryid, categoryname
     */
    public function get_assignments()
    {
        global $DB;
        $sql = 'SELECT a.id assignid, a.name assignname, c.id courseid, c.fullname coursename, cat.id categoryid, cat.name categoryname
            FROM mdl_assign a
            JOIN mdl_course c
            ON c.id=a.course
            JOIN mdl_course_categories cat
            ON cat.id=c.category
            GROUP BY categoryid, categoryname, courseid, coursename, assignid, assignname';
        $assignments = $DB->get_records_sql($sql, []);
        return $assignments;
    }
    
    /////////////////////////////////////////////
    //    Методы для работы блока notgraded    //
    /////////////////////////////////////////////
    
    /**
     * Получить неотсортированный массив непроверенных заданий
     * @param int $timeform
     * @param int $timeto
     * @param boolean $viewall - показывать ли все задания на проверку
     * @param int $userid - ID пользователя (преподавателя)
     * @param int $groupid - ID группы, если null, то все группы
     * @return array|stdClass[]
     */
    protected function get_notgraded_elements($timeform = null, $timeto = null, $viewall = false, $userid = null, $groupid = null)
    {
        global $CFG, $DB, $USER;
        // получаем все задания курса
        $assignments = $this->get_course_instances();
        // переводим название задания на русский
        $strassignment = get_string('modulename', $this->get_instance_name());
        // собираем id пользователей в массив
        if( ! $userids = $this->get_course_users() )
        {//пользователей нет - значит и заданий нет
            return [];
        }
        //Если не передан пользователь (преподаватель), то считать пользователя текущим.
        if (is_null($userid)) {
            $userid = $USER->id;
        }
        
        // будет хранить результат
        $result = [];
        
        // Указание группы. Если не список ответов, то все группы.
        if( !is_null($groupid))
        {
            $currentgroup = $groupid;
        } else
        {
            $currentgroup = 0;
        }
        
        foreach($assignments as $assignment)
        {//среди всех заданий ищем непроверенные
            if( $assignment->teamsubmission )
            {// Если групповой режим, пропускаем
                continue;
            }
            
            $course = get_course($assignment->course);
            $context = context_module::instance($assignment->coursemodule);
            $modinfo = get_fast_modinfo($course);
            $assignmentobject = new assign($context, null, null);
    
            //является ли действующий групповой режим в элементе курса режимом "изолированные группы"
            $is_cm_separategroups = $modinfo->get_cm($assignment->coursemodule)->effectivegroupmode == SEPARATEGROUPS;
            
            list($esql, $params) = get_enrolled_sql($context, 'mod/assign:submit', $currentgroup, true);
            
            $markergroupsids = array_keys(groups_get_all_groups($course->id, $userid));
            
            $groupsparams = [];
            //если изолированные группы, то добавить фильтр по группам в запрос
            if ($is_cm_separategroups && !empty($markergroupsids)) {
                list($groupidssql, $groupsparams) = $DB->get_in_or_equal($markergroupsids, SQL_PARAMS_NAMED, 'markergroupid');
                $groupsql = 'JOIN {groups_members} gm
                ON (gm.userid = e.id AND gm.groupid ' . $groupidssql . ')';
            } else {
                $groupsql = '';
            }
            
            //подготовка параметров для запроса
            $params['assignid'] = $assignment->id;
            $params['submitted'] = 'submitted';
            $params['userid'] = $userid;
            $params += $groupsparams;
            if( $viewall )
            {
                $fieldallocatedmarker = '';
                $joinallocatedmarker = '';
                $selectallocatedmarker = '';
            } else
            {
                $fieldallocatedmarker = ', auf.allocatedmarker';
                $joinallocatedmarker = ' JOIN {assign} a ON s.assignment=a.id';
                // получаем записи с отключенным режимом оценщиков (или этапов оценивания)
                // либо те, где оценщик не назначен
                // либо те, где указанный пользователь является оценщиком
                $selectallocatedmarker = 'AND (a.markingworkflow=0 OR a.markingallocation=0 OR (auf.allocatedmarker IS NULL OR auf.allocatedmarker = 0 OR auf.allocatedmarker = :userid))';
            }
    
            $sql = 'SELECT s.userid, s.timemodified' . $fieldallocatedmarker . '
                      FROM {assign_submission} s
                 LEFT JOIN {assign_grades} g
                        ON s.assignment = g.assignment
                       AND s.userid = g.userid
                       AND g.attemptnumber = s.attemptnumber
                      JOIN (' . $esql . ') e
                        ON e.id = s.userid
                      ' . $groupsql . '
                      ' . $joinallocatedmarker . '
                 LEFT JOIN {assign_user_flags} auf
                        ON s.assignment = auf.assignment
                       AND s.userid = auf.userid
                     WHERE s.latest = 1
                       AND s.assignment = :assignid
                       AND s.timemodified IS NOT NULL
                       AND s.status = :submitted';
            
            if ( $assignmentobject->get_instance()->grade < 0 )
            {
                $sql .= ' AND (s.timemodified >= g.timemodified OR g.timemodified IS NULL OR g.grade IS NULL OR g.grade = -1)';
            } else
            {
                $sql .= ' AND (s.timemodified >= g.timemodified OR g.timemodified IS NULL OR g.grade IS NULL)';
            }
            $sql .= $selectallocatedmarker;
            
            $records = $DB->get_records_sql($sql, $params);
            
            if( ! empty($records) )
            {
                $num = 0;
                foreach($records as $record)
                {
                    $element = new stdClass();
                    //перевод названия модуля на местный язык
                    $element->type = $strassignment;
                    if( $this->groupid )
                    {// если указана конкретная группа - то покажем задания только для нее
                        $element->name    = '<a title="'.$strassignment.'" href="'.$CFG->wwwroot.
                        '/mod/assign/view.php?id='.$assignment->coursemodule.'&group='.$this->groupid.'">'.
                        $assignment->name.'</a>';
                    } else
                    {// группа не указана - покажем просто ссылку на просмотр выполненных заданий
                        $element->name    = '<a title="'.$strassignment.'" href="'.$CFG->wwwroot.
                        '/mod/assign/view.php?id='.$assignment->coursemodule.'&action=grading'.'">'.
                        $assignment->name.'</a>';
                    }
                    if( false )
                    {// @todo вставить сюда проверку настройки "разрешить быструю проверку"
                        $element->name .= $this->get_fast_check_link($record->userid, $num, $assignment->coursemodule);
                    }
    
                    // ФИО ученика
                    $element->student = fullname($DB->get_record('user', ['id' => $record->userid]));
                    $element->student .= $this->get_link_for_grading($record->userid, $assignment->coursemodule);
                    // время выполнения задания
                    //$element->time    = date("d.m.y", $val->timemodified);
                    $element->time    = $record->timemodified;
                    // добавляем непроверенный элемент к итоговому массиву
                    $result[] = $element;
                }
            }
        }
        return $result;
    }
    
    /**
     * Получить ссылку на оценивание конкретного ответа
     * @param int $userid идентификатор пользователя
     * @param int $assignmentid идентификатор модуля курса
     * @return string ссылка с классом btn, обернутая в блок
     */
    protected function get_link_for_grading($userid, $assignmentid)
    {
        $params = [
            'id' => $assignmentid,
            'rownum' => 0,
            'action' => 'grader',
            'userid' => $userid
        ];
        $url = new moodle_url('/mod/assign/view.php', $params);
        return html_writer::div(html_writer::link($url, get_string('gradeoutofhelp', $this->get_instance_name()), ['class' => 'btn btn-primary']));
    }
    
    /** Получить ссылку на быструю проверку задания
     * @todo добавить эту функцию в код, когда она будет готова
     *
     * @return
     * @param object $userid
     * @param object $offset
     * @param object $assignmentid
     */
    protected function get_fast_check_link($userid, $offset, $assignmentid)
    {
        global $CFG;
        return '<a onclick="'.$this->get_checknow_onclick($val->userid, $num, $assignment->coursemodule).
        '" href="'.$CFG->wwwroot.
        '/mod/assignment/submissions.php?id='.$assignment->coursemodule.
        '&userid='.$val->userid.'&mode=single&offset='.$num.'">a</a>';
    }
    
    /** Получить JS для открытия окна для проверки задания
     *
     * @todo подключить эти функцию когда будет готова быстрая проверка
     * @param int $userid
     * @param int $offset
     * @param int $assignmentid
     * @return string
     */
    protected function get_checknow_onclick($userid, $offset, $assignmentid)
    {
        return "this.target='grade{$userid}';".
            "return openpopup('/mod/assignment/submissions.php?id={$assignmentid}&userid={$userid}&mode=single&offset={$offset}',".
            "'grade{$userid}', 'menubar=0,location=0,scrollbars,resizable,width=780,height=600', 0);";
    }
    
    /**
     * Получить неопбуликованные задания с выставленными оценками
     * @return array
     */
    public function get_notreleased_assignments() {
        global $DB;
        $sql = "SELECT submission.id, a.id AS assign, g.grade, gg.finalgrade, a.name AS assignname, cm.id AS cmid,
                       a.course AS courseid, c.fullname AS coursefullname, c.summary, c.summaryformat,
                       " . $DB->sql_fullname('u.firstname', 'u.lastname') . " AS userfullname, u.id AS userid,
                       " . $DB->sql_fullname('u1.firstname', 'u1.lastname') . " AS allocatedmarkerfullname, uf.allocatedmarker
                 FROM {assign_submission} submission
                 JOIN {user} u ON u.id = submission.userid
                 JOIN {assign} a ON a.id = submission.assignment AND submission.userid = u.id
                 JOIN {assign_grades} g ON g.assignment = a.id AND submission.userid = g.userid AND submission.attemptnumber = g.attemptnumber
            LEFT JOIN {assign_user_flags} uf ON uf.assignment = a.id AND uf.userid = g.userid
                 JOIN {course_modules} cm ON cm.course = a.course AND cm.instance = a.id
                 JOIN {modules} md ON md.id = cm.module AND md.name = 'assign'
                 JOIN {course} c ON c.id = a.course
            LEFT JOIN {user} u1 ON u1.id = uf.allocatedmarker
                 JOIN {grade_items} gri ON gri.iteminstance = a.id AND gri.itemtype = 'mod' AND gri.itemmodule = 'assign' AND gri.itemnumber = 0
                 JOIN {grade_grades} gg ON gri.id = gg.itemid AND gg.userid = submission.userid
                WHERE uf.workflowstate != :wfreleased AND g.grade > 0 AND a.markingworkflow = 1 AND submission.latest = 1
                      AND gg.finalgrade IS NULL
             ORDER BY c.fullname, a.name, userfullname";
        
        $params = [
            'wfreleased' => ASSIGN_MARKING_WORKFLOW_STATE_RELEASED,
        ];
        return $DB->get_records_sql($sql, $params);
    }
}
?>