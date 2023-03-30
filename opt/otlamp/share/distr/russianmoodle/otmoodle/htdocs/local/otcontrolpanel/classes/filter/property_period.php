<?php
namespace local_otcontrolpanel\filter;

use local_otcontrolpanel\entity\abstract_entity;

class property_period extends \local_otcontrolpanel\filter\abstract_filter {

    protected $property;
    /**
     * @var bool - флаг, сообщающий о том, что выбрать надо будет то, что вне указанного периода
     */
    protected $outof;

    /**
     *
     * @param abstract_entity $entity
     * @param string $property
     * @param int $datestart
     * @param int $dateend
     * @param bool $outof - выбрать то, что вне указанного периода
     */
    public function __construct(abstract_entity $entity, string $property, int $datestart=null, int $dateend=null, bool $outof=false) {
        $value = [];
        if (!is_null($datestart) && !is_null($dateend))
        {
            // исправление перепутанных местами дат
            $value['datestart'] = ($datestart < $dateend ? $datestart : $dateend);
            $value['dateend'] = ($datestart < $dateend ? $dateend : $datestart);
        } else {
            if (!is_null($datestart))
            {
                $value['datestart'] = $datestart;
            }
            if (!is_null($dateend))
            {
                $value['dateend'] = $dateend;
            }
        }

        parent::__construct($entity, $value);
        $this->property = $property;
        $this->outof = $outof;

    }

    /**
     * {@inheritDoc}
     * @see \local_otcontrolpanel\filter\abstract_filter::get_param_shortname()
     */
    public function get_param_shortname() {
        return 'propper';
    }

    public function get_params()
    {
        $params = [];
        if (!empty($this->value['datestart']))
        {
            $params[$this->param(1)] = $this->value['datestart'];
        }
        if (!empty($this->value['dateend']))
        {
            $params[$this->param(2)] = $this->value['dateend'];
        }
        return $params;
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
        $selects = [];
        $prop = $this->get_select_property();
        if (!empty($this->value['datestart']))
        {
            $selects[] = $prop.'>'.':'.$this->param(1);
        }
        if (!empty($this->value['dateend']))
        {
            $selects[] = $prop.'<'.':'.$this->param(2);
        }
        return implode(' AND ', $selects);
    }

    protected function register_joins()
    {
    }
}