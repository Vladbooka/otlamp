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
 * Блок Витрина курсов
 *
 * @package    block
 * @subpackage courses_showcase
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

class block_courses_showcase extends block_base
{
    /**
     * {@inheritDoc}
     * @see block_base::instance_can_be_docked()
     */
    public function instance_can_be_docked() {
        return false;
    }
    
    /**
     * {@inheritDoc}
     * @see block_base::instance_can_be_hidden()
     */
    public function instance_can_be_hidden() {
        return false;
    }
    
    /**
     * {@inheritDoc}
     * @see block_base::instance_can_be_collapsed()
     */
    public function instance_can_be_collapsed() {
        return false;
    }
    
    /**
     * Инициализация блока
     *
     * @return void
     */
    public function init()
    {
        $this->title = get_string('pluginname', 'block_courses_showcase');
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
        $attributes['data-showcase-role'] = 'default';
        $attributes['data-viewtype'] = $this->config->view_type ?? 'crw_default';
        return $attributes;
    }
    
    /**
     * {@inheritDoc}
     * @see block_base::hide_header()
     */
    function hide_header() {
        return empty($this->config->title);
    }
    
    /**
     * {@inheritDoc}
     * @see block_base::get_content()
     */
    public function get_content()
    {
        global $CFG, $USER;
        
        if ($this->content != null)
        {
            return $this->content;
        }
        
        if (!empty($this->config->title))
        {
            $this->title = format_text(trim($this->config->title), FORMAT_PLAIN);
        }
        
        $this->content = new stdClass();
        
        $viewtype = $this->config->view_type ?? 'crw_default';
        $vto = $this->get_viewtype_object($viewtype);
        
        $this->content->text = $vto->get_showcase();
        
        
    }
    
    function is_public_profile() {
        
        global $SCRIPT;
        
        $parentcontext = context::instance_by_id($this->instance->parentcontextid);
        
        return ($parentcontext->contextlevel == CONTEXT_USER && $SCRIPT !== '/my/index.php');
        
    }
    
    function get_viewtype_object($viewtype)
    {
        $classname = "\block_courses_showcase\\viewtype\\$viewtype";
        return new $classname($this);
    }
}
