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
 * Аргумент
 *
 * @package    local_opentechnology
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_opentechnology\availability_condition;

class arg {

    private $name;
    private $filter;
    private $description;
    private $value;

    public function __construct(string $name, int $filter=FILTER_DEFAULT, string $description='') {
        $this->setName($name);
        $this->setFilter($filter);
        $this->setDescription($description);
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName(string $name)
    {
        $this->name = $name;
    }

    /**
     * @return int
     */
    public function getFilter()
    {
        return $this->filter;
    }

    /**
     * @param int $filter
     */
    public function setFilter(int $filter)
    {
        $this->filter = $filter;
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @param string $description
     */
    public function setDescription(string $description)
    {
        $this->description = $description;
    }

    public function getValue() {
        return $this->value;
    }

    public function setValue($value) {
        $filteredvalue = filter_var($value, $this->filter, FILTER_NULL_ON_FAILURE);

        if (!is_null($value) && is_null($filteredvalue)) {
            throw new \Exception('Value is not valid');
        }

        $this->value = $filteredvalue;
    }

}