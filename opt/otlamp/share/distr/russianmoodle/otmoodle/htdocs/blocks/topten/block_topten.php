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
use block_topten\report;

class block_topten extends block_base
{
    /**
     * Инициализация блока
     *
     * @return void
     */
    public function init()
    {
        $this->title = get_string('pluginname', 'block_topten');
    }

    /**
     * {@inheritDoc}
     * @see block_base::applicable_formats()
     */
    public function applicable_formats()
    {
        return [
            'all' => true
        ];
    }

    /**
     * {@inheritDoc}
     * @see block_base::instance_allow_multiple()
     */
    public function instance_allow_multiple()
    {
        return true;
    }
    
    /**
     * {@inheritDoc}
     * @see block_base::html_attributes()
     */
    function html_attributes() {
        $attributes = parent::html_attributes();
        if (!empty($this->config->rating_type))
        {
            $attributes['class'] .= ' '.$this->config->rating_type;
        }
        return $attributes;
    }
    
    /**
     * {@inheritDoc}
     * @see block_base::hide_header()
     */
    function hide_header() {
        return !empty($this->config->hide_rating_title);
    }
    
    /**
     * 
     * {@inheritDoc}
     * @see block_base::instance_config_commit()
     */
    function instance_config_save($data, $nolongerused = false) {
        parent::instance_config_save($data, $nolongerused = false);
        // проверяем а выбран ли отчет в настройках
        if ( ! empty($data->rating_type) ) {
            // Инициализация класса отчета
            $toptenclass = new report($data, $this->instance->id);
            // Обновим кеш
            $toptenclass->update_cache();
        }
    }
    
    /**
     * {@inheritDoc}
     * @see block_base::get_content()
     */
    public function get_content()
    {
        if ( $this->content !== null )
        {
            return $this->content;
        }
        // Объявление контента блока
        $this->content = new stdClass();
        $this->content->text = '';
        $this->content->footer = '';
        // проверяем а выбран ли отчет в настройках
        if ( ! empty($this->config->rating_type) )
        {
            // Инициализация класса отчета
            $toptenclass = new report($this->config, $this->instance->id);
            // получение заголовка
            $this->title = $toptenclass->header();
            // Получение контента отчета
            $this->content->text = $toptenclass->get_content();
        } 
    }
}
