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
 * Activetime report renderer.
 *
 * @package    report_activetime
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use local_learninghistory\local\utilities;
use local_learninghistory\attempt\attempt_base;
use local_learninghistory\attempt\mod\attempt_mod_assign;
use local_learninghistory\attempt\mod\attempt_mod_quiz;
use local_learninghistory\activetime;
use report_activetime\report_helper;

defined('MOODLE_INTERNAL') || die;

/**
 * Report activetime renderable class.
 *
 * @package    report_activetime
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once($CFG->dirroot . '/report/activetime/locallib.php');
require_once($CFG->libdir . '/gradelib.php');
require_once($CFG->libdir . '/grade/grade_item.php');

class report_activetime_renderable implements renderable {
    
    /** @var stdClass course record */
    public $course;
    /**
     * Тип подписок all|active|archive
     * @var string
     */
    public $enrolmode;
    /**
     * Тип модулей all|active|archive
     * @var string
     */
    public $modulemode;
    /**
     * Список идентификаторов пользователей
     * @var array
     */
    public $users;
    /**
     * Список страндартных полей пользователей
     * @var array
     */
    public $userfields;
    /**
     * Список кастомных полей пользователей
     * @var array
     */
    public $customfields;
    /**
     * Список идентификаторов модулей
     * @var array
     */
    public $modules;
    /**
     * Тип экспорта
     * @var string
     */
    public $exporttype;
    /**
     * Кеш отчета на уровне объекта отчета
     * @var stdClass
     */
    protected $cache;
    /**
     * Активности, которые необходимо вывести в отчет
     * @var array
     */
    protected $actions;
    /**
     * Объект класса activetime
     * @var local_learninghistory\activetime
     */
    protected $activetime;
    /**
     * Доступные форматы отчета
     * @var array
     */
    protected $availabledataformat;
    /**
     * Текущий формат отчета
     * @var string
     */
    protected $format;
    
    /**
     * Таблица отчета
     * @var html_table
     */
    protected $table;
    
    /**
     * Флаг, указывающий как собирать отчет:по всей системе или только внутри курса
     * @var bool
     */
    protected $global;
    
    /**
     * Максимальное число элементов в одном курсе
     * @var int
     */
    protected $maxelements;
    
    /**
     * Суммарное время по элементам, попавшим в отчет, для каждого пользователя
     * @var array
     */
    protected $modactivetimesumm;
    
    /**
     * Суммарное время по всем попыткам за элемент, попавшим в отчет
     * @var array
     */
    protected $activetimesummforattempts;
    
    /**
     * Ориентация данных по модулям курса в отчете
     * @var array
     */
    protected $dataorientation;

    /**
     * Конструктор
     * @param stdClass|int $course объект курса или идентификатор
     * @param string $enrolmode Тип подписок all|active|archive
     * @param string $modulemode Тип модулей all|active|archive
     * @param array $users Список идентификаторов пользователей
     * @param array $userfields Список полей пользователей
     * @param array $modules Список идентификаторов модулей
     */
    public function __construct($course = 0, $enrolmode = 'active', $modulemode = 'active', $users = [], $userfields = [], $modules = []) 
    {
        global $SITE;
        // Use site course id, if course is empty.
        $this->global = false;
        if( ! empty($course) && is_int($course) ) 
        {
            $this->course = get_course($course);
        } elseif( empty($course) )
        {
            $this->course = get_site();
            $this->global = true;
        } else 
        {
            $this->course = $course;
        }
        if( $this->course->id == $SITE->id )
        {
            $this->global = true;
        }
        $this->enrolmode = $enrolmode;
        $this->modulemode = $modulemode;
        $this->users = $users;
        $this->set_fields($userfields);
        $this->modules = $modules;
        $this->cache = new stdClass();
        if( ! isset($this->cache->cm) )
        {
            $this->cache->cm = [];
        }
        if( ! isset($this->cache->user) )
        {
            $this->cache->user = [];
        }
        if( ! isset($this->cache->course) )
        {
            $this->cache->course = [];
        }
        if( ! isset($this->cache->category) )
        {
            $this->cache->category = [];
        }
        if( ! isset($this->cache->activetime) )
        {
            $this->cache->activetime = [];
        }
        if( ! isset($this->cache->moduleblock) )
        {
            $this->cache->moduleblock = [];
        }
        $this->actions = ['module', 'activetime', 'attempts', 'completion', 'grade'];
        $this->activetime = new activetime($this->course->id);
        $this->availabledataformat = ['html', 'csv', 'xlsx', 'ods'];
        $this->format = 'html';
        $this->maxelements = 0;
        $this->modactivetimesumm = $this->activetimesummforattempts = [];
        $this->dataorientation = ['vertical', 'horizontal'];
    }
    
    /**
     * Установить тип экспорта отчета
     * @param string $type
     */
    public function set_export_type($type = 'csv')
    {
        $this->exporttype = $type;
    }
    
    /**
     * Отобразить отчет
     */
    public function display()
    {

        echo $this->render();
    }
    
    /**
     * Вернуть html представление отчета
     * @return string
     */
    public function render()
    {
        if( ! empty($this->table) )
        {
            $output = html_writer::table($this->table);
            $output .= html_writer::div(get_string('time_hint', 'report_activetime'));
            return $output;
        } else 
        {
            return get_string('no_report_data', 'report_activetime');
        }
    }
    
    /**
     * Получить данные для отчета на основе переданных параметров
     * @return array
     */
    protected function get_data()
    {
        global $DB;
        $params = $paramscustomfields = $paramsenrolmode = $paramsmodulemode = $paramsusers = $paramsmodules = $result = [];
        $enrolmodeselect = $modulemodeselect = $usersselect = $modulesselect = $courseselect = $userfieldsselect = $customfieldsselect = '';
        
        if( ! empty($this->customfields) )
        {
            foreach($this->customfields as $key => $values)
            {
                /**
                 * @TODO сейчас сравниваем то, что вписали в поле фильтра с тем, что лежит в базе.
                 * Нужно сравнивать то, что вписали в поле фильтра с display_data. 
                 * Поиск пользователей с такой display_data путем перебора ресурсоемкая операция, нужно другое решение.
                 */
                $paramscustomfields[] = substr($key, 14);
                foreach($values as $value)
                {
                    $customfieldsor[] = 'uid.data=?';
                    $paramscustomfields[] = $value;
                }
                $fieldsselectpart[] = '(uif.shortname=? AND ' . implode(' OR ', $customfieldsor) . ')';
            }
            $customfieldsselect = 'JOIN
                                   (SELECT uid.userid user FROM {user_info_data} uid
                                   JOIN {user_info_field} uif
                                   ON uid.fieldid=uif.id
                                   WHERE ' . implode(' AND ', $fieldsselectpart) . ' GROUP BY user) ucf
                                   ON ucf.user=u.id';
            $params = array_merge($params, $paramscustomfields);
        }
        
        if( ! empty($this->enrolmode) )
        {// Фильтрация по типу подписки: все, текущие, удаленные
            $enrolmode = $this->enrolmode == 'all' ? ['active', 'archive'] : [$this->enrolmode];
            list($insqlenrolmode, $paramsenrolmode) = $DB->get_in_or_equal($enrolmode);
            $params = array_merge($params, $paramsenrolmode);
            $enrolmodeselect = ' ll.status ' . $insqlenrolmode;
        }
        
        if( ! empty($this->modulemode) )
        {// Фильтрация по типу модулей: все, текущие, удаленные
            $modulemode = $this->modulemode == 'all' ? ['active', 'archive'] : [$this->modulemode];
            list($insqlmodulemode, $paramsmodulemode) = $DB->get_in_or_equal($modulemode);
            $params = array_merge($params, $paramsmodulemode);
            $modulemodeselect = ' AND llm.status ' . $insqlmodulemode;
        }
        
        if( in_array($this->modulemode, ['all', 'archive']) )
        {
            $coursemoduleselect = '';
            $cminstanceselect = '';
        } else 
        {
            $coursemoduleselect = 'JOIN {course_modules} cm
                                   ON cm.id=llcm.cmid';
            $cminstanceselect = ', cm.instance';
        }
        
        if( ! empty($this->users) )
        {// Фильтрация по пользователям
            list($insqlusers, $paramsusers) = $DB->get_in_or_equal($this->users);
            $params = array_merge($params, $paramsusers);
            $usersselect = ' AND u.id ' . $insqlusers;
        }
        
        if( ! empty($this->modules) )
        {// Фильтрация по модулям
            list($insqlmodules, $paramsmodules) = $DB->get_in_or_equal($this->modules);
            $params = array_merge($params, $paramsmodules);
            $modulesselect = ' AND llcm.cmid ' . $insqlmodules;
        }
        
        if( ! $this->global )
        {
            $params = array_merge($params, [$this->course->id]);
            $courseselect = ' AND ll.courseid = ?';
        }
        
        if( ! empty($this->userfields) )
        {// Фильтрация по полям
            foreach($this->userfields as $key => $values)
            {
                foreach($values as $value)
                {
                    $userfieldsor[] = 'u.' . $key . ' =?';
                    $paramsuserfields[] = $value;
                }
                $userfieldsselect .= ' AND (' . implode(' OR ', $userfieldsor) . ')';
            }
            $params = array_merge($params, $paramsuserfields);
        }
        $alternatenames = [
            'firstnamephonetic',
            'lastnamephonetic',
            'middlename',
            'alternatename'
        ];
        $ufields = report_activetime_get_userfields_list($alternatenames, false);
        // Поля, которые попадут в выборку
        $ufieldsselect = implode(',', array_map(function($str){return 'u.'.$str;}, $ufields));
        
        $sql = 'SELECT llcm.*, ' . $ufieldsselect . ',ll.courseid, ll.finalgrade as coursefinalgrade' . $cminstanceselect . ' 
            FROM {local_learninghistory_cm} llcm 
            JOIN {local_learninghistory} ll
            ON ll.id=llcm.llid
            JOIN {user} u
            ON u.id=llcm.userid ' .
            $coursemoduleselect . '
            JOIN {local_learninghistory_module} llm 
            ON llm.cmid=llcm.cmid ' .
            $customfieldsselect . '
            WHERE' . $enrolmodeselect .  
            $modulemodeselect . 
            $usersselect .
            $modulesselect . 
            $courseselect . 
            $userfieldsselect . '
            ORDER BY u.lastname ASC, u.firstname ASC, ll.courseid ASC, llcm.cmid ASC, llcm.attemptnumber ASC';
        
        $data = $DB->get_records_sql($sql, $params);
        
        if( ! empty($data) )
        {
            foreach($data as $row)
            {// Компановка данных в нужном виде
                $result[$row->userid][$row->courseid][$row->cmid] = $row;
                $this->maxelements = max($this->maxelements, count($result[$row->userid][$row->courseid]));
                if( isset($this->modactivetimesumm[$row->userid][$row->courseid]) )
                {
                    $this->modactivetimesumm[$row->userid][$row->courseid] += $row->activetime;
                } else 
                {
                    $this->modactivetimesumm[$row->userid][$row->courseid] = $row->activetime;
                }
                if( isset($this->activetimesummforattempts[$row->userid][$row->courseid][$row->cmid]) )
                {
                    $this->activetimesummforattempts[$row->userid][$row->courseid][$row->cmid] += $row->activetime;
                } else
                {
                    $this->activetimesummforattempts[$row->userid][$row->courseid][$row->cmid] = $row->activetime;
                }
            }
        }
        return $result;
    }

    /**
     * Получить таблицу для отображения отчета
     * @param array $data данные, полученные методом get_data()
     * @return html_table
     */
    protected function get_report_table_vertical($data)
    {
        global $CFG;
        if( file_exists($CFG->dirroot . '/blocks/dof/locallib.php') )
        {
            require_once($CFG->dirroot . '/blocks/dof/locallib.php');
            dof_hugeprocess();
        }
        
        $table = new html_table();
        $table->align = [];
        // Задаем класс для таблицы
        $table->attributes = ['class' => 'generaltable report_activetime_table'];
        // Получаем список полей, заданных настройками
        $availableuserfields = explode(',', get_config('report_activetime', 'userfields'));
        
        // Формируем тело таблицы по переданным данным
        // В зависимости от формата затребованного отчета таблица может меняться:
        // - в части объединения ячеек
        // - в части наличия/отстуствия пустых ячеек
        
        // Счетчик строк таблицы
        $count = 1;
        foreach($data as $userid => $courserow)
        {
            if( ! empty($courserow) )
            {
                foreach($courserow as $courseid => $userrow)
                {
                    // Формируем ссылку на курс
                    $course = $this->get_course($courseid);
                    $coursefullname = html_writer::link('/course/view.php?id=' . $courseid, $course->fullname);
                    // Оценка за курс
                    if( ! isset($current[$courseid]) )
                    {
                        $current[$courseid] = current($userrow);
                        if ( isset($current[$courseid]->coursefinalgrade) && is_string($current[$courseid]->coursefinalgrade) )
                        {// Итоговая оценка имеется
                            $coursegrade = floatval($current[$courseid]->coursefinalgrade);
                        } else
                        {
                            $coursegrade = '-';
                        }
                    }
                    // Формируем ссылку на категорию
                    $category = $this->get_category($course->category);
                    $categoryfullname = html_writer::link('/course/index.php?categoryid=' . $category->id, $category->name);
                    // Форматируем суммарное время по выбранным модулям курса
                    $modactivetimesumm = $this->get_formatted_interval(0, $this->modactivetimesumm[$userid][$courseid]);
                    // Получем объект activetime, для получения времени затраченного на изучение курса
                    $this->activetime = $this->get_activetime($courseid);
                    // Форматируем время затраченное на изучение курса
                    $courseactivetime = $this->get_formatted_interval(0, $this->activetime->get_current_activetime($userid));
                    foreach($this->actions as $action)
                    {
                        $tablerow = new html_table_row();
                        if( in_array($count%10, [1,2,3,4,5]) )
                        {// Закрашиваем строки таблицы 5 через 5
                            $tablerow->attributes['class'] = 'greyrow';
                        }
                        $count++;
                        // Получаем ссылку на профиль пользователя
                        $userfullname = html_writer::link('/user/profile.php?id=' . $userid, $this->get_user_fullname($userid));
                        switch($action)
                        {
                            case 'module':
                                $tablecell = new html_table_cell($userfullname);
                                $tablecell->attributes['class'] = 'username';
                                switch($this->format)
                                {
                                    case 'html':
                                    case 'xlsx':
                                    case 'ods':
                                        $tablecell->rowspan = 5;
                                        break;
                                    default:
                                        break;
                                }
                                $tablerow->cells[] = $tablecell;
                                
                                $fields = [];
                                if( ! empty($availableuserfields) )
                                {// Добавляем данные по полям
                                    foreach($availableuserfields as $availableuserfield)
                                    {
                                        if( ! empty($availableuserfield) )
                                        {
                                            if( strpos($availableuserfield, 'profile_field_') !== false )
                                            {
                                                $fieldvalue = $this->get_customfield_value(substr($availableuserfield, 14), $userid);
                                            } else
                                            {
                                                $fieldvalue = $this->cache->user[$userid]->record->$availableuserfield;
                                            }
                                            // Собираем полученные данные в массив для дальнейшей подстановки в csv
                                            $fields[$userid][$availableuserfield] = $fieldvalue;
                                            $tablecell = new html_table_cell($fieldvalue);
                                            $tablecell->attributes['class'] = 'userfield';
                                            switch($this->format)
                                            {
                                                case 'html':
                                                case 'xlsx':
                                                case 'ods':
                                                    $tablecell->rowspan = 5;
                                                    break;
                                                default:
                                                    break;
                                            }
                                            $tablerow->cells[] = $tablecell;
                                        }
                                    }
                                }
                                
                                $tablecell = new html_table_cell($categoryfullname);
                                $tablecell->attributes['class'] = 'categoryname';
                                switch($this->format)
                                {
                                    case 'html':
                                    case 'xlsx':
                                    case 'ods':
                                        $tablecell->rowspan = 5;
                                        break;
                                    default:
                                        break;
                                }
                                $tablerow->cells[] = $tablecell;

                                $tablecell = new html_table_cell($coursefullname);
                                $tablecell->attributes['class'] = 'coursename';
                                switch($this->format)
                                {
                                    case 'html':
                                    case 'xlsx':
                                    case 'ods':
                                        $tablecell->rowspan = 5;
                                        break;
                                    default:
                                        break;
                                }
                                $tablerow->cells[] = $tablecell;

                                $tablecell = new html_table_cell($coursegrade);
                                $tablecell->attributes['class'] = 'coursegrade';
                                switch($this->format)
                                {
                                    case 'html':
                                    case 'xlsx':
                                    case 'ods':
                                        $tablecell->rowspan = 5;
                                        break;
                                    default:
                                        break;
                                }
                                $tablerow->cells[] = $tablecell;
                                
                                $tablecell = new html_table_cell($courseactivetime);
                                $tablecell->attributes['class'] = 'courseactivetime';
                                switch($this->format)
                                {
                                    case 'html':
                                    case 'xlsx':
                                    case 'ods':
                                        $tablecell->rowspan = 5;
                                        break;
                                    default:
                                        break;
                                }
                                $tablerow->cells[] = $tablecell;
                                
                                $tablecell = new html_table_cell($modactivetimesumm);
                                $tablecell->attributes['class'] = 'modactivetimesumm';
                                switch($this->format)
                                {
                                    case 'html':
                                    case 'xlsx':
                                    case 'ods':
                                        $tablecell->rowspan = 5;
                                        break;
                                    default:
                                        break;
                                }
                                $tablerow->cells[] = $tablecell;
                                
                                $tablecell = new html_table_cell(get_string('caption_modulename', 'report_activetime'));
                                $tablecell->attributes['class'] = 'modulename';
                                $tablerow->cells[] = $tablecell;
                                break;
                            case 'activetime':
                                switch($this->format)
                                {
                                    case 'xlsx':
                                    case 'ods':
                                        $tablecell = new html_table_cell('');
                                        $tablerow->cells[] = $tablecell;
                                        
                                        if (array_key_exists($userid, $fields)) {
                                            foreach($fields[$userid] as $field)
                                            {
                                                $tablecell = new html_table_cell('');
                                                $tablerow->cells[] = $tablecell;
                                            }
                                        }
                                        
                                        $tablecell = new html_table_cell('');
                                        $tablerow->cells[] = $tablecell;
                                        
                                        $tablecell = new html_table_cell('');
                                        $tablerow->cells[] = $tablecell;
                                        
                                        $tablecell = new html_table_cell('');
                                        $tablerow->cells[] = $tablecell;
                                        
                                        $tablecell = new html_table_cell('');
                                        $tablerow->cells[] = $tablecell;
                                        
                                        $tablecell = new html_table_cell('');
                                        $tablerow->cells[] = $tablecell;
                                        break;
                                    case 'csv':
                                        $tablecell = new html_table_cell($userfullname);
                                        $tablerow->cells[] = $tablecell;
                                        
                                        if (array_key_exists($userid, $fields)) {
                                            foreach($fields[$userid] as $field)
                                            {
                                                $tablecell = new html_table_cell($field);
                                                $tablerow->cells[] = $tablecell;
                                            }
                                        }
                                        
                                        $tablecell = new html_table_cell($categoryfullname);
                                        $tablerow->cells[] = $tablecell;
                                        
                                        $tablecell = new html_table_cell($coursefullname);
                                        $tablerow->cells[] = $tablecell;
                                        
                                        $tablecell = new html_table_cell($coursegrade);
                                        $tablerow->cells[] = $tablecell;
                                        
                                        $tablecell = new html_table_cell($courseactivetime);
                                        $tablerow->cells[] = $tablecell;
                                        
                                        $tablecell = new html_table_cell($modactivetimesumm);
                                        $tablerow->cells[] = $tablecell;
                                        break;
                                    case 'html':
                                    default:
                                        break;
                                }
                                
                                $tablecell = new html_table_cell(get_string('caption_activetime_mod', 'report_activetime'));
                                $tablecell->attributes['class'] = 'activetime';
                                $tablerow->cells[] = $tablecell;
                                break;
                            case 'attempts':
                                switch($this->format)
                                {
                                    case 'xlsx':
                                    case 'ods':
                                        $tablecell = new html_table_cell('');
                                        $tablerow->cells[] = $tablecell;
                                        
                                        if (array_key_exists($userid, $fields)) {
                                            foreach($fields[$userid] as $field)
                                            {
                                                $tablecell = new html_table_cell('');
                                                $tablerow->cells[] = $tablecell;
                                            }
                                        }
                                        
                                        $tablecell = new html_table_cell('');
                                        $tablerow->cells[] = $tablecell;
                                        
                                        $tablecell = new html_table_cell('');
                                        $tablerow->cells[] = $tablecell;
                                        
                                        $tablecell = new html_table_cell('');
                                        $tablerow->cells[] = $tablecell;
                                        
                                        $tablecell = new html_table_cell('');
                                        $tablerow->cells[] = $tablecell;
                                        
                                        $tablecell = new html_table_cell('');
                                        $tablerow->cells[] = $tablecell;
                                        break;
                                    case 'csv':
                                        $tablecell = new html_table_cell($userfullname);
                                        $tablerow->cells[] = $tablecell;
                                        
                                        if (array_key_exists($userid, $fields)) {
                                            foreach($fields[$userid] as $field)
                                            {
                                                $tablecell = new html_table_cell($field);
                                                $tablerow->cells[] = $tablecell;
                                            }
                                        }
                                        
                                        $tablecell = new html_table_cell($categoryfullname);
                                        $tablerow->cells[] = $tablecell;
                                        
                                        $tablecell = new html_table_cell($coursefullname);
                                        $tablerow->cells[] = $tablecell;
                                        
                                        $tablecell = new html_table_cell($coursegrade);
                                        $tablerow->cells[] = $tablecell;
                                        
                                        $tablecell = new html_table_cell($courseactivetime);
                                        $tablerow->cells[] = $tablecell;
                                        
                                        $tablecell = new html_table_cell($modactivetimesumm);
                                        $tablerow->cells[] = $tablecell;
                                        break;
                                    case 'html':
                                    default:
                                        break;
                                }
                                
                                $tablecell = new html_table_cell(get_string('caption_attempts', 'report_activetime'));
                                $tablecell->attributes['class'] = 'attempts';
                                $tablerow->cells[] = $tablecell;
                                break;
                            case 'completion':
                                switch($this->format)
                                {
                                    case 'xlsx':
                                    case 'ods':
                                        $tablecell = new html_table_cell('');
                                        $tablerow->cells[] = $tablecell;
                                        
                                        if (array_key_exists($userid, $fields)) {
                                            foreach($fields[$userid] as $field)
                                            {
                                                $tablecell = new html_table_cell('');
                                                $tablerow->cells[] = $tablecell;
                                            }
                                        }
                                        
                                        $tablecell = new html_table_cell('');
                                        $tablerow->cells[] = $tablecell;
                                        
                                        $tablecell = new html_table_cell('');
                                        $tablerow->cells[] = $tablecell;
                                        
                                        $tablecell = new html_table_cell('');
                                        $tablerow->cells[] = $tablecell;
                                        
                                        $tablecell = new html_table_cell('');
                                        $tablerow->cells[] = $tablecell;
                                        
                                        $tablecell = new html_table_cell('');
                                        $tablerow->cells[] = $tablecell;
                                        break;
                                    case 'csv':
                                        $tablecell = new html_table_cell($userfullname);
                                        $tablerow->cells[] = $tablecell;
                                        
                                        if (array_key_exists($userid, $fields)) {
                                            foreach($fields[$userid] as $field)
                                            {
                                                $tablecell = new html_table_cell($field);
                                                $tablerow->cells[] = $tablecell;
                                            }
                                        }
                                        
                                        $tablecell = new html_table_cell($categoryfullname);
                                        $tablerow->cells[] = $tablecell;
                                        
                                        $tablecell = new html_table_cell($coursefullname);
                                        $tablerow->cells[] = $tablecell;
                                        
                                        $tablecell = new html_table_cell($coursegrade);
                                        $tablerow->cells[] = $tablecell;
                                        
                                        $tablecell = new html_table_cell($courseactivetime);
                                        $tablerow->cells[] = $tablecell;
                                        
                                        $tablecell = new html_table_cell($modactivetimesumm);
                                        $tablerow->cells[] = $tablecell;
                                        break;
                                    case 'html':
                                    default:
                                        break;
                                }
                                
                                $tablecell = new html_table_cell(get_string('caption_completion', 'report_activetime'));
                                $tablecell->attributes['class'] = 'completion';
                                $tablerow->cells[] = $tablecell;
                                break;
                            case 'grade':
                                switch($this->format)
                                {
                                    case 'xlsx':
                                    case 'ods':
                                        $tablecell = new html_table_cell('');
                                        $tablerow->cells[] = $tablecell;
                                        
                                        if (array_key_exists($userid, $fields)) {
                                            foreach($fields[$userid] as $field)
                                            {
                                                $tablecell = new html_table_cell('');
                                                $tablerow->cells[] = $tablecell;
                                            }
                                        }
                                        
                                        $tablecell = new html_table_cell('');
                                        $tablerow->cells[] = $tablecell;
                                        
                                        $tablecell = new html_table_cell('');
                                        $tablerow->cells[] = $tablecell;
                                        
                                        $tablecell = new html_table_cell('');
                                        $tablerow->cells[] = $tablecell;
                                        
                                        $tablecell = new html_table_cell('');
                                        $tablerow->cells[] = $tablecell;
                                        
                                        $tablecell = new html_table_cell('');
                                        $tablerow->cells[] = $tablecell;
                                        break;
                                    case 'csv':
                                        $tablecell = new html_table_cell($userfullname);
                                        $tablerow->cells[] = $tablecell;
                                        
                                        if (array_key_exists($userid, $fields)) {
                                            foreach($fields[$userid] as $field)
                                            {
                                                $tablecell = new html_table_cell($field);
                                                $tablerow->cells[] = $tablecell;
                                            }
                                        }
                                        
                                        $tablecell = new html_table_cell($categoryfullname);
                                        $tablerow->cells[] = $tablecell;
                                        
                                        $tablecell = new html_table_cell($coursefullname);
                                        $tablerow->cells[] = $tablecell;
                                        
                                        $tablecell = new html_table_cell($coursegrade);
                                        $tablerow->cells[] = $tablecell;
                                        
                                        $tablecell = new html_table_cell($courseactivetime);
                                        $tablerow->cells[] = $tablecell;
                                        
                                        $tablecell = new html_table_cell($modactivetimesumm);
                                        $tablerow->cells[] = $tablecell;
                                        break;
                                    case 'html':
                                    default:
                                        break;
                                }
                                
                                $tablecell = new html_table_cell(get_string('caption_grade', 'report_activetime'));
                                $tablecell->attributes['class'] = 'grade';
                                $tablerow->cells[] = $tablecell;
                            default:
                                break;
                        }
                        
                        foreach($userrow as $row)
                        {
                            switch($action)
                            {
                                case 'module':
                                    $modblock = $this->get_module_block($this->get_module($row->cmid));
                                    $tablecell = new html_table_cell($modblock);
                                    $tablecell->attributes['class'] = 'moduleitem';
                                    $tablerow->cells[] = $tablecell;
                                    break;
                                case 'activetime':
                                    $tablecell = new html_table_cell($this->get_formatted_interval(0, (int)$this->activetimesummforattempts[$row->userid][$row->courseid][$row->cmid]));
                                    $tablecell->attributes['class'] = 'activetimeitem';
                                    $tablerow->cells[] = $tablecell;
                                    break;
                                case 'attempts':
                                    $attempts = $this->get_attempts_count($userid, $row->cmid, $row->attemptnumber);
                                    $tablecell = new html_table_cell($attempts);
                                    $tablecell->attributes['class'] = 'attemptsitem';
                                    $tablerow->cells[] = $tablecell;
                                    break;
                                case 'completion':
                                    if( is_null($row->completion) || empty($row->completion) )
                                    {// Если у пользователя нет отметки о выполнении или модуль не выполнен
                                        $completion = get_string('no');
                                    } else
                                    {
                                        $completion = get_string('yes');
                                    }
                                    $tablecell = new html_table_cell($completion);
                                    $tablecell->attributes['class'] = 'completionitem';
                                    $tablerow->cells[] = $tablecell;
                                    break;
                                case 'grade':
                                    if( isset($row->instance) )
                                    {
                                        $params = [
                                            'courseid' => $courseid,
                                            'itemtype' => 'mod',
                                            'itemmodule' => $this->get_module($row->cmid)->modname,
                                            'iteminstance' => $row->instance,
                                            'itemnumber' => 0
                                        ];
                                        $grade_item = new grade_item($params);
                                        $finalgrade = $this->get_final_grade($row->finalgrade, $grade_item);
                                    } else 
                                    {
                                        $finalgrade = $row->finalgrade;
                                    }
                                    $tablecell = new html_table_cell($finalgrade);
                                    $tablecell->attributes['class'] = 'completionitem';
                                    $tablerow->cells[] = $tablecell;
                                    break;
                                default:
                                    break;
                            }
                        }
                        
                        for($i = 0; $i < ($this->maxelements - count($userrow)); $i++)
                        {// Докинем пустых ячеек в те ряды, где элементов меньше максимального числа в ряду
                            $tablecell = new html_table_cell('');
                            $tablecell->attributes['class'] = 'moduleitem';
                            $tablerow->cells[] = $tablecell;
                        }
                        
                        $table->data[] = $tablerow;
                    }
                }
            }
        }
        
        // Шапка таблицы
        $tablecell = new html_table_cell(get_string('caption_username', 'report_activetime'));
        $tablecell->header = true;
        $tablecell->attributes['class'] = 'username_caption';
        $table->head[] = $tablecell;
        
        // Поля пользователя
        if( ! empty($availableuserfields) )
        {// Добавляем только те, что указаны в настройках плагина
            $userfields = report_activetime_get_userfields_list();
            $customfields = report_activetime_get_customfields_list();
            foreach($availableuserfields as $availableuserfield)
            {
                if( ! empty($availableuserfield) )
                {
                    if( strpos($availableuserfield, 'profile_field_') !== false )
                    {
                        $tablecell = new html_table_cell($customfields[$availableuserfield]);
                    } else
                    {
                        $tablecell = new html_table_cell($userfields[$availableuserfield]);
                    }
                    $tablecell->header = true;
                    $tablecell->attributes['class'] = 'profilefield_caption ' . $availableuserfield;
                    $table->head[] = $tablecell;
                }
            }
        }
        
        // Категория курса
        $tablecell = new html_table_cell(get_string('caption_categoryname', 'report_activetime'));
        $tablecell->header = true;
        $tablecell->attributes['class'] = 'categoryname_caption';
        $table->head[] = $tablecell;
        
        // Курс
        $tablecell = new html_table_cell(get_string('caption_coursename', 'report_activetime'));
        $tablecell->header = true;
        $tablecell->attributes['class'] = 'coursename_caption';
        $table->head[] = $tablecell;
        
        // Оценка за курс
        $tablecell = new html_table_cell(get_string('caption_coursegrade', 'report_activetime'));
        $tablecell->header = true;
        $tablecell->attributes['class'] = 'coursegrade_caption';
        $table->head[] = $tablecell;
        
        // Время на весь курс
        $tablecell = new html_table_cell(get_string('caption_activetime_course', 'report_activetime'));
        $tablecell->header = true;
        $tablecell->attributes['class'] = 'courseactivetime_caption';
        $table->head[] = $tablecell;
        
        // Сумма времени по модулям
        $tablecell = new html_table_cell(get_string('caption_activetime_mod_summ', 'report_activetime'));
        $tablecell->header = true;
        $tablecell->attributes['class'] = 'modactivetimesumm_caption';
        $table->head[] = $tablecell;
        
        // Пустая ячейка
        $tablecell = new html_table_cell('');
        $tablecell->header = true;
        $tablecell->attributes['class'] = 'actions_caption';
        $table->head[] = $tablecell;
        
        for($i = 0; $i < $this->maxelements; $i++)
        {
            $a = new stdClass();
            $a->count = $i + 1;
            $tablecell = new html_table_cell(get_string('caption_element', 'report_activetime', $a));
            $tablecell->header = true;
            $tablecell->attributes['class'] = 'element_caption';
            $table->head[] = $tablecell;
        }

        return $table;
    }
    
    /**
     * Сформировать данные для отображение или экспорта
     * @param string $format формат отображения html|csv|xlsx|ods
     */
    public function set_data($format = null)
    {
        if( empty($format) || ! in_array($format, $this->availabledataformat) )
        {
            $this->format = 'html';
        } else 
        {
            $this->format = $format;
        }
        $data = $this->get_data();
        $orientation = get_config('report_activetime', 'dataorientation');
        if( empty($orientation) || ! in_array($orientation, $this->dataorientation) )
        {
            $orientation = 'vertical';
        }
        $get = 'get_report_table_' . $orientation;
        $this->table = $this->$get($data);
    }
    
    /**
     * Запустить экспорт отчета
     */
    public function download()
    {
        // Обработчик экспорта
        report_helper::export($this->exporttype, $this->table);
    }
    
    /**
     * Разбиение переданных полей пользователя на кастомные и стандартные
     * @param array $userfields список полей
     */
    protected function set_fields($userfields)
    {
        $this->customfields = $this->userfields = [];
        if( ! empty($userfields) )
        {
            foreach($userfields as $key => $values)
            {
                foreach($values as $value)
                {
                    if( strpos($key, 'profile_field_') !== false )
                    {
                        $this->customfields[$key][] = $value;
                    } else
                    {
                        $this->userfields[$key][] = $value;
                    }
                }
            }
        }
    }
    
    /**
     * Получить объект модуля и положить его в кеш отчета
     * @param int $cmid
     * @return stdClass|string
     */
    protected function get_module($cmid)
    {
        if( ! empty($this->cache->cm[$cmid]) )
        {
            return $this->cache->cm[$cmid];
        } else 
        {
            $module = utilities::get_learninghistory_module_snapshot_last($cmid);
            if( ! empty($module) )
            {
                return $this->cache->cm[$cmid] = $module;
            } else 
            {
                return $this->cache->cm[$cmid] = '';
            }
        }
    }
    
    /**
     * Получить количество попыток прохождения модуля
     * @param int $userid идентификатор пользователя
     * @param int $cmid идентификатор модуля
     * @param int $attemptnumber номер последней попытки попытки
     * @return int|string
     */
    protected function get_attempts_count($userid, $cmid, $attemptnumber = null)
    {
        if( ! empty($this->cache->cm[$cmid]) )
        {
            if( in_array($this->cache->cm[$cmid]->modname, activetime::get_mods_supported_attempts()) )
            {
                $classname = 'local_learninghistory\attempt\mod\attempt_mod_' . $this->cache->cm[$cmid]->modname;
        
            } else
            {
                $classname = 'local_learninghistory\attempt\attempt_base';
            }
            $attemptmod = new $classname($cmid, $userid);
            return $attemptmod->get_attempts_count($attemptnumber);
        } else
        {
            $module = utilities::get_learninghistory_module_snapshot_last($cmid);
            if( ! empty($module) )
            {
                $this->cache->cm[$cmid] = $module;
                if( in_array($this->cache->cm[$cmid]->modname, activetime::get_mods_supported_attempts()) )
                {
                    $classname = 'local_learninghistory\attempt\mod\attempt_mod_' . $this->cache->cm[$cmid]->modname;
        
                } else
                {
                    $classname = 'local_learninghistory\attempt\attempt_base';
                }
                $attemptmod = new $classname($cmid, $userid);
                return $attemptmod->get_attempts_count($attemptnumber);
            } else
            {
                $this->cache->cm[$cmid] = '';
                return '-';
            }
        }
    }
    
    /**
     * Получить фио пользователя и положить его в кеш отчета
     * @param int $userid идентификатор пользователя
     * @return string
     */
    protected function get_user_fullname($userid)
    {
        $this->init_user_cache($userid);
        if( isset($this->cache->user[$userid]->fullname) )
        {
            return $this->cache->user[$userid]->fullname;
        } else 
        {
            return $this->cache->user[$userid]->fullname = fullname($this->cache->user[$userid]->record);
        }
    }
    
    /**
     * Получить объект курса
     * @param int $courseid идентификатор курса
     * @return stdClass
     */
    protected function get_course($courseid)
    {
        if( isset($this->cache->course[$courseid]) )
        {
            return $this->cache->course[$courseid];
        } else 
        {
            return $this->cache->course[$courseid] = get_course($courseid);
        }
    }
    
    /**
     * Получить объект категории курса
     * @param int $categoryid идентификатор категории
     * @return coursecat
     */
    protected function get_category($categoryid)
    {
        if( isset($this->cache->category[$categoryid]) )
        {
            return $this->cache->category[$categoryid];
        } else 
        {
            return $this->cache->category[$categoryid] = core_course_category::get($categoryid, IGNORE_MISSING, true);
        }
    }
    
    /**
     * Получить значение кастомного поля
     * @param string $shortname краткое название поля
     * @param int $userid идентификатор пользователя
     * @return string
     */
    protected function get_customfield_value($shortname, $userid)
    {
        global $CFG;
        $this->init_user_cache($userid);
        if( isset($this->cache->user[$userid]->customfield[$shortname]) )
        {
            return $this->cache->user[$userid]->customfield[$shortname];
        } else
        {
            global $DB;
            $field = $DB->get_record('user_info_field', ['shortname' => $shortname]);
            if( ! empty($field) )
            {
                // Получение данных
                $data = $DB->get_record('user_info_data', [
                    'fieldid' => $field->id,
                    'userid' => $userid
                ]);
                if ( ! empty($data) )
                {
                    // Подключим библиотеки для работы с кастомными полями
                    require_once($CFG->dirroot . '/user/profile/lib.php');
                    if( file_exists($CFG->dirroot . '/user/profile/field/' . $field->datatype . '/field.class.php') )
                    {
                        require_once($CFG->dirroot . '/user/profile/field/' . $field->datatype . '/field.class.php');
                        $classname = 'profile_field_' . $field->datatype;
                        $formfield = new $classname($field->id, $userid);
                        $fielddata = $formfield->display_data();
                    } else 
                    {// Если библиотеку не нашли, отображаем как есть
                        $fielddata = $data->data;
                    }
                    return $this->cache->user[$userid]->customfield[$shortname] = $fielddata;
                }
            }
            return $this->cache->user[$userid]->customfield[$shortname] = '';
        }   
    }
    
    /**
     * Инициализация кеша пользователя
     * @param int $userid идентификатор пользователя
     */
    protected function init_user_cache($userid)
    {
        global $DB;
        if( ! isset($this->cache->user[$userid]) )
        {
            $user = $DB->get_record('user', ['id' => $userid]);
            $this->cache->user[$userid] = new stdClass();
            $this->cache->user[$userid]->record = $user;
        }
    }
    
    /**
     * Получить список объектов модулей для отчета
     * @return array массив объектов
     */
    protected function get_modules()
    {
        global $DB;
        $data = [];
        if( ! empty($this->modules) )
        {
            foreach($this->modules as $cmid)
            {
                $data[] = $this->get_module($cmid);
            }
            return $data;
        } else 
        {
            $paramsmodulemode = $params = [];
            $insqlmodulemode = $modulemodeselect = '';
            if( ! empty($this->modulemode) )
            {
                $modulemode = $this->modulemode == 'all' ? ['active', 'archive'] : [$this->modulemode];
                list($insqlmodulemode, $paramsmodulemode) = $DB->get_in_or_equal($modulemode);
                $modulemodeselect = 'status ' . $insqlmodulemode;
                $params = array_merge($params, $paramsmodulemode);
                if( ! $this->global )
                {
                    $modulemodeselect .= ' AND courseid = ?';;
                    $params = array_merge($params, [$this->course->id]);
                }
                $data = $DB->get_records_select('local_learninghistory_module', $modulemodeselect, $params);
            }
            return $data;
        }
    }
    
    /**
     * Получить иконку модуля
     * @param string $modname имя модуля assign|quiz|...
     * @param int $cmid идентификатор модуля
     * @return string
     */
    protected function get_mod_icon($modname, $cmid)
    {
        global $DB;
        $iconimg = '';
        $icon = new stdClass();
        $icon->attributes = [
            'class' => 'icon itemicon'
        ];
        $icon->title = $modname;
        $courseid = $DB->get_field('course_modules', 'course', ['id' => $cmid]);
        $instanceid = $DB->get_field('course_modules', 'instance', ['id' => $cmid]);
        if( ! empty($instanceid) )
        {
            $modinfo = get_fast_modinfo($courseid);
            if( isset($modinfo->instances[$modname][$instanceid]) )
            {
                $icon->url = $modinfo->instances[$modname][$instanceid]->get_icon_url();
                $iconimg = html_writer::img($icon->url, $icon->title, $icon->attributes);
            }
            return $iconimg;
        } else 
        {
            return '';
        }
    }
    
    /**
     * Получить отформатировнную строку временного интервала
     * @param int $begin timestamp начала интервала
     * @param int $end timestamp конца интервала
     * @return string
     */
    protected function get_formatted_interval($begin = null, $end = null)
    {
        if( is_null($begin) )
        {
            $begin = 0;
        }
        if( is_null($end) )
        {
            $end = time();
        }
        //Находим интервал в секундах между началом и окончанием
        $interval = $end - $begin;
        if ($interval < 1)
        {
            return '0 ' . get_string('sec');
        }
        $format= [];
        //Формируем массив с часами, минутами и секундами
        if ($hours = intdiv($interval, 3600))
        {
            $format[] = $hours . ' ' . get_string('hour');
        }
        if ($minutes = intdiv($interval % 3600, 60))
        {
            $format[] = $minutes . ' ' . get_string('min');
        }
        if ($seconds = $interval % 60)
        {
            $format[] = $seconds . ' ' . get_string('sec');
        }
        //Возвращаем строку со временем
        return implode(' ', $format);
    }
    
    /**
     * Получить html код ячейки с элементом курса
     * @param stdClass $module объект модуля (должен содержать cmid, name, modname)
     * @return string html код ячейки с элементом курса
     */
    protected function get_module_block($module)
    {
        if( isset($this->cache->moduleblock[$module->cmid]) )
        {
            return $this->cache->moduleblock[$module->cmid];
        } else 
        {
            $iconimg = $this->get_mod_icon($module->modname, $module->cmid);
            if( $module->status == 'active' )
            {// Если модуль существует
                $url = new moodle_url('/mod/' . $module->modname . '/view.php', ['id' => $module->cmid]);
            } else
            {// Если не существует - дадим ссылку на заглушку для удаленных модулей
                $url = new moodle_url('/report/activetime/deletedmodule.php', ['cmid' => $module->cmid, 'courseid' => $module->courseid]);
            }
            return $this->cache->moduleblock[$module->cmid] = html_writer::link($url, $iconimg. $module->name);
        }
    }
    
    /**
     * Получить объект activetime
     * @param int $courseid идентификатор курса
     * @return \local_learninghistory\activetime
     */
    protected function get_activetime($courseid)
    {
        if( isset($this->cache->activetime[$courseid]) )
        {
            return $this->cache->activetime[$courseid];
        } else
        {
            return $this->cache->activetime[$courseid] = new activetime($courseid);
        }
    }
    
    /**
     * Отвечает на вопрос "Является ли отчет глобальным?"
     * @return boolean
     */
    public function is_global()
    {
        return $this->global;
    }
    
    /**
     * Возвращает отформатированную строку с оценкой (проценты)
     * @param float $value оценка
     * @param grade_item $grade_item объект grade_item
     * @return string
     */
    protected function get_final_grade($value, $grade_item)
    {
        if( empty($grade_item) )
        {
            return '-';
        }
        return grade_format_gradevalue($value, $grade_item, true, GRADE_DISPLAY_TYPE_PERCENTAGE);
    }
    
    /**
     * Получить таблицу для отображения отчета
     * @param array $data данные, полученные методом get_data()
     * @return html_table
     */
    protected function get_report_table_horizontal($data)
    {
        global $CFG;
        if( file_exists($CFG->dirroot . '/blocks/dof/locallib.php') )
        {
            require_once($CFG->dirroot . '/blocks/dof/locallib.php');
            dof_hugeprocess();
        }
        
        $table = new html_table();
        $table->align = [];
        // Задаем класс для таблицы
        $table->attributes = ['class' => 'generaltable report_activetime_table'];
        // Получаем список полей, заданных настройками
        $availableuserfields = explode(',', get_config('report_activetime', 'userfields'));
    
        // Формируем тело таблицы по переданным данным
        // В зависимости от формата затребованного отчета таблица может меняться:
        // - в части объединения ячеек
        // - в части наличия/отстуствия пустых ячеек
    
        // Счетчик строк таблицы
        $count = 1;
        foreach($data as $userid => $courserow)
        {
            if( ! empty($courserow) )
            {
                foreach($courserow as $courseid => $userrow)
                {
                    // Формируем ссылку на курс
                    $course = $this->get_course($courseid);
                    $coursefullname = html_writer::link('/course/view.php?id=' . $courseid, $course->fullname);
                    // Оценка за курс
                    if( ! isset($current[$courseid]) )
                    {
                        $current[$courseid] = current($userrow);
                        if ( isset($current[$courseid]->coursefinalgrade) && is_string($current[$courseid]->coursefinalgrade) )
                        {// Итоговая оценка имеется
                            $coursegrade = floatval($current[$courseid]->coursefinalgrade);
                        } else
                        {
                            $coursegrade = '-';
                        }
                    }
                    // Формируем ссылку на категорию
                    $category = $this->get_category($course->category);
                    $categoryfullname = html_writer::link('/course/index.php?categoryid=' . $category->id, $category->name);
                    // Форматируем суммарное время по выбранным модулям курса
                    $modactivetimesumm = $this->get_formatted_interval(0, $this->modactivetimesumm[$userid][$courseid]);
                    // Получем объект activetime, для получения времени затраченного на изучение курса
                    $this->activetime = $this->get_activetime($courseid);
                    // Форматируем время затраченное на изучение курса
                    $courseactivetime = $this->get_formatted_interval(0, $this->activetime->get_current_activetime($userid));

                    $tablerow = new html_table_row();
                    if( $count%2 == 1 )
                    {// Закрашиваем строки таблицы 5 через 5
                        $tablerow->attributes['class'] = 'greyrow';
                    }
                    $count++;
                    // Получаем ссылку на профиль пользователя
                    $userfullname = html_writer::link('/user/profile.php?id=' . $userid, $this->get_user_fullname($userid));
                    
                    $tablecell = new html_table_cell($userfullname);
                    $tablecell->attributes['class'] = 'username';
                    $tablerow->cells[] = $tablecell;
                    
                    if( ! empty($availableuserfields) )
                    {// Добавляем данные по полям
                        $fields = [];
                        foreach($availableuserfields as $availableuserfield)
                        {
                            if( ! empty($availableuserfield) )
                            {
                                if( strpos($availableuserfield, 'profile_field_') !== false )
                                {
                                    $fieldvalue = $this->get_customfield_value(substr($availableuserfield, 14), $userid);
                                } else
                                {
                                    $fieldvalue = $this->cache->user[$userid]->record->$availableuserfield;
                                }
                                // Собираем полученные данные в массив для дальнейшей подстановки в csv
                                $fields[$userid][$availableuserfield] = $fieldvalue;
                                $tablecell = new html_table_cell($fieldvalue);
                                $tablecell->attributes['class'] = 'userfield';
                                $tablerow->cells[] = $tablecell;
                            }
                        }
                    }
                    
                    $tablecell = new html_table_cell($categoryfullname);
                    $tablecell->attributes['class'] = 'categoryname';
                    $tablerow->cells[] = $tablecell;
                    
                    $tablecell = new html_table_cell($coursefullname);
                    $tablecell->attributes['class'] = 'coursename';
                    $tablerow->cells[] = $tablecell;
                    
                    $tablecell = new html_table_cell($coursegrade);
                    $tablecell->attributes['class'] = 'coursegrade';
                    $tablerow->cells[] = $tablecell;
                    
                    $tablecell = new html_table_cell($courseactivetime);
                    $tablecell->attributes['class'] = 'courseactivetime';
                    $tablerow->cells[] = $tablecell;
                    
                    $tablecell = new html_table_cell($modactivetimesumm);
                    $tablecell->attributes['class'] = 'modactivetimesumm';
                    $tablerow->cells[] = $tablecell;
                    
                    foreach($userrow as $row)
                    {
                        $modblock = $this->get_module_block($this->get_module($row->cmid));
                        $tablecell = new html_table_cell($modblock);
                        $tablecell->attributes['class'] = 'moduleitem';
                        $tablerow->cells[] = $tablecell;
                    
                        $tablecell = new html_table_cell($this->get_formatted_interval(0, (int)$row->activetime));
                        $tablecell->attributes['class'] = 'activetimeitem';
                        $tablerow->cells[] = $tablecell;
                    
                        $attempts = $this->get_attempts_count($userid, $row->cmid, $row->attemptnumber);
                        $tablecell = new html_table_cell($attempts);
                        $tablecell->attributes['class'] = 'attemptsitem';
                        $tablerow->cells[] = $tablecell;
                    
                        if( is_null($row->completion) || empty($row->completion) )
                        {// Если у пользователя нет отметки о выполнении или модуль не выполнен
                            $completion = get_string('no');
                        } else
                        {
                            $completion = get_string('yes');
                        }
                        $tablecell = new html_table_cell($completion);
                        $tablecell->attributes['class'] = 'completionitem';
                        $tablerow->cells[] = $tablecell;
                    
                        if( isset($row->instance) )
                        {
                            $params = [
                                'courseid' => $courseid,
                                'itemtype' => 'mod',
                                'itemmodule' => $this->get_module($row->cmid)->modname,
                                'iteminstance' => $row->instance,
                                'itemnumber' => 0
                            ];
                            $grade_item = new grade_item($params);
                            $finalgrade = $this->get_final_grade($row->finalgrade, $grade_item);
                        } else 
                        {
                            $finalgrade = $row->finalgrade;
                        }
                        
                        $tablecell = new html_table_cell($finalgrade);
                        $tablecell->attributes['class'] = 'completionitem';
                        $tablerow->cells[] = $tablecell;
                    }
                    
                    for($i = 0; $i < (($this->maxelements - count($userrow)) * 5); $i++)
                    {// Докинем пустых ячеек в те ряды, где элементов меньше максимального числа в ряду
                        $tablecell = new html_table_cell('');
                        $tablecell->attributes['class'] = 'moduleitem';
                        $tablerow->cells[] = $tablecell;
                    }
                    
                    $table->data[] = $tablerow;
                }
            }
        }
        
        // Шапка таблицы
        $tablecell = new html_table_cell(get_string('caption_username', 'report_activetime'));
        $tablecell->header = true;
        $tablecell->attributes['class'] = 'username_caption';
        $table->head[] = $tablecell;
    
        // Поля пользователя
        if( ! empty($availableuserfields) )
        {// Добавляем только те, что указаны в настройках плагина
            $userfields = report_activetime_get_userfields_list();
            $customfields = report_activetime_get_customfields_list();
            foreach($availableuserfields as $availableuserfield)
            {
                if( ! empty($availableuserfield) )
                {
                    if( strpos($availableuserfield, 'profile_field_') !== false )
                    {
                        $tablecell = new html_table_cell($customfields[$availableuserfield]);
                    } else
                    {
                        $tablecell = new html_table_cell($userfields[$availableuserfield]);
                    }
                    $tablecell->header = true;
                    $tablecell->attributes['class'] = 'profilefield_caption ' . $availableuserfield;
                    $table->head[] = $tablecell;
                }
            }
        }
    
        // Категория курса
        $tablecell = new html_table_cell(get_string('caption_categoryname', 'report_activetime'));
        $tablecell->header = true;
        $tablecell->attributes['class'] = 'categoryname_caption';
        $table->head[] = $tablecell;
    
        // Курс
        $tablecell = new html_table_cell(get_string('caption_coursename', 'report_activetime'));
        $tablecell->header = true;
        $tablecell->attributes['class'] = 'coursename_caption';
        $table->head[] = $tablecell;
        
        // Оценка за курс
        $tablecell = new html_table_cell(get_string('caption_coursegrade', 'report_activetime'));
        $tablecell->header = true;
        $tablecell->attributes['class'] = 'coursegrade_caption';
        $table->head[] = $tablecell;
    
        // Время на весь курс
        $tablecell = new html_table_cell(get_string('caption_activetime_course', 'report_activetime'));
        $tablecell->header = true;
        $tablecell->attributes['class'] = 'courseactivetime_caption';
        $table->head[] = $tablecell;
    
        // Сумма времени по модулям
        $tablecell = new html_table_cell(get_string('caption_activetime_mod_summ', 'report_activetime'));
        $tablecell->header = true;
        $tablecell->attributes['class'] = 'modactivetimesumm_caption';
        $table->head[] = $tablecell;
    
        for($i = 0; $i < $this->maxelements; $i++)
        {
            $a = new stdClass();
            $a->count = $i + 1;
            $tablecell = new html_table_cell(get_string('caption_element', 'report_activetime', $a));
            $tablecell->header = true;
            $tablecell->attributes['class'] = 'element_caption';
            $table->head[] = $tablecell;
            
            $tablecell = new html_table_cell(get_string('caption_activetime_mod', 'report_activetime'));
            $tablecell->header = true;
            $tablecell->attributes['class'] = 'activetime';
            $table->head[] = $tablecell;
            
            $tablecell = new html_table_cell(get_string('caption_attempts', 'report_activetime'));
            $tablecell->header = true;
            $tablecell->attributes['class'] = 'attempts';
            $table->head[] = $tablecell;
            
            $tablecell = new html_table_cell(get_string('caption_completion', 'report_activetime'));
            $tablecell->header = true;
            $tablecell->attributes['class'] = 'completion';
            $table->head[] = $tablecell;
            
            $tablecell = new html_table_cell(get_string('caption_grade', 'report_activetime'));
            $tablecell->header = true;
            $tablecell->attributes['class'] = 'grade';
            $table->head[] = $tablecell;
        }
    
        return $table;
    }
}
