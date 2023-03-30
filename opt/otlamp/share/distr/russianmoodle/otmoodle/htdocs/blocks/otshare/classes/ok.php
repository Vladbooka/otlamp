<?php

namespace block_otshare;

use block_otshare\base as base;
use moodle_url;

class ok extends base
{
    public function get_serviceshortname()
    {
        return 'ok';
    }
    
    protected function get_servicelinkshareurl()
    {
        return new moodle_url('https://connect.ok.ru/offer');
    }
    
    protected function get_share_link_parameters()
    {
        return [
            'url' => $this->urltoshare
        ];
    }
    
    protected function get_servicejsurl()
    {
        return new moodle_url('https://connect.ok.ru/connect.js');
    }
    
    protected function get_servicebuttontype()
    {
        return "{'sz':20,'st':'rounded','ck':2}";
    }
    
    protected function get_share_button_parameters()
    {
        return [];
    }
}