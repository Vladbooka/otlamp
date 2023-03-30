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

defined('MOODLE_INTERNAL') || die();

class crw_course_tagcollection_custom1 extends crw_course_tags {
    
    // компонент элемента, который помечается тегом - витрина курсов
    protected $tag_component = 'local_crw';
    // тип элемента, который помечается тегом - курсы витрины для настраиваемой коллекции 2
    protected $tag_itemtype = 'crw_course_custom1';
    
}
