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
 * Отзыв
 *
 * @package    local_crw
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_crw\feedback;

use local_crw\model\item as baseitem;
use core_user;
use local_crw\model\field;
use local_pprocessing\processor\handler\get_user;

class item extends baseitem {
    
    const TABLE = 'crw_feedback';
    
    protected function init_fields()
    {
        global $USER;
        
        $this->fields['id'] = new field('id', PARAM_INT, false, null, null);
        // источник комментария
        $this->fields['contextid'] = new field('contextid', PARAM_INT, true, null, null);
        // компонент источника комментария
        $this->fields['component'] = new field('component', PARAM_ALPHANUMEXT, false, null, null);
        // тип комментируемого объекта
        $this->fields['commentarea'] = new field('commentarea', PARAM_ALPHANUMEXT, true, null, null);
        // идентификатор комментируемого объекта
        $this->fields['itemid'] = new field('itemid', PARAM_INT, true, null, null);
        $this->fields['content'] = new field('content', PARAM_RAW, true, null, null);
        $this->fields['format'] = new field('format', PARAM_INT, true, null, null);
        $this->fields['userid'] = new field('userid', PARAM_INT, true, null, $USER->id);
        $this->fields['status'] = new field('status', PARAM_ALPHA, true, ['new', 'accepted', 'rejected'], 'new');
        $this->fields['acceptor'] = new field('acceptor', PARAM_INT, false, null, null);
        $this->fields['timeaccepted'] = new field('timeaccepted', PARAM_INT, false, null, null);
        $this->fields['timecreated'] = new field('timecreated', PARAM_INT, true, null, time());
    }
    
    public function export_for_template()
    {
        global $DB;
        
        switch($this->commentarea)
        {
            case 'course':
                // Получение курса из БД
                $course = $DB->get_record('course', ['id' => $this->itemid]);
                $areaitemname = $course->fullname ?? get_string('feedback_course_unknown', 'local_crw');
                $areaname = get_string('feedback_area_'.$this->commentarea, 'local_crw');
                break;
            default:
                $areaitemname = get_string('feedback_item_unknown', 'local_crw');
                $areaname = get_string('feedback_area_unknown', 'local_crw');
                break;
        }
        
        $sourcetype = '';
        $sourcename = '';
        switch($this->component)
        {
            case 'mod_endorsement':
                $context = \context::instance_by_id($this->contextid);
                $cm = get_coursemodule_from_id('endorsement', $context->instanceid, 0, false, MUST_EXIST);
                $sourcetype = get_string('modulename', $this->component);
                $sourcename = $cm->name;
                break;
            default: 
                break;
        }
        
        $user = core_user::get_user($this->userid);
        $username = fullname($user);
        
        return [
            'id' => $this->id,
            'areaname' => $areaname,
            'areaitemname' => $areaitemname,
            'sourcetype' => $sourcetype,
            'sourcename' => $sourcename,
            'text' => format_text($this->content, $this->format),
            'pubdate' => userdate($this->timecreated),
            'status' => $this->status,
            'username' => $username
        ];
    }
    
}