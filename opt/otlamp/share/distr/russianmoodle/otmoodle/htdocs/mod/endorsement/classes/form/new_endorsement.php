<?php

namespace mod_endorsement\form;

use context;
use context_module;
use core_user;
use html_writer;
use core\message\message;

class new_endorsement {
    
    public static function render_form($cmid, $formaction, $successurl, $cancelurl = null)
    {
        $result = '';
        
        $formyaml  = "class:" . PHP_EOL;
        $formyaml .= "  content:" . PHP_EOL;
        $formyaml .= "      type: 'textarea'" . PHP_EOL;
        $formyaml .= "      label: '".get_string('endorsement_form_field_endorsement', 'mod_endorsement')."'" . PHP_EOL;
        $formyaml .= "      rules: ['required']" . PHP_EOL;
        $formyaml .= "  save:" . PHP_EOL;
        $formyaml .= "      type: submit" . PHP_EOL;
        $formyaml .= "      label: '".get_string('endorsement_form_field_save', 'mod_endorsement')."'";
        
        if (isset($cancelurl))
        {
            $cancellink = \html_writer::link(
                $cancelurl,
                get_string('endorsement_form_field_cancel', 'mod_endorsement'),
                ['class' => 'btn btn-primary']
            );
            $formyaml .= PHP_EOL;
            $formyaml .= "  '".$cancellink."':" . PHP_EOL;
            $formyaml .= "      type: html";
        }
        
        $parseresult = \otcomponent_customclass\utils::parse($formyaml);
        
        if ( $parseresult->is_form_exists() )
        {
            $form = $parseresult->get_form();
            $form->setForm($formaction->out(false));
            $formdata = $form->get_data();
            if ($formdata)
            {
                $result .= static::process_form($formdata, $cmid, $successurl);
            }
            
            $result .= $form->render();
        }
        
        return \html_writer::div($result, 'mod_endorsement_form_add_item');
    }
    
    protected static function send_notification($feedbackitem)
    {
        $modulecontext = context::instance_by_id($feedbackitem->contextid);
        $coursecontext = $modulecontext->get_course_context();
        
        $receivers = get_users_by_capability(
            $modulecontext,
            'mod/endorsement:receive_new_endorsement_notification',
            'u.id'
        );
        
        if (!empty($receivers))
        {
            
            $course = get_course($coursecontext->instanceid);
            $messagevars = new \stdClass();
            $messagevars->userfullname = fullname($feedbackitem->userid);
            $messagevars->coursefullname = $course->fullname;
            $messagevars->courseshortname = $course->shortname;
            $messagevars->moderatelink = '';
            $messagevars->endorsementcontent = nl2br($feedbackitem->content);
            
            foreach($receivers as $receiver)
            {
                if (has_capability('mod/endorsement:moderate_endorsements', $modulecontext, $receiver->id, false))
                {
                    $moderateurl = new \moodle_url('/mod/endorsement/list.php', [
                        'id' => $modulecontext->instanceid,
                        'status' => 'new'
                    ]);
                    $moderatelink = \html_writer::link(
                        $moderateurl,
                        get_string('moderate_link_text', 'mod_endorsement')
                    );
                    $messagevars->moderatelink = \html_writer::tag('p', $moderatelink);
                }
                $message = new message();
                $message->userfrom = core_user::get_noreply_user();
                $message->component = 'mod_endorsement';
                $message->fullmessageformat = FORMAT_HTML;
                $message->subject = strip_tags(get_string(
                    'message__new_endorsement__subject',
                    'mod_endorsement',
                    $messagevars
                ));
                $message->smallmessage = strip_tags(get_string(
                    'message__new_endorsement__smallmessage',
                    'mod_endorsement',
                    $messagevars
                ));
                $message->fullmessagehtml = get_string(
                    'message__new_endorsement__fullmessage',
                    'mod_endorsement',
                    $messagevars
                );
                $message->fullmessage = strip_tags($message->fullmessagehtml);
                $message->name = 'new_endorsement';
                $message->userto = $receiver->id;
                $message->notification = 1;
                
                message_send($message);
            }
        }
    }
        
    
    protected static function process_form($formdata, $cmid, $successurl)
    {
        global $USER;
        
        $modulecontext = context_module::instance($cmid);
        
        if (!has_capability('mod/endorsement:to_endorse', $modulecontext))
        {
            return html_writer::div(
                get_string('endorsement_endorse_access_denied', 'mod_endorsement'),
                'mod_endorsement_publication_failed'
            );
        }
        
        if (isset($formdata->content))
        {
            $coursecontext = $modulecontext->get_course_context();
            $courseid = $coursecontext->instanceid;
            
            $feedbackitem = new \local_crw\feedback\item([
                'contextid' => $modulecontext->id,
                'component' => 'mod_endorsement',
                'commentarea' => 'course',
                'itemid' => $courseid,
                'userid' => $USER->id,
                'content' => $formdata->content,
                'format' => FORMAT_PLAIN
            ]);
            
            if ($feedbackitem->save())
            {
                self::send_notification($feedbackitem);
                redirect($successurl);
                
            } else
            {
                return html_writer::div(
                    get_string('endorsement_save_failed', 'mod_endorsement'),
                    'mod_endorsement_publication_failed'
                );
            }
        } else
        {
            return html_writer::div(
                get_string('endorsement_was_empty', 'mod_endorsement'),
                'mod_endorsement_publication_failed'
            );
        }
    }
}