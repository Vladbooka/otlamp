<?php

namespace local_otcontrolpanel\entity\course\relations;

use local_otcontrolpanel\relation\abstract_relation;
use local_otcontrolpanel\filter_form_parameter;

class userscompleted extends abstract_relation {

    public function get_supported_filter_form_parameters() {
        return [
            new filter_form_parameter('course_completions__timecompleted__start', null, PARAM_INT),
            new filter_form_parameter('course_completions__timecompleted__end',   null, PARAM_INT)
        ];
    }

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


        $userscompleted = $this->get_related_entity();
        // фильтрация по курсу
        $userscompleted->add_filter_by_code('completedcourse', $courseid);
        // Фильтрация по дате завершения (если значения для фильтрации не будут переданы - никакие условия добавлены не будут)
        $datestart = $this->get_filter_form_parameter_value('course_completions__timecompleted__start', null);
        $dateend = $this->get_filter_form_parameter_value('course_completions__timecompleted__end', null);
        $userscompleted->add_filter_by_config([
            'filtercode' => 'property_period',
            'property' => 'f_completedcourse_course_completions.timecompleted',
            'datestart' => $datestart,
            'dateend' => $dateend,
            'outof' => false
        ]);

        return $userscompleted;
    }
}