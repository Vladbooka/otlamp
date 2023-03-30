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
 * Конструктор форм
 *
 * @package    block_otcustomform
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

class block_otcustomform extends block_base {
    
    /**
     * Инициализация блока
     *
     * @return void
     */
    public function init()
    {
        $this->title = get_string('title', 'block_otcustomform');
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
     * @see block_base::instance_can_be_hidden()
     */
    public function instance_can_be_hidden()
    {
        // Блок разрешено скрывать
        return true;
    }
    
    /**
     * {@inheritDoc}
     * @see block_base::html_attributes()
     */
    function html_attributes() {
        $attributes = parent::html_attributes();
        $attributes['data-name'] = get_string('title', 'block_otcustomform');
        if ( ! empty($this->config->block_name) )
        {
            $attributes['data-name'] = $this->config->block_name;
        }
        $attributes['data-instanceid'] = $this->instance->id;
        return $attributes;
    }
    
    /**
     * {@inheritDoc}
     * @see block_base::hide_header()
     */
    function hide_header() {
        return !empty($this->config->hide_header);
    }
    
    /**
     * {@inheritDoc}
     * @see block_base::get_content()
     */
    public function get_content()
    {
        global $PAGE;
        if ( $this->content !== null )
        {
            return $this->content;
        }
        
        // Объявление контента блока
        $this->content = new stdClass();
        $this->content->text = '';
        $this->content->footer = '';
        
        if ( ! empty($this->config->block_name) )
        {
            $this->title = $this->config->block_name;
        }
        
        if ( ! empty($this->config->customformid) )
        {
            // страница просмотра результатов анкеты
            if ( has_capability('block/otcustomform:viewresponses', context_block::instance($this->instance->id)) )
            {
                $this->content->text .= html_writer::link(
                        new moodle_url('/blocks/otcustomform/responses.php', ['id' => $this->instance->id]),
                        get_string('view_responses', 'block_otcustomform'),
                        ['class' => 'btn', 'target' => '_blank']);
            }
            // получение формы
            $form = \block_otcustomform\utils::get_form($this->config->customformid);
            if ( $form )
            {
                // подключение js
                $PAGE->requires->js_call_amd('block_otcustomform/customform', 'init', []);
                
                // рендер формы
                $this->content->text .= $form->render();
            }
        }
        
        return $this->content;
    }
}