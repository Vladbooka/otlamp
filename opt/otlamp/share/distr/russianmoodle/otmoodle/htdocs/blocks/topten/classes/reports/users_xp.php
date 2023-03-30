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

use MoodleQuickForm;
use block_topten\base as base;
use cache;
use context_system;
use core_plugin_manager;
use html_table;
use html_writer;
use moodle_url;
use user_picture;
use html_table_cell;

class users_xp extends base
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
     * Проверка существования плагина "Опыт!" в системе
     * 
     * @return boolean
     */
    protected function is_xp_installed()
    {
        $installlist = core_plugin_manager::instance()->get_installed_plugins('block');
        return array_key_exists('xp', $installlist);
    }
    
    /**
     * {@inheritDoc}
     * @see \block_topten\base::is_ready()
     */
    public function is_ready()
    {
        return $this->is_xp_installed();
    }
    
    /**
     * {@inheritDoc}
     * @see \block_topten\base::get_cache_data()
     */
    public function get_cache_data($oldcache = false)
    {
        global $PAGE;

        $sql = "SELECT xp.*, u.*
                FROM {user} as u
                LEFT JOIN {block_xp} as xp ON xp.userid = u.id
                WHERE (u.confirmed = 1) AND (u.deleted = 0) AND (xp.courseid = :siteid)
                ORDER BY xp.xp DESC";
        $records = $this->db->get_records_sql($sql, ['siteid' => SITEID], 0, intval($this->config->rating_number));
        
        // Объявление класса работы с хранилищем бейджей
        $resolver = new \block_xp\local\xp\file_storage_badge_url_resolver(context_system::instance(), 'block_xp', 'badges', 0);
        
        // Объевление класса рендера блока XP
        $renderer = $PAGE->get_renderer('block_xp');
        $data = [];
        if ( ! empty($records) )
        {
            foreach ( $records as $userid => $info )
            {
                // Объявление класса "уровня" пользователя
                $userlvl = new \block_xp\local\xp\badged_level($info->lvl, 1, '', $resolver);
                
                // Объявление класса изображение пользователя
                $pic = new user_picture($info);
                $data[] = [
                    'userid' => $info->id,
                    'fio' => html_writer::link(new moodle_url('/user/profile.php', ['id' => $info->id]), html_writer::img($pic->get_url($PAGE), '', ['class' => 'topten-avatar']) . fullname($info)),
                    'lvl' => $renderer->small_level_badge($userlvl)
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
            $header = new html_table_cell(html_writer::span(get_string('users_xp_lvl', 'block_topten'), 'block-topten-header'));
            $header->header = false;
            $table->head = [
                '',
                $header,
                ''
            ];

            $position = 1;
            foreach ( $data as $row )
            {
                $table->data[] = [$position++, $row['lvl'], $row['fio']];
            }
            $html .= html_writer::table($table);
        }
        return $html;
    }
    
    /**
     * Добавление собственных настроек в форму
     *
     * @param MoodleQuickForm $mform
     */
    public function definition(&$mform, $formsave = null)
    {
        $mform->addElement('static', 'type_info', get_string('type_description', 'block_topten'), get_string('users_xp_description', 'block_topten'));
    }
    /**
     * Получаем заголовок по умолчанию
     * {@inheritDoc}
     * @see \block_topten\base::get_default_header()
     */
    public static function get_default_header($small = false)
    {
        return get_string($small ? 'users_xp_header' : 'users_xp', 'block_topten');
    }

}
