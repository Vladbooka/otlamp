<?php
namespace local_otcontrolpanel\entity\course\filters;

class cohortid extends \local_otcontrolpanel\filter\abstract_filter {
    public function get_params()
    {
        return [
            $this->param(1) => $this->value,
            $this->param(2) => 'cohort'
        ];
    }

    public function get_select()
    {
        return '{cohort}.id=:'.$this->param(1);
    }

    protected function register_joins()
    {
        $this->register_new_join('enrol', '{enrol}.courseid={course}.id AND {enrol}.enrol=:'.$this->param(2));
        $this->register_new_join('cohort', '{cohort}.id={enrol}.customint1');
    }
}