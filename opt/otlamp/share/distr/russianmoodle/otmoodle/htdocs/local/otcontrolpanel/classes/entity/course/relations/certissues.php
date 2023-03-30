<?php

namespace local_otcontrolpanel\entity\course\relations;

use local_otcontrolpanel\relation\abstract_relation;
use local_otcontrolpanel\filter_form_parameter;

class certissues extends abstract_relation {

    public function get_supported_filter_form_parameters() {
        return [
            new filter_form_parameter('simplecertificate_issues__timecreated__start', null, PARAM_INT),
            new filter_form_parameter('simplecertificate_issues__timecreated__end',   null, PARAM_INT),
        ];
    }

    /**
     * {@inheritDoc}
     * @see \local_otcontrolpanel\relation\abstract_relation::get_related_entity_storagename()
     */
    public function get_related_entity_storagename() {
        return 'simplecertificate_issues';
    }

    /**
     * {@inheritDoc}
     * @see \local_otcontrolpanel\relation\abstract_relation::get_connected_entity()
     */
    public function get_connected_entity($record) {

        $courseid = $this->get_entity_field_value($record, 'id');

        $certissues = $this->get_related_entity();
        $certissues->add_filter_by_code('courseid', $courseid);
        // Добавление условия фильтрации по дате первичного выпуска сертификата
        $datestart = $this->get_filter_form_parameter_value('simplecertificate_issues__timecreated__start', null);
        $dateend = $this->get_filter_form_parameter_value('simplecertificate_issues__timecreated__end', null);
        $certissues->add_filter_by_config([
            'filtercode' => 'property_period',
            'property' => 'timecreated',
            'datestart' => $datestart,
            'dateend' => $dateend,
            'outof' => false
        ]);

        return $certissues;
    }
}