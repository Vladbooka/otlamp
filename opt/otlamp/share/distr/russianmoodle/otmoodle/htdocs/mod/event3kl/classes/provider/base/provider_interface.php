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

namespace mod_event3kl\provider\base;

defined('MOODLE_INTERNAL') || die();

/**
 * Интерфейс провайдера
 *
 * @package   mod_event3kl
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
interface provider_interface {
    /**
     * общий метод объявления элементов формы для реализации настроек плагина по данному провайдеру
     */
    public function settings_definition(& $mform, & $form);
    /**
     * общий метод валидации формы настроек плагина в части элементов формы провайдера
     */
    public function settings_validation($data, $files);
    /**
     * общий метод обработки отправленной формы настроек плагина в части элементов формы провайдера
     */
    public function settings_processing(& $mform, & $form);
    /**
     * общий метод объявления элементов формы для реализации настроек инстанса по данному провайдеру
     * @param \MoodleQuickForm $mform
     * @param \mod_event3kl_mod_form $form
     */
    public function mod_form_definition(\MoodleQuickForm &$mform, \mod_event3kl_mod_form &$form);
    /**
     * общий метод валидации формы настроек инстанса в части элементов формы провайдера
     */
    public function mod_form_validation($data, $files);
    /**
     * Метод обработки формы, возвращающий конфиг, настроенный для экземпляра провайдера
     * @param array $formdata
     * @return json-encodable конфиг
     */
    public function mod_form_processing(array $formdata);
    /**
     * название провайдера ("очное занятие", "внешний провайдер")
     */
    public function get_display_name();
}
