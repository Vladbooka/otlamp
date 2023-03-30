<?php

namespace mod_endorsement;

class moderatorside extends endorsements {
    
    public static function render_items($items, $baseurl=null)
    {
        return parent::render_items_with_template($items, 'mod_endorsement/moderator_endorsements_items', $baseurl);
    }
}