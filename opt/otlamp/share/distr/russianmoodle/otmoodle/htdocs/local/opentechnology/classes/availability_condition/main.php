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
 * Основной класс проверки условий
 *
 * @package    local_opentechnology
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_opentechnology\availability_condition;

class main {

    private $contextid;

    public function __construct($contextid) {
        $this->contextid = $contextid;
    }

    public function check_conditions(array $config, bool $quiet=false) {
        if (empty($config)) {
            return true;
        }
        $falsydesc = [];
        $result = $this->check_level($config, $falsydesc);
        if (!$result && !$quiet) {
            throw new check_conditions_exception($this->process_falsydesc($falsydesc));
        }
        return $result;
    }

    private function process_falsydesc(array $falsydesc=[]) {
        $items = [];
        foreach($falsydesc as $data) {
            if (array_key_exists('groupdescriptions', $data) && count($data['groupdescriptions']) == 1) {
                $items[] = $this->process_falsydesc($data['groupdescriptions']);
                continue;
            }

            if (array_key_exists('groupdescriptions', $data)) {

                $subitems = $this->process_falsydesc($data['groupdescriptions']);
                $items[] = \html_writer::div($data['fullclassname']::get_user_description($subitems));
            } else if (array_key_exists('fields', $data)) {
                $a = new \stdClass();
                foreach($data['fields'] as $k => $v) {
                    if ($v === '') {
                        $v = get_string('empty_string', 'local_opentechnology');
                    }
                    $a->{'arg'.($k+1)} = \html_writer::span($v, 'badge badge-info');
                }
                $items[] = \html_writer::div($data['fullclassname']::get_user_description($a));
            }
        }
        if (count($items) == 1) {
            return array_shift($items);
        } else {
            return \html_writer::alist($items);
        }
    }

    public function check_level(array $config, array &$falsydesc = []) {

        if (!is_array($config) || count($config) != 1 || !$this->array_is_assoc($config)) {
            throw new \Exception('Every level of config should be an associative array (object) with one item only');
        }

        $groupdescriptions = [];
        $code = $this->array_key_first($config);
        $data = $config[$code];
        $code = strtolower($code);

        if (!is_array($data) || empty($data) || $this->array_is_assoc($data)) {
            throw new \Exception('Config-level value must be presented as not empty sequenced array');
        }

        $fullclassname = null;

        if (is_null($fullclassname)) {
            $logicalgroups = abstract_logical_group::get_classes_info();
            if (array_key_exists($code, $logicalgroups)) {
                $fullclassname = $logicalgroups[$code]['fullclassname'];

                $args = [];
                foreach($data as $groupitem) {
                    $args[] = $this->check_level($groupitem, $groupdescriptions);
                }
            }
        }

        $fields = [];
        if (is_null($fullclassname)) {
            $comparisonoperators = abstract_comparison_operator::get_classes_info();
            if (array_key_exists($code, $comparisonoperators)) {
                $fullclassname = $comparisonoperators[$code]['fullclassname'];
                $args = [];
                foreach($data as $value) {
                    $fields[] = abstract_replacement::prepare_displayname($value, $this->contextid);
                    $args[] = abstract_replacement::make_replacement($value, $this->contextid);
                }
            }
        }

        if (is_null($fullclassname)) {
            throw new \Exception('Unknown code "'.$code.'" in config');
        }
        try {
            $handler = new $fullclassname(...$args);
            $result = $handler->execute();
        } catch (\Exception $e) {
            $result = false;
        }
        if ($result == false) {
            $description = [
                'code' => $code,
                'fullclassname' => $fullclassname
            ];
            if (!empty($fields)) {
                $description['fields'] = $fields;
            }
            if (!empty($groupdescriptions)) {
                $description['groupdescriptions'] = $groupdescriptions;
            }
            $falsydesc[] = $description;
        }

        return $result;
    }

    private function array_is_assoc(array $arr) {
        if (array() === $arr) return false;
        return array_keys($arr) !== range(0, count($arr) - 1);
    }

    private function array_key_first(array $arr) {
        foreach(array_keys($arr) as $key) {
            return $key;
        }
        return NULL;
    }

}