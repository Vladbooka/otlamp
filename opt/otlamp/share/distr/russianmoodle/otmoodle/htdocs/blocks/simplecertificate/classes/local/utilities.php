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
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle. If not, see <http://www.gnu.org/licenses/>.

/**
 * Сертификаты.
 * Класс хелпера.
 *
 * @package block
 * @subpackage simplecertificate
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace block_simplecertificate\local;

defined('MOODLE_INTERNAL') || die();

use context_system;
use html_table;
use html_writer;
use moodle_url;

require_once ("$CFG->dirroot/blocks/simplecertificate/lib.php");
class utilities
{
    /**
     * Получить массив сертификатов
     */
    static function get_certificates($options = [], $sort = '', $dir = '')
    {
        global $DB;
        
        $where = ' i.pathnamehash<>\'\' ';
        $params = [ ];
        
        if ( isset($options ['courses']) )
        { // Фильтрация по курсам
            if ( is_array($options ['courses']) )
            { // Передан массив значений
                if ( ! empty($where) )
                { // Добавим AND
                    $where .= ' AND ';
                }
                $courseidsstring = implode(', ', $options ['courses']);
                $where .= ' s.course IN (' . $courseidsstring . ') ';
            } else
            { // Единичный ID
                if ( is_int($options ['courses']) || is_string($options ['courses']) )
                { // Передан ID
                    if ( ! empty($where) )
                    { // Добавим AND
                        $where .= ' AND ';
                    }
                    $where .= ' s.course = :courseid ';
                    $params ['courseid'] = $options ['courses'];
                }
            }
        }
        
        if ( isset($options ['users']) )
        { // Фильтрация по пользователям
            if ( empty($options ['users']) )
            {
                if ( ! empty($where) )
                { // Добавим AND
                    $where .= ' AND ';
                }
                $where .= ' i.userid = :userid ';
                $params ['userid'] = 0;
            } else
            {
                if ( is_array($options ['users']) )
                { // Передан массив значений
                    if ( ! empty($where) )
                    { // Добавим AND
                        $where .= ' AND ';
                    }
                    $useridsstring = implode(',', $options ['users']);
                    $where .= ' i.userid IN (' . $useridsstring . ') ';
                } else
                { // Единичный ID
                    if ( is_int($options ['users']) || is_string($options ['users']) )
                    { // Передан ID
                        if ( ! empty($where) )
                        { // Добавим AND
                            $where .= ' AND ';
                        }
                        $where .= ' i.userid = :userid ';
                        $params ['userid'] = $options ['users'];
                    }
                }
            }
        }
        
        if ( ! empty($options['active']) )
        {
            if ( ! empty($where) )
            { // Добавим AND
                $where .= ' AND ';
            }
            $where .= ' i.timedeleted IS NULL ';
        }
        
        if ( ! empty($where) )
        { // Добавлена фильтрация
            $where = ' WHERE ' . $where;
        }
        
        $ordering = ' ORDER BY ';
        switch ( $sort )
        {
            case 'course' :
                $ordering .= ' s.course ';
                break;
            case 'timecreated' :
                $ordering .= ' i.timecreated ';
                break;
            case 'code' :
                $ordering .= ' i.code ';
                break;
            case 'userid' :
            default :
                $ordering .= ' i.userid ';
                break;
        }
        
        switch ( $dir )
        {
            case 'DESC' :
                $ordering .= ' DESC ';
                break;
            case 'ASC' :
            default :
                $ordering .= ' ASC ';
                break;
        }
        $sql = ' 
                SELECT i.*, s.course 
                FROM {simplecertificate} as s RIGHT JOIN {simplecertificate_issues} as i ON s.id = i.certificateid
                ' . $where . $ordering . ';
        ';
        
        $result = $DB->get_records_sql($sql, $params);
        
        return $result;
    }
    
    /**
     * Получить массив курсов системы для SELECT поля
     */
    static function get_courses_select()
    {
        global $SITE;
        
        // Получим все курсы
        $allcourses = get_courses('all', 'c.sortorder ASC', 'c.id, c.fullname, c.shortname, c.visible, c.category');
        unset($allcourses[$SITE->id]);
        
        // Сформируем список курсов
        $courses = [ ];
        foreach ( $allcourses as $id => $course )
        {
            $courses[$id] = $course->fullname . ' (' . $course->shortname . ')';
        }
        // Отсортируем по имени
        asort($courses);
        return [0 => get_string('all')] + $courses;
    }
    
    /**
     * Получить массив ID пользователей, содержащих в своем ФИО участок текста
     *
     * @param string $username
     *            - текст, по которому ищутся пользователи
     *            
     * @return array - массив ID пользователей
     */
    static function get_userids_by_username($username = '')
    {
        global $DB;
        
        $select = ' ( firstname LIKE "%' . $username . '%" OR lastname LIKE "%' . $username . '%" ) AND deleted = 0 ';
        
        $result = $DB->get_fieldset_select('user', 'id', $select);
        return ( array ) $result;
    }
    
    /**
     * Отобразить таблицу сертификатов
     *
     * @param array $certificats
     *            - массив сертификтов
     * @param int $page
     *            - Смещение
     * @param int $limit
     *            - Число записей
     * @param int $sort
     *            - Поле сортировки
     * @param int $dir
     *            - Направление сортировки
     *            
     * @return string - HTML-код таблицы
     */
    static function get_certificates_table($certificates, $page = 0, $limit = 0, $sort = '', $dir = '')
    {
        global $OUTPUT, $PAGE;
        
        // Языковые переменные
        $str_user = get_string('table_user', 'block_simplecertificate');
        $str_course = get_string('table_course', 'block_simplecertificate');
        $str_createdate = get_string('table_createdate', 'block_simplecertificate');
        $str_code = get_string('table_code', 'block_simplecertificate');
        $str_err_usernotfound = get_string('error_user_not_found', 'block_simplecertificate');
        $str_err_coursenotfound = get_string('error_course_not_found', 'block_simplecertificate');
        $str_err_createdatenotfound = get_string('error_createdate_not_found', 'block_simplecertificate');
        $str_err_codenotfound = get_string('error_code_not_found', 'block_simplecertificate');
        
        // Кэши
        $cache_courses = [ ];
        $cache_users = [ ];
        
        // Формирование таблицы
        $table = new html_table();
        $table->width = '95%';
        $table->tablealign = "center";
        
        // Указываем тип сортировки
        if ( $dir == 'DESC' )
        {
            $dir = 'ASC';
            $columnicon = 'sort_asc';
        } else
        {
            $dir = 'DESC';
            $columnicon = 'sort_desc';
        }
        
        // Заголовки таблицы
        $h_user = html_writer::link(new moodle_url($PAGE->url, [ 
                        'sort' => 'user',
                        'dir' => $dir 
        ]), $str_user);
        $h_course = html_writer::link(new moodle_url($PAGE->url, [ 
                        'sort' => 'course',
                        'dir' => $dir 
        ]), $str_course);
        $h_createdate = html_writer::link(new moodle_url($PAGE->url, [ 
                        'sort' => 'timecreated',
                        'dir' => $dir 
        ]), $str_createdate);
        $h_code = html_writer::link(new moodle_url($PAGE->url, [ 
                        'sort' => 'code',
                        'dir' => $dir 
        ]), $str_code);
        
        switch ( $sort )
        {
            case 'course' :
                $h_course .= "<img class='iconsort' src=\"" . $OUTPUT->image_url('t/' . $columnicon) . "\" alt=\"\" />";
                break;
            case 'timecreated' :
                $h_createdate .= "<img class='iconsort' src=\"" . $OUTPUT->image_url('t/' . $columnicon) . "\" alt=\"\" />";
                break;
            case 'code' :
                $h_code .= "<img class='iconsort' src=\"" . $OUTPUT->image_url('t/' . $columnicon) . "\" alt=\"\" />";
                break;
            default :
            case 'user' :
                $h_user .= "<img class='iconsort' src=\"" . $OUTPUT->image_url('t/' . $columnicon) . "\" alt=\"\" />";
                break;
        }
        $table->head = array (
                        $h_user,
                        $h_course,
                        $h_createdate,
                        $h_code 
        );
        $table->align = array (
                        'left',
                        'left',
                        'left',
                        'center' 
        );
        $table->data = [ ];
        
        // Получим срез массива
        $page = abs($page);
        $limit = abs($limit);
        if ( empty($limit) )
        {
            $limit = NULL;
        }
        $certificates = array_slice($certificates, $page * $limit, $limit, true);
        
        foreach ( $certificates as $certificate )
        { // Сформируем данные таблицы
          // Пользователь
            $field_user = $str_err_usernotfound;
            if ( isset($certificate->userid) && $certificate->userid > 0 )
            { // ID пользователя передан
                if ( ! isset($cache_users [$certificate->userid]) )
                { // В кэше еще нет данных о пользователе, добавим ее
                    $user = get_complete_user_data('id', $certificate->userid);
                    if ( ! empty($user) )
                    { // Пользователь получен
                        $cache_users [$certificate->userid] = $OUTPUT->user_picture($user) . fullname($user);
                    } else
                    { // ПОльзователь не найден
                        $cache_users [$certificate->userid] = $str_err_usernotfound;
                    }
                }
                // Получим строку информации о пользователе из кэша
                $field_user = $cache_users [$certificate->userid];
            }
            
            // Курс
            $field_course = $str_err_coursenotfound;
            if ( isset($certificate->course) && $certificate->course > 0 )
            { // ID курса передано
                if ( ! isset($cache_courses [$certificate->course]) )
                { // В кэше нет записи о курсе, сохраним ее
                    $course = get_course($certificate->course);
                    if ( ! empty($course) )
                    { // Курс получен
                        $cache_courses [$certificate->course] = html_writer::link(new moodle_url('/course/view.php', [ 
                                        'id' => $certificate->course 
                        ]), $course->shortname);
                    } else
                    { // Курс не найден
                        $cache_courses [$certificate->course] = $str_err_coursenotfound;
                    }
                }
                $field_course = $cache_courses [$certificate->course];
            }
            
            // Дата создания
            $field_createdate = $str_err_createdatenotfound;
            if ( isset($certificate->timecreated) )
            { // Получена дата создания сертификата
                $field_createdate = userdate($certificate->timecreated) . print_issue_certificate_file($certificate);
            }
            
            // Код сертификата
            $field_code = $str_err_codenotfound;
            if ( isset($certificate->code) )
            { // Получен код сертификата
                $field_code = $certificate->code;
            }
            // Добавим строку
            $table->data [] = array (
                            $field_user,
                            $field_course,
                            $field_createdate,
                            $field_code 
            );
        }
        
        return html_writer::table($table);
    }
    
    /**
     * Отобразить таблицу сертификатов
     *
     * @param array $certificats
     *            - массив сертификтов
     * @param int $page
     *            - Смещение
     * @param int $limit
     *            - Число записей
     *            
     * @return string - HTML-код таблицы
     */
    static function get_certificates_short_table($certificates, $page = 0, $limit = 0)
    {
        global $OUTPUT;
        
        // Языковые переменные
        $str_course = get_string('table_course', 'block_simplecertificate');
        $str_createdate = get_string('table_createdate', 'block_simplecertificate');
        $str_err_coursenotfound = get_string('error_course_not_found', 'block_simplecertificate');
        $str_err_createdatenotfound = get_string('error_createdate_not_found', 'block_simplecertificate');
        
        // Кэши
        $cache_courses = [ ];
        
        // Формирование таблицы
        $table = new html_table();
        $table->width = '95%';
        $table->tablealign = "center";
        $table->head = array (
                        $str_course,
                        $str_createdate 
        );
        $table->align = array (
                        'left',
                        'left' 
        );
        $table->data = [ ];
        
        // Получим срез массива
        $page = abs($page);
        $limit = abs($limit);
        if ( empty($limit) )
        {
            $limit = NULL;
        }
        $certificates = array_slice($certificates, $page * $limit, $limit, true);
        foreach ( $certificates as $certificate )
        { // Сформируем данные таблицы
          // Курс
            $field_course = $str_err_coursenotfound;
            if ( isset($certificate->course) && $certificate->course > 0 )
            { // ID курса передано
                if ( ! isset($cache_courses [$certificate->course]) )
                { // В кэше нет записи о курсе, сохраним ее
                    $course = get_course($certificate->course);
                    if ( ! empty($course) )
                    { // Курс получен
                        $cache_courses [$certificate->course] = html_writer::link(new moodle_url('/course/view.php', [ 
                                        'id' => $certificate->course 
                        ]), $course->shortname);
                    } else
                    { // Курс не найден
                        $cache_courses [$certificate->course] = $str_err_coursenotfound;
                    }
                }
                $field_course = $cache_courses [$certificate->course];
            }
            
            // Дата создания
            $field_createdate = $str_err_createdatenotfound;
            if ( isset($certificate->timecreated) )
            { // Получена дата создания сертификата
                $field_createdate = userdate($certificate->timecreated) . print_issue_certificate_file($certificate);
            }
            
            // Добавим строку
            $table->data [] = [ 
                            $field_course,
                            $field_createdate 
            ];
        }
        
        return html_writer::table($table);
    }
    

    
    /**
     * Отобразить сертификаты, новая верстка
     *
     * @param array $certificats
     *            - массив сертификтов
     * @param int $page
     *            - Смещение
     * @param int $limit
     *            - Число записей
     *            
     * @return string - HTML-код представления
     */
    static function get_certificates_short_view( $certificates, $page = 0, $limit = 0 )
    {
        global $OUTPUT;
        
        // Языковые переменные
        $str_course = get_string('table_course', 'block_simplecertificate');
        $str_createdate = get_string('table_createdate', 'block_simplecertificate');
        $str_err_coursenotfound = get_string('error_course_not_found', 'block_simplecertificate');
        $str_err_createdatenotfound = get_string('error_createdate_not_found', 
            'block_simplecertificate');
        
        //сертификаты пользователя
        $mycertificates = [];
        
        // Получим срез массива
        $page = abs($page);
        $limit = abs($limit);
        if ( empty($limit) )
        {
            $limit = NULL;
        }
        
        //получим изображение сертификата из настроек блока
        $certificateimageurl = self::get_certificate_image();
        //блок с изображением сертификата (левая часть) 
        $certificateimage = \html_writer::div('', 'block_simplecertificate_image', 
            [
                'style' => 'background-image:url(' . $certificateimageurl . ');'
            ]);
        
        $certificates = array_slice($certificates, $page * $limit, $limit, true);
        foreach ( $certificates as $certificate )
        { // Сформируем данные таблицы
            

            // Дата создания
            $field_createdate = $str_err_createdatenotfound;
            if ( isset($certificate->timecreated) )
            { // Получена дата создания сертификата
                $field_createdate = userdate($certificate->timecreated, '%d.%m.%Y');
            }
            
            $certificateurl = new moodle_url('/mod/simplecertificate/wmsendfile.php');
            $certificateurl->param('id', $certificate->id);
            $certificateurl->param('sk', sesskey());
            
            //блок с названием сертификата
            $certificatename = \html_writer::div($certificate->certificatename, 
                'block_simplecertificate_name');
            //блок с датой сертификата
            $certificatecreatedate = \html_writer::div(
                get_string('table_createdate', 'block_simplecertificate') . ': ' . $field_createdate, 
                'block_simplecertificate_createdate');
            //блок с данными сертификата (правая часть)
            $certificatedata = \html_writer::div($certificatename . $certificatecreatedate, 
                'block_simplecertificate_data');
            //добавляем блок сертификата в массив сертификатов пользователя
            $mycertificates[] = \html_writer::link($certificateurl->out(false), 
                $certificateimage . $certificatedata, 
                [
                    'class' => 'block_simplecertificate_certificate'
                ]);
        }
        //возвращаем все сертификаты
        return implode('', $mycertificates);
    }

    /**
     * Получить адрес изображения сертификата
     *
     * @return string адрес изображения сертификата
     */
    static function get_certificate_image()
    {
        global $CFG;
        $fs = get_file_storage();
        $context = \context_system::instance();
        $files = $fs->get_area_files($context->id, 'block_simplecertificate', 'certificateimage');
        // Вывод первого изображения
        if ( count($files) )
        {
            foreach ( $files as $file )
            {
                
                if ( $file->is_valid_image() )
                {
                    // файл является изображением
                    // Получаем адрес изображения
                    $url = moodle_url::make_pluginfile_url($file->get_contextid(), 
                        $file->get_component(), $file->get_filearea(), $file->get_itemid(), 
                        $file->get_filepath(), $file->get_filename());
                    // Возвращаем url
                    return $url;
                }
            }
        }
        //возвращаем дефолтную картинку
        return file_encode_url($CFG->wwwroot, 
            '/blocks/simplecertificate/assets/certificateimage.png');
        ;
    }
}
