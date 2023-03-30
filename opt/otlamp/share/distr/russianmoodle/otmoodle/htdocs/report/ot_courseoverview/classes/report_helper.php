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
 * Сводка по курсам. Класс хелпера.
 *
 * @package    report_ot_courseoverview
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace report_ot_courseoverview;

use Exception;
use MoodleExcelWorkbook;
use core_plugin_manager;
use core_course_category;
use html_table;
use html_writer;
use context_course;
use moodle_url;

class report_helper 
{
    /**
     * Доступные типы экспорта
     * 
     * @var array
     */
    protected static $export_types = ['xls'];
    
    /**
     * Экcпорт в XLS
     *
     * @param string $data
     *
     * @return void
     */
    protected static function export_xls(html_table $table)
    {
        global $CFG;
        
        // Подключение библиотеки xls
        require_once($CFG->libdir.'/excellib.class.php');
        
        // Создание объекта xls файла
        $workbook = new MoodleExcelWorkbook('report_ot_courseoverview_' . date('d.m.Y', time()));
        
        // Задаем название файла
        $workbook->send('report_ot_courseoverview');
        $sheettitle = get_string('pluginname', 'report_ot_courseoverview');
        $myxls = $workbook->add_worksheet($sheettitle);
        
        // Стили
        $style_header = $workbook->add_format();
        $style_header->set_bold(1);
        
        $colnum = 0;
        foreach ( $table->head as $item )
        {
            $myxls->write(0, $colnum, $item, $style_header);
            $colnum++;
        }
        $rownum = 1;
        
        foreach ( (array)$table->data as $item)
        {
            $colnum = 0;
            foreach ( $item as $row )
            {
                $myxls->write($rownum, $colnum, trim(strip_tags($row)));
                $colnum++;
            }
            $rownum++;
        }
        
        $workbook->close();
        exit;
    }
    
    /**
     * Получение администраторов курса
     * 
     * @param int $course_id
     * 
     * @return string $administrators
     */
    public static function get_course_administrators($course_id = null)
    {
        // Валидация
        if ( empty($course_id) )
        {
            return '';
        }
        
        $html = '';
        $administrators = [];
        
        // Контект курса
        $context = context_course::instance($course_id, IGNORE_MISSING);
        if ( empty($context) )
        {
            return $html;
        }
        
        if ( ! empty($context) )
        {
            // Получение пользователей с ролью "Преподаватель"
            $administrators = get_role_users(3, $context);
            
            // Получение пользователей с ролью "Управляющий"
            $managers = get_role_users(1, $context);
            
            foreach ( $managers as $key => $user) 
            {
                if ( ! isset($administrators[$key]) )
                {
                    $administrators[$key] = $user;
                }
            }
        }
        
        if ( ! empty($administrators) )
        {
            // Формирование HTML кода администраторов
            $last = array_pop($administrators);
            foreach ( $administrators as $admin )
            {
                // Ссылка на профиль админа курса
                $admin_profile_link = new moodle_url(
                        '/user/profile.php',
                        ['id' => $admin->id]
                        );
                $html .= html_writer::link(
                        $admin_profile_link->out(false), 
                        fullname($admin) . ',',
                        ['class' => 'link-admin', 'target' => '_blank']
                        );
            }
            // Ссылка на профиль админа курса
            $admin_profile_link = new moodle_url(
                    '/user/profile.php',
                    ['id' => $last->id]
                    );
            $html .= html_writer::link(
                    $admin_profile_link->out(false),
                    fullname($last),
                    ['class' => 'link-admin', 'target' => '_blank']
                    );
        }
        
        return $html;
    }
    
    /**
     * Получение групп курса
     *
     * @param int $course_id
     *
     * @return string $groups
     */
    public static function get_course_cohorts($course_id = null)
    {
        // Валидация
        if ( empty($course_id) )
        {
            return '';
        }
        
        GLOBAL $DB;
        
        // HTML код вывода
        $html = '';
        
        // Параметры для селекта
        $params = [
            'enrolname' => 'cohort',
            'courseid' => $course_id
        ];
        
        // Формирование запроса
        $sql = "SELECT c.name AS name, c.contextid AS contextid
              FROM {groups} g
              JOIN {enrol} e ON (e.enrol = :enrolname AND e.courseid = g.courseid AND e.courseid = :courseid)
              JOIN {cohort} c ON (e.customint1 = c.id)
              WHERE g.id = e.customint2 AND e.status = 0";
        
        // Получение активных глобальных групп, синхронизированных с текущим курсом
        $cohorts = $DB->get_records_sql($sql, $params);
        if ( ! empty($cohorts) )
        {
            // Формирование HTML кода
            $last = array_pop($cohorts);
            foreach ( $cohorts as $cohort )
            {
                // Формирование ссылки на глобальную группу
                $cohort_url = new moodle_url(
                        '/cohort/index.php',
                        [
                            'search' => $cohort->name,
                            'contextid' => $cohort->contextid,
                            'showall' => 1
                        ]
                        );
                
                $html .= html_writer::link(
                        $cohort_url->out(false),
                        $cohort->name . ',',
                        ['class' => 'link-cohort', 'target' => '_blank']
                        );
            }
            // Формирование ссылки на глобальную группу
            $cohort_url = new moodle_url(
                    '/cohort/index.php',
                    [
                        'search' => $last->name,
                        'contextid' => $last->contextid,
                        'showall' => 1
                    ]
                    );
            $html .= html_writer::link(
                    $cohort_url->out(false),
                    $last->name,
                    ['class' => 'link-cohort', 'target' => '_blank']
                    );
        }
        
        return $html;
    }
    
    /**
     * Получение категории курса
     *
     * @param int $course_id
     *
     * @return string $category
     */
    public static function get_course_categories($course_id = null)
    {
        // Валидация
        if ( empty($course_id) )
        {
            return '';
        }
        
        $html = '';
        $category = null;
        $category_parents = [];
        $category_param = '';
        
        try 
        {
            // Получение объекта курса
            $course = get_course($course_id);
        } catch (Exception $e) 
        {
            return $html;
        }
        
        if ( ! empty($course) )
        {
            if ( isset($course->category) &&
                    ! empty($course->category) )
            {
                $cat_temp = core_course_category::get($course->category);
                if ( ! empty($cat_temp) )
                {
                    $category = $cat_temp->get_db_record();
                    $parents = $cat_temp->get_parents();
                    foreach ( $parents as $parent )
                    {
                        $parent_temp = core_course_category::get($parent);
                        if ( ! empty($parent_temp) )
                        {
                            $category_parents[] = $parent_temp->get_db_record();
                        }
                    }
                }
            }
        
            // Определение шаблона ссылки на категорию
            $installlist = core_plugin_manager::instance()->get_installed_plugins('local');
            if ( array_key_exists('crw', $installlist) )
            {
                $url = new moodle_url('/local/crw/index.php');
                $category_param = 'cid';
            } else 
            {
                $url = new moodle_url('/course/index.php');
                $category_param = 'categoryid';
            }
            
            if ( ! empty($category) )
            {
                foreach ( $category_parents as $parent )
                {
                    $url->param($category_param, $parent->id);
                    $html .= html_writer::link(
                            $url->out(false),
                            $parent->name . '/',
                            ['style' => 'link-category', 'target' => '_blank']
                            );
                }
                
                $url->param($category_param, $category->id);
                $html .= html_writer::link(
                        $url->out(false),
                        $category->name,
                        ['style' => 'link-category', 'target' => '_blank']
                        );
            }
        }
        
        return $html;
    }
    
    /**
     * Экcпорт
     *
     * @param string $type
     *
     * @return void
     */
    public static function export($type = null, html_table $table)
    {
        if ( ! empty($type) )
        {
            if ( in_array($type, self::$export_types) )
            {
                $method_name = 'export_' . $type;
                self::$method_name($table);
            } else 
            {// Пока только XLS
                $method_name = 'export_xls';
                self::$method_name($table);
            }
        }
    }
}