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
 * Functions and classes for mylearninghistory block
 *
 * @package   block_mylearninghistory
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace block_mylearninghistory\local;

defined('MOODLE_INTERNAL') || die();

use context_course;
use context_system;
use course_enrolment_manager;
use html_table;
use html_table_cell;
use html_table_row;
use html_writer;
use local_learninghistory\local\enrol_manager;
use local_learninghistory\local\grades_manager;
use moodle_page;
use moodle_url;
use local_learninghistory\local\completion_tracker;
use core_competency\api;
use otcomponent_customclass\utils;

require_once($CFG->dirroot . '/group/lib.php');
require_once($CFG->dirroot . '/enrol/locallib.php');
require_once($CFG->libdir . '/completionlib.php');
require_once($CFG->libdir . '/gradelib.php');
require_once($CFG->libdir . '/grade/grade_item.php');

/**
 * Utilities is helper class
 *
 * @package   block_mylearninghistory
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class utilities {

    public static function display_links($userid = 0)
    {
        $linkdescription = get_string('linktointerface', 'block_mylearninghistory');
        $params = [];
        if (!empty($userid)) {
            $params = ['uid' => $userid];
        }
        $url = new moodle_url('/local/learninghistory/index.php', $params);
        $link = html_writer::link($url, $linkdescription, ['class'=>'btn btn-primary', 'role' => 'button']);
        return html_writer::div($link, 'learninghistorylinks');
    }

    /**
     * Получить статистику по активным курсам пользователя
     *
     * @param integer $userid - ID пользователя
     * @return array
     */
    public static function get_course_stats($userid = 0)
    {
        // Получение активных курсов
        $courses = enrol_manager::get_active_courses($userid);
        if ( empty($courses) )
        {// Нет активных курсов
            return [];
        }

        foreach ($courses as $id => $course)
        {
            $coursecontext = context_course::instance($course->id);
            if( ! $course->visible and ! has_capability('moodle/course:viewhiddencourses', $coursecontext) )
            {// Курс скрыт и нет прав видеть скрытый курс
                continue;
            }
            // Добавим оценки пользователя к курсам
            $grade = grades_manager::get_course_finalgrade($course, $userid);
            $courses[$id]->_finalgrade = self::get_course_finalgrade($course, $grade);

            //добавим максимально возможную оценку за курс
            $maxgrade = self::get_course_maxgrade($course, $userid);
            $courses[$id]->_maxgrade = $maxgrade;

            //добавим процент завершения курса
            $completiontracker = new completion_tracker($course);
            $completion = $completiontracker->get_user_completion_all($userid);
            if($completion AND $completion->criteriacount > 0)
            {
                $courses[$id]->_progress = (int)$completion->percentcompleted;
            }
            else
            {
                $courses[$id]->_progress = get_string('progressdoesnttracking', 'block_mylearninghistory');
            }

            // завершен ли курс пользователем
            $course_status = new \completion_info($course);
            $courses[$id]->_completed = $course_status->is_course_complete($userid);


            $page = new moodle_page();
            $page->set_pagelayout('admin');
            $manager = new course_enrolment_manager($page, $course);

            // Добавим количество студентов в курсе
            $totalusers = $manager->get_total_users();
            $courses[$id]->_totalusers = empty($totalusers) ? get_string('nograde', 'block_mylearninghistory') : $totalusers;

            // родительские категории курса
            $courses[$id]->_coursecats = self::get_course_categories($course);

            // Имя родительской категории
            $courses[$id]->_parentcatname = $courses[$id]->_coursecats[count($courses[$id]->_coursecats)-1];

            if (api::is_enabled()){
                list($competencieslink, $competenciespercent) = self::get_course_competencies_info($course, $userid);
                // ссылка на просмотр пользовательских компетенций
                $courses[$id]->_competencieslink = $competencieslink;
                $courses[$id]->_competenciespercent = $competenciespercent;
            }

            //добавим даты окончания подписок
            $userenrolments = $manager->get_user_enrolments($userid);
            $ueenddates=array();
            //будем проверять каждую из подписок пользователя
            foreach($userenrolments as $userenrolment) {
                //если понадобится выводить роль, знай, что в mdl_enrol может храниться не то что надо
                $ueenddates[]=$userenrolment->enrolmentinstancename.": ".
                        ($userenrolment->timeend == 0 ? get_string('ueenddatenolimit', 'block_mylearninghistory') : userdate($userenrolment->timeend, get_string('strftimerecent')) );
            }
            $ueenddates='<div>'.implode('</div><div>', $ueenddates).'</div>';
            $courses[$id]->_ueenddates=$ueenddates;
        }
        return $courses;
    }

    public static function get_course_stats_view($userid = 0, $limit = 0)
    {
        $coursestatsview = self::get_course_stats_view_separate($userid,$limit);
        return $coursestatsview['my_studcourses'].$coursestatsview['my_teachcourses'];
    }

    /**
     * Является ли пользователь студентом в переданном контексте
     *
     * @param int $userid
     * @param context $context
     *
     * @return boolean
     */
    protected static function is_student($userid, $context)
    {
        global $CFG;

        //является ли пользователь студентом в курсе
        $result = false;

        //пробегаемся по оцениваемым ролям
        foreach( explode(",", $CFG->gradebookroles) as $gradebookrole)
        {
            // проверяем есть ли оцениваемая роль у пользователя в контексте курса
            if ( user_has_role_assignment($userid, $gradebookrole, $context->id) )
            {
                //данная роль является оцениваемой, пользователь - студент
                $result = true;
                //выходим из цикла
                break;
            }
        }

        return $result;
    }

    /**
     * Получить отформатированную строку с оценкой
     *
     * @param object $course
     * @param float $finalgrade
     *
     * @return string
     */
    protected static function get_course_finalgrade($course, $finalgrade)
    {
        if (empty($finalgrade)) {
            $grade = get_string('nograde', 'block_mylearninghistory');
        }
        else
        {// Вытаскиваем оценку за курс

            // Получаем grade_item
            $gradeitem = new \grade_item([
                'courseid' => $course->id,
                'itemtype' => 'course'
            ], true);

            // Получаем отформатированную оценку
            $grade = grade_format_gradevalue($finalgrade, $gradeitem, true, null, 0);

            // Если оценка - число, округляем, иначе оставляем как есть
            if ( is_numeric($grade) )
            {
                $grade = round($grade);
            }
        }
        return $grade;
    }

    /**
     * Получить список родительских категорий курса
     *
     * @param object $course
     *
     * @return string[]
     */
    protected static function get_course_categories($course)
    {
        $coursecategories = [];
        $coursecat = \core_course_category::get($course->category, MUST_EXIST, true);
        if (!empty($coursecat->path))
        {
            $coursecats = explode('/', $coursecat->path);
            foreach($coursecats as $categoryid)
            {
                if( is_number($categoryid) )
                {
                    $coursecategories[] = \core_course_category::get($categoryid, MUST_EXIST, true)->name;
                }
            }
        }
        return $coursecategories;
    }

    /**
     * Получить сведения о компетенциях, освоенных пользователем в курсе
     *
     * @param object $course
     * @param int $userid
     *
     * @return number[]|string[]
     */
    protected static function get_course_competencies_info($course, $userid)
    {
        $coursecompetencies = 0;
        $proficientcompetencies = 0;
        $coursecompetencies = api::count_competencies_in_course($course->id);
        $proficientcompetencies = api::count_proficient_competencies_in_course_for_user($course->id, $userid);
        $competenciesurl = new \moodle_url(
            '/admin/tool/lp/coursecompetencies.php',
            [
                'courseid'=>$course->id
            ]
        );
        $competencieslink = html_writer::link(
            $competenciesurl,
            $proficientcompetencies . "/" . $coursecompetencies,
            [
                'class' => 'block_mylearninghistory_progressbar_inner'
            ]
        );
        $competenciespercent = 0;
        if( $coursecompetencies > 0 )
        {
            $competenciespercent = $proficientcompetencies * 100 / $coursecompetencies;
        }
        return [$competencieslink, $competenciespercent];
    }

    /**
     * Получить отформатированную строку, отражающую прогресс прохождения курса
     *
     * @param object $course
     *
     * @return string
     */
    protected static function get_course_progress_label($course)
    {
        if(is_number($course->_progress))
        {
            $courseprogresslabel = $course->_progress."%";
            if ( (int)$course->_progress == 100 )
            {
                $courseprogresslabel = get_string('course_completed','block_mylearninghistory');
            }
        } else
        {
            $courseprogresslabel = $course->_progress;
        }

        return $courseprogresslabel;
    }

    /**
     * Является ли пользователь преподавателем (контакт курса) в переданном контексте
     *
     * @param int $userid
     * @param context $context
     *
     * @return boolean
     */
    protected static function is_teacher($userid, $context)
    {
        global $CFG;

        $result = false;

        // Роли, которые являются контактами курсов
        $coursecontacts = explode(',', $CFG->coursecontact);
        $userroles = get_user_roles($context, $userid);

        if ( ! empty($userroles))
        {
            foreach ($userroles as $role)
            {
                if ( in_array($role->roleid, $coursecontacts) )
                {
                    $result = true;
                    break;
                }
            }
        }

        return $result;
    }

    /**
     * Получение html-кода таблицы раздела "я преподаю"
     *
     * @param array $coursesrows - массив строк таблицы
     *
     * @return string - html-код
     */
    protected static function get_teaching_view($coursesrows)
    {
        $html = '';

        $configdata=[
            'teaching_enrolscount' => get_config('block_mylearninghistory', 'teaching_enrolscount'),
            'teaching_enroldata' => get_config('block_mylearninghistory', 'teaching_enroldata'),
        ];

        if (!empty($coursesrows))
        {
            // Формируем таблицу "Я преподаю"
            $table = new html_table();
            $table->width = '100%';
            $table->size = ['50%'];
            $table->align = ['left'];
            $table->head = [html_writer::span(
                get_string('course', 'block_mylearninghistory')
            )];

            if ( $configdata['teaching_enrolscount'] == 1 )
            {
                $table->size[] = '25%';
                $table->align[] = 'center';
                $table->head[] = html_writer::span(
                    get_string('enrolscount', 'block_mylearninghistory')
                );
            }
            if ( $configdata['teaching_enroldata'] == 1 )
            {
                $table->size[] = '25%';
                $table->align[] = 'right';
                $table->head[] = html_writer::span(
                    get_string('ueenddate', 'block_mylearninghistory')
                );
            }
            $table->data = $coursesrows;

            $html .= html_writer::table($table);

        }
        return $html;
    }

    /**
     * Получение html-кода таблицы раздела "я изучаю"
     *
     * @param array $coursesrows - массив строк таблицы
     *
     * @return string - html-код
     */
    protected static function get_learning_view($coursesrows)
    {
        $html = '';
        $tableclasses = [];

        $configdata=[
            'learning_grade' => get_config('block_mylearninghistory', 'learning_grade'),
            'learning_competencies' => get_config('block_mylearninghistory', 'learning_competencies'),
            'learning_progress' => get_config('block_mylearninghistory', 'learning_progress'),
            'learning_enroldata' => get_config('block_mylearninghistory', 'learning_enroldata'),
            'learning_grade_view' => get_config('block_mylearninghistory', 'learning_grade_view'),
        ];

        $tableclasses[] = 'grade' . ($configdata['learning_grade_view'] ? $configdata['learning_grade_view'] : 'overflowhidden');
        $tableclasses[] = 'generaltable';

        if (!empty($coursesrows))
        {
            // Формируем таблицу "Я изучаю"
            $table = new html_table();
            $table->attributes['class'] = implode(' ', $tableclasses);
            $table->width = '100%';
            $table->size = ['40%'];
            $table->align = ['left'];
            $table->head = [ html_writer::span(get_string('course', 'block_mylearninghistory')) ];
            if ( $configdata['learning_grade'] == 1 )
            {
                $table->size[] = '10%';
                $table->align[] = 'center';
                $table->head[] = html_writer::span(
                    get_string('rating', 'block_mylearninghistory')
                );
            }
            if ( $configdata['learning_competencies'] == 1 && api::is_enabled())
            {
                $table->size[] = '10%';
                $table->align[] = 'center';
                $table->head[] = html_writer::span(
                    get_string('competencies', 'block_mylearninghistory')
                );
            }
            if ( $configdata['learning_progress'] == 1 )
            {
                $table->size[] = '15%';
                $table->align[] = 'center';
                $table->head[] = html_writer::span(
                    get_string('progress', 'block_mylearninghistory')
                );
            }
            if ( $configdata['learning_enroldata'] == 1 )
            {
                $table->size[] = '25%';
                $table->align[] = 'right';
                $table->head[] = html_writer::span(
                    get_string('ueenddate', 'block_mylearninghistory')
                );
            }
            // Таблица с активными курсами
            $table->data = $coursesrows;

            $html .= html_writer::table($table);
        }

        return $html;
    }

    /**
     * Получение кода для отображения изучаемых, пройденных и преподаваемых курсов
     *
     * @param context $pagecontext - контекст страницы для проверки прав
     * @param number $userid - идентификатор пользователя
     * @param number $limit - ограничение количества курсов для отображения
     *
     * @return string - html-код
     */
    public static function get_course_stats_view_separate($pagecontext, $userid = 0, $limit = 0)
    {
        global $DB, $CFG, $USER;

        if ( empty($userid) )
        {
            $userid = $USER->id;
        }

        if( $userid == $USER->id )
        {
            //пользователь просматривает свои данные
            $learninghistorycapability = has_capability('local/learninghistory:viewmylearninghistory', $pagecontext);
            $mylearninghistorycapability = has_capability('block/mylearninghistory:viewmylearninghistory', $pagecontext);
        }
        else
        {
            //пользователь просматривает чужие данные
            $learninghistorycapability = has_capability('local/learninghistory:viewuserslearninghistory', $pagecontext);
            $mylearninghistorycapability = has_capability('block/mylearninghistory:viewuserslearninghistory', $pagecontext);
        }

        $learninghistorylink = '';
        //если есть право на просмотр истории обучения в локальном плагине - отобразим ссылку на ее просмотр
        if($learninghistorycapability)
        {
            $learninghistorylink = self::display_links($userid);
        }

        if (!$mylearninghistorycapability)
        {
            return $learninghistorylink .  html_writer::tag('p', get_string('accessdenied', 'block_mylearninghistory'));
        }

        $courses = self::get_course_stats($userid);

        $lfilteredcourses = self::get_filtered_courses($courses, 'learning');
        $tfilteredcourses = self::get_filtered_courses($courses, 'teaching');

        if ( $limit > 0 && count($lfilteredcourses) > $limit)
        {
            $lfilteredcourses = array_slice($lfilteredcourses, 0, $limit, true);
        }
        if ( $limit > 0 && count($tfilteredcourses) > $limit)
        {
            $tfilteredcourses = array_slice($tfilteredcourses, 0, $limit, true);
        }

        $learning = [];
        $learningspoiled = [];
        $teaching = [];

        $configdata=[
            'learning_grade' => get_config('block_mylearninghistory', 'learning_grade'),
            'learning_competencies' => get_config('block_mylearninghistory', 'learning_competencies'),
            'learning_progress' => get_config('block_mylearninghistory', 'learning_progress'),
            'learning_enroldata' => get_config('block_mylearninghistory', 'learning_enroldata'),
            'teaching_enrolscount' => get_config('block_mylearninghistory', 'teaching_enrolscount'),
            'teaching_enroldata' => get_config('block_mylearninghistory', 'teaching_enroldata'),
            'max_grade' => get_config('block_mylearninghistory', 'max_grade'),
            'view_type' => get_config('block_mylearninghistory', 'view_type'),
            'learning_group_by' => get_config('block_mylearninghistory', 'learning_group_by'),
            'teaching_group_by' => get_config('block_mylearninghistory', 'teaching_group_by'),
            'learning_course_link_url' => get_config('block_mylearninghistory', 'learning_course_link_url'),
            'teaching_course_link_url' => get_config('block_mylearninghistory', 'teaching_course_link_url'),
        ];

        // Формирование ссылок в блоке "Я изучаю"
        switch($configdata['learning_course_link_url'])
        {
            case 'crw':
                $lcourseurl = '/local/crw/course.php';
                break;
            case 'course':
            default:
                $lcourseurl = '/course/view.php';
                break;
        }
        // Формирование ссылок в блоке "Я преподаю"
        switch($configdata['teaching_course_link_url'])
        {
            case 'crw':
                $tcourseurl = '/local/crw/course.php';
                break;
            case 'course':
            default:
                $tcourseurl = '/course/view.php';
                break;
        }

        foreach ($lfilteredcourses as $course) {
            $context = context_course::instance($course->id);

            // Пользователь является студентом в курсе
            if( self::is_student($userid, $context)
                && ($course->visible || has_capability('moodle/course:viewhiddencourses', $context)) )
            {
                // Наименование курса
                $link = html_writer::link(
                    new moodle_url($lcourseurl, ['id' => $course->id]),
                    \html_writer::span($course->fullname, 'has_before_icon'),
                    [ 'title' => implode(' / ', $course->_coursecats) ]
                );

                $coursename = new html_table_cell($link);
                $rowdata=[$coursename];

                // Оценка за курс
                if ( $configdata['learning_grade'] == 1 )
                {
                    $gradehtml = html_writer::div(
                        $course->_finalgrade,
                        'lh-grade-current'
                    );

                    // Максимально возможная оценка за курс
                    if ( $configdata['max_grade'] == 1 )
                    {
                        $gradedividerhtml = html_writer::div("", 'lh-grade-divider');
                        $maxgradehtml = html_writer::div($course->_maxgrade, 'lh-grade-max');
                        $gradehtml .= $gradedividerhtml . $maxgradehtml;
                    }

                    $rowdata[] = new html_table_cell(\html_writer::link(
                        new moodle_url('/grade/report/user/index.php', ['id' => $course->id]),
                        $gradehtml,
                        ['class' => 'lh-grade'])
                    );
                }

                // Освоенные компетенции
                if ( $configdata['learning_competencies'] == 1 && api::is_enabled())
                {
                    $rowdata[] = html_writer::div(
                        $course->_competencieslink,
                        'block_mylearninghistory_progressbar',
                        [
                            'data-percent' => $course->_competenciespercent
                        ]
                    );
                }

                // Процент завершения курса
                if ( $configdata['learning_progress'] == 1 )
                {
                    $progressbarinner = \html_writer::div(
                        self::get_course_progress_label($course),
                        'block_mylearninghistory_progressbar_inner'
                    );

                    $rowdata[] = new html_table_cell(\html_writer::div(
                        $progressbarinner,
                        'block_mylearninghistory_progressbar',
                        [
                            'data-percent' => $course->_progress
                        ]
                    ));
                }

                // Информация о подписке
                if ( $configdata['learning_enroldata'] == 1 )
                {
                    $ueenddates = new html_table_cell($course->_ueenddates);
                    $rowdata[] = $ueenddates;
                }

                if ($course->_completed && $configdata['view_type'] == 1)
                {
                    switch((int)$configdata['learning_group_by'])
                    {
                        case '1':
                            // группировка по названию категории
                            $learningspoiled[$course->_parentcatname][] = new html_table_row($rowdata);
                            break;
                        case '0':
                        default:
                            // нет никакой группировки
                            $learningspoiled['default'][] = new html_table_row($rowdata);
                    }
                } else
                {
                    switch((int)$configdata['learning_group_by'])
                    {
                        case '1':
                            // группировка по названию категории
                            $learning[$course->_parentcatname][] = new \html_table_row($rowdata);
                            break;
                        case '0':
                        default:
                            // нет никакой группировки
                            $learning['default'][] = new \html_table_row($rowdata);
                    }
                }
            }
        }

        foreach ($tfilteredcourses as $course) {
            $context = context_course::instance($course->id);

            // Пользователь является преподавателем (контакты курса) в курсе
            if (self::is_teacher($userid, $context)
                && ($course->visible || has_capability('moodle/course:viewhiddencourses', $context)))
            {
                // Наименование курса
                $link = html_writer::link(
                    new moodle_url($tcourseurl, [ 'id' => $course->id ]),
                    \html_writer::span($course->fullname, 'has_before_icon'),
                    [ 'title' => implode(' / ', $course->_coursecats) ]
                );
                $rowdata=[new html_table_cell($link)];

                // Количество пользователей
                if ( $configdata['teaching_enrolscount'] == 1 )
                {
                    $rowdata[] = new html_table_cell(\html_writer::link(
                        new moodle_url('/user/index.php', [ 'id' => $course->id ]),
                        $course->_totalusers
                    ));
                }

                // Информация о подписке
                if ( $configdata['teaching_enroldata'] == 1 )
                {
                    $rowdata[] = new html_table_cell($course->_ueenddates);
                }



                switch((int)$configdata['teaching_group_by'])
                {
                    case '1':
                        // группировка по названию категории
                        $teaching[$course->_parentcatname][] = new html_table_row($rowdata);
                        break;
                    case '0':
                    default:
                        // нет никакой группировки
                        $teaching['default'][] = new html_table_row($rowdata);
                }
            }
        }


        $html = '';

        if (!empty($learning) || !empty($learningspoiled))
        {
            $html .= html_writer::tag('h2', get_string('my_studcourses', 'block_mylearninghistory'));
            if (!empty($learning))
            {
                ksort($learning);
                foreach($learning as $category => $courses)
                {
                    if ((int)$configdata['learning_group_by'] > 0)
                    {
                        // имеется группировка, выведем заголовок для группы
                        $html .= html_writer::tag('h3', $category);
                    }
                    $html .= self::get_learning_view($courses);
                }
            }
            if (!empty($learningspoiled))
            {
                $html .= html_writer::tag('a',
                    get_string('my_studcourses_completed', 'block_mylearninghistory'),
                    [
                        'data-toggle' => 'collapse',
                        'data-target' => '#html_student_courses_completed',
                        'id' => 'html_student_courses_completed_header',
                        'class' => 'collapsed button btn btn-primary'
                    ]
                );

                $spoiledhtml = '';
                ksort($learningspoiled);
                foreach($learningspoiled as $category => $courses)
                {
                    if ((int)$configdata['learning_group_by'] > 0)
                    {
                        // имеется группировка, выведем заголовок для группы
                        $spoiledhtml .= html_writer::tag('h3', $category);
                    }
                    $spoiledhtml .= self::get_learning_view($courses);
                }
                $html .= \html_writer::div($spoiledhtml, '', [
                    'class' => 'collapse',
                    'id' => 'html_student_courses_completed'
                ]);
            }
        }
        $html .= $learninghistorylink;
        if (!empty($teaching))
        {
            $html .= html_writer::tag('h2', get_string('my_teachcourses', 'block_mylearninghistory'));

            ksort($teaching);
            foreach($teaching as $category => $courses)
            {
                if ((int)$configdata['teaching_group_by'] > 0)
                {
                    // имеется группировка, выведем заголовок для группы
                    $html .= html_writer::tag('h3', $category);
                }
                $html .= self::get_teaching_view($courses);
            }
        }

        return $html;
    }


    /**
     * Получить максимально возможную оценку за курс
     *
     * @param object $course
     * @return boolean|string максимально возможная оценка за курс или false в случае неудачи
     */
    public static function get_course_maxgrade($course) {
        global $DB;
        $gradeparams = [
                'courseid' => $course->id,
                'itemtype' => 'course'
        ];
        $gradeitem = new \grade_item($gradeparams, true);

        $grademaxval = grade_format_gradevalue($gradeitem->grademax, $gradeitem, true, null, 0);

        return $grademaxval;
    }

    /**
     * Check access rights to see the block content
     *
     * @param int $userid current user id
     * @return bool true if have rights to see contents
     */
    public static function is_access($userid = 0) {
        global $DB, $USER, $COURSE;
        if (empty($userid)) {
            $userid = $USER->id;
        }
        if (!isloggedin() || isguestuser())
        {
            return false;
        }
        return true;
   }

   /**
    * Фильтрация курсов
    * @param array $courses массив курсов
    * @param string $section секция отображения курсов
    */
   public static function get_filtered_courses($courses, $section) {
       global $DB;
       $filterformdatajson = get_config('block_mylearninghistory', $section . '_courses_filter');
       if (empty($filterformdatajson)) {
           // Настройки фильтрации не заданы
           return $courses;
       }
       $filterrulesformdatajson = get_config('block_mylearninghistory', $section . '_courses_filter_rules');
       if (empty($filterrulesformdatajson)) {
           // Настройки правил фильтрации не заданы
           return $courses;
       }
       $filter = json_decode($filterformdatajson);
       $filterrules = json_decode($filterrulesformdatajson);
       $parameters = $sqlpieces = [];

       $customcoursefields = get_config('local_crw', 'custom_course_fields');
       if (!empty($customcoursefields)) {
           $result = utils::parse($customcoursefields);
           if ($result->is_form_exists()) {
               // Форма
               $customform = $result->get_form();
               // Кастомные поля формы
               $cffields = $customform->get_fields();

               foreach ($cffields as $fieldname => $cffield) {
                   if (isset($filterrules->{'filter_field_' . $fieldname})) {
                       if (isset($filter->{$fieldname}) && $filter->{$fieldname} != '') {
                           list($condition, $params) = self::build_condition(
                               $filterrules->{'filter_rule_' . $fieldname}, $fieldname, $filter->{$fieldname});
                           $sqlpieces[] = " c.id IN
                                            ( SELECT DISTINCT c.id
                                                         FROM {crw_course_properties} cprop
                                                   RIGHT JOIN {course} c
                                                           ON cprop.courseid = c.id
                                                        WHERE " . $condition . "
                                             ) ";
                           $parameters = array_merge($parameters, $params);
                       }
                   }
               }
               if (!empty($sqlpieces)) {
                   $where = implode(' AND ', $sqlpieces);
                   $sql = 'SELECT c.id
                             FROM {course} c
                            WHERE c.id != :siteid AND ' . $where;
                   $parameters['siteid'] = SITEID;
                   // Получить курсы
                   $filteredcourses = $DB->get_records_sql($sql, $parameters);
                   if (empty($filteredcourses)) {
                       $courses = [];
                   } else {
                       foreach ($courses as $course) {
                           if (!array_key_exists($course->id, $filteredcourses)) {
                               unset($courses[$course->id]);
                           }
                       }
                   }
               }
           }
       }
       return $courses;
   }

   /**
    * Получить часть sql-запроса и параметры для поиска курсов по кастомным полям
    * @param string $rule правило сравнения
    * @param string $fieldname имя кастомного поля
    * @param mixed $value значение, к которому применяется правило сранения
    * @return array[]|string[] массив [$sql, $params], где $params - массив именованных параметров, $sql - часть зарпоса
    */
   private static function build_condition($rule, $fieldname, $value) {
       global $DB;
       $condition = '';
       $params = [];
       switch ($rule) {
           case 'like':
               $condition = $DB->sql_like('cprop.svalue', ':cff_' . $fieldname);
               $params = ['cff_' . $fieldname => '%' . $value . '%'];
               break;
           case 'notlike':
               $condition = $DB->sql_like('cprop.svalue', ':cff_' . $fieldname, true, true, true);
               $params = ['cff_' . $fieldname => '%' . $value . '%'];
               break;
           case 'equal':
               $condition = 'cprop.svalue = :cff_' . $fieldname;
               $params = ['cff_' . $fieldname => $value];
               break;
           case 'notequal':
               $condition = 'cprop.svalue != :cff_' . $fieldname;
               $params = ['cff_' . $fieldname => $value];
               break;
           case 'graterorequal':
               $condition = 'cprop.svalue >= :cff_' . $fieldname;
               $params = ['cff_' . $fieldname => $value];
               break;
           case 'lessorequal':
               $condition = 'cprop.svalue <= :cff_' . $fieldname;
               $params = ['cff_' . $fieldname => $value];
               break;
           case 'grater':
               $condition = 'cprop.svalue > :cff_' . $fieldname;
               $params = ['cff_' . $fieldname => $value];
               break;
           case 'less':
               $condition = 'cprop.svalue < :cff_' . $fieldname;
               $params = ['cff_' . $fieldname => $value];
               break;
           case 'in':
               list($sqlin, $params) = $DB->get_in_or_equal($value, SQL_PARAMS_NAMED);
               $condition = 'cprop.svalue ' . $sqlin;
               break;
           case 'notin':
               list($sqlin, $params) = $DB->get_in_or_equal($value, SQL_PARAMS_NAMED, 'param', false);
               $condition = 'cprop.svalue ' . $sqlin;
               break;
           default:
               break;
       }
       if (!empty($condition)) {
           $condition = '(cprop.name = \'cff_' . $fieldname . '\' AND ' . $condition . ')';
       }
       return [$condition, $params];
   }
}
