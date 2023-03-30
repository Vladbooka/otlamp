<?php

namespace local_otcontrolpanel\entity\cohort\relations;

use local_otcontrolpanel\relation\abstract_relation;

class user extends abstract_relation {

    /**
     * {@inheritDoc}
     * @see \local_otcontrolpanel\relation\abstract_relation::get_connected_entity()
     */
    public function get_connected_entity($record) {

        $cohortid = $this->get_entity_field_value($record, 'id');

        $user = $this->get_related_entity();
        $user->add_filter_by_code('cohortid', $cohortid);

        return $user;
    }
}