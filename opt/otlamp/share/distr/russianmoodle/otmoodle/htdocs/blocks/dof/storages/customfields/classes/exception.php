<?php

class dof_storage_customfields_exception extends dof_exception
{
    public function __construct($errorcode, $plugin = '', $link = '', $a = null, $debuginfo = null)
    {
        parent::__construct($errorcode, 'storage_customfields', $link, $a, $debuginfo);
    }
}