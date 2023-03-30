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

namespace mod_event3kl\provider;

defined('MOODLE_INTERNAL') || die();

use mod_event3kl\provider\base\provider_interface;
use mod_event3kl\provider\base\abstract_provider;
use Exception;
use mod_event3kl\event3kl;
use mod_event3kl\manage_providers_form;
use mod_event3kl\session;
use moodle_url;
use core\notification;

/**
 * Абстрактный класс провайдера
 *
 * @package   mod_event3kl
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class facetoface extends abstract_provider implements provider_interface {
    /**
     * общий метод объявления элементов формы для реализации настроек плагина по данному провайдеру
     */
    public function settings_definition(& $mform, & $form) {

    }
    /**
     * общий метод валидации формы настроек плагина в части элементов формы провайдера
     */
    public function settings_validation($data, $files) {

    }
    /**
     * общий метод обработки отправленной формы настроек плагина в части элементов формы провайдера
     */
    public function settings_processing(& $mform, & $form) {

    }
    /**
     * общий метод объявления элементов формы для реализации настроек инстанса по данному провайдеру
     * {@inheritDoc}
     * @see \mod_event3kl\provider\base\provider_interface::mod_form_definition()
     */
    public function mod_form_definition(\MoodleQuickForm &$mform, \mod_event3kl_mod_form &$form) {

    }
    /**
     * общий метод валидации формы настроек инстанса в части элементов формы провайдера
     */
    public function mod_form_validation($data, $files) {
        return [];
    }
    /**
     * {@inheritDoc}
     * @see \mod_event3kl\provider\base\provider_interface::mod_form_processing()
     */
    public function mod_form_processing(array $formdata)
    {
        return [];
    }
    /**
     * Отображаемое название типа провайдера ("очное занятие")
     */
    public function get_display_name() {
        return get_string($this->get_code() . '_provider_display_name', 'mod_event3kl');
    }
    /**
     * Получить код типа провайдера
     */
    public function get_code() {
        return (new \ReflectionClass($this))->getShortName();
    }

    public function start_session(session $session, event3kl $event3kl)
    {
        return null;
    }

    public function get_participate_link(session $session, event3kl $event3kl, $userid)
    {
        return null;
    }

    public function finish_session(session $session, event3kl $event3kl)
    {
        // для финиширования сессии никаких дополнительных действий не требуется
        // считаем, что успешно финишировали
        return true;
    }

    public function supports_records_download() {
        return false;
    }

    public function get_records(session $session, event3kl $event3kl) : array {
        return [];
    }

    public function get_record_content(array $recorddata) {
        return null;
    }






}