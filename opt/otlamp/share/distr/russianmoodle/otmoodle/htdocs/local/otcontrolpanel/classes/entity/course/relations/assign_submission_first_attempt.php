<?php

namespace local_otcontrolpanel\entity\course\relations;


class assign_submission_first_attempt extends assign_submission {

    /**
     * {@inheritDoc}
     * @see \local_otcontrolpanel\relation\abstract_relation::get_related_entity_storagename()
     */
    public function get_related_entity_storagename() {
        return 'assign_submission';
    }

    /**
     * {@inheritDoc}
     * @see \local_otcontrolpanel\relation\abstract_relation::get_connected_entity()
     */
    public function get_connected_entity($record) {
        // сущность - ответ на задание (assign submission)
        $asgnsubmission = parent::get_connected_entity($record);

        $asgnsubmission->add_filter_by_config([
            'filtercode' => 'property_equals',
            'property' => 'attemptnumber',
            'value' => 0
        ]);

        return $asgnsubmission;
    }
}