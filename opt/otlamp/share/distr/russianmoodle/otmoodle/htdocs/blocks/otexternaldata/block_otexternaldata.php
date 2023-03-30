<?php
use core\notification;

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
 * Внешние данные
 *
 * @package    block_otexternaldata
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

class block_otexternaldata extends block_base {
    
    public function init() {
        $this->title = get_string('otexternaldata', 'block_otexternaldata');
    }
    
    public function specialization() {
        if (isset($this->config->title)) {
            $this->title = format_string($this->config->title, true, ['context' => $this->context]);
        } else {
            $this->title = get_string('otexternaldata', 'block_otexternaldata');
        }
    }
    
    public function get_content() {
        
        if ($this->content == null) {
            
            $this->content         =  new stdClass();
            $this->content->text   = '';
            $this->content->footer = '';
            
            if (!empty($this->config->content_type))
            {
                try {
                    $contenttypename = $this->config->content_type;
                    $contenttype = \block_otexternaldata\content_type::get_content_type_instance($contenttypename, $this->instance);
                    if (!empty($this->config->content_type_configs[$contenttypename]))
                    {
                        $contenttypeconfig = $this->config->content_type_configs[$contenttypename];
                        $data = $contenttype->export_for_template($contenttypeconfig);
                        if (!empty($contenttypeconfig['mustache']))
                        {
                            $renderer = $this->page->get_renderer('block_otexternaldata');
    //                         $mustache = new Mustache_Engine();
    //                         $template = $mustache->loadLambda($contenttypeconfig['mustache']);
                            $this->content->text .= $renderer->render_from_template_source($contenttypeconfig['mustache'],$data);
                        }
                    }
                } catch(Exception $ex)
                {
                    if (has_capability('block/otexternaldata:managecontent', $this->context))
                    {
                        $renderer = $this->page->get_renderer('block_otexternaldata');
                        $notification = new \core\output\notification(
                            get_string('error_prepare_content', 'block_otexternaldata', $ex->getMessage()),
                            'error'
                        );
                        $this->content->text .= $renderer->render_from_template(
                            $notification->get_template_name(),
                            $notification->export_for_template($renderer)
                        );
                    }
                }
            }
            
            if (has_capability('block/otexternaldata:managecontent', $this->context))
            {
                $managecontenturl = new moodle_url('/blocks/otexternaldata/content_management.php', ['cid' => $this->context->id]);
                $managecontentlink = html_writer::link($managecontenturl, get_string('content_management', 'block_otexternaldata'), ['class' => 'btn btn-primary']);
                $this->content->text .= html_writer::div($managecontentlink, 'manage-content-link');
            }
        }
        
        return $this->content;
    }
    
    public function instance_allow_multiple() {
        return true;
    }
    
    public function instance_can_be_docked() {
        return (!empty($this->config->title) && parent::instance_can_be_docked());
    }
}