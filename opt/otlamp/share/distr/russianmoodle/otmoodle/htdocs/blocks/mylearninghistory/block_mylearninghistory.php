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
 * История обучения. Класс блока
 *
 * @package    block
 * @subpackage mylearninghistory
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

use block_mylearninghistory\local\utilities;

class block_mylearninghistory extends block_base 
{
    function init() {
        $this->title = get_string('pluginname', 'block_mylearninghistory');
    }

    function specialization() 
    {
    }

    function applicable_formats() 
    {
        return [
            'all' => true
        ];
    }

    /**
     * Поддержка блоком страницы конфигурации
     *
     * @return boolean
     */
    public function has_config()
    {
        return true;
    }
    
    function instance_allow_multiple() 
    {
        return false;
    }

    function get_content() 
    {
        global $USER,$PAGE;

        if ( $this->content !== NULL ) 
        {
            return $this->content;
        }
        if ( empty($this->instance) ) 
        {
            return $this->content;
        }
        $PAGE->requires->js(new moodle_url('/blocks/mylearninghistory/script.js'));
        
        $context = $PAGE->context;
        //if($context->contextlevel == CONTEXT_USER)
        if($PAGE->url->get_path() == '/user/profile.php')
        {
            $userid = optional_param('id', 0, PARAM_INT);
            $userid = $userid ? $userid : $USER->id;
        }
        else 
        {
            $userid = $USER->id;
        }
        
        if ( ! utilities::is_access($USER->id) ) 
        {
            $this->content = new stdClass();
            $this->content->text = get_string('error_loginrequired', 'block_mylearninghistory');
            $this->content->footer = '';
            return $this->content;
        }

        $this->content = new stdClass();
        $this->content->text = utilities::get_course_stats_view_separate($context, $userid);
        
        $this->content->footer = '';
        return $this->content;
    }
}
