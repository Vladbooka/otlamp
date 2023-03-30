<?php
namespace local_otcontrolpanel\modifier\common;


abstract class abstract_modifier {

    abstract public function modify($value, $record);
    /**
     * Получить код модификатора
     * @return string
     */
    public function get_code() {
        $reflect = new \ReflectionClass($this);
        return $reflect->getShortName();
    }

    public function get_full_code() {
        return 'mod_'.$this->get_code();
    }

    public function get_default_display_name() {
        return $this->get_code();
    }

    public function get_display_name() {
        if (!is_null($this->displayname))
        {
            return $this->displayname;
        } else {
            if (get_string_manager()->string_exists($this->get_full_code(), 'local_otcontrolpanel'))
            {
                return get_string($this->get_full_code(), 'local_otcontrolpanel');

            } else {
                return $this->get_default_display_name();
            }
        }
    }
}