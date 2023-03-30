<?php

namespace local_otcontrolpanel\entity\course\relations;

use local_otcontrolpanel\relation\abstract_relation;

class contacts extends abstract_relation {

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
        $course = get_course($courseid);

        if (class_exists('\core_course_list_element'))
        {
            $courseinlist = new \core_course_list_element($course);
        } else {
            // TODO: удалить после миграции на 3.9, совместимость с более старыми версиями
            $courseinlist = new \course_in_list($course);
        }

        $userids = [];
        foreach ($courseinlist->get_course_contacts() as $contact ) {
            $userid = $contact['user']->id;
            if (!in_array($userid, $userids))
            {
                $userids[] = $userid;
            }
        }

        $user = $this->get_related_entity();
        if (empty($userids)) {
            $user->add_filter_by_config(['filtercode' => 'empty_result']);
        } else {
            $user->add_filter_by_config([
                'filtercode' => 'property_in',
                'property' => 'id',
                'values' => $userids,
                'equal' => true
            ]);
        }

        return $user;
    }
}