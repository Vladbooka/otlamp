<?php
namespace local_otcontrolpanel\entity\cohort\filters;

class courseid extends \local_otcontrolpanel\filter\abstract_filter {
    public function get_params()
    {
        return [
            'e_cohort__courseid' => $this->value,
            'e_cohort__enroltype' => 'cohort'
        ];
    }

    public function get_select()
    {
        return '{course}.id=:e_cohort__courseid';
    }

    protected function register_joins()
    {
        $this->register_new_join('enrol', '{enrol}.customint1={cohort}.id AND {enrol}.enrol=:e_cohort__enroltype');
        $this->register_new_join('course', '{course}.id={enrol}.courseid');
    }
}