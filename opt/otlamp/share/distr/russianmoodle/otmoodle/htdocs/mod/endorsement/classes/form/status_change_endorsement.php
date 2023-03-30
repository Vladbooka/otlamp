<?php

namespace mod_endorsement\form;

use context_module;
use html_writer;

class status_change_endorsement {
    
    public static function render_form($itemid, $baseurl=null, $ajaxformdata=null)
    {
        $result = '';
        
        $formyaml  = "class:" . PHP_EOL;
        
        foreach(\mod_endorsement\endorsements::get_statuses() as $statuscode => $statusname)
        {
            $formyaml .= "  item_".$itemid."_status[".$statuscode."]:" . PHP_EOL;
            $formyaml .= "      type: 'radio'" . PHP_EOL;
            $formyaml .= "      autoindex: 1" . PHP_EOL;
            $formyaml .= "      desc: null" . PHP_EOL;
            $formyaml .= "      label: '".$statusname."'" . PHP_EOL;
            $formyaml .= "      value: '".$statuscode."'" . PHP_EOL;
        }
        
        $formyaml .= "  save:" . PHP_EOL;
        $formyaml .= "      type: submit" . PHP_EOL;
        $formyaml .= "      label: '".get_string('endorsement_form_field_save', 'mod_endorsement')."'";
        
        
        $parseresult = \otcomponent_customclass\utils::parse($formyaml);
        
        if ( $parseresult->is_form_exists() )
        {
            $endorsement = \local_crw\feedback\item::get($itemid);
            
            $form = $parseresult->get_form();
            
            $form->setForm(
                $baseurl instanceof \moodle_url ? $baseurl->out(false) : $baseurl,
                null,
                'post',
                '',
                ['class' => 'unresponsive'],
                true,
                $ajaxformdata
            );
            
            $defaultvalues = new \stdClass();
            $defaultvalues->{'item_'.$itemid.'_status'} = $endorsement->status;
            $form->set_data($defaultvalues);
            
            $formdata = $form->get_data();
            if ($formdata)
            {
                $result .= static::process_form($formdata, $itemid, $baseurl);
            }
            
            $result .= $form->render();
        }
        
        return \html_writer::div($result, 'mod_endorsement_form_change_status');
    }
    
    protected static function process_form($formdata, $itemid, $baseurl=null)
    {
        
        if (isset($formdata->{'item_'.$itemid.'_status'}))
        {
            $endorsement = \local_crw\feedback\item::get($itemid);
            $modulecontext = \context::instance_by_id($endorsement->contextid);
            if (!has_capability('mod/endorsement:moderate_endorsements', $modulecontext))
            {
                return html_writer::div(
                    get_string('endorsement_save_failed_access_denied', 'mod_endorsement'),
                    'mod_endorsement_change_status_failed'
                );
            }
            
            $endorsement->status = $formdata->{'item_'.$itemid.'_status'};
            
            if ($endorsement->save())
            {
                if (!is_null($baseurl))
                {
                    redirect($baseurl);
                }
                else
                {
                    return true;
                }
            }
            else
            {
                return html_writer::div(
                    get_string('endorsement_save_failed_access_denied', 'mod_endorsement'),
                    'mod_endorsement_change_status_failed'
                );
            }
            
        } else
        {
            return html_writer::div(
                get_string('endorsement_was_empty', 'mod_endorsement'),
                'mod_endorsement_change_status_failed'
            );
        }
    }
}