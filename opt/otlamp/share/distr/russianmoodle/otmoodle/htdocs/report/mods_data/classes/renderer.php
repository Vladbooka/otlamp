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
 * Log report renderer.
 *
 * @package    report_log
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die;

/**
 * Report log renderer's for printing reports.
 *
 * @package    report_log
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class report_mods_data_renderer extends plugin_renderer_base {

    /**
     * Render log report page.
     *
     * @param report_log_renderable $reportlog object of report_log.
     */
    protected function render_report_mods_data(report_mods_data_renderable $reportmods_data) {
        return $reportmods_data->render();
    }
}

