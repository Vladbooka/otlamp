<?php
namespace local_otcontrolpanel\entity\assign_submission;

use local_otcontrolpanel\sql_join;
use local_otcontrolpanel\entity\abstract_entity;

class assign_submission extends abstract_entity  {
    protected $storagename = 'assign_submission';
    protected $defaultfield = 'assignuserattempt';

    protected function get_basic_joins() {

        $basicjoins = [];

        // подключение пользователя-владельца попытки
        $storage = 'user';
        $alias = 'e_asgnsubm_j_user';
        $condition = '{assign_submission}.userid=e_asgnsubm_j_user.id';
        $basicjoins[$alias] = new sql_join('LEFT JOIN', $storage, $alias, $condition);

        // подключение задания, к которому относится попытка
        $storage = 'assign';
        $alias = 'e_asgnsubm_j_assign';
        $condition = '{assign_submission}.assignment=e_asgnsubm_j_assign.id';
        $basicjoins[$alias] = new sql_join('LEFT JOIN', $storage, $alias, $condition);

        return $basicjoins;
    }
}