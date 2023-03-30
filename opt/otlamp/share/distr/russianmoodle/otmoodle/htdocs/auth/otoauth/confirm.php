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
 * Confirm self registered user.
 *
 * @package    auth
 * @subpackage otoauth
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

global $DB;

require('../../config.php');

$data = optional_param('data', '', PARAM_RAW);  // Formatted as:  uid/sha1(uid+time())

$PAGE->set_url('/auth/otoauth/confirm.php');
$PAGE->set_context(context_system::instance());

$authplugin = get_auth_plugin('otoauth');

if (!empty($data)) {

    $datareceived = base64_decode(urldecode($data));
    if (!empty($datareceived)) {
        $dataelements = explode('/', $datareceived, 2); // Stop after 1st slash
        $uid = $dataelements[0];
        $usersecret = $dataelements[1];
    } else {
        print_error("errorwhenconfirming");
    }
    
    $confirmed = $authplugin->user_confirm_email($uid, $usersecret);

    if ($confirmed == AUTH_CONFIRM_ALREADY) {
        $oauthuser = $DB->get_record('auth_otoauth', array('id' => $uid));
        $user = get_complete_user_data('username', $oauthuser->userid);
        
        $PAGE->navbar->add(get_string("alreadyconfirmed"));
        $PAGE->set_title(get_string("alreadyconfirmed"));
        $PAGE->set_heading($COURSE->fullname);
        
        $gratitude = get_string("thanks") . ", " . fullname($USER);
        
        echo $OUTPUT->header();
        echo $OUTPUT->box_start('generalbox centerpara boxwidthnormal boxaligncenter');
            echo html_writer::tag('h3', $gratitude);
            echo html_writer::tag('p', get_string('alreadyconfirmed'));
            echo $OUTPUT->single_button("$CFG->wwwroot/course/", get_string('courses'));
        echo $OUTPUT->box_end();
        echo $OUTPUT->footer();      
        
    } else if ($confirmed == AUTH_CONFIRM_OK) {

        // Пользователь подтвержден, залонинем
        if (!$user = get_complete_user_data('id', $uid)) {
            print_error('cannotfinduser', '', '', s($uid));
        }

        complete_user_login($user);

        // Отсылаем пользователей туда, куда они направлялись
        if (!empty($SESSION->wantsurl)) { 
            $goto = $SESSION->wantsurl;
            unset($SESSION->wantsurl);
            redirect($goto);
        }

        $PAGE->navbar->add(get_string("confirmed"));
        $PAGE->set_title(get_string("confirmed"));
        $PAGE->set_heading($COURSE->fullname);
        
        $gratitude = get_string("thanks") . ", " . fullname($USER);
        
        echo $OUTPUT->header();
        echo $OUTPUT->box_start('generalbox centerpara boxwidthnormal boxaligncenter');
            echo html_writer::tag('h3', $gratitude);
            echo html_writer::tag('p', get_string('confirmed'));
            echo $OUTPUT->single_button("$CFG->wwwroot/course/", get_string('courses'));
        echo $OUTPUT->box_end();
        echo $OUTPUT->footer();      
        
    } else if ($confirmed == AUTH_CONFIRM_FAIL) {
        print_error('invalidconfirmdata');
    } else {
        print_error("errorwhenconfirming");
    }
} else {
    print_error("errorwhenconfirming");
}