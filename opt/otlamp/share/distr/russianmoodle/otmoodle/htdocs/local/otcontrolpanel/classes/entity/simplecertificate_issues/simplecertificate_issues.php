<?php
namespace local_otcontrolpanel\entity\simplecertificate_issues;

use local_otcontrolpanel\entity\abstract_entity;

class simplecertificate_issues extends abstract_entity  {
    protected $storagename = 'simplecertificate_issues';
    protected $defaultfield = 'code';

    /**
     * Переопределение кода сущности, т.к. при соответствии со стораджем слишком длинное
     * @return string
     */
    public function get_code() {
        return 'certissues';
    }
}