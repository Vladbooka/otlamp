<?php
namespace local_otcontrolpanel\field;

use local_otcontrolpanel\entity\abstract_entity;

class property_field extends \local_otcontrolpanel\field\abstract_field {

    protected $property;

    public function __construct(abstract_entity $entity, $property, $displayname=null)
    {
        parent::__construct($entity, $displayname);
        $this->property = $property;
    }

    /**
     * {@inheritDoc}
     * @see \local_otcontrolpanel\field\abstract_field::get_plain_value()
     */
    protected function get_plain_value($record, $suffix='')
    {
        return $this->get_record_property($record, $this->property);
    }

    public function get_code() {
        return $this->property;
    }
}