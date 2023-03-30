<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Настраиваемые поля. 
 * Класс поля, использующийся другими плагинами для объявления служебных полей
 *
 * @package    local_mcov
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_mcov;

class hcfield {

    private $entitycode;
    private $prop;
    private $config;

    public function __construct() {}

    /**
     * Сеттер кода сущности, в привязке к которой должны храниться значения этого поля
     * @param string $entitycode
     */
    public function set_entity_code($entitycode) {
        $this->entitycode = $entitycode;
    }

    /**
     * Сеттер названия настраиваемого поля, под которым должны храниться значения этого поля
     * @param string $prop
     */
    public function set_prop($prop) {
        // TODO: сделать дополнительную проверку, что свойство не начинается с pub_
        // чтобы плагины не могли создать служебные данные, которые можно редактировать через mcov
        $this->prop = $prop;
    }

    /**
     * Сеттер конфига элемента формы, подходящего для компоненты формы в customclass в плагине local_opentechnology 
     * @param array $config
     */
    public function set_config($config) {
        $this->config = $config;
    }
    
    /**
     * Геттер кода сущности, в привязке к которой должны храниться значения этого поля
     * @param string $entitycode
     */
    public function get_entity_code() {
        return $this->entitycode;
    }
    
    /**
     * Геттер названия настраиваемого поля, под которым должны храниться значения этого поля
     * @param string $prop
     */
    public function get_prop() {
        return $this->prop;
    }
    
    /**
     * Геттер конфига элемента формы, подходящего для компоненты формы в customclass в плагине local_opentechnology
     * @param array $config
     */
    public function get_config() {
        return $this->config;
    }


    /**
     * Проверка наличия прав на редактирование поля
     * 
     * @param mixed $objid - идентификатор объекта
     * @param object $closure - функция для проверки прав
     * @return boolean
     */
    public function has_edit_capability($objid=null) {
        return false;
    }

    /**
     * Метод, позволяющий сущности сделать собственные преобразования перед сохранением
     *
     * @param mixed $formvalue - значение, которое было отправлено с помощью формы
     *
     * @return mixed - значение, которое надо хранить в базе
     * @throws hcfield_exception
     */
    public function process_form_value($formvalue) {
        throw new hcfield_exception("Process form value not implemented", 501);
    }

    /**
     * Метод, позволяющий сущности представит данные форме в виде, отличном от того, в каком она хранится в БД
     * @param mixed $storedvalue - значение, хранимое в БД
     * @return mixed - значение для формы
     * @throws hcfield_exception
     */
    public function process_stored_value($storedvalue) {
        throw new hcfield_exception("Process stored value not implemented", 501);
    }

}