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
 * Блок списка категорий в виде ссылок с иконками. Класс плагина.
 * 
 * @package    local
 * @subpackage crw
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

class crw_categories_list_icons extends local_crw_plugin 
{
    
    protected $type = CRW_PLUGIN_TYPE_CATEGORIES_LIST;
    
    /**
     * Сформировать html блока
     *
     * @param int $id - ID категории курсов
     * @param array $options - Дополнительные опции
     *
     * @return string - HTML-код блока
     */
    public function outputhtml($id = 0, $options = array() )
    {
        global $CFG;
        require_once($CFG->dirroot .'/local/crw/plugins/categories_list_icons/renderer.php');
        $renderer = new crw_categories_list_icons_renderer();
        return $renderer->get_block($id, $options);
    }
    
    /**
     * Сформировать html блока
     *
     * @param int $id - ID категории курсов
     * @param array $options - Дополнительные опции
     *
     * @return string - HTML-код блока
     */
    public function display($options = array() )
    {
        // Получим id категории
        if ( isset($options['cid']) )
        {
            $catid = $options['cid'];
        } else
        {
            $catid = 0;
        }
        return $this->outputhtml($catid, $options);
    }
}

function crw_categories_list_icons_pluginfile($course, $cm, $context, $filearea, $args, $forcedownload, array $options = array())
{
    $itemid = array_shift($args);

    $filename = array_pop($args); // The last item in the $args array.
    if (!$args) {
        $filepath = '/'; // $args is empty => the path is '/'
    } else {
        $filepath = '/'.implode('/', $args).'/'; // $args contains elements of the filepath
    }

    $fs = get_file_storage();
    $file = $fs->get_file($context->id, 'crw_categories_list_icons', $filearea, $itemid, $filepath, $filename);

    if (!$file) {
        return false; // The file does not exist.
    }

    \core\session\manager::write_close();
    send_stored_file($file, null, 0, $forcedownload, $options);
}
