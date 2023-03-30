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
 * Область поиска по курсам, доступным в витрине
 *
 * @package    local_crw
 * @subpackage crw_system_search
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace crw_system_search\search;

use \core_search\manager as core_search_manager;
defined('MOODLE_INTERNAL') || die();

class crw_course extends \core_search\base {
    
    /**
     * The context levels the search implementation is working on.
     *
     * @var array
     */
    protected static $levels = [CONTEXT_SYSTEM];
    
    /**
     * Returns recordset containing required data for indexing courses.
     *
     * @param int $modifiedfrom timestamp
     * @return \moodle_recordset
     */
    public function get_recordset_by_timestamp($modifiedfrom = 0)
    
    {
        global $DB;
        $sql = 'SELECT c.id, c.shortname, c.fullname, c.summary, c.summaryformat, c.timecreated,
                       CASE WHEN cptm.svalue > c.timemodified THEN cptm.svalue ELSE c.timemodified END as \'timemodified\',
                       cpad.value as \'additional_description\',
                       cpadv.svalue as \'additional_description_view\'
                  FROM {course} c
             LEFT JOIN {crw_course_properties} cptm ON cptm.courseid=c.id AND cptm.name=\'timemodified\'
             LEFT JOIN {crw_course_properties} cpad ON cpad.courseid=c.id AND cpad.name=\'additional_description\'
             LEFT JOIN {crw_course_properties} cpadv ON cpadv.courseid=c.id AND cpadv.name=\'additional_description_view\'
                 WHERE c.timemodified >= :coursetime OR cptm.svalue >= :crwcoursetime
              ORDER BY CASE WHEN cptm.svalue > c.timemodified THEN cptm.svalue ELSE c.timemodified END ASC';
        return $DB->get_recordset_sql($sql, [
            'coursetime' => $modifiedfrom,
            'crwcoursetime' => $modifiedfrom
        ]);
    }
    
    /**
     * Returns the document associated with this course.
     *
     * @param stdClass $record
     * @param array    $options
     * @return \core_search\document
     */
    public function get_document($record, $options = array())
    {
        // Prepare associative array with data from DB.
        $doc = \core_search\document_factory::instance($record->id, $this->componentname, $this->areaname);
        $doc->set('owneruserid', core_search_manager::NO_OWNER_ID);
        $doc->set('modified', $record->timemodified);
        
        // устанавливаем контекст системы, чтобы поисковая система сама не обрезала доступ, зная, что к курсу доступа нет
        $systemcontext = \context_system::instance();
        $doc->set('contextid', $systemcontext->id);
        $doc->set('courseid', $record->id);
        
        
        $this->set_index_data($doc, $record);
        
        // Контакты курса
        $doc->set_extra('coursecontacts', '');
        // Теги назначенные курсу из коллекции 1
        $doc->set_extra('crw_course_tags_collection_custom1', '');
        // Теги назначенные курсу из коллекции 2
        $doc->set_extra('crw_course_tags_collection_custom2', '');
        // Обычные теги курса
        $doc->set_extra('course_tags', '');
        
        // Check if this document should be considered new.
        if (isset($options['lastindexedtime']) && $options['lastindexedtime'] < $record->timecreated) {
            // If the document was created after the last index time, it must be new.
            $doc->set_is_new(true);
        }
        
        return $doc;
    }
    
    /**
     * Устанавливает для документа данные, которые будут индексироваться для последующего поиска
     *
     * @param object $doc - документ
     * @param object $record - запись с исходными данными
     */
    protected function set_index_data(&$doc, $record)
    {
        $doc->set('title', content_to_text($record->fullname, false));
        $doc->set('content', content_to_text($record->summary, $record->summaryformat));
        // если краткое описание доступно на странице описания курса в витрине
        if (!empty($record->additional_description_view) && in_array((int)$record->additional_description_view,[1,2]) )
        {
            $doc->set('description1', content_to_text($record->additional_description, FORMAT_MOODLE));
        }
        $a = new \stdClass();
        $a->fullname = content_to_text($record->fullname, false);
        $a->shortname = $record->shortname;
        $doc->set('description2', get_string('search_course_names', 'crw_system_search', $a));
        
    }
    
    /**
     * Whether the user can access the document or not.
     *
     * @param int $id The course instance id.
     * @return int
     */
    public function check_access($id)
    {
        global $DB;
        
        $course = $DB->get_record('course', ['id' => $id]);
        
        if (!$course) {
            return core_search_manager::ACCESS_DELETED;
        }
        
        $coursecontext = \context_course::instance($id);
        
        // проврека, не скрыт ли курс целиком
        if (!empty($course->visible) && $course->visible==0 && !has_capability('moodle/course:viewhiddencourses', $coursecontext))
        {
            return core_search_manager::ACCESS_DENIED;
        }
        
        // проверка, не скрыт ли курс из витрины
        $cphc = $DB->get_record('crw_course_properties', [
            'courseid' => $id,
            'name' => 'hide_course'
        ]);
        if (!empty($cphc->svalue) && !has_capability('local/crw:view_hidden_courses', $coursecontext))
        {
            return core_search_manager::ACCESS_DENIED;
        }
        
        return core_search_manager::ACCESS_GRANTED;
    }
    
    /**
     * Link to the course.
     *
     * @param \core_search\document $doc
     * @return \moodle_url
     */
    public function get_doc_url(\core_search\document $doc)
    {
        return $this->get_context_url($doc);
    }
    
    /**
     * Link to the course.
     *
     * @param \core_search\document $doc
     * @return \moodle_url
     */
    public function get_context_url(\core_search\document $doc)
    {
        return new \moodle_url('/local/crw/course.php', ['id' => $doc->get('itemid')]);
    }
}
