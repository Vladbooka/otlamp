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
 * Блок топ-10
 *
 * @package    block
 * @subpackage topten
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace block_topten\reports;

use block_topten\base as base;
use cache;
use core_plugin_manager;
use html_table;
use html_writer;
use moodle_url;
use block_rate_course;
use html_table_cell;

class courses_rating extends base
{
    /**
     * {@inheritDoc}
     * @see \block_topten\base::is_cached()
     */
    public function is_cached()
    {
        return true;
    }
    /**
     * Проверка существования плагина "Рейтинг курса" в системе
     * 
     * @return boolean
     */
    protected function is_rate_course_installed()
    {
        $installlist = core_plugin_manager::instance()->get_installed_plugins('block');
        return array_key_exists('rate_course', $installlist);
    }
    
    /**
     * {@inheritDoc}
     * @see \block_topten\base::is_ready()
     */
    public function is_ready()
    {
        return $this->is_rate_course_installed();
    }
    
    /**
     * Получение даты для кеширования
     * 
     * {@inheritDoc}
     * @see \block_topten\base::get_cache_data()
     */
    public function get_cache_data($oldcache = false)
    {
        global $CFG;
        //подключаем библиотеки для работы
        require_once($CFG->dirroot.'/blocks/moodleblock.class.php');
        require_once($CFG->dirroot.'/blocks/rate_course/block_rate_course.php');
        
        // Получение всех курсов
        $records = $this->db->get_records('course', ['visible' => 1], 'fullname ASC');
        unset($records[1]);
        $obj = new block_rate_course();
        if ( ! empty($records) )
        {
            $data = [];
            foreach ( $records as $courseid => $info )
            {
                $data[] = [
                    'courseid' => $courseid,
                    'fullname' => html_writer::link(new moodle_url('/course/view.php', ['id' => $courseid]), $info->fullname),
                    'rating' => $obj->get_rating($courseid)
                ];
            }
            
            // Сортировка курсов по рейтингу
            usort($data, function ( $a, $b )
            {
                return $a['rating'] < $b['rating'];
            });
            
            $newdata = [];
            $number = 1;
            foreach ( $data as $id => $row )
            {
                if ( $number > $this->config->rating_number )
                {
                    break;
                }
                $number++;
                $row['rating'] = html_writer::div(
                        html_writer::img($CFG->wwwroot.'/blocks/rate_course/pix/rating_graphic.php?courseid='.$row['courseid'], get_string('courses_rating_rating', 'block_topten')), 
                        'block-toptent-crop'
                        );
                $newdata[$id] = $row;
            }
            return $newdata;
        }
    }
    
    /**
     * {@inheritDoc}
     * @see \block_topten\base::get_html()
     */
    public function get_html($data)
    {
        $html = '';
        if ( ! empty($data) )
        {
            $table = new html_table();
            $table->align = ['left', 'center', 'left'];
            $table->attributes = ['class' => 'generaltable topten-table topten-table-courserating'];
            $header = new html_table_cell(html_writer::span(get_string('courses_rating_rating', 'block_topten'), 'block-topten-header'));
            $header->header = false;
            $table->head = [
                '',
                $header,
                ''
            ];

            $position = 1;
            foreach ( $data as $row )
            {
                $table->data[] = [$position++, $row['rating'], $row['fullname']];
            }
            $html .= html_writer::table($table);
        }
        
        return $html;
    }
    
    /**
     * Получаем заголовок по умолчанию
     * {@inheritDoc}
     * @see \block_topten\base::get_default_header()
     */
    public static function get_default_header($small = false)
    {
        return get_string($small ? 'courses_rating_header' : 'courses_rating', 'block_topten');
    }
    
}
