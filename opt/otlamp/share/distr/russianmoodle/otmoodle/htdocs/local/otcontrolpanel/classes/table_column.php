<?php
namespace local_otcontrolpanel;

use local_otcontrolpanel\field\abstract_field;

class table_column {

    private $field;

    public function __construct(abstract_field $field)
    {
        $this->field = $field;
    }

    public function get_value($record, $suffix='')
    {
        return $this->field->get_value($record, $suffix);
    }

    public function get_classes()
    {
        $classes = [];
        if (is_a($this->field, '\\local_otcontrolpanel\\field\\related_table'))
        {
            $classes[] = 'p-0';
        }
        return $classes;
    }

    public function get_code()
    {
        static $i = 0;
        $i++;
        return $this->field->get_full_code().'_'.$i;
    }

    public function get_display_name()
    {
        return $this->field->get_display_name();
    }
}
