<?php

namespace block_otshare;

use block_otshare\base as base;
use moodle_url;


class vk extends base
{
    public function get_serviceshortname()
    {
        return 'vk';
    }
    
    protected function get_servicelinkshareurl()
    {
        return new moodle_url('http://vk.com/share.php');
    }
    
    protected function get_share_link_parameters()
    {
        return [
            'url' => $this->urltoshare
        ];
    }
    
    protected function get_servicejsurl()
    {
        return new moodle_url('http://vk.com/js/api/share.js?90');
    }
    
    protected function get_servicebuttontype()
    {
        return 'button';
    }
    
    protected function get_share_button_parameters()
    {
        return [];
    }
}