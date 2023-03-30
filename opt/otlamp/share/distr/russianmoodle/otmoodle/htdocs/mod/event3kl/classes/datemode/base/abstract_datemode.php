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

namespace mod_event3kl\datemode\base;

use mod_event3kl\event3kl;
use mod_event3kl\datemodifiers;

defined('MOODLE_INTERNAL') || die();

/**
 * Абстрактный класс способа указания даты сессии
 *
 * @package   mod_event3kl
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
abstract class abstract_datemode {

    protected $modifiers = [];
    protected $event3kl;
    protected $groupid;
    protected $userid;
    protected $startingpoint;

    public function __construct(event3kl $event3kl=null, int $groupid=null, int $userid=null) {
        $this->event3kl = $event3kl;
        $this->groupid = $groupid;
        $this->userid = $userid;
    }

    /**
     * Получение точки отсчета для формирования даты
     * @return string
     */
    protected function set_default_startingpoint() {
        $this->startingpoint = 'now';
    }

    protected function get_modifiers() {
        if (!isset($this->event3kl)) {
            throw new \Exception('No enough data to detect date starting point');
        }

        $modifiers = [];

        $datemodedata = json_decode($this->event3kl->get('datemodedata'), true);
        if (array_key_exists('datemodifiers', $datemodedata)) {
            foreach($datemodedata['datemodifiers'] as $modifierdata) {
                $modifiercode = $modifierdata['code'];
                $modifierconfig = $modifierdata['config'];
                $modifiers[] = datemodifiers::instance($modifiercode, $modifierconfig);
            }
        }

        return $modifiers;
    }

    protected function get_startingpoint_timezone() {
        if (!isset($this->event3kl)) {
            throw new \Exception('No enough data to detect date starting point');
        }

        $timezone = \core_date::get_server_timezone();

        $datemodedata = json_decode($this->event3kl->get('datemodedata'), true);
        if (array_key_exists('timezone', $datemodedata)) {
            $timezone = $datemodedata['timezone'];
        }

        return $timezone;
    }

    /**
     * {@inheritDoc}
     * @see datemode_interface::get_start_date()
     */
    public function get_start_date()
    {
        if (!isset($this->startingpoint)) {
            $this->set_default_startingpoint();
        }
        $date = new \DateTime($this->startingpoint);
        // подробные пояснения про трудности часовых поясов описаны в методе mod_form_definition
        $date->setTimezone(new \DateTimeZone($this->get_startingpoint_timezone()));

        foreach($this->get_modifiers() as $modifier) {
            $date->modify($modifier);
        }
        return $date->getTimestamp();
    }

    /**
     * название способа указания даты сессии
     */
    public static function get_display_name() {
        return get_string(self::get_code() . '_datemode_display_name', 'mod_event3kl');
    }

    /**
     * возвращает короткий код текущего способа указания даты сессии, основываясь на классе
     */
    public static function get_code() {
        return (new \ReflectionClass(get_called_class()))->getShortName();
    }

    /**
     * {@inheritDoc}
     * @see \mod_event3kl\datemode\base\datemode_interface::mod_form_definition()
     */
    public function mod_form_definition(\MoodleQuickForm &$mform, \mod_event3kl_mod_form &$form) {


        // date mode code - код способа определения даты и времени занятия
        $dmcode = $this->get_code();
        // массив повторяющихся элементов для добавленния модификаторов
        $modifier = [];
        // массив опций для повторяющихся элементов
        $options = [
            $dmcode.'_mcode' => [
                'disabledif' => ['datemode_edit_confirmed', 'notchecked']
            ],
            $dmcode.'_modifiers' => [
                'disabledif' => ['datemode_edit_confirmed', 'notchecked']
            ]
        ];

        // Для удаления лишних модификаторов
        $yesno = [
            0 => get_string('apply_modifier','mod_event3kl'),
            1 => get_string('delete_modifier','mod_event3kl')
        ];

        // поддерживаемые способом определения даты и времени занятися модификаторы
        $maintainedmodifiers = $this->get_maintained_modifiers();
        $maintainedmodifiersnum = count($maintainedmodifiers);
        if ($maintainedmodifiersnum > 0) {
            if ($maintainedmodifiersnum > 1) {
                // добавление выпадающего списка для выбора типа добавляемого модификатора
                // селект для выбора типа добавляемого модификатора
                $modifier[] = $mform->createElement('select', $dmcode.'_mcode',
                    get_string('date_calculation', 'mod_event3kl'), $this->get_maintained_modifiers(true));
                // скрытие типа модификатора, если он относится не к выбранному способу определения даты и времени занятия
                $options[$dmcode.'_mcode']['hideif'] = ['datemode', 'neq', $dmcode];
            } else {
                // у нас всего один модификатор - выпадающего списка не будет, добавим скрытое поле, чтобы не менять логику обработки
                $modifier[] = $mform->createElement('hidden', $dmcode.'_mcode', $maintainedmodifiers[0]);
                $mform->setType($dmcode.'_mcode', PARAM_ALPHAEXT);
            }

            $elementsgroups = [];
            // название группы элементов настройки модификатора
            foreach($maintainedmodifiers as $modifiercode) {
                $modifierclass = '\\mod_event3kl\\datemodifier\\'.$modifiercode;
                $elements = call_user_func_array([$modifierclass, 'get_mform_elements'], [&$mform]);
                $elements[] = $mform->createElement('select', 'deleter', '', $yesno);

                // добавляем группу элементов формы, соответствующих модификатору
                $elementsgroups[] = $mform->createElement('group', $modifiercode, '', $elements);
                // скрытие элементов настройки модификатора, если они не относятся к выбраному модификатору
                $options[$dmcode . '_modifiers['.$modifiercode.']'] = ['hideif' => [$dmcode.'_mcode', 'neq', $modifiercode]];
                foreach($elements as $element) {
                    if (property_exists($element, '__options')) {
                        $options[$dmcode . '_modifiers['.$modifiercode.']['.$element->getName().']'] = $element->__options;
                    }
                }
            }
            // добавление всех возможных модификаторов для текущего способа определения даты и времени занятия
            $modifier[] = $mform->createElement('group', $dmcode . '_modifiers', '&nbsp;', $elementsgroups);
            // скрытие всех модификаторов, которые не относятся к выбранному способу определения даты и времени зантия
            $options[$dmcode . '_modifiers']['hideif'] = ['datemode', 'neq', $dmcode];

            // название кнопки добавления +1 модификатора в форму (repeat button)
            $repeatbuttonname = $dmcode . '_add_modifier';
            $repeathiddenname = $dmcode . '_modifier_repeats';

            // Количество модификаторов, их надо вывести в репит
            $repeatnum = 1;
            if (! empty($form->datemodifiers)){
                $repeatnum = count($form->datemodifiers);
                // на 1 меньше, если есть set_time
                foreach ($form->datemodifiers as $dm){
                    // для set_time в массиве модификатора будет присутствовать код "set_time"
                    if (in_array('set_time', $dm) && ($repeatnum > 1)){
                        $repeatnum--;
                        break;
                    }
                }
            }

            $form->repeat_elements($modifier, $repeatnum, $options, $repeathiddenname, $repeatbuttonname, 1, null, true);
            $mform->hideIf($repeatbuttonname, 'datemode', 'neq', $dmcode);


            if (!$this->supports_repeat()) {
                // скрытие кнопки создания +1 модификатора, так как выбранный способ не поддерживает такую фичу
                $mform->hideIf($repeatbuttonname, 'datemode', 'neq', 'always_hide');
                // скрытие удаления модификатора
                $mform->hideIf($dmcode.'_modifiers[0]['.$maintainedmodifiers[0].'][deleter]', 'datemode', 'neq', 'always_hide');
            }
        }
    }

    /**
     * {@inheritDoc}
     * @see \mod_event3kl\datemode\base\datemode_interface::mod_form_validation()
     */
    public function mod_form_validation($data, $files)
    {
        return [];
    }

    /**
     * {@inheritDoc}
     * @see \mod_event3kl\datemode\base\datemode_interface::mod_form_processing()
     */
    public static function mod_form_processing(array $formdata)
    {

        $dmcode = self::get_code();

        if (!array_key_exists('datemode_edit_confirmed', $formdata) || $formdata['datemode_edit_confirmed']) {
            $modifiers = [];
            if (!empty($formdata[$dmcode . '_modifiers'])) {
                foreach($formdata[$dmcode . '_modifiers'] as $r => $modifierdata) {
                    $modifiercode = $formdata[$dmcode . '_mcode'][$r];
                    // set_time обрабатываектся отдельно
                    if ($modifiercode == 'set_time'){
                        continue;
                    }
                    $elements = $modifierdata[$modifiercode];
                    // Не будем сохранять данные для удаленных модификаторов
                    if ((! empty($elements['deleter'])) && ($elements['deleter'] == 1)){
                        continue;
                    }
                    $modifierclass = '\\mod_event3kl\\datemodifier\\'.$modifiercode;
                    $modifier = call_user_func([$modifierclass, 'instance_by_formdata'], $elements);
                    $modifiers[] = [
                        'code' => $modifiercode,
                        'config' => $modifier->get_config()
                    ];
                }
            }

            // Добавляем модификатор времени
            if (! empty($formdata['set_time']['set_time'])){
                $elements = $formdata['set_time']['set_time'];
                $modifierclass = '\\mod_event3kl\\datemodifier\\set_time';
                $modifier = call_user_func([$modifierclass, 'instance_by_formdata'], $elements);
                $modifiers[] = [
                    'code' => 'set_time',
                    'config' => $modifier->get_config()
                ];
            }

            return [
                'datemodifiers' => $modifiers,
                // временная зона того пользователя, который настраивал модификаторы
                // именно он, глядя на исходную дату в своем часовом поясе,
                // предполагал, что после применения модификаторов получится та или иная конечная дата
                'timezone' => \core_date::get_user_timezone()
            ];
        }

        // мы здесь можем оказаться только если происходило редактирование инстанса модуля
        // и при этом пользователь не нажал поставил галку подтверждения изменений

        return null;
    }

    public static function get_suitable_formats() {
        // По умолчанию выводим все форматы
        $formats = \mod_event3kl\formats::get_all_formats();

        return array_keys($formats->get_select_options());
    }
}