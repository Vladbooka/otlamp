<?php
namespace local_otcontrolpanel\entity\course;

use local_otcontrolpanel\sql_join;
use local_otcontrolpanel\entity\abstract_entity;
use local_otcontrolpanel\filter\property_in;

class course  extends abstract_entity  {
    protected $storagename = 'course';
    protected $defaultfield = 'fullname';

    /**
     * {@inheritDoc}
     * @see \local_otcontrolpanel\entity\abstract_entity::get_default_filters()
     */
    protected function get_default_filters() {
        global $CFG;

        $defaultfilters = [];

        // С фильтром из выборки будет исключён курс главной страницы (сайт)
        require_once $CFG->libdir.'/datalib.php';
        $site = get_site();
        $defaultfilters[] = new property_in($this, 'id', [$site->id], false);

        return $defaultfilters;
    }


    protected function get_basic_joins() {

        $basicjoins = [];

        // Подключение непосредственной категории курса
        $storage = 'course_categories';
        $alias = 'e_course_j_cat';
        $condition = '{course}.category=e_course_j_cat.id';
        $basicjoins[$alias] = new sql_join('LEFT JOIN', $storage, $alias, $condition);

        return $basicjoins;
    }
}