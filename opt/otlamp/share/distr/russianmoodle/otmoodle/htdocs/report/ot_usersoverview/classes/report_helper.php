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
 * Сводка по пользователям. Класс хелпера.
 *
 * @package    report_ot_usersoverview
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace report_ot_usersoverview;

require_once ($CFG->dirroot . '/cohort/lib.php');

use MoodleExcelWorkbook;
use MoodleODSWorkbook;
use cache;
use context_system;
use csv_export_writer;
use html_table;
use html_writer;
use moodle_exception;
use stdClass;

class report_helper 
{
    /**
     * Доступные типы экспорта
     * 
     * @var array
     */
    protected static $export_types = ['xls', 'ods', 'csv'];
    
    /**
     * Доступные поля
     *
     * @var array
     */
    protected static $institutions = [];
    
    /**
     * Регион по умолчанию
     * 
     * @var string
     */
    protected static $default_institution = '';
    
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
        $workbook = new MoodleExcelWorkbook('report_ot_usersoverview_' . date('d-m-Y', time()));
        
        // Задаем название файла
        $sheettitle = get_string('pluginname', 'report_ot_usersoverview');
        $myxls = $workbook->add_worksheet($sheettitle);
        
        // Стили
        $style_header = $workbook->add_format();
        $style_header->set_bold(1);
        $style_header->set_align('left');
        $style_header->set_border(1);
        
        $style_col = $workbook->add_format();
        $style_col->set_align('left');
        $style_col->set_border(1);
        
        $style_col_left = $workbook->add_format();
        $style_col_left->set_align('left');
        $style_col_left->set_border(1);
        
        $colnum = 0;
        foreach ( $table->head as $item )
        {
            $myxls->write_string(0, $colnum, $item, $style_header);
            $myxls->set_column($colnum, $colnum, 10);
            $colnum++;
        }
        $rownum = 1;
        
        foreach ( (array)$table->data as $item)
        {
            $colnum = 0;
            foreach ( $item as $row )
            {
                if ( $colnum == 0 )
                {
                    $myxls->write_string($rownum, $colnum, trim(strip_tags($row)), $style_col_left);
                } else 
                {
                    $myxls->write_string($rownum, $colnum, trim(strip_tags($row)), $style_col);
                }
                $colnum++;
            }
            $rownum++;
        }
        $workbook->close();
        exit;
    }
    
    /**
     * Экспорт в ODS
     *
     * @param array $info
     *
     * @return void
     */
    protected static function export_ods(html_table $table)
    {
        global $CFG;
        
        // Подключение файла классов работы с ODS
        require_once($CFG->libdir.'/odslib.class.php');
        
        // Создание объекта xls файла
        $workbook = new MoodleODSWorkbook('report_ot_usersoverview_' . date('d.m.Y', time()));
        
        // Задаем название файла
        $workbook->send('report_ot_usersoverview.ods');
        $sheettitle = get_string('pluginname', 'report_ot_usersoverview');
        $myxls = $workbook->add_worksheet($sheettitle);
        
        // Стили
        $style_header = $workbook->add_format();
        $style_header->set_bold(1);
        $style_header->set_align('left');
        $style_header->set_border(1);
        
        $style_col = $workbook->add_format();
        $style_col->set_align('left');
        $style_col->set_border(1);
        
        $style_col_left = $workbook->add_format();
        $style_col_left->set_align('left');
        $style_col_left->set_border(1);
        
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
                if ( $colnum == 0 )
                {
                    $myxls->write($rownum, $colnum, trim(strip_tags($row)), $style_col_left);
                } else
                {
                    $myxls->write($rownum, $colnum, trim(strip_tags($row)), $style_col);
                }
                $myxls->set_column($colnum, $colnum+1, '24px');
                $colnum++;
            }
            $rownum++;
        }
        $workbook->add_format();
        $workbook->close();
        exit;
    }
    
    /**
     * Экcпорт в CSV
     *
     * @param string $data
     *
     * @return void
     */
    protected static function export_csv(html_table $table)
    {
        global $CFG;
        
        // Подключение библиотеки работы с CSV
        require_once($CFG->libdir . '/csvlib.class.php');
        
        // Создание объекта xls файла
        $csv = new csv_export_writer('semicolon');
        $csv->add_data($table->head);
        foreach ( $table->data as $row )
        {
            $csv->add_data($row);
        }
        
        // Название файла
        $csv->set_filename('report_ot_usersoverview_' . date('d.m.Y', time()));
        
        // Скачивание csv файла
        $csv->download_file();
        exit;
    }
    
    /**
     * Обработка ключа
     * 
     * @param string $string
     */
    public static function process_key_institution($string = '')
    {
        if ( empty($string) || ! is_string($string) )
        {
            return $string;
        }
        
        return (str_replace(' ', '', mb_strtolower(trim($string))));
    }
    
    /**
     * Получение типов экспорта для селекта
     * 
     * @return string[]
     */
    public static function get_export_types_select()
    {
        $string_types = [];
        foreach ( self::$export_types as $type )
        {
            $string_types[$type] = get_string('export_' . $type, 'report_ot_usersoverview');
        }
        
        return $string_types;
    }
    
    /**
     * Получение всех городов пользователей
     * 
     * return array
     */
    public static function get_all_existing_institutions()
    {
        global $DB, $USER;
        
        $institutions = [];
        if ( has_capability('report/ot_usersoverview:view_all', context_system::instance()) )
        {
            $institutions['all'] = self::$default_institution = get_string('field_all', 'report_ot_usersoverview');
            
            // Формирование запроса
            $sql = "SELECT u.institution FROM {user} as u WHERE u.deleted = 0 GROUP BY u.institution";
            $institutions_extra = $DB->get_records_sql($sql);
            
            if ( ! empty($institutions_extra) )
            {
                unset($institutions_extra['all']);
                foreach ( $institutions_extra as $item )
                {
                    if ( ! empty($item->institution) )
                    {
                        $institutions[self::process_key_institution($item->institution)] = $item->institution;
                    }
                }
            }
        } elseif ( has_capability('report/ot_usersoverview:view_my', context_system::instance()) )
        {
            if ( ! empty($USER->institution) )
            {
                $processed_field = self::process_key_institution($USER->institution);
                $institutions[$processed_field] = self::$default_institution = $USER->institution;
            }
        }
        
        return self::$institutions = $institutions;
    }
    
    /**
     * Получение данных
     * 
     * @param string $field
     */
    public static function get_data($field = '')
    {
        global $DB;
        
        $curtime = time();
        
        $field = self::process_key_institution($field);
        $field_processed = null;
        if ( ! empty($field) && array_key_exists($field, self::$institutions))
        {
            $field_processed = self::$institutions[$field];
        }
        // Результирующий массив
        $processed_data = [];
        
        $params = [
            'deleted' => 0   
        ];
        
        // обработка поля institution
        $institution = '';
        if ( ! empty($field_processed) )
        {
            if ( ($field_processed != get_string('field_all', 'report_ot_usersoverview')) )
            {
                $institution = $field_processed;
            }
        } elseif ( ! empty(self::$default_institution) )
        {
            if ( (self::$default_institution != get_string('field_all', 'report_ot_usersoverview')) )
            {
                $institution = self::$default_institution;
            }
        } else 
        {
            // Ошибка
            throw new moodle_exception('view_report_denied', 'report_ot_usersoverview');
        }
        
        // данные по всем instituion храним с ключом -1
        if ( ! empty($institution) )
        {
            $key = $institution;
        } else 
        {
            $key = -1;
        }
        
        // поиск в кеше готового отчета
        $reportdatacache = cache::make('report_ot_usersoverview', 'fullreportdata');
        $reportdata = $reportdatacache->get($key);
        if ( ! empty($reportdata) )
        {
            return $reportdata;
        }
        
        // массив пользователей
        $users = [];
        
        // получение кеш хранилища со списком пользователей
        $cache = cache::make('report_ot_usersoverview', 'users');
        $preusers = $cache->get('all');
        if ( $preusers === false )
        {
            $preusers = $DB->get_records('user', $params, '', 'id,firstname,lastname,email,department,institution');
            $cache->set('all', $preusers);
        }
        if ( ! empty($institution) )
        {
            foreach ( $preusers as $user )
            {
                if ( $user->institution == $institution )
                {
                    $users[] = $user;
                }
            }
        } else 
        {
            $users = $preusers;
        }
        if ( empty($users) )
        {
            return $processed_data;
        }
        
        // кеш данных пользователей
        $cache = cache::make('report_ot_usersoverview', 'usersdata');
        $cacheallusersgroups = cache::make('report_ot_usersoverview', 'usersgroups');
        
        $context = \context_system::instance();
        
        $groupedusersenrols = $cache->get('groupedusersenrols');
        if ( $groupedusersenrols === false )
        {
            $groupedusersenrols = [];
            // получение всех подписок пользователей
            $usersenrols = $DB->get_records_sql('SELECT lh.*
                                              FROM mdl_local_learninghistory as lh
                                        INNER JOIN (SELECT MAX(lhh.id) as id FROM mdl_local_learninghistory as lhh GROUP BY lhh.userid, lhh.courseid) as dh ON lh.id = dh.id;');
            // сгруппируем подписки по пользователям
            foreach ( $usersenrols as $enrol )
            {
                $groupedusersenrols[$enrol->userid][] = $enrol;
            }
            // установка данных в кеш
            $cache->set('usersdata', $groupedusersenrols);
        }
        
        $allcohorts = $cacheallusersgroups->get(-1);
        if ( $allcohorts === false )
        {
            $allcohorts = cohort_get_cohorts($context->id, 0, 0)['cohorts'];
            $cacheallusersgroups->set(-1, $allcohorts);
        }
        foreach ( $users as $user )
        {
            if ( empty($user->department) || empty($user->institution) )
            {
                continue;
            }
            
            if ( ! array_key_exists($user->id, $groupedusersenrols) || empty($groupedusersenrols[$user->id]) )
            {
                continue;
            }
            
            $processed_department = str_replace(' ', '', mb_strtolower(trim($user->department)));
            if ( ! array_key_exists($processed_department, $processed_data) )
            {
                // По текущему отделу(должности) нет данных, фомирование объекта
                $row = new stdClass();
                $row->name = ucfirst($user->department);
                $row->clean_name = $user->department;
                $row->number_users = 0;
                $row->number_enrols_all = 0;
                $row->number_enrols_active = 0;
                $row->number_enrols_completed = 0;
                $row->number_enrols_failed = 0;
                // кешируем юзеров
                $row->number_enrols_all_users = [];
                $row->groupsnumber = 0;
                $processed_data[$processed_department] = $row;
            }
            
            $user->ot_usersoverview_cohortids = $cacheallusersgroups->get($user->id);
            if ( $user->ot_usersoverview_cohortids === false )
            {
                $user->ot_usersoverview_cohortids = [];
                $usercohorts = cohort_get_user_cohorts($user->id);
                foreach ( $usercohorts as $usercohort )
                {
                    if ( $usercohort->contextid == $context->id )
                    {
                        $user->ot_usersoverview_cohortids[] = $usercohort->id;
                    }
                }
                $cacheallusersgroups->set($user->id, $user->ot_usersoverview_cohortids);
            }
            if ( count($user->ot_usersoverview_cohortids) > $row->groupsnumber )
            {
                $row->groupsnumber = count($user->ot_usersoverview_cohortids);
            }
            
            // У пользователя есть хотя бы одна подписка, попадает в выборку
            $processed_data[$processed_department]->number_users++;
            $processed_data[$processed_department]->number_enrols_all_users[$user->id] = $user;
            
            // Успешно завершенный
            $in_process = false;
            $failed = false;
            foreach ( $groupedusersenrols[$user->id] as $enrol )
            {
                if ( empty($enrol->enddate) || empty($enrol->coursecompletion) )
                {
                    $processed_data[$processed_department]->number_enrols_all++;
                }
                if ( ! $failed )
                {
                    if ( ! empty($enrol->enddate) && $curtime >= $enrol->enddate && empty($enrol->coursecompletion) )
                    {
                        $failed = true;
                    } elseif ( (empty($enrol->enddate) || ( ! empty($enrol->enddate) && $curtime < $enrol->enddate)) && empty($enrol->coursecompletion) )
                    {
                        $in_process = true;
                    }
                }
            }
            
            if ( $failed )
            {
                $processed_data[$processed_department]->number_enrols_failed++;
            } elseif ( $in_process )
            {
                $processed_data[$processed_department]->number_enrols_active++;
            } else 
            {
                $processed_data[$processed_department]->number_enrols_completed++;
            }
        }
        
        // установка последней строки Итого
        $total = new stdClass();
        $total->name = html_writer::span(get_string('total', 'report_ot_usersoverview'), '', ['style' => "font-weight:bold;font-style:italic;"]);
        $total->clean_name = '';
        $total->number_users = 0;
        $total->number_enrols_all = 0;
        $total->number_enrols_active = 0;
        $total->number_enrols_completed = 0;
        $total->number_enrols_failed = 0;
        // кешируем юзеров
        $total->number_enrols_all_users = [];
        
        // подсчет итоговых баллов
        foreach ( $processed_data as $row )
        {
            $total->number_users += $row->number_users;
            $total->number_enrols_all += $row->number_enrols_all;
            $total->number_enrols_active += $row->number_enrols_active;
            $total->number_enrols_completed += $row->number_enrols_completed;
            $total->number_enrols_failed += $row->number_enrols_failed;
            $total->number_enrols_all_users = array_replace($total->number_enrols_all_users, $row->number_enrols_all_users);
        }
        
        array_push($processed_data, $total);
        
        // установка кеша готового отчета
        $reportdatacache->set($key, $processed_data);
        
        return $processed_data;
    }
    
    /**
     * Получение детализированных данных по организации и отделу
     *
     * @param string $institution - организация
     * @param string $department - отдел
     * 
     * @return stdClass
     */
    public static function get_data_detail($institution = '', $department = '') : stdClass
    {
        $cache = cache::make('report_ot_usersoverview', 'usersgroups');
        
        $result = new stdClass();
        $result->groupsnumber = 0;
        $result->data = null;
        $result->cohorts = [];
        
        $processed_department = str_replace(' ', '', mb_strtolower(trim($department)));
        $data = self::get_data($institution);
        if ( empty($data) )
        {
            return $result;
        }
        
        $departmentdata = null;
        if ( ! empty($department) )
        {
            if ( array_key_exists($processed_department, $data) )
            {
                $departmentdata = $data[$processed_department];
            }
        } else 
        {
            $departmentdata = end($data);
        }
        
        foreach ( $departmentdata->number_enrols_all_users as $userdata )
        {
            if ( count($userdata->ot_usersoverview_cohortids) > $result->groupsnumber ) 
            {
                $result->groupsnumber = count($userdata->ot_usersoverview_cohortids);
            }
        }
        
        $result->cohorts = $cache->get(-1) !== false ? $cache->get(-1) : [];
        $result->data = $departmentdata;
        
        return $result;        
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
    
    /**
     * Сброс кеша
     * 
     * @return void
     */
    public static function purgecaches()
    {
        foreach ( ['users', 'usersdata', 'fullreportdata', 'usersgroups'] as $cachename )
        {
            $cache = cache::make('report_ot_usersoverview', $cachename);
            $cache->purge();
        }
    }
    
    /**
     * Сбор данных в кеш
     * 
     * @return void
     */
    public static function collectcache()
    {
        // получение списка institution, по которым будем сбор кеша
        $institutions = report_helper::get_all_existing_institutions();
        
        // сбор по всем значениям сразу
        report_helper::get_data('');
        
        // сбор по каждому institution
        if ( ! empty($institutions) )
        {
            foreach ( $institutions as $institution )
            {
                report_helper::get_data($institution);
            }
        }
    }
}