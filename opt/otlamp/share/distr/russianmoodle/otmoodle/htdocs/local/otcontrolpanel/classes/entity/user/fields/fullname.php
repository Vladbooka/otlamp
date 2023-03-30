<?php
namespace local_otcontrolpanel\entity\user\fields;

class fullname extends \local_otcontrolpanel\field\abstract_field {

    /**
     * {@inheritDoc}
     * @see \local_otcontrolpanel\field\abstract_field::get_plain_value()
     */
    protected function get_plain_value($record, $suffix='')
    {
        return fullname($record);
    }
}