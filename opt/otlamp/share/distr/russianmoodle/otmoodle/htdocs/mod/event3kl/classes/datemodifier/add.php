<?php

namespace mod_event3kl\datemodifier;

use mod_event3kl\datemodifier\base\abstract_datemodifier;

class add extends abstract_datemodifier {

    public function __construct(string $sign, int $value, string $unit) {
        $this->set_sign($sign);
        $this->set_value($value);
        $this->set_unit($unit);
    }

    public static function instance_from_config($config) {
        if (!array_key_exists('sign', $config)) {
            throw new \Exception('Missing required "sign"');
        }
        if (!array_key_exists('value', $config)) {
            throw new \Exception('Missing required "value"');
        }
        if (!array_key_exists('unit', $config)) {
            throw new \Exception('Missing required "unit"');
        }
        return new self($config['sign'], $config['value'], $config['unit']);
    }

    /**
     * @return mixed
     */
    public function get_sign()
    {
        return $this->config['sign'];
    }

    /**
     * @return mixed
     */
    public function get_value()
    {
        return $this->config['value'];
    }

    /**
     * @return mixed
     */
    public function get_unit()
    {
        return $this->config['unit'];
    }

    /**
     * @param mixed $sign
     */
    public function set_sign($sign)
    {
        if (!in_array($sign, ['+', '-'])) {
            throw new \coding_exception('unknown sign passed to add modifier');
        }
        $this->config['sign'] = $sign;
    }

    /**
     * @param int $value
     */
    public function set_value(int $value)
    {
        $this->config['value'] = $value;
    }

    /**
     * @param mixed $units
     */
    public function set_unit($unit)
    {
        if (!in_array($unit, $this->get_known_units())) {
            throw new \coding_exception('unknown unit passed to add modifier');
        }
        $this->config['unit'] = $unit;
    }

    private static function get_known_units($localized = false) {
        if ($localized) {
            return ['days' => get_string('days'), 'weeks' => get_string('weeks'),
                'months' => get_string('months'), 'years' => get_string('years')];
        }
        return ['days', 'weeks', 'months', 'years'];
    }

    public function __toString() {
        return $this->get_sign() . $this->get_value() . ' ' . $this->get_unit();
    }


    /**
     * @return \HTML_QuickForm_element[]
     */
    public static function get_mform_elements(\MoodleQuickForm &$mform) {

        $sign = $mform->createElement('select', 'sign', '', ['+' => '+', '-' => '-']);
        $value = $mform->createElement('text', 'value', '');
        $value->__options = ['type' => PARAM_INT];
        $unit = $mform->createElement('select', 'unit', '', self::get_known_units(true));

        return [$sign, $value, $unit];
    }

    public static function instance_by_formdata(array $formdata) {
        return new self($formdata['sign'], (int)$formdata['value'], $formdata['unit']);
    }
}