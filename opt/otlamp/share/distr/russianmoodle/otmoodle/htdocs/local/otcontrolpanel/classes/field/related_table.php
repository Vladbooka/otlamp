<?php
namespace local_otcontrolpanel\field;

use local_otcontrolpanel\entity\abstract_entity;
use local_otcontrolpanel\relation\abstract_relation;

class related_table extends \local_otcontrolpanel\field\abstract_field {


    protected $fieldconfigs;
    /**
     * @var abstract_relation
     */
    protected $relation;

    public function __construct(abstract_entity $entity, abstract_relation $relation, array $fieldconfigs, $displayname=null)
    {
        parent::__construct($entity, $displayname);
        $this->fieldconfigs = $fieldconfigs;
        $this->relation = $relation;
    }

    public function get_value($record, $suffix='')
    {
        // переопределили, чтобы здесь не применялись модификаторы ко всей таблице
        // если будут указаны модификаторы для полей вложенной таблице, то к ним они применятся,
        // так как будут уже в другом типе поля
        return $this->get_plain_value($record, $suffix);
    }

    /**
     * {@inheritDoc}
     * @see \local_otcontrolpanel\field\abstract_field::get_plain_value()
     */
    protected function get_plain_value($record, $suffix='')
    {
        global $OUTPUT;
        $relatedentity = $this->relation->get_connected_entity($record);
        foreach($this->fieldconfigs as $fieldconfig)
        {
            $relatedentity->add_field($fieldconfig);
        }
        $options = ['suffix' => $suffix];
        if (!empty($this->template))
        {
            foreach($this->template as $templatetype => $templatename)
            {
                $options['template_'.$templatetype] = 'local_otcontrolpanel/'.$templatename;
            }
        }
        return $relatedentity->auto_render($OUTPUT, '-', $options);
    }

    public function get_code() {
        $fieldcodes = [];
        foreach($this->fieldconfigs as $fieldconfig)
        {
            $fieldcodes[] = $fieldconfig['fieldcode'];
        }
        return implode('-', $fieldcodes);
    }

    public function get_full_code() {
        return $this->relation->get_full_code().'_fld_'.$this->get_code();
    }

}