<?php
namespace local_otcontrolpanel\entity;

class entity extends abstract_entity {

    public function __construct($storagename) {
        $this->storagename = $storagename;
        parent::__construct();
    }

    /**
     * Получить код сущности вида [класс_сущности]
     * @return string
     */
    public function get_code() {
        return $this->storagename;
    }
}