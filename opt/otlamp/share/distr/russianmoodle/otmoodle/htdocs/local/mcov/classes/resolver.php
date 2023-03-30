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
 * Настраиваемые поля. Класс распределения событий на обработку.
 *
 * @package    local_mcov
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_mcov;

class resolver {
    /**
     * Запускает обработчики сущностей, подписанных на событие
     * @param \core\event\base $event
     */
    public static function resolve_event(\core\event\base $event) {
        if ($subscribers = helper::get_event_subscribers($event)) {
            foreach ($subscribers as $entitycode) {
                $entity = helper::get_entity($entitycode);
                $entity->handle_event($event);
            }
        }
    }
}