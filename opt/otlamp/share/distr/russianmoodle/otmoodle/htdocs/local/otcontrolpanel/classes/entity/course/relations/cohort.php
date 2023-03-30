<?php

namespace local_otcontrolpanel\entity\course\relations;

use local_otcontrolpanel\relation\abstract_relation;

class cohort extends abstract_relation {

    /**
     * {@inheritDoc}
     * @see \local_otcontrolpanel\relation\abstract_relation::get_connected_entity()
     */
    public function get_connected_entity($record) {

        $courseid = $this->get_entity_field_value($record, 'id');

        $cohort = $this->get_related_entity();
        $cohort->add_filter_by_code('courseid', $courseid);

        return $cohort;
    }
}