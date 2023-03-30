<?php
namespace local_otcontrolpanel\filter;

use local_otcontrolpanel\entity\abstract_entity;

class property_null extends \local_otcontrolpanel\filter\abstract_filter {

    protected $property;
    protected $notnull;

    public function __construct(abstract_entity $entity, $property, bool $notnull=false) {

        parent::__construct($entity, null);
        $this->property = $property;
        $this->notnull = $notnull;
    }

    /**
     * {@inheritDoc}
     * @see \local_otcontrolpanel\filter\abstract_filter::get_params()
     */
    public function get_params()
    {
        return [];
    }

    public function get_select_property() {

        if (preg_match('/(.+)\.(.*)/', $this->property))
        {
            return $this->property;
        }
        return '{'.$this->entity->get_storagename().'}.'.$this->property;
    }

    /**
     * {@inheritDoc}
     * @see \local_otcontrolpanel\filter\abstract_filter::get_select()
     */
    public function get_select()
    {
        return $this->get_select_property().' IS'.($this->notnull ? ' NOT':'').' NULL';
    }

    /**
     *
     * {@inheritDoc}
     * @see \local_otcontrolpanel\filter\abstract_filter::register_joins()
     */
    protected function register_joins() {}

}