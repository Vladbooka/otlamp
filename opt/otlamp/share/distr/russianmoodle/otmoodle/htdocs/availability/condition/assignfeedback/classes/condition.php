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
 * Condition main class.
 *
 * @package    availability_assignfeedback
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace availability_assignfeedback;

defined('MOODLE_INTERNAL') || die();

/**
 * Condition main class.
 *
 * @package    availability_assignfeedback
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class condition extends \core_availability\condition {
    /** @var string assign cmid  */
    protected $assigncmid;
    /** @var string assign feedback code */
    protected $assignfeedbackcode;
 
    /**
     * Constructor.
     *
     * @param \stdClass $object Data structure from JSON decode
     * @throws \coding_exception If invalid data structure.
     */
    public function __construct($object)
    {
        if( ! empty($object->assign) )
        {
            $this->assigncmid = (string)$object->assign;
        }
        if( ! empty($object->assignfeedback) )
        {
            $this->assignfeedbackcode = (string)$object->assignfeedback;
        }
    }
 
    /**
     * {@inheritDoc}
     * @see \core_availability\tree_node::save()
     */
    public function save()
    {
        $saveobj = new \stdClass();
        $saveobj->type = 'assignfeedback';
        $saveobj->assign = (string)$this->assigncmid;
        $saveobj->assignfeedback = (string)$this->assignfeedbackcode;
        return $saveobj;
    }
    /**
     * JSON код ограничения доступа
     * 
     * @return stdClass Object representing condition
     */
    public static function get_json($assigncmid, $assignfeedbackcode) 
    {
        return (object)['type' => 'assignfeedback', 'assign' => (string)$assigncmid, 'assignfeedback' => (string)$assignfeedbackcode];
    }
    
    /**
     * {@inheritDoc}
     * @see \core_availability\condition::is_available()
     */
    public function is_available($not, \core_availability\info $info, $grabthelot, $userid)
    {
        // Идентификатор пользователя
        $userid = empty($userid) ? $USER->id : $userid;
        // Курс с ограничением доступа
        $course = $info->get_course();
        // Доступность по предоставлению отзыва на задание
        $available = $this->feedback_given($course, $userid);
        // Инвертируем, если пользовтель НЕ должен соответствовать условиям
        return $not xor $available;
    }
    
    /**
     * Восстановление из бэкапа
     * {@inheritDoc}
     * @see \core_availability\tree_node::update_after_restore()
     */
    public function update_after_restore($restoreid, $courseid, \base_logger $logger, $name) 
    {
        global $DB;
        $rec = \restore_dbops::get_backup_ids_record($restoreid, 'course_module', $this->assigncmid);
        // Запись не найдена
        if ( empty($rec->newitemid) )
        {
            // If we are on the same course (e.g. duplicate) then we can just
            // use the existing one.
            if ( $DB->record_exists('course_modules', ['id' => $this->assigncmid, 'course' => $courseid]) )
            {
                return false;
            }
            
            // Otherwise it's a warning.
            $this->assigncmid = '0';
            $logger->process('Restored item (' . $name . ') has availability condition on module that was not restored', \backup::LOG_WARNING);
        } else 
        {
            $this->assigncmid = (string)$rec->newitemid;
        }
        
        return true;
    }
    
    public function update_dependency_id($table, $oldid, $newid) 
    {
        if ($table === 'course_modules' && (int)$this->assigncmid === (int)$oldid) 
        {
            $this->assigncmid = (string)$newid;
            return true;
        } else {
            return false;
        }
    }
    
    /**
     * Возвращает состояние отзыва на задание пользователя (был ли дан отзыв настроенного типа)
     * 
     * @param stdClass $course - объект курса Moodle
     * @param int $userid - идентификатор пользователя
     * @return boolean
     */
    private function feedback_given($course, $userid)
    {
        global $CFG;
        
        if ( (int)$this->assigncmid == 0 || (string)$this->assignfeedbackcode == "0" )
        {
            return false;
        }
        
        if( file_exists($CFG->dirroot.'/mod/assign/locallib.php'))
        {
            require_once($CFG->dirroot.'/mod/assign/locallib.php');
        
            $modinfo = get_fast_modinfo($course);
            if( ! empty($modinfo->cms[$this->assigncmid]) )
            {
                $assigncm = $modinfo->cms[$this->assigncmid];

                $assigncmcontext = \context_module::instance($assigncm->id);
                $assign = new \assign($assigncmcontext, $assigncm, $course);

                foreach( $assign->get_feedback_plugins() as $plugin )
                {
                    if( $plugin->is_visible() && $plugin->is_enabled() && 
                        get_class($plugin) == $this->assignfeedbackcode )
                    {
                        $grade = $assign->get_user_grade($userid, false);
                        if ($grade) {
                            return ! $plugin->is_empty($grade);
                        }
                    }
                }
            }
        }
        return false;
    }
 
    /**
     * {@inheritDoc}
     * @see \core_availability\condition::get_description()
     */
    public function get_description($full, $not, \core_availability\info $info) 
    {
        global $USER, $CFG;
        
        $assignname = get_string('unknown_assign', 'availability_assignfeedback');
        $feedbacktype = get_string('unknown_feedbacktype', 'availability_assignfeedback');
        
        if( (int)$this->assigncmid !== 0 && (string)$this->assignfeedbackcode !== "0" )
        {
            if( file_exists($CFG->dirroot.'/mod/assign/locallib.php'))
            {
                require_once($CFG->dirroot.'/mod/assign/locallib.php');
    
                // Курс с ограничением доступа
                $course = $info->get_course();
                $coursecontext = \context_course::instance($course->id);
                $modinfo = get_fast_modinfo($course);
                
                if( ! empty($modinfo->cms[$this->assigncmid]) )
                {
                    $assigncm = $modinfo->cms[$this->assigncmid];
            
                    $assigncmcontext = \context_module::instance($assigncm->id);
                    $assign = new \assign($assigncmcontext, $assigncm, $course);
                    
                    $assignname = format_string($assigncm->name, true, [
                        'context' => $coursecontext
                    ]);
            
                    $feedbackplugintype = substr($this->assignfeedbackcode, strlen('assign_feedback_'));
                    $plugin = $assign->get_feedback_plugin_by_type($feedbackplugintype);
                    if( $plugin !== null )
                    {
                        $feedbacktype = $plugin->get_name();
                    }
                }
            }
        }
        
        $a = new \stdClass();
        $a->assignname = $assignname;
        $a->feedbacktype = $feedbacktype;
        
        if( $not )
        {
            $description = get_string('no_feedback_required', 'availability_assignfeedback', $a);
        } else 
        {
            $description = get_string('requires_feedback', 'availability_assignfeedback', $a);
        }
        
        return $description;
    }
 
    /**
     * {@inheritDoc}
     * @see \core_availability\condition::get_debug_string()
     */
    protected function get_debug_string()
    {
        
    }
}
?>