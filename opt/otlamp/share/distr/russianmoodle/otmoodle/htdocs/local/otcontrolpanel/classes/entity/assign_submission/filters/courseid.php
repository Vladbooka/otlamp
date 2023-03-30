<?php
namespace local_otcontrolpanel\entity\assign_submission\filters;

class courseid extends \local_otcontrolpanel\filter\abstract_filter {
    public function get_params()
    {
        return [$this->param() => $this->value];
    }

    public function get_select()
    {
        return 'e_asgnsubm_j_assign.course=:'.$this->param();
    }

    protected function register_joins()
    {
        // подключение задания, к которому относится попытка
        $join = $this->entity->get_basic_join('e_asgnsubm_j_assign');
        $this->register_join($join);
    }
}