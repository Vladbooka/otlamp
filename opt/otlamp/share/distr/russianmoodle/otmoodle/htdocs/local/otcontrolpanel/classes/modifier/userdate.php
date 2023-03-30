<?php
namespace local_otcontrolpanel\modifier;

use local_otcontrolpanel\modifier\common\abstract_modifier;

class userdate extends abstract_modifier{
    public function modify($value, $record)
    {
        if ((string)$value == (string)(int)$value)
        {
            $value = userdate($value);
        }
        return $value;
    }
}