<?php
namespace local_otcontrolpanel\entity\user\filters;

class completedcourse extends \local_otcontrolpanel\filter\abstract_filter {

    public function get_params()
    {
        return [$this->param() => $this->value];
    }

    public function get_select()
    {
        $where = [
            '{course_completions}.course=:'.$this->param(),
            '{course_completions}.timecompleted IS NOT NULL'
        ];
        return implode(' AND ', $where);
    }

    protected function register_joins()
    {
        $this->register_new_join(
            'course_completions',
            '{user}.id={course_completions}.userid',
            'LEFT JOIN',
            'f_completedcourse_course_completions'
        );
    }
}