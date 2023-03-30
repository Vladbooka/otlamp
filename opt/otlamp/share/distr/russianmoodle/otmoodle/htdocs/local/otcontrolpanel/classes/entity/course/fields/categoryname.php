<?php
namespace local_otcontrolpanel\entity\course\fields;

class categoryname extends \local_otcontrolpanel\field\abstract_field {

    /**
     * {@inheritDoc}
     * @see \local_otcontrolpanel\field\abstract_field::get_plain_value()
     */
    protected function get_plain_value($record, $suffix='')
    {
        return $record->categoryname;
    }

    public function register_joins() {
        try {
            // подключение категории курса
            $join = $this->entity->get_basic_join('e_course_j_cat');
            $join->add_required_db_fields(['name AS "categoryname"']);
            $this->register_join($join);
        } catch(\Exception $ex){}
    }


}