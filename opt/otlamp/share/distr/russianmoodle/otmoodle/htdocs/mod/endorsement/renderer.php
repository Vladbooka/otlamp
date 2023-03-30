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
 * Endorsements renderer.
 *
 * @package     mod_endorsement
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_endorsement\output;

defined('MOODLE_INTERNAL') || die();

class renderer extends \plugin_renderer_base {
    
    public function render_userside($items, $totalcount, $baseurl, $newitemurl = null, $pagenum=0, $savesuccess=false) {
        
        $successmessage = $savesuccess ? get_string('endorsement_publication_success', 'mod_endorsement') : '';
        
        $items = \mod_endorsement\userside::render_items($items) ?? '';
        
        $pagingbar = $this->output->paging_bar(
            $totalcount,
            $pagenum,
            \mod_endorsement\userside::DISPLAY_RESULTS_PER_PAGE,
            $baseurl
        );
        
        $data = [
            'header' => get_string('user_list_header', 'mod_endorsement'),
            'successmessage' => $successmessage,
            'items' => $items,
            'paging_bar' => $pagingbar
        ];
        
        if (isset($newitemurl))
        {
            $data['onemoreurl'] = $newitemurl->out(false);
            $data['onemore'] = get_string('onemore', 'mod_endorsement');
        }
        
        
        return $this->output->render_from_template('mod_endorsement/user_endorsements_page', $data);
    }
    
    protected function render_moderatorside_filter(\moodle_url $pageurl, $currentcourse)
    {
        $data = [];
        
        $statuses = \mod_endorsement\moderatorside::get_statuses_filter($pageurl);
        if (!empty($statuses))
        {
            $data['statuses_title'] = get_string('filter_statuses', 'mod_endorsement');
            $data['statuses'] = $statuses;
            $data['statuses_count'] = count($data['statuses']);
        }
        
        $courses = get_courses('all', 'c.sortorder ASC', 'c.id, c.fullname, c.visible, c.category');
        
        if (!empty($courses))
        {
            if (array_key_exists($currentcourse, $courses))
            {
                $courses[$currentcourse]->active = true;
            }
            $data['courses_title'] = get_string('filter_courses', 'mod_endorsement');
            $data['courses'] = array_values($courses);
            $data['courses_count'] = count($data['courses']);
            
            $formaction = clone($pageurl);
            $formaction->remove_params('courseid');
            $data['formaction'] = $formaction->out(false);
        }
        
        return $this->output->render_from_template('mod_endorsement/moderator_endorsements_filter', $data);
    }
    
    public function render_moderatorside($items, $totalcount, $baseurl, $pagenum=0, $currentcourse = 1) {
        
        $filter = $this->render_moderatorside_filter($baseurl, $currentcourse) ?? '';
        
        $items = \mod_endorsement\moderatorside::render_items($items, $baseurl) ?? '';
        
        $pagingbar = $this->output->paging_bar(
            $totalcount,
            $pagenum,
            \mod_endorsement\moderatorside::DISPLAY_RESULTS_PER_PAGE,
            $baseurl
        );
        
        $data = [
            'header' => get_string('moderator_list_header', 'mod_endorsement'),
            'filter' => $filter,
            'items' => $items,
            'paging_bar' => $pagingbar
        ];
        
        return $this->output->render_from_template('mod_endorsement/moderator_endorsements_page', $data);
    }
}