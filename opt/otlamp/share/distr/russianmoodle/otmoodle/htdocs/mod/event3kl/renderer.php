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
 * This file contains a renderer for the event3kl class
 *
 * @package   mod_event3kl
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * A custom renderer class that extends the plugin_renderer_base and is used by the event3kl module.
 *
 * @package mod_event3kl
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class mod_event3kl_renderer extends plugin_renderer_base {
    /**
     * Render a course index summary
     *
     * @param event3kl_course_index_summary $indexsummary
     * @return string
     */
    public function render_event3kl_course_index_summary(event3kl_course_index_summary $indexsummary) {
        $o = '';
        
        $strplural = get_string('modulenameplural', 'event3kl');
        $strsectionname  = $indexsummary->courseformatname;
        $strgrade = get_string('grade');
        
        $table = new html_table();
        if ($indexsummary->usesections) {
            $table->head  = array ($strsectionname, $strplural, $strgrade);
            $table->align = array ('left', 'left', 'center', 'right', 'right');
        } else {
            $table->head  = array ($strplural, $strgrade);
            $table->align = array ('left', 'left', 'center', 'right');
        }
        $table->data = array();
        
        $currentsection = '';
        foreach ($indexsummary->modules as $info) {
            $params = array('id' => $info['cmid']);
            $link = html_writer::link(new moodle_url('/mod/event3kl/view.php', $params),
                $info['cmname']);
            
            $printsection = '';
            if ($indexsummary->usesections) {
                if ($info['sectionname'] !== $currentsection) {
                    if ($info['sectionname']) {
                        $printsection = $info['sectionname'];
                    }
                    if ($currentsection !== '') {
                        $table->data[] = 'hr';
                    }
                    $currentsection = $info['sectionname'];
                }
            }
            
            if ($indexsummary->usesections) {
                $row = array($printsection, $link, $info['gradeinfo']);
            } else {
                $row = array($link, $info['gradeinfo']);
            }
            $table->data[] = $row;
        }
        
        $o .= html_writer::table($table);
        
        return $o;
    }
    
    /**
     * Page is done - render the footer.
     *
     * @return void
     */
    public function render_footer() {
        return $this->output->footer();
    }
}