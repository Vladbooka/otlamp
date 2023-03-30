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
 * Блок топ-10
 * 
 * @package    block
 * @subpackage topten
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

function block_topten_pluginfile($course, $cm, $context, $filearea, $args, $forcedownload, array $options = [])
{
    // Leave this line out if you set the itemid to null in make_pluginfile_url (set $itemid to 0 instead).
    $itemid = array_shift($args); // The first item in the $args array.
    
    // Use the itemid to retrieve any relevant data records and perform any security checks to see if the
    // user really does have access to the file in question.
    
    // Extract the filename / filepath from the $args array.
    $filename = array_pop($args); // The last item in the $args array.
    if (!$args) {
        $filepath = '/'; // $args is empty => the path is '/'
    } else {
        $filepath = '/'.implode('/', $args).'/'; // $args contains elements of the filepath
    }
    
    // Retrieve the file from the Files API.
    $fs = get_file_storage();
    $file = $fs->get_file($context->id, 'block_topten', $filearea, $itemid, $filepath, $filename);
    
    if (!$file) {
        return false; // The file does not exist.
    }
    
    // NOTE: it woudl be nice to have file revisions here, for now rely on standard file lifetime,
    //       do not lower it because the files are dispalyed very often.
    \core\session\manager::write_close();
    send_stored_file($file, null, 0, $forcedownload, $options);
}

function block_topten_output_fragment_router($args)
{
    if (empty($args['rating_type']) || empty($args['output_fragment']))
    {
        throw new coding_exception(get_string('exception_required_paramater_not_specified','block_topten'));
    }
    $blockobj = new block_topten();
    // псевдо конфиг для получения фрагмента
    $config = new stdClass();
    $config->rating_type = $args['rating_type'];
    // получим обьект отчета
    $ratingobject = block_topten\report::get_rating_object($config, $blockobj->instance->id);
    // получим назмание метода фрагмента в текущем отчете
    $methodname = 'output_fragment_'.$args['output_fragment'];
    if (method_exists($ratingobject, $methodname))
    {
        return $ratingobject->$methodname($args);
    }
    
    throw new coding_exception(get_string('exception_output_fragment_not_found','block_topten'));
}
/**
 * Возвращает объект dof
 * @return NULL dof_control
 */
function block_topten_get_dof() {
    global $CFG;
    $dof = null;
    if (file_exists($CFG->dirroot . '/blocks/dof/locallib.php')) {
        require_once($CFG->dirroot . '/blocks/dof/locallib.php');
        global $DOF;
        $dof = & $DOF;
    }
    return $dof;
}

