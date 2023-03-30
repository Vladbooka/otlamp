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
 * Класс-помощник для заполнения хранилищ данными
 *
 * @package    local_learninghistory
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_learninghistory;

defined('MOODLE_INTERNAL') || die();

use stdClass;
use Exception;

class fill {
    /**
     * Процесс заполнение данными таблицы local_learninghistory_cm после обновления
     * Добавляются данные: rawgrade, rawgrademin, rawgrademax, rawscaleid, scalesnapshot
     */
    public function fill_rawgrade_data() {
        global $DB;
        $sql = 'SELECT llh_cm.*, gg.rawgrade, gg.rawgrademax, gg.rawgrademin, gg.rawscaleid, s.scale, sh.scale AS scalehistory 
                  FROM {local_learninghistory_cm} llh_cm
             LEFT JOIN 
                       (SELECT cm.*, m.name AS modname FROM {course_modules} cm
                     LEFT JOIN {modules} m
                            ON cm.module=m.id) AS cm
                    ON llh_cm.cmid=cm.id
             LEFT JOIN {grade_items} gi
                    ON cm.instance=gi.iteminstance AND cm.modname=gi.itemmodule
             LEFT JOIN {grade_grades} gg
                    ON gg.itemid=gi.id AND llh_cm.userid=gg.userid
             LEFT JOIN {scale} s
                    ON s.id=gg.rawscaleid
             LEFT JOIN {scale_history} sh
                    ON sh.oldid=gg.rawscaleid
                 WHERE gi.itemtype != \'course\' AND llh_cm.status = \'active\'';
        $rs = $DB->get_recordset_sql($sql);
        if ($rs->valid()) {
            foreach ($rs as $record) {
                $llhcm = new stdClass();
                $llhcm->id = $record->id;
                $llhcm->rawgrade = $record->rawgrade;
                $llhcm->rawgrademax = $record->rawgrademax ?? 100.00000;
                $llhcm->rawgrademin = $record->rawgrademin ?? 0.00000;
                $llhcm->rawscaleid = $record->rawscaleid;
                if (!is_null($record->scale)) {
                    $llhcm->scalesnapshot = $record->scale;
                } else {
                    $llhcm->scalesnapshot = $record->scalehistory;
                }
                try {
                    if ($DB->update_record('local_learninghistory_cm', $llhcm)) {
                        mtrace('successful local_learninghistory_cm record update with id=' . $llhcm->id);
                    }
                } catch (Exception $e) {
                    mtrace($e->getMessage() . PHP_EOL . $e->getTraceAsString() . PHP_EOL);
                    continue;
                }
            }
        }
        $rs->close();
    }
    
    /**
     * Процесс заполнение данными таблицы local_learninghistory после обновления
     * Добавляются данные: coursefullname, courseshortname
     */
    public function fill_course_data() {
        global $DB;
        $sql = 'SELECT llh.*, c.fullname, c.shortname FROM {local_learninghistory} llh
             LEFT JOIN {course} c
                    ON c.id=llh.courseid
                 WHERE c.id IS NOT NULL';
        $rs = $DB->get_recordset_sql($sql);
        if ($rs->valid()) {
            foreach ($rs as $record) {
                $llh = new stdClass();
                $llh->id = $record->id;
                $llh->coursefullname = $record->fullname;
                $llh->courseshortname = $record->shortname;
                try {
                    if ($DB->update_record('local_learninghistory', $llh)) {
                        mtrace('successful local_learninghistory record update with id=' . $llh->id);
                    }
                } catch (Exception $e) {
                    mtrace($e->getMessage() . PHP_EOL . $e->getTraceAsString() . PHP_EOL);
                    continue;
                }
            }
        }
        $rs->close();
    }
}