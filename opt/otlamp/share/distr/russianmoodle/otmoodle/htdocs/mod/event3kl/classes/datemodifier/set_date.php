<?php

namespace mod_event3kl\datemodifier;

use mod_event3kl\datemodifier\base\abstract_datemodifier;

class set_date extends abstract_datemodifier {

    public function __construct(int $timestamp) {
        $this->set_timestamp($timestamp);
    }

    public static function instance_from_config($config) {
        if (!array_key_exists('timestamp', $config)) {
            throw new \Exception('Missing required "timestamp"');
        }
        return new self($config['timestamp']);
    }

    /**
     * @return int
     */
    public function get_timestamp()
    {
        return $this->config['timestamp'];
    }

    /**
     * @param int $timestamp
     */
    public function set_timestamp($timestamp)
    {
        $this->config['timestamp'] = $timestamp;
    }

    public function __toString() {
        return '@' . $this->get_timestamp();
    }

    /**
     * @return \HTML_QuickForm_element[]
     */
    public static function get_mform_elements(\MoodleQuickForm &$mform) {
        $options = [];
        // применение модификаторов производится под часовым поясом пользователя, настраивающего модификаторы
        // это было необходимо чтобы избежать недоразумения при вычислении относительных дат
        // (см. комментарии в mod_form_definition в abstract_datemode)
        // Поскольку данный модификатор задает конкретную дату, а не просто смещает её, мы должны для элемента формы отменить
        // дефолтные преобразования даты из пользовательского часового пояса, и оставить дату
        // как есть (будто Etc/GMT), чтобы при примнении модификатора всё отработало как ожидается
        $options['timezone'] = 'Etc/GMT';
        return [
            $mform->createElement('date_selector', 'set_date', null, $options)
        ];
    }

    public static function instance_by_formdata(array $formdata) {
        return new self((int)$formdata['set_date']);
    }
}