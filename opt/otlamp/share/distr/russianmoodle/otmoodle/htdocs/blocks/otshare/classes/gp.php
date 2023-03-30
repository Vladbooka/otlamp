<?php

namespace block_otshare;

use block_otshare\base as base;
use moodle_url;

class gp extends base
{
    public function get_serviceshortname()
    {
        return 'gp';
    }
    
    protected function get_servicelinkshareurl()
    {
        return new moodle_url('https://plus.google.com/share');
    }
    
    protected function get_share_link_parameters()
    {
        return [
            'url' => $this->urltoshare
        ];
    }
    
    protected function get_servicejsurl()
    {
        return new moodle_url('https://apis.google.com/js/platform.js');
    }
    
    protected function get_servicebuttontype()
    {
        return 'bubble';
    }
    
    protected function get_share_button_parameters()
    {
        return [
            'class' => 'g-plus',
            'data-action' => 'share',
            'data-href' => $this->urltoshare,
            'data-annotation' => $this->get_servicebuttontype()
        ];
    }
}