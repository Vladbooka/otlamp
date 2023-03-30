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
 * Блок зачисление на курс по купону
 *
 * @package    block
 * @subpackage otcouponenrol
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
use block_otcouponenrol\otcouponenrol_form as form;

class block_otcouponenrol extends block_base
{
    /**
     * Инициализация блока
     *
     * @return void
     */
    public function init()
    {
        $this->title = get_string('pluginname', 'block_otcouponenrol');
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
     * @see block_base::get_content()
     */
    public function get_content()
    {
        global $PAGE;
        
        if (!isloggedin() || isguestuser()) {
            return null;
        }
        $html = '';
        
        if ($this->content !== null)
        {
            return $this->content;
        }
        
        // Объявление контента блока
        $this->content = new stdClass();
        $this->content->text = '';
        $this->content->footer = '';
        if (!empty($this->config->block_name)) {
            $this->title = $this->config->block_name;
        }
        
        $formdata = [
            'show_link' => ($this->config->show_link ?? 0),
            'id' => $this->instance->id
        ];
        
        $adduserenrolmentform = new form($PAGE->url->out(false), $formdata);
        // если форма передана выполним подписку на курс
        $courseurl = $adduserenrolmentform->process();
        // сформируем форму
        $html .= $adduserenrolmentform->render();
        if ($courseurl) {
            
            $html .= html_writer::div(
                get_string('subscribed_text','block_otcouponenrol') . $courseurl,
                'course_url');
        }
        $this->content->text = $html;
        return $this->content;
    }
}
