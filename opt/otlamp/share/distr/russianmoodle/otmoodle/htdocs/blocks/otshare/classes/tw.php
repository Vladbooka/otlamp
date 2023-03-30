<?php

namespace block_otshare;

use block_otshare\base as base;
use moodle_url;


class tw extends base
{
    public function get_serviceshortname()
    {
        return 'tw';
    }
    
    protected function get_servicelinkshareurl()
    {
        return new moodle_url('https://twitter.com/share');
    }
    
    protected function get_share_link_parameters()
    {
        return [
            'url' => $this->urltoshare
        ];
    }
    
    protected function get_servicejsurl()
    {
        return new moodle_url('https://platform.twitter.com/widgets.js');
    }
    
    protected function get_service_tag()
    {
        return 'a';
    }
    
    protected function get_servicebuttontype()
    {
        return 'small';
    }
    
    protected function get_share_button_parameters()
    {
        return [
            'class' => 'twitter-share-button',
            'data-url' => $this->urltoshare,
            'data-size' => $this->get_servicebuttontype()
        ];
    }
}