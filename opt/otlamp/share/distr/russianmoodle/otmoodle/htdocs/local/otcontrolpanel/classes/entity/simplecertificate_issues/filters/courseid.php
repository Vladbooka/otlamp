<?php
namespace local_otcontrolpanel\entity\simplecertificate_issues\filters;

class courseid extends \local_otcontrolpanel\filter\abstract_filter {
    public function get_params()
    {
        return [$this->param() => $this->value];
    }

    public function get_select()
    {
        return '{simplecertificate}.course=:'.$this->param();
    }

    protected function register_joins()
    {
        $joincondition = '{simplecertificate_issues}.certificateid={simplecertificate}.id';
        $this->register_new_join('simplecertificate', $joincondition);
    }
}