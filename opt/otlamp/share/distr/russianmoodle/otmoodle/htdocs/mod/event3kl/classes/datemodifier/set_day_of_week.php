<?php

namespace mod_event3kl\datemodifier;

use mod_event3kl\datemodifier\base\abstract_datemodifier;

class set_day_of_week extends abstract_datemodifier {

    public function __construct(string $dayofweek) {
        $this->set_dayofweek($dayofweek);
    }

    public static function instance_from_config($config) {
        if (!array_key_exists('dayofweek', $config)) {
            throw new \Exception('Missing required "dayofweek"');
        }
        return new self($config['dayofweek']);
    }

    /**
     * @return mixed
     */
    public function get_dayofweek()
    {
        return $this->config['dayofweek'];
    }

    /**
     * @param string $dayofweek
     */
    public function set_dayofweek(string $dayofweek)
    {
        if (!in_array($dayofweek, $this->get_known_days_of_week())) {
            throw new \coding_exception('unknown day of week passed to set_day_of_week modifier');
        }
        $this->config['dayofweek'] = $dayofweek;
    }

    private static function get_known_days_of_week($localized = false) {
        if ($localized) {
            return  [
                'monday' => get_string('monday', 'core_calendar'),
                'tuesday' => get_string('tuesday', 'core_calendar'),
                'wednesday' => get_string('wednesday', 'core_calendar'),
                'thursday' => get_string('thursday', 'core_calendar'),
                'friday' => get_string('friday', 'core_calendar'),
                'saturday' => get_string('saturday', 'core_calendar'),
                'sunday' => get_string('sunday', 'core_calendar')
            ];
        }
        return  [
            'monday',
            'tuesday',
            'wednesday',
            'thursday',
            'friday',
            'saturday',
            'sunday'
        ];
    }

    public function __toString() {
        return 'this ' . $this->get_dayofweek();
    }


    /**
     * @return \HTML_QuickForm_element[]
     */
    public static function get_mform_elements(\MoodleQuickForm &$mform) {
        return [
            $mform->createElement('select', 'dayofweek', '', self::get_known_days_of_week(true))
        ];
    }

    public static function instance_by_formdata(array $formdata) {
        return new self($formdata['dayofweek']);
    }



}