<?php

namespace mod_endorsement;

class userside extends endorsements {
    
    public static function render_items($items, $baseurl=null)
    {
        return parent::render_items_with_template($items, 'mod_endorsement/user_endorsements_items', $baseurl);
    }
}