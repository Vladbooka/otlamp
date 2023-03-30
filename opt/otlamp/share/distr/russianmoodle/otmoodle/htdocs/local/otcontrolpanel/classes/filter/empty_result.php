<?php
namespace local_otcontrolpanel\filter;

use local_otcontrolpanel\entity\abstract_entity;

class empty_result extends \local_otcontrolpanel\filter\abstract_filter {

    protected $property;

    public function __construct(abstract_entity $entity, $value=null) {

        parent::__construct($entity, null);
    }

    /**
     * {@inheritDoc}
     * @see \local_otcontrolpanel\filter\abstract_filter::get_params()
     */
    public function get_params()
    {
        return [];
    }

    /**
     * {@inheritDoc}
     * @see \local_otcontrolpanel\filter\abstract_filter::get_select()
     */
    public function get_select()
    {
        return '1=2';
    }

    /**
     *
     * {@inheritDoc}
     * @see \local_otcontrolpanel\filter\abstract_filter::register_joins()
     */
    protected function register_joins() {}

}