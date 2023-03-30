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
use core_plugin_manager;
use core_user;
use html_table;
use html_writer;
use moodle_url;
use user_picture;
use html_table_cell;

class users_dof_achievements extends base
{
    /**
     * Контроллер деканата
     *
     * @var \dof_control
     */
    protected $dof = null;
    
    /**
     * Проверка существования плагина "Электронный деканат" в системе
     *
     * @return boolean
     */
    protected function is_dof_installed()
    {
        $installlist = core_plugin_manager::instance()->get_installed_plugins('block');
        if ( array_key_exists('dof', $installlist) )
        {
            if ( is_null($this->dof) )
            {
                global $CFG;
                require_once($CFG->dirroot . '/blocks/dof/locallib.php');
                global $DOF;
                
                $this->dof = $DOF;
            }
            return true;
        }
        
        return false;
    }
    
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
     * @see \block_topten\base::get_cache_data()
     */
    public function get_cache_data($oldcache = false)
    {
        global $PAGE;
        
        if ( ! $this->is_dof_installed() )
        {
            return;
        }
        if ( ! $this->dof->plugin_exists('im', 'achievements') )
        {
            return;
        }
        
        $catid = $this->config->users_dof_achievements_selectcat;
        // Статусы
        $statuses = array_keys($this->dof->workflow('achievementins')->get_meta_list('active'));

        // Массив категорий для подсчета рейтинга
        $searchcategories = [$catid => $catid];
        
        // Получение потомков
        $children = $this->dof->storage('achievementcats')->get_categories($catid);
        if( ! empty($children) )
        {
            foreach ( $children as $child )
            {
                $searchcategories[$child->id] = $child->id;
            }
        }
        // Параметры для получения рейтинга по разделу
        $addoptions = [
            'status' => $statuses,
            'achievementcats' => $searchcategories
        ];
        $records = $this->dof->storage('achievementins')->get_rating(0, $this->config->rating_number, $addoptions);
        $data = [];
        if ( ! empty($records) )
        {
            foreach ( $records as $info )
            {
                $dofperson = $this->dof->storage('persons')->get_record(['id' => $info->userid]);
                if ( ! empty($dofperson) )
                {
                    $fio = $this->dof->storage('persons')->get_fullname($dofperson->id);
                    if ( ! empty($dofperson->mdluser) )
                    {
                        $mdlperson = core_user::get_user($dofperson->mdluser);
                        if ( ! empty($mdlperson) )
                        {
                            $pic = new user_picture($mdlperson);
                            $fio = html_writer::link(new moodle_url('/user/profile.php', ['id' => $mdlperson->id]), html_writer::img($pic->get_url($PAGE), '', ['class' => 'topten-avatar']). fullname($mdlperson));
                        }
                    }
                } else
                {
                    $fio = get_string('users_dof_achievements_incognito', 'block_topten');
                }
                
                $data[] = [
                    'rate' => intval($info->points), 'btn table-styled-btn',
                    'fio' => $fio
                ];
            }
        }
        return $data;
    }
    
    /**
     * {@inheritDoc}
     * @see \block_topten\base::is_ready()
     */
    public function is_ready()
    {
        if ( ! $this->is_dof_installed() )
        {
            return false;
        }
        
        $categories = $this->dof->storage('achievementcats')->get_categories_select_options();
        if ( empty($this->config->users_dof_achievements_selectcat) ||
                ! array_key_exists($this->config->users_dof_achievements_selectcat, $categories) )
        {
            return false;
        }
        
        return true;
    }
    
    /**
     * {@inheritDoc}
     * @see \block_topten\base::get_content()
     */
    public function get_html($data)
    {
        if ( ! $this->is_dof_installed() )
        {
            return;
        }
        if ( ! $this->dof->plugin_exists('im', 'achievements') )
        {
            return;
        }
        $html = '';
        if ( ! empty($data) )
        {
            $categories = $this->dof->storage('achievementcats')->get_categories_select_options();
            
            $table = new html_table();
            $table->attributes = ['class' => 'generaltable topten-table topten-table-btn'];
            $table->align = ['left', 'left', 'left'];
            $header = new html_table_cell(html_writer::span(get_string('users_dof_achievements_rating', 'block_topten'), 'block-topten-header'));
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
                    $max = $row['rate'];
                }
                $table->data[] = [$position++, $this->get_indicator($row['rate'], $max), $row['fio']];
            }
            
            $html .= html_writer::div(
                    get_string(
                            'users_dof_achievements_header_cat',
                            'block_topten',
                            ltrim($categories[$this->config->users_dof_achievements_selectcat], '-')
                            ),
                    'block-topten-header-info'
                    );
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
        if ( ! $this->is_dof_installed() )
        {
            return;
        }
        
        $categories = $this->dof->storage('achievementcats')->get_categories_select_options();
        if ( ! empty($categories) )
        {
            $mform->addElement(
                    'select',
                    'config_users_dof_achievements_selectcat',
                    get_string('users_dof_achievements_selectcat', 'block_topten'),
                    $categories
                    );
            $mform->setType('config_users_dof_achievements_selectcat', PARAM_INT);
            if ( ! empty($this->block->config->users_dof_achievements_selectcat) &&
                    array_key_exists($this->block->config->users_dof_achievements_selectcat, $categories) )
            {
                $mform->setDefault('config_users_dof_achievements_selectcat', $formsave->block->config->users_dof_achievements_selectcat);
            }
        }
    }
    
    /**
     * Получаем заголовок по умолчанию
     * {@inheritDoc}
     * @see \block_topten\base::get_default_header()
     */
    public static function get_default_header($small = false)
    {
        return get_string($small ? 'users_dof_achievements_header' : 'users_dof_achievements', 'block_topten');
    }
}
