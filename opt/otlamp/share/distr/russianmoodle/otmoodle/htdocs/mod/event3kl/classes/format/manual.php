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

namespace mod_event3kl\format;

defined('MOODLE_INTERNAL') || die();

use mod_event3kl\format\base\format_interface;
use mod_event3kl\format\base\abstract_format;
use Exception;
use moodle_url;
use core\notification;
use mod_event3kl\event3kl;
use mod_event3kl\session;

/**
 * Класс формата занятия "подгруппы" (преподаватель планирует подгруппы вручную)
 *
 * @package   mod_event3kl
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class manual extends abstract_format implements format_interface {

    /**
     * {@inheritDoc}
     * @see \mod_event3kl\format\base\format_interface::mod_form_definition()
     */
    public function mod_form_definition(\MoodleQuickForm &$mform, \mod_event3kl_mod_form &$form) {

    }
    /**
     * общий метод валидации формы настроек инстанса в части элементов формы формата
     */
    public function mod_form_validation() {
        return [];
    }
    /**
     * {@inheritDoc}
     * @see \mod_event3kl\format\base\format_interface::mod_form_processing()
     */
    public function mod_form_processing(array $formdata) {
        return [];
    }

    /**
     * {@inheritDoc}
     * @see \mod_event3kl\format\base\abstract_format::actualize_sessions()
     */
    public function actualize_sessions(event3kl $event3kl) {

        // описание логики для формата:
        // препод сам создает сессии (подгруппы) внутри группы, доступной ему согласно правам доступа
        // запись участников в сессии производится либо вручную преподом, либо
        // если включен дейтмод vacantseat (Время по заявке), то путём выбора учащимся одной из доступных сессий

        // никаких автоматических подписок участников в сессии тут не будет, ибо ручной режим
        // должны только действовать незыблемые правила, которые и так будут выполнены
        // так как после актуализации через формат, выполняется код актуализации в классе event3kl

    }
}