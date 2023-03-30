<?php
namespace local_otcontrolpanel\filter;

use local_otcontrolpanel\entity\abstract_entity;

class property_equals extends \local_otcontrolpanel\filter\abstract_filter {

    protected $property;

    public function __construct(abstract_entity $entity, $property, $value) {

        parent::__construct($entity, $value);
        $this->property = $property;
    }

    /**
     * {@inheritDoc}
     * @see \local_otcontrolpanel\filter\abstract_filter::get_param_shortname()
     */
    public function get_param_shortname() {
        return 'propeq';
    }

    /**
     * {@inheritDoc}
     * @see \local_otcontrolpanel\filter\abstract_filter::get_params()
     */
    public function get_params()
    {
        return [$this->param() => $this->value];
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
        return $this->get_select_property().'=:'.$this->param();
    }

    /**
     *
     * {@inheritDoc}
     * @see \local_otcontrolpanel\filter\abstract_filter::register_joins()
     */
    protected function register_joins() {}

}