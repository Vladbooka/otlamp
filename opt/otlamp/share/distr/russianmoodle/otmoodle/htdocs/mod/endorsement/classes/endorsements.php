<?php

namespace mod_endorsement;

class endorsements {
    
    /**
     * @var int Number of results per page.
     */
    const DISPLAY_RESULTS_PER_PAGE = 10;
    
    public static function process_items($items, $baseurl=null)
    {
        $processeditems = [];
        $itemsdata = [];
        if (!empty($items))
        {
            foreach($items as $item)
            {
                $modulecontext = \context::instance_by_id($item->contextid);
                $ismoderator = has_capability('mod/endorsement:moderate_endorsements', $modulecontext);
                if (!has_capability('mod/endorsement:view_'.$item->status.'_own_endorsements', $modulecontext) &&
                    !has_capability('mod/endorsement:view_endorsements', $modulecontext) &&
                    !$ismoderator)
                {
                    continue;
                } else
                {
                    $processeditems[$item->id] = $item;
                    $moderform = '';
                    if($ismoderator)
                    {
                        $moderform = \mod_endorsement\form\status_change_endorsement::render_form(
                            $item->id,
                            $baseurl
                        );
                    }
                    $itemsdata[$item->id] = [
                        'moderator_tools' => $moderform
                    ];
                }
                
            }
        }
        return [$processeditems, $itemsdata];
    }
    
    protected static function render_items_with_template($items, $templatename, $baseurl=null)
    {
        global $OUTPUT;
        
        list($processeditems, $itemsdata) = self::process_items($items, $baseurl);
        
        $data = \local_crw\feedback\items::export_for_template(
            $processeditems,
            $itemsdata
        );
        
        return $OUTPUT->render_from_template($templatename, $data);
    }
    
    public static function get_statuses()
    {
        return [
            'rejected' => get_string('endorsement_status_rejected', 'mod_endorsement'),
            'new' => get_string('endorsement_status_new', 'mod_endorsement'),
            'accepted' => get_string('endorsement_status_accepted', 'mod_endorsement')
        ];
    }
    
    public static function get_statuses_filter(\moodle_url $url)
    {
        $result = [];
        
        $statuses = self::get_statuses();
        foreach($statuses as $statuscode => $statusname)
        {
            $statusurl = clone($url);
            $statusurl->param('status', $statuscode);
            $result[] = [
                'code' => $statuscode,
                'link' => $statusurl->out(false),
                'name' => $statusname,
                'active' => ($url->get_param('status') == $statuscode)
            ];
        }
        $allstatusesurl = clone($url);
        $allstatusesurl->remove_params(['status']);
        $result[] = [
            'link' => $allstatusesurl->out(false),
            'name' => get_string('endorsement_status_all', 'mod_endorsement'),
            'active' => is_null($url->get_param('status'))
        ];
        
        return $result;
    }
}