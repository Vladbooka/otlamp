<?php

namespace block_otshare;

use block_otshare\base as base;
use moodle_url;

class fb extends base
{
    public function get_serviceshortname()
    {
        return 'fb';
    }
    
    protected function get_servicelinkshareurl()
    {
        return new moodle_url('https://www.facebook.com/sharer/sharer.php');
    }
    
    protected function get_share_link_parameters()
    {
        return [
            'u' => $this->urltoshare
        ];
    }
    
    protected function get_servicejsurl()
    {
        return new moodle_url('https://connect.facebook.net/ru_RU/sdk.js#xfbml=1&version=v2.4');
    }
    
    protected function get_servicebuttontype()
    {
        return 'button_count';
    }
    
    protected function get_share_button_parameters()
    {
        return [
            'class' => 'fb-share-button',
            'data-href' => $this->urltoshare,
            'data-layout' => $this->get_servicebuttontype()
        ];
    }
}