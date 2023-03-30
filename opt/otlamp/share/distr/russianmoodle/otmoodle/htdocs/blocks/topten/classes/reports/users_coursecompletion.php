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

class users_coursecompletion extends base
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
     * Добавление собственных настроек в форме
     *
     * @param \MoodleQuickForm $mform
     */
    public function definition(&$mform, $formsave = null)
    {
        
        $curtime = time();
        
        $mform->addElement(
            'date_selector',
            'config_users_coursecompletion_accept_completions_from',
            get_string('users_coursecompletion_accept_completions_from', 'block_topten'),
            ['optional' => true],
            ['data-default' => $curtime]
        );
        $mform->addElement(
            'date_selector',
            'config_users_coursecompletion_accept_completions_to',
            get_string('users_coursecompletion_accept_completions_to', 'block_topten'),
            ['optional' => true],
            ['data-default' => $curtime]
        );
    }
    
    /**
     * {@inheritDoc}
     * @see \block_topten\base::get_cache_data()
     */
    public function get_cache_data($oldcache = false)
    {
        	global $PAGE;
        	$conditions = [
                'u.confirmed = 1',
                'u.deleted = 0',
                '(cc.timecompleted IS NOT NULL)',
                'c.id != :siteid'
            ];
            $params = ['siteid' => SITEID];
            
            if (!empty($this->config->users_coursecompletion_accept_completions_from))
            {
                $conditions[] = 'cc.timecompleted >= :completions_from';
                $params['completions_from'] = $this->config->users_coursecompletion_accept_completions_from;
            }
            if (!empty($this->config->users_coursecompletion_accept_completions_to))
            {
                $conditions[] = 'cc.timecompleted <= :completions_to';
                $params['completions_to'] = $this->config->users_coursecompletion_accept_completions_to;
            }
        	// Связываем пользователей с таблицей завершенности курсов и табилцей самих курсов,
            // группируем по записи по пользователям и считаем количество завершенных курсов
            $sql = "SELECT u.*,
                       COUNT(cc.id) as counter FROM {user} as u
                LEFT JOIN {course_completions} as cc ON u.id = cc.userid
                INNER JOIN {course} as c ON cc.course = c.id
                WHERE " . implode(' AND ', $conditions) . "
                GROUP BY u.id
                ORDER BY counter DESC";
            
        	$records = $this->db->get_records_sql($sql, $params, 0, $this->config->rating_number);
        	$data = [];
        	if ( ! empty($records) )
        	{
            	foreach ( $records as $userid => $info )
            	{
                	$pic = new user_picture($info);
                	$fio = html_writer::link(new moodle_url('/user/profile.php', ['id' => $userid]), html_writer::img($pic->get_url($PAGE), '', ['class' => 'topten-avatar']) . fullname($info));
                	$data[] = [
                    	'userid' => $userid,
                    	'fio' => $fio,
                    	'count' => $info->counter
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
            $header = new html_table_cell(html_writer::span(get_string('users_coursecompletion_number', 'block_topten'), 'block-topten-header'));
            $header->header = false;
            $table->head = [
                '',
                $header,
                ''
            ];
            
            $position = 1;
            $data = array_slice($data, 0, intval($this->config->rating_number));
            // Первый элемент в массиве имеет максимальное значение
            $max = false;
            foreach ( $data as $row )
            {
                if ( $max === false )
                {
                    $max = $row['count'];
                }
                $table->data[] = [$position++, $this->get_indicator($row['count'], $max), $row['fio']];
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
        return get_string($small ? 'users_coursecompletion_header' : 'users_coursecompletion', 'block_topten');
    }
    /**
     * {@inheritDoc}
     * @see \block_topten\base::is_ready()
     */
    public function is_ready()
    {
        return true;
    }
}