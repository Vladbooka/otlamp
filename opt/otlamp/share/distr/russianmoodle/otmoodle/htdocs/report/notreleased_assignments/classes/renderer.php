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
 * Report renderer.
 *
 * @package    report
 * @subpackage notreleased_assignments
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die;

/**
 * Отчет по неопубликованным заданиям. Рендерер отчета.
 *
 * @package    report
 * @subpackage notreleased_assignments
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class report_notreleased_assignments_renderer extends plugin_renderer_base {

    /**
     * Render report page.
     *
     * @param report_notreleased_assignments_renderable $reportnotreleased_assignments object of report_notreleased_assignments.
     */
    protected function render_report_notreleased_assignments(report_notreleased_assignments_renderable $reportnotreleased_assignments) {
        return $reportnotreleased_assignments->render();
    }
}

