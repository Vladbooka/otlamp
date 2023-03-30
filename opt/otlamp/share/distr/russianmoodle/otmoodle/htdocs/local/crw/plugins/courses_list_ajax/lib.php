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

defined('MOODLE_INTERNAL') || die();

/**
 * Блок таблиы курсов c AJAX - отображением описания. Класс плагина.
 * 
 * @package    local
 * @subpackage crw
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

class crw_courses_list_ajax extends local_crw_plugin 
{
    
    protected $type = CRW_PLUGIN_TYPE_COURSES_LIST;
    
    /**
     * Сформировать html блока
     *
     * @param array $options - Дополнительные опции
     *
     * @return string - HTML-код блока
     */
    public function display($options = array())
    {
        global $CFG, $PAGE;
        require_once($CFG->dirroot .'/local/crw/plugins/courses_list_ajax/renderer.php');
        $renderer = new crw_courses_list_ajax_renderer();
        
        // Подключение js плагина
        if( get_config('crw_courses_list_ajax', 'enable_ajax') == '1' )
        {
            $url = new moodle_url($this->get_file_url('plugin.js', false));
            $PAGE->requires->js($url);
        }
        
        $hidemorethan = get_config('crw_courses_list_ajax','hide_more_than');
        if( ! empty($hidemorethan) )
        {
            $url = new moodle_url($this->get_file_url('showhidecourses.js', false));
            $PAGE->requires->js($url);
        }
        
        return $renderer->display($options);
    }
}

function crw_courses_list_ajax_pluginfile($course, $cm, $context, $filearea, $args, $forcedownload, array $options = array())
{
    $itemid = array_shift($args);

    $filename = array_pop($args); // The last item in the $args array.
    if (!$args) {
        $filepath = '/'; // $args is empty => the path is '/'
    } else {
        $filepath = '/'.implode('/', $args).'/'; // $args contains elements of the filepath
    }

    $fs = get_file_storage();
    $file = $fs->get_file($context->id, 'crw_courses_list_ajax', $filearea, $itemid, $filepath, $filename);

    if (!$file) {
        return false; // The file does not exist.
    }

    \core\session\manager::write_close();
    send_stored_file($file, null, 0, $forcedownload, $options);
}