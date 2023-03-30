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
 * Область поиска по курсам, доступным в витрине
 *
 * @package    local_crw
 * @subpackage crw_system_search
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace crw_system_search\search;

use core_tag_tag;

defined('MOODLE_INTERNAL') || die();

class crw_course_tags extends crw_course {
    
    // компонент элемента, который помечается тегом - ядро
    protected $tag_component = 'core';
    // типа элемента, который помечается тегом - курс
    protected $tag_itemtype = 'course';
    
    /**
     * {@inheritDoc}
     * @see \crw_system_search\search\crw_course::set_index_data()
     */
    protected function set_index_data(&$doc, $record)
    {
        $doc->set('title', content_to_text($record->fullname, false));
        
        $tags = \core_tag_tag::get_item_tags_array($this->tag_component, $this->tag_itemtype , $record->id);
        if (!empty($tags))
        {
            $doc->set('content', implode(', ',$tags));
        } else
        {
            $doc->set('content', '');
        }
    }
    
}
