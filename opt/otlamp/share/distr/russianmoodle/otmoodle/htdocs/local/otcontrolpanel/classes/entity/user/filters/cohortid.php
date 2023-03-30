<?php
namespace local_otcontrolpanel\entity\user\filters;

class cohortid extends \local_otcontrolpanel\filter\abstract_filter {
    public function get_params()
    {
        return [
            $this->param() => $this->value
        ];
    }

    public function get_select()
    {
        return '{cohort}.id=:'.$this->param();
    }

    protected function register_joins()
    {
        $this->register_new_join('cohort_members', '{cohort_members}.userid={user}.id');
        $this->register_new_join('cohort', '{cohort}.id={cohort_members}.cohortid');
    }
}