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
 * Сертификаты. Главная библиотека.
 *
 * @package    block
 * @subpackage simplecertificate
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Отобразить ссылку на файл сертификата
 * 
 * @param stdClass $issuecert The issued certificate object
 * @return string file link url
 */
function print_issue_certificate_file(stdClass $issuecert) {
    global $CFG, $OUTPUT;
    require_once("$CFG->dirroot/mod/simplecertificate/locallib.php");
    
    // Trying to cath course module context
    try {
        $fs = get_file_storage();
        if (!$fs->file_exists_by_hash($issuecert->pathnamehash)) {
            throw new Exception();
        }
        $file = $fs->get_file_by_hash($issuecert->pathnamehash);
        $output = '<img src="' . $OUTPUT->image_url(file_mimetype_icon($file->get_mimetype())) . '" height="16" width="16" alt="' .
         $file->get_mimetype() . '" />&nbsp;';
        
        $url = new moodle_url('/mod/simplecertificate/wmsendfile.php');
        $url->param('id', $issuecert->id);
        $url->param('sk', sesskey());
        
        $output .= '<a href="' . $url->out(true) . '" target="_blank" >' . s($file->get_filename()) . '</a>';
    
    } catch (Exception $e) {
        $output = get_string('filenotfound', 'simplecertificate', '');
    }
    
    return '<div class="files">' . $output . '<br /> </div>';

}

/**
 * Serves any files associated with the theme settings.
 *
 * @param stdClass $course
 * @param stdClass $cm
 * @param context $context
 * @param string $filearea
 * @param array $args
 * @param bool $forcedownload
 * @param array $options
 * @return bool
 */
function block_simplecertificate_pluginfile($course, $cm, $context, $filearea, $args, $forcedownload, array $options = array())
{

    // Check the contextlevel is as expected - if your plugin is a block, this becomes CONTEXT_BLOCK, etc.
    /*if ($context->contextlevel != CONTEXT_MODULE) {
    return false;
    }*/

    // Make sure the filearea is one of those used by the plugin.
    /*if ($filearea !== 'expectedfilearea' && $filearea !== 'anotherexpectedfilearea') {
    return false;
    }*/

    // Make sure the user is logged in and has access to the module (plugins that are not course modules should leave out the 'cm' part).
    //require_login($course, true, $cm);

    // Check the relevant capabilities - these may vary depending on the filearea being accessed.
    /*if (!has_capability('mod/MYPLUGIN:view', $context)) {
    return false;
    }*/
    
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
    $file = $fs->get_file($context->id, 'block_simplecertificate', $filearea, $itemid, $filepath, $filename);

    if (!$file) {
        return false; // The file does not exist.
    }

    // NOTE: it woudl be nice to have file revisions here, for now rely on standard file lifetime,
    //       do not lower it because the files are dispalyed very often.
    \core\session\manager::write_close();
    send_stored_file($file, null, 0, $forcedownload, $options);
}