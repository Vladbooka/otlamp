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
use html_table;
use html_writer;
use moodle_url;
use user_picture;
use html_table_cell;

class users_activity extends base
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
     * {@inheritDoc}
     * @see \block_topten\base::is_ready()
     */
    public function is_ready()
    {
        return true;
    }
    /**
     * {@inheritDoc}
     * @see \block_topten\base::get_cache_data()
     */
    public function get_cache_data($oldcache = false)
    {
        global $CFG, $PAGE;
        
        require_once($CFG->libdir . '/datalib.php');
        $logmanager = get_log_manager();
        $readers = $logmanager->get_readers();
        $logreader = reset($readers);
        
        if ( $logreader instanceof \logstore_legacy\log\store ) 
        {
            // Старый логгер
            $logtable = 'log';
            $timefield = 'time';
        } else 
        {
            // Новый логгер
            $logtable = $logreader->get_internal_log_table_name();
            $timefield = 'timecreated';
        }
        
        // Конечная - текущая, начальная - текущая минус 24 часа
        $endtime = time();
        $begintime = $endtime - 86400;
        
        // Связываем пользователей и таблицу логов с группировкой по пользователю и сортировкой по убыванию количества логов
        $sql = "SELECT u.*, COUNT(l.id) as logcounter
                FROM {user} as u
                LEFT JOIN {{$logtable}} as l ON l.userid = u.id
                WHERE (u.confirmed = 1) AND (u.deleted = 0) AND (l.$timefield >= $begintime) AND (l.$timefield <= $endtime)
                GROUP BY u.id
                ORDER BY logcounter DESC";
        $records = $this->db->get_records_sql($sql, [], 0, $this->config->rating_number);
        $data = [];
        if ( ! empty($records) )
        {
            foreach ( $records as $userid => $info )
            {
                $pic = new user_picture($info);
                
                $data[] = [
                    'userid' => $userid,
                    'counter' => $info->logcounter,
                    'fio' => html_writer::link(new moodle_url('/user/profile.php', ['id' => $userid]), html_writer::img($pic->get_url($PAGE), '', ['class' => 'topten-avatar']) . fullname($info)),
                ];
            }
        }
        return $data;
    }
    
    /**
     * {@inheritDoc}
     * @see \block_topten\base::get_content()
     */
    public function get_html($data)
    {
        $html = '';
        if ( ! empty($data) )
        {
            $table = new html_table();
            $table->align = ['left', 'left', 'left'];
            $table->attributes = ['class' => 'generaltable topten-table'];
            $header = new html_table_cell(html_writer::span(get_string('users_activity_counter', 'block_topten'), 'block-topten-header'));
            $header->header = false;
            $table->head = [
                '',
                $header,
                ''
            ];

            $data = array_slice($data, 0, intval($this->config->rating_number));
            $position = 1;
            // Первый элемент в массиве имеет максимальное значение
            $max = false;
            foreach ( $data as $row )
            {
                if ( $max === false )
                {
                    $max = $row['counter'];
                }
                $table->data[] = [$position++, $this->get_indicator($row['counter'], $max), $row['fio']];
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
        return get_string($small ? 'users_activity_header' : 'users_activity', 'block_topten');
    }

}
