<?php

namespace local_otcontrolpanel\entity\course\relations;

use local_otcontrolpanel\relation\abstract_relation;
use local_otcontrolpanel\filter_form_parameter;

class assign_submission extends abstract_relation {

    public function get_supported_filter_form_parameters() {
        return [
            new filter_form_parameter('assign_submission__timecreated__start', null, PARAM_INT),
            new filter_form_parameter('assign_submission__timecreated__end',   null, PARAM_INT),
        ];
    }

    /**
     * {@inheritDoc}
     * @see \local_otcontrolpanel\relation\abstract_relation::get_connected_entity()
     */
    public function get_connected_entity($record) {

        $courseid = $this->get_entity_field_value($record, 'id');

        // сущность - ответ на задание (assign submission)
        $asgnsubmission = $this->get_related_entity();
        $asgnsubmission->add_filter_by_code('courseid', $courseid);
        // Добавление условия фильтрации по дате первичного выпуска сертификата
        $datestart = $this->get_filter_form_parameter_value('assign_submission__timecreated__start', null);
        $dateend = $this->get_filter_form_parameter_value('assign_submission__timecreated__end', null);
        $asgnsubmission->add_filter_by_config([
            'filtercode' => 'property_period',
            'property' => 'timecreated',
            'datestart' => $datestart,
            'dateend' => $dateend,
            'outof' => false
        ]);

        return $asgnsubmission;
    }
}