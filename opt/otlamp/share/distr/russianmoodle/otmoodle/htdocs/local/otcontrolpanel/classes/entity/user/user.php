<?php
namespace local_otcontrolpanel\entity\user;

use local_otcontrolpanel\entity\abstract_entity;
use local_otcontrolpanel\filter\property_equals;

class user extends abstract_entity  {
    protected $storagename = 'user';
    protected $defaultfield = 'fullname';

    /**
     * {@inheritDoc}
     * @see \local_otcontrolpanel\entity\abstract_entity::get_default_filters()
     */
    protected function get_default_filters() {

        $defaultfilters = [];

        // С фильтром из выборки будут исключены удалённые пользователи
        $defaultfilters[] = new property_equals($this, 'deleted', '0');

        return $defaultfilters;
    }
}