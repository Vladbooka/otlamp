<?php
namespace local_otcontrolpanel\entity\course\fields;


class coursepath extends \local_otcontrolpanel\field\abstract_field {

    /**
     * {@inheritDoc}
     * @see \local_otcontrolpanel\field\abstract_field::get_plain_value()
     */
    protected function get_plain_value($record, $suffix='')
    {
        $categorieslist = \core_course_category::make_categories_list();

        if (array_key_exists($record->coursepathid, $categorieslist))
        {
            return $categorieslist[$record->coursepathid];
        } else {
            return '?';
        }
    }

    public function register_joins() {
        try {
            // подключение категории курса
            $join = $this->entity->get_basic_join('e_course_j_cat');
            $join->add_required_db_fields(['id AS "coursepathid"']);
            $this->register_join($join);
        } catch(\Exception $ex){}
    }


}