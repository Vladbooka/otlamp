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

namespace mod_event3kl\datemode;

defined('MOODLE_INTERNAL') || die();

use mod_event3kl\datemode\base\datemode_interface;
use mod_event3kl\datemode\base\abstract_datemode;
use local_mcov\hcfield_group_datestart;

/**
 * Класс способа указания даты - относительная дата (относительно даты старта группы)
 *
 * @package   mod_event3kl
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class relative_to_group extends abstract_datemode implements datemode_interface {


    /**
     * Получение точки отсчета для формирования даты относительно даты группы
     * @return string
     */
    protected function set_default_startingpoint() {

        // если указана группа - берем за точку отсчета дату её старта
        if (isset($this->groupid)) {
            $groupmcov = new \local_mcov\entity('group');
            $datestartrecord = $groupmcov->get_mcov($this->groupid, 'local_mcov_group_datestart', false);
            $datestart = $datestartrecord->value ?? null;
        }

        // групповой режим может быть отключен, как следствие группа не известна
        // кроме того, в самой группе может быть не задана дата старта
        // в таком случае согласно проекту, дату требуется указывать из курса
        if (empty($datestart)) {
            if (!isset($this->event3kl)) {
                throw new \Exception('No enough data to detect date starting point');
            }
            $course = get_course($this->event3kl->get('course'));
            $datestart = $course->startdate;
        }

        $this->startingpoint = '@' . ($datestart ?? time());
    }

    protected function get_maintained_modifiers($localized = false) {
        if ($localized) {
            return ['add' => get_string('add_relative_to_group_datemode', 'mod_event3kl'),
                'set_day_of_week' => get_string('set_day_of_week_relative_to_group_datemode', 'mod_event3kl')];
        }
        return ['add', 'set_day_of_week'];
    }

    protected function supports_repeat() {
        return true;
    }


}