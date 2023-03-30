<?php

namespace local_otcontrolpanel\entity\course\relations;

use local_otcontrolpanel\relation\abstract_relation;

class students extends abstract_relation {

    /**
     * {@inheritDoc}
     * @see \local_otcontrolpanel\relation\abstract_relation::get_related_entity_storagename()
     */
    public function get_related_entity_storagename() {
        return 'user';
    }

    /**
     * {@inheritDoc}
     * @see \local_otcontrolpanel\relation\abstract_relation::get_connected_entity()
     */
    public function get_connected_entity($record) {

        $courseid = $this->get_entity_field_value($record, 'id');

        $user = $this->get_related_entity();
        $user->add_filter_by_code('coursestudents', $courseid);

        return $user;
    }
}