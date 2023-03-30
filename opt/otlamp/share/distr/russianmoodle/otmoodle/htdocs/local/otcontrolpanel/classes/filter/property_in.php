<?php
namespace local_otcontrolpanel\filter;

use local_otcontrolpanel\entity\abstract_entity;

class property_in extends \local_otcontrolpanel\filter\abstract_filter {

    protected $property;
    protected $sql;
    protected $params;

    public function __construct(abstract_entity $entity, string $property, array $values, bool $equal=true) {
        global $DB;
        parent::__construct($entity, $values);
        $this->property = $property;
        list($this->sql, $this->params) = $DB->get_in_or_equal($this->value, SQL_PARAMS_NAMED, $this->param(null, 20), $equal);
    }

    /**
     * {@inheritDoc}
     * @see \local_otcontrolpanel\filter\abstract_filter::get_param_shortname()
     */
    public function get_param_shortname() {
        return 'propin';
    }

    public function get_params()
    {
        return $this->params;
    }

    public function get_select_property() {

        if (preg_match('/(.+)\.(.*)/', $this->property))
        {
            return $this->property;
        }
        return '{'.$this->entity->get_storagename().'}.'.$this->property;
    }

    public function get_select()
    {
        return $this->get_select_property().' '.$this->sql;
    }

    protected function register_joins()
    {
    }
}