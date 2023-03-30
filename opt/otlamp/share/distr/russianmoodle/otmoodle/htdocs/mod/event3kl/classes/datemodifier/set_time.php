<?php

namespace mod_event3kl\datemodifier;

use mod_event3kl\datemodifier\base\abstract_datemodifier;

class set_time extends abstract_datemodifier {

    public function __construct(int $hours, int $minutes) {
        $this->set_minutes($minutes);
        $this->set_hours($hours);
    }

    public static function instance_from_config($config) {
        if (!array_key_exists('hours', $config)) {
            throw new \Exception('Missing required "hours"');
        }
        if (!array_key_exists('minutes', $config)) {
            throw new \Exception('Missing required "minutes"');
        }
        return new self($config['hours'], $config['minutes']);
    }

    /**
     * @return mixed
     */
    public function get_hours()
    {
        return $this->config['hours'];
    }

    /**
     * @return mixed
     */
    public function get_minutes()
    {
        return $this->config['minutes'];
    }

    /**
     * @param mixed $hours
     */
    public function set_hours(int $hours)
    {
        if (($hours < 0) || ($hours > 23)) {
            throw new \coding_exception('Incorrect hours passed to set_time modifier');
        }
        $this->config['hours'] = $hours;
    }

    /**
     * @param mixed $minutes
     */
    public function set_minutes(int $minutes)
    {
        if (($minutes < 0) || ($minutes > 59)) {
            throw new \coding_exception('Incorrect minutes passed to set_time modifier');
        }
        $this->config['minutes'] = $minutes;
    }

    public function __toString() {
        return 'midnight +'. $this->get_hours() . ' hours +' . $this->get_minutes() .' minutes';
    }

    /**
     * @return \HTML_QuickForm_element[]
     */
    public static function get_mform_elements(\MoodleQuickForm &$mform) {
        $digits = ['00','01','02','03','04','05','06','07','08','09'];
        $hours = array_merge($digits,range(10,23));
        $minutes = array_merge($digits,range(10,59));
        $elements = [];
        $elements[] = $mform->createElement('select', 'hours', '', $hours);
        $elements[] = $mform->createElement('select', 'minutes', '', $minutes);

        return [
            $mform->createElement('group', 'set_time', '', $elements)
        ];
    }

    public static function instance_by_formdata(array $formdata) {
        return new self((int)$formdata['hours'],(int)$formdata['minutes']);
    }
}