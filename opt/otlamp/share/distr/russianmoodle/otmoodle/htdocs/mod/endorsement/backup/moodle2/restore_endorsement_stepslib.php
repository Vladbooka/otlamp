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
 * @package    mod_endorsement
 * @subpackage backup-moodle2
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

class restore_endorsement_activity_structure_step extends restore_activity_structure_step
{
    /**
     * Определение структуры для восстановления
     * {@inheritDoc}
     * @see restore_structure_step::define_structure()
     */
    protected function define_structure()
    {
        // Пути к объектам восстановления
        $paths = [];
        
        // Флаг восстановления пользовательских данных
        $userinfo = $this->get_setting_value('userinfo');

        $paths[] = new restore_path_element('endorsement', '/activity/endorsement');
        if ( $userinfo )
        {
            $paths[] = new restore_path_element('feedback', '/activity/endorsement/feedback');
        }
        
        return $this->prepare_activity_structure($paths);
    }

    /**
     * Восстановление самого элемента курса
     *
     * @param array $data
     */
    protected function process_endorsement($data)
    {
        global $DB;

        $data = (object)$data;
        
        $this->oldcourseid = $data->course;
        $data->course = $this->get_courseid();

        $newitemid = $DB->insert_record('endorsement', $data);
        $this->apply_activity_instance($newitemid);
    }

    /**
     * Восстановление отзывов, созданных этим объектом
     *
     * @param array $data
     */
    protected function process_feedback($data)
    {
        global $DB;
        
        $data = (object)$data;
        $oldid = $data->id;
        
        $cmid = $this->task->get_moduleid();
        $modulecontext = context_module::instance($cmid);
        $data->contextid = $modulecontext->id;
        $data->userid = $this->get_mappingid('user', $data->userid);
        if ($data->commentarea == 'course' && $this->oldcourseid == $data->itemid)
        {
            // отзыв был о курсе, из которого восстанавливается элемент курса
            // будем считать, что отзыв - о текущем курсе
            $data->itemid = $this->get_courseid();
        }
        
        $newitemid = $DB->insert_record('crw_feedback', $data);
        $this->set_mapping('crw_feedback', $oldid, $newitemid);
    }

}
